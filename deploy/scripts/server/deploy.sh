#!/usr/bin/env bash

# =============================================================================
# Server-Side Deployment Script
# =============================================================================
# This script runs ON THE SERVER to perform the actual deployment.
# It is called by the local deploy.sh script via SSH.
#
# Environment variables are passed from the local script.
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}→ $1${NC}"; }

# Validate required environment variables
REQUIRED_VARS=(
    "IMAGE_NAME"
    "IMAGE_TAG"
    "TARBALL_PATH"
    "DOMAIN"
    "SANITIZED_DOMAIN"
    "DEPLOY_PATH"
    "BLUE_PORT"
    "GREEN_PORT"
)

for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        print_error "Required environment variable $var is not set"
        exit 1
    fi
done

# Container names
BLUE_CONTAINER="${SANITIZED_DOMAIN}-blue"
GREEN_CONTAINER="${SANITIZED_DOMAIN}-green"
CURRENT_SLOT_FILE="${DEPLOY_PATH}/current-slot.txt"
SCRIPT_DIR="${DEPLOY_PATH}/scripts"

# =============================================================================
# Functions
# =============================================================================

get_current_slot() {
    if [ -f "$CURRENT_SLOT_FILE" ]; then
        cat "$CURRENT_SLOT_FILE"
    else
        echo "none"
    fi
}

get_container_status() {
    local container=$1
    if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
        echo "running"
    elif docker ps -a --format '{{.Names}}' | grep -q "^${container}$"; then
        echo "stopped"
    else
        echo "none"
    fi
}

# Merge incoming env vars into the persistent .env file.
# - New variables are added
# - Changed values are updated
# - Variables present in existing but missing from incoming are kept (never deleted)
# - Only variable names are logged, never values (security)
# - APP_KEY rotation: when APP_KEY changes, the old key is automatically appended
#   to APP_PREVIOUS_KEYS so Laravel can still decrypt data encrypted with it.
#   See: https://laravel.com/docs/encryption#gracefully-rotating-encryption-keys
merge_env_file() {
    local incoming_file="$1"
    local persistent_file="$2"

    if [ ! -f "$incoming_file" ]; then
        print_error "Incoming env file not found: $incoming_file"
        return 1
    fi

    local added=0
    local updated=0
    local kept=0
    local app_key_rotated=false

    # If no persistent file yet, just use the incoming file as-is
    if [ ! -f "$persistent_file" ]; then
        cp "$incoming_file" "$persistent_file"
        added=$(wc -l < "$incoming_file" | tr -d ' ')
        print_success "Created env file with $added variables"
        return 0
    fi

    # Build associative arrays for existing and incoming vars
    declare -A existing_vars
    declare -A incoming_vars

    # Read existing persistent file
    while IFS= read -r line; do
        [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue
        local key="${line%%=*}"
        local value="${line#*=}"
        [[ -n "$key" ]] && existing_vars["$key"]="$value"
    done < "$persistent_file"

    # Read incoming file
    while IFS= read -r line; do
        [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue
        local key="${line%%=*}"
        local value="${line#*=}"
        [[ -n "$key" ]] && incoming_vars["$key"]="$value"
    done < "$incoming_file"

    # Handle APP_KEY rotation before the main merge loop.
    # If APP_KEY changed, append the old key to APP_PREVIOUS_KEYS so Laravel
    # can still decrypt data encrypted with previous keys.
    if [ -n "${existing_vars[APP_KEY]+x}" ] && [ -n "${incoming_vars[APP_KEY]+x}" ]; then
        if [ "${existing_vars[APP_KEY]}" != "${incoming_vars[APP_KEY]}" ]; then
            local old_key="${existing_vars[APP_KEY]}"
            local previous_keys="${existing_vars[APP_PREVIOUS_KEYS]:-}"

            # Append old key to APP_PREVIOUS_KEYS (comma-separated), avoiding duplicates
            if [ -z "$previous_keys" ]; then
                previous_keys="$old_key"
            elif [[ ",$previous_keys," != *",$old_key,"* ]]; then
                previous_keys="${previous_keys},${old_key}"
            fi

            # Store the updated APP_PREVIOUS_KEYS in incoming so it gets written
            incoming_vars[APP_PREVIOUS_KEYS]="$previous_keys"
            app_key_rotated=true

            print_warning "  APP_KEY rotated - old key preserved in APP_PREVIOUS_KEYS"
        fi
    fi

    # Start building the merged output
    local merged_file="${persistent_file}.tmp"
    > "$merged_file"

    # Process incoming vars (add new, update changed)
    for key in "${!incoming_vars[@]}"; do
        if [ -z "${existing_vars[$key]+x}" ]; then
            # New variable - add it
            echo "${key}=${incoming_vars[$key]}" >> "$merged_file"
            # Don't log APP_PREVIOUS_KEYS as "Added" if it was created by key rotation
            if [ "$key" = "APP_PREVIOUS_KEYS" ] && [ "$app_key_rotated" = true ]; then
                : # Already logged above
            else
                print_info "  Added: $key"
            fi
            added=$((added + 1))
        elif [ "${existing_vars[$key]}" != "${incoming_vars[$key]}" ]; then
            # Changed value - update it
            echo "${key}=${incoming_vars[$key]}" >> "$merged_file"
            # Don't log APP_PREVIOUS_KEYS as "Updated" if it was modified by key rotation
            if [ "$key" = "APP_PREVIOUS_KEYS" ] && [ "$app_key_rotated" = true ]; then
                : # Already logged above
            else
                print_info "  Updated: $key"
            fi
            updated=$((updated + 1))
        else
            # Same value - keep as-is
            echo "${key}=${existing_vars[$key]}" >> "$merged_file"
        fi
    done

    # Keep vars that exist in persistent but not in incoming (never delete)
    for key in "${!existing_vars[@]}"; do
        if [ -z "${incoming_vars[$key]+x}" ]; then
            echo "${key}=${existing_vars[$key]}" >> "$merged_file"
            kept=$((kept + 1))
        fi
    done

    # Replace persistent file with merged result
    mv "$merged_file" "$persistent_file"

    if [ $added -gt 0 ] || [ $updated -gt 0 ] || [ $kept -gt 0 ]; then
        print_success "Env file merged: $added added, $updated updated, $kept kept from previous"
    else
        print_success "Env file unchanged"
    fi
}

# =============================================================================
# Step 1: Load Docker Image
# =============================================================================

echo ""
print_info "Loading Docker image from tarball..."

if [ ! -f "$TARBALL_PATH" ]; then
    print_error "Tarball not found: $TARBALL_PATH"
    exit 1
fi

gunzip -c "$TARBALL_PATH" | docker load || {
    print_error "Failed to load Docker image"
    exit 1
}

print_success "Image loaded: ${IMAGE_NAME}:${IMAGE_TAG}"

# Clean up tarball
rm -f "$TARBALL_PATH"

# =============================================================================
# Step 2: Determine Target Slot
# =============================================================================

CURRENT_SLOT=$(get_current_slot)

if [ "$CURRENT_SLOT" = "blue" ]; then
    TARGET_SLOT="green"
    TARGET_CONTAINER="$GREEN_CONTAINER"
    TARGET_PORT="$GREEN_PORT"
    OLD_CONTAINER="$BLUE_CONTAINER"
elif [ "$CURRENT_SLOT" = "green" ]; then
    TARGET_SLOT="blue"
    TARGET_CONTAINER="$BLUE_CONTAINER"
    TARGET_PORT="$BLUE_PORT"
    OLD_CONTAINER="$GREEN_CONTAINER"
else
    # First deployment - use blue
    TARGET_SLOT="blue"
    TARGET_CONTAINER="$BLUE_CONTAINER"
    TARGET_PORT="$BLUE_PORT"
    OLD_CONTAINER=""
fi

print_info "Current slot: $CURRENT_SLOT"
print_info "Target slot:  $TARGET_SLOT (port $TARGET_PORT)"

# =============================================================================
# Step 3: Stop and Remove Target Container (if exists)
# =============================================================================

TARGET_STATUS=$(get_container_status "$TARGET_CONTAINER")
if [ "$TARGET_STATUS" != "none" ]; then
    print_info "Removing existing $TARGET_SLOT container..."
    docker rm -f "$TARGET_CONTAINER" 2>/dev/null || true
fi

# =============================================================================
# Step 4: Start New Container
# =============================================================================

print_info "Starting $TARGET_SLOT container..."

# Data paths
DATA_PATH="${DEPLOY_PATH}/data"
SQLITE_FILE="${DATA_PATH}/database.sqlite"
ENV_FILE="${DATA_PATH}/.env"
INCOMING_ENV_FILE="/tmp/${SANITIZED_DOMAIN}-env-incoming"

# Ensure data directories exist with correct permissions
# These directories are mounted into the container, so they must exist on the host
# with the correct structure before the container starts
mkdir -p "${DATA_PATH}/storage/app/public"
mkdir -p "${DATA_PATH}/storage/framework/sessions"
mkdir -p "${DATA_PATH}/storage/framework/views"
mkdir -p "${DATA_PATH}/storage/framework/cache"
mkdir -p "${DATA_PATH}/storage/logs"

# Create SQLite file if it doesn't exist (required for volume mount)
if [ ! -f "$SQLITE_FILE" ]; then
    touch "$SQLITE_FILE"
fi

# Set ownership to container user (configured via CONTAINER_USER, UID 1337, GID 1337)
chown 1337:1337 "$SQLITE_FILE"
chmod 664 "$SQLITE_FILE"
chown -R 1337:1337 "${DATA_PATH}/storage"
chmod -R 775 "${DATA_PATH}/storage"

# Merge incoming env vars into the persistent .env file
# New vars are added, changed vars are updated, missing vars are kept (never deleted)
print_info "Merging environment configuration..."
merge_env_file "$INCOMING_ENV_FILE" "$ENV_FILE"

# Set permissions on env file (same as database file)
chown 1337:1337 "$ENV_FILE"
chmod 664 "$ENV_FILE"

# Clean up incoming env file
rm -f "$INCOMING_ENV_FILE"

docker run -d \
    --name "$TARGET_CONTAINER" \
    --restart unless-stopped \
    -p "127.0.0.1:${TARGET_PORT}:80" \
    --add-host=host.docker.internal:host-gateway \
    -v "${SQLITE_FILE}:/var/www/html/database/database.sqlite:rw" \
    -v "${DATA_PATH}/storage:/var/www/html/storage:rw" \
    --env-file "$ENV_FILE" \
    -e "FRESH_DB=${FRESH_DB:-false}" \
    -e "SEED_DB=${SEED_DB:-false}" \
    "${IMAGE_NAME}:${IMAGE_TAG}" || {
    print_error "Failed to start container"
    exit 1
}

print_success "Container started: $TARGET_CONTAINER"

# =============================================================================
# Step 5: Health Check
# =============================================================================

print_info "Running health checks..."

HEALTH_CHECK_TIMEOUT="${HEALTH_CHECK_TIMEOUT:-60}"
HEALTH_CHECK_INTERVAL="${HEALTH_CHECK_INTERVAL:-2}"

if ! bash "$SCRIPT_DIR/health-check.sh" "$TARGET_PORT" "$HEALTH_CHECK_TIMEOUT" "$HEALTH_CHECK_INTERVAL"; then
    print_error "Health check failed"
    echo ""
    print_info "Container logs (last 100 lines):"
    echo "----------------------------------------"
    docker logs "$TARGET_CONTAINER" --tail 100 2>&1
    echo "----------------------------------------"
    echo ""
    print_info "Container status:"
    docker inspect "$TARGET_CONTAINER" --format '{{.State.Status}} - {{.State.Error}}' 2>&1 || true
    echo ""
    print_info "Cleaning up failed container..."
    docker rm -f "$TARGET_CONTAINER" 2>/dev/null || true
    exit 1
fi

print_success "Health check passed"

# =============================================================================
# Step 6: Update Nginx Upstream
# =============================================================================

print_info "Updating Nginx upstream configuration..."

cat > "/etc/nginx/conf.d/${SANITIZED_DOMAIN}-upstream.conf" << EOF
# Upstream configuration for ${DOMAIN}
# Managed by deployment scripts - do not edit manually
# Active slot: ${TARGET_SLOT}
# Updated: $(date -Iseconds)

upstream ${SANITIZED_DOMAIN}-backend {
    server 127.0.0.1:${TARGET_PORT};
}
EOF

# Test Nginx configuration
nginx -t || {
    print_error "Nginx configuration test failed"
    docker rm -f "$TARGET_CONTAINER" 2>/dev/null || true
    exit 1
}

# Reload Nginx (zero-downtime)
systemctl reload nginx

print_success "Nginx updated to $TARGET_SLOT slot"

# =============================================================================
# Step 6: Update Current Slot File
# =============================================================================

echo "$TARGET_SLOT" > "$CURRENT_SLOT_FILE"

# =============================================================================
# Step 7: Stop Old Container
# =============================================================================

if [ -n "$OLD_CONTAINER" ]; then
    OLD_STATUS=$(get_container_status "$OLD_CONTAINER")
    if [ "$OLD_STATUS" = "running" ]; then
        print_info "Stopping old container: $OLD_CONTAINER"
        docker stop "$OLD_CONTAINER" 2>/dev/null || true
        print_success "Old container stopped"
    fi
fi

# =============================================================================
# Step 8: Cleanup Old Images
# =============================================================================

KEEP_OLD_IMAGES="${KEEP_OLD_IMAGES:-3}"

print_info "Cleaning up old images (keeping last $KEEP_OLD_IMAGES)..."

# Get all image IDs for this image name, sorted by creation date
OLD_IMAGES=$(docker images "${IMAGE_NAME}" --format "{{.ID}} {{.CreatedAt}}" | \
    sort -k2 -r | \
    tail -n +$((KEEP_OLD_IMAGES + 1)) | \
    awk '{print $1}')

if [ -n "$OLD_IMAGES" ]; then
    for image_id in $OLD_IMAGES; do
        docker rmi "$image_id" 2>/dev/null || true
    done
    print_success "Old images cleaned up"
else
    print_success "No old images to clean up"
fi

# =============================================================================
# Summary
# =============================================================================

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Deployment Complete"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "  Domain:     $DOMAIN"
echo "  Active:     $TARGET_SLOT (port $TARGET_PORT)"
echo "  Container:  $TARGET_CONTAINER"
echo "  Image:      ${IMAGE_NAME}:${IMAGE_TAG}"
echo ""
