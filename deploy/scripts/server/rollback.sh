#!/usr/bin/env bash

# =============================================================================
# Rollback Script
# =============================================================================
# Rolls back to the previous deployment slot (blue <-> green).
#
# Usage: rollback.sh
# =============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}→ $1${NC}"; }

# Determine paths from script location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_PATH="$(dirname "$SCRIPT_DIR")"
SANITIZED_DOMAIN="$(basename "$DEPLOY_PATH")"

# Container names
BLUE_CONTAINER="${SANITIZED_DOMAIN}-blue"
GREEN_CONTAINER="${SANITIZED_DOMAIN}-green"
CURRENT_SLOT_FILE="${DEPLOY_PATH}/current-slot.txt"

# Default ports (can be overridden by environment)
BLUE_PORT="${BLUE_PORT:-8080}"
GREEN_PORT="${GREEN_PORT:-8081}"

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
# Main
# =============================================================================

echo ""
print_info "Starting rollback..."

CURRENT_SLOT=$(get_current_slot)

if [ "$CURRENT_SLOT" = "none" ]; then
    print_error "No current deployment found. Nothing to rollback."
    exit 1
fi

# Determine rollback target
if [ "$CURRENT_SLOT" = "blue" ]; then
    TARGET_SLOT="green"
    TARGET_CONTAINER="$GREEN_CONTAINER"
    TARGET_PORT="$GREEN_PORT"
    CURRENT_CONTAINER="$BLUE_CONTAINER"
else
    TARGET_SLOT="blue"
    TARGET_CONTAINER="$BLUE_CONTAINER"
    TARGET_PORT="$BLUE_PORT"
    CURRENT_CONTAINER="$GREEN_CONTAINER"
fi

print_info "Current slot: $CURRENT_SLOT"
print_info "Rollback to:  $TARGET_SLOT"

# Check if target container exists
TARGET_STATUS=$(get_container_status "$TARGET_CONTAINER")

if [ "$TARGET_STATUS" = "none" ]; then
    print_error "Cannot rollback: $TARGET_CONTAINER does not exist"
    echo ""
    echo "This can happen if:"
    echo "  - This is the first deployment"
    echo "  - The previous container was manually removed"
    exit 1
fi

# Start target container if stopped
if [ "$TARGET_STATUS" = "stopped" ]; then
    print_info "Starting $TARGET_CONTAINER..."
    docker start "$TARGET_CONTAINER" || {
        print_error "Failed to start $TARGET_CONTAINER"
        exit 1
    }
fi

# Health check
print_info "Running health check on $TARGET_SLOT..."
if ! bash "$SCRIPT_DIR/health-check.sh" "$TARGET_PORT" 30 2; then
    print_error "Health check failed for $TARGET_CONTAINER"
    echo ""
    echo "The previous deployment may have issues."
    echo "Check logs: docker logs $TARGET_CONTAINER"
    exit 1
fi

print_success "Health check passed"

# Update Nginx upstream
print_info "Updating Nginx upstream..."

# Get domain from existing config or derive from sanitized domain
DOMAIN="${SANITIZED_DOMAIN//-/.}"
NGINX_CONF="/etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf"
if [ -f "$NGINX_CONF" ]; then
    DOMAIN=$(grep "server_name" "$NGINX_CONF" | head -1 | awk '{print $2}' | tr -d ';')
fi

cat > "/etc/nginx/conf.d/${SANITIZED_DOMAIN}-upstream.conf" << EOF
# Upstream configuration for ${DOMAIN}
# Managed by deployment scripts - do not edit manually
# Active slot: ${TARGET_SLOT} (ROLLBACK)
# Updated: $(date -Iseconds)

upstream ${SANITIZED_DOMAIN}-backend {
    server 127.0.0.1:${TARGET_PORT};
}
EOF

# Test and reload Nginx
nginx -t || {
    print_error "Nginx configuration test failed"
    exit 1
}

systemctl reload nginx

# Update current slot file
echo "$TARGET_SLOT" > "$CURRENT_SLOT_FILE"

# Stop current container
CURRENT_STATUS=$(get_container_status "$CURRENT_CONTAINER")
if [ "$CURRENT_STATUS" = "running" ]; then
    print_info "Stopping $CURRENT_CONTAINER..."
    docker stop "$CURRENT_CONTAINER" 2>/dev/null || true
fi

print_success "Rollback completed!"
echo ""
echo "Active deployment:"
echo "  Slot:      $TARGET_SLOT"
echo "  Container: $TARGET_CONTAINER"
echo "  Port:      $TARGET_PORT"
echo ""
