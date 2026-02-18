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
    "NETWORK_NAME"
    "BLUE_PORT"
    "GREEN_PORT"
    "APP_KEY"
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

docker run -d \
    --name "$TARGET_CONTAINER" \
    --restart unless-stopped \
    --network "$NETWORK_NAME" \
    -p "127.0.0.1:${TARGET_PORT}:80" \
    --add-host=host.docker.internal:host-gateway \
    -v "${DATA_PATH}/database:/var/www/html/database:rw" \
    -v "${DATA_PATH}/storage:/var/www/html/storage:rw" \
    -e "APP_NAME=${APP_NAME}" \
    -e "APP_ENV=${APP_ENV:-production}" \
    -e "APP_KEY=${APP_KEY}" \
    -e "APP_DEBUG=${APP_DEBUG:-false}" \
    -e "APP_TIMEZONE=${APP_TIMEZONE:-UTC}" \
    -e "APP_URL=${APP_URL}" \
    -e "DB_CONNECTION=sqlite" \
    -e "LOG_CHANNEL=${LOG_CHANNEL:-stack}" \
    -e "LOG_LEVEL=${LOG_LEVEL:-error}" \
    -e "SESSION_DRIVER=${SESSION_DRIVER:-database}" \
    -e "SESSION_LIFETIME=${SESSION_LIFETIME:-120}" \
    -e "CACHE_STORE=${CACHE_STORE:-redis}" \
    -e "QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}" \
    -e "REDIS_CLIENT=phpredis" \
    -e "REDIS_HOST=${REDIS_HOST:-host.docker.internal}" \
    -e "REDIS_PORT=${REDIS_PORT:-6379}" \
    -e "REDIS_PASSWORD=${REDIS_PASSWORD:-null}" \
    -e "FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}" \
    -e "MAIL_MAILER=${MAIL_MAILER:-log}" \
    -e "MAIL_HOST=${MAIL_HOST:-}" \
    -e "MAIL_PORT=${MAIL_PORT:-587}" \
    -e "MAIL_USERNAME=${MAIL_USERNAME:-}" \
    -e "MAIL_PASSWORD=${MAIL_PASSWORD:-}" \
    -e "MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}" \
    -e "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-}" \
    -e "MAIL_FROM_NAME=${MAIL_FROM_NAME:-}" \
    -e "BROADCAST_CONNECTION=log" \
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
    print_info "Cleaning up failed container..."
    docker logs "$TARGET_CONTAINER" --tail 50
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
# Step 7: Update Current Slot File
# =============================================================================

echo "$TARGET_SLOT" > "$CURRENT_SLOT_FILE"

# =============================================================================
# Step 8: Stop Old Container
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
# Step 9: Cleanup Old Images
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
