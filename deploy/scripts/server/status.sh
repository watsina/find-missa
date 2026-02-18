#!/usr/bin/env bash

# =============================================================================
# Status Script
# =============================================================================
# Shows the current deployment status.
#
# Usage: status.sh
# =============================================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Determine paths from script location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_PATH="$(dirname "$SCRIPT_DIR")"
SANITIZED_DOMAIN="$(basename "$DEPLOY_PATH")"

# Container names
BLUE_CONTAINER="${SANITIZED_DOMAIN}-blue"
GREEN_CONTAINER="${SANITIZED_DOMAIN}-green"
CURRENT_SLOT_FILE="${DEPLOY_PATH}/current-slot.txt"

# Default ports
BLUE_PORT="${BLUE_PORT:-8080}"
GREEN_PORT="${GREEN_PORT:-8081}"

# =============================================================================
# Functions
# =============================================================================

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

get_container_image() {
    local container=$1
    docker inspect --format='{{.Config.Image}}' "$container" 2>/dev/null || echo "N/A"
}

get_container_uptime() {
    local container=$1
    local status=$(docker ps --format '{{.Status}}' --filter "name=^${container}$" 2>/dev/null)
    if [ -n "$status" ]; then
        echo "$status"
    else
        echo "Not running"
    fi
}

check_health() {
    local port=$1
    local code=$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:${port}/up" 2>/dev/null || echo "000")
    if [ "$code" = "200" ]; then
        echo -e "${GREEN}Healthy${NC}"
    else
        echo -e "${RED}Unhealthy (HTTP $code)${NC}"
    fi
}

# =============================================================================
# Main
# =============================================================================

# Get domain from nginx config
DOMAIN="${SANITIZED_DOMAIN//-/.}"
NGINX_CONF="/etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf"
if [ -f "$NGINX_CONF" ]; then
    DOMAIN=$(grep "server_name" "$NGINX_CONF" | head -1 | awk '{print $2}' | tr -d ';')
fi

# Get current slot
if [ -f "$CURRENT_SLOT_FILE" ]; then
    CURRENT_SLOT=$(cat "$CURRENT_SLOT_FILE")
else
    CURRENT_SLOT="none"
fi

# Get container statuses
BLUE_STATUS=$(get_container_status "$BLUE_CONTAINER")
GREEN_STATUS=$(get_container_status "$GREEN_CONTAINER")

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Deployment Status: $DOMAIN"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Current Active Slot: $CURRENT_SLOT"
echo ""

# Blue slot info
echo "┌─────────────────────────────────────────────────────────────┐"
if [ "$CURRENT_SLOT" = "blue" ]; then
    echo "│ BLUE SLOT (Port $BLUE_PORT) - ACTIVE                          │"
else
    echo "│ BLUE SLOT (Port $BLUE_PORT)                                    │"
fi
echo "├─────────────────────────────────────────────────────────────┤"
if [ "$BLUE_STATUS" = "none" ]; then
    echo "│ Container: Not created                                      │"
else
    printf "│ Container: %-47s │\n" "$BLUE_CONTAINER"
    printf "│ Status:    %-47s │\n" "$BLUE_STATUS"
    if [ "$BLUE_STATUS" = "running" ]; then
        printf "│ Uptime:    %-47s │\n" "$(get_container_uptime "$BLUE_CONTAINER")"
        printf "│ Health:    %-47s │\n" "$(check_health "$BLUE_PORT")"
        printf "│ Image:     %-47s │\n" "$(get_container_image "$BLUE_CONTAINER")"
    fi
fi
echo "└─────────────────────────────────────────────────────────────┘"
echo ""

# Green slot info
echo "┌─────────────────────────────────────────────────────────────┐"
if [ "$CURRENT_SLOT" = "green" ]; then
    echo "│ GREEN SLOT (Port $GREEN_PORT) - ACTIVE                         │"
else
    echo "│ GREEN SLOT (Port $GREEN_PORT)                                   │"
fi
echo "├─────────────────────────────────────────────────────────────┤"
if [ "$GREEN_STATUS" = "none" ]; then
    echo "│ Container: Not created                                      │"
else
    printf "│ Container: %-47s │\n" "$GREEN_CONTAINER"
    printf "│ Status:    %-47s │\n" "$GREEN_STATUS"
    if [ "$GREEN_STATUS" = "running" ]; then
        printf "│ Uptime:    %-47s │\n" "$(get_container_uptime "$GREEN_CONTAINER")"
        printf "│ Health:    %-47s │\n" "$(check_health "$GREEN_PORT")"
        printf "│ Image:     %-47s │\n" "$(get_container_image "$GREEN_CONTAINER")"
    fi
fi
echo "└─────────────────────────────────────────────────────────────┘"
echo ""

# SSL status
echo "SSL Status:"
if [ -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
    CERT_EXPIRY=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/${DOMAIN}/cert.pem" 2>/dev/null | cut -d= -f2)
    echo -e "  ${GREEN}✓${NC} SSL enabled"
    echo "  Certificate expires: $CERT_EXPIRY"
else
    echo -e "  ${YELLOW}○${NC} SSL not configured"
    echo "  Run: ./deploy/scripts/deploy.sh --setup-ssl"
fi
echo ""

# Docker images
echo "Docker Images:"
docker images "${SANITIZED_DOMAIN}-app" --format "  {{.Repository}}:{{.Tag}}\t{{.Size}}\t{{.CreatedSince}}" 2>/dev/null || echo "  No images found"
echo ""
