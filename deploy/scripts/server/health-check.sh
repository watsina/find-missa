#!/usr/bin/env bash

# =============================================================================
# Health Check Script
# =============================================================================
# Verifies that a container is healthy and responding on the specified port.
#
# The health endpoint defaults to /up (Laravel 11+ convention).
# If your application uses a different health endpoint, change the
# HEALTH_URL variable below (line 26).
#
# Usage: health-check.sh <port> [timeout] [interval]
# =============================================================================

set -e

PORT="${1:-8080}"
TIMEOUT="${2:-60}"
INTERVAL="${3:-2}"
INITIAL_DELAY="${4:-5}"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ELAPSED=0
HEALTH_URL="http://127.0.0.1:${PORT}/up"

echo -e "${YELLOW}→ Waiting for container to be healthy (timeout: ${TIMEOUT}s)...${NC}"

# Initial delay to let supervisor start the PHP server
if [ "$INITIAL_DELAY" -gt 0 ]; then
    echo "  Initial delay: ${INITIAL_DELAY}s (waiting for server to start)"
    sleep $INITIAL_DELAY
fi

while [ $ELAPSED -lt $TIMEOUT ]; do
    # Try to connect to the health endpoint
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" 2>/dev/null || echo "000")
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}✓ Health check passed (HTTP $HTTP_CODE) after ${ELAPSED}s${NC}"
        exit 0
    fi
    
    # Show progress
    echo "  Attempt at ${ELAPSED}s: HTTP $HTTP_CODE"
    
    sleep $INTERVAL
    ELAPSED=$((ELAPSED + INTERVAL))
done

echo "Health check failed after ${TIMEOUT}s (last HTTP code: $HTTP_CODE)"
exit 1
