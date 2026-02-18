#!/usr/bin/env bash

# =============================================================================
# Blue-Green Deployment Script
# =============================================================================
# Main deployment script that runs on your LOCAL machine.
# Builds Docker image, transfers to server, and triggers deployment.
#
# Usage:
#   ./deploy/scripts/deploy.sh              # Deploy using .env.deploy
#   ./deploy/scripts/deploy.sh --env staging    # Deploy using .env.deploy.staging
#   ./deploy/scripts/deploy.sh --env production # Deploy using .env.deploy.production
#   ./deploy/scripts/deploy.sh --force      # Skip confirmations
#   ./deploy/scripts/deploy.sh --status     # Check deployment status
#   ./deploy/scripts/deploy.sh --rollback   # Rollback to previous version
#   ./deploy/scripts/deploy.sh --setup-ssl  # Setup SSL with Certbot
#   ./deploy/scripts/deploy.sh --help       # Show help
# =============================================================================

set -e

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# =============================================================================
# Helper Functions
# =============================================================================

print_header() {
    echo -e "\n${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

show_help() {
    cat << EOF
Blue-Green Deployment Script

Usage: ./deploy/scripts/deploy.sh [OPTIONS]

Options:
    --env <name>      Use .env.deploy.<name> configuration file
                      Examples: --env staging, --env production
    --force           Skip all confirmation prompts
    --status          Check current deployment status on server
    --rollback        Rollback to previous deployment
    --setup-ssl       Setup SSL certificates with Certbot
    --help            Show this help message

Examples:
    ./deploy/scripts/deploy.sh                    # Deploy with .env.deploy
    ./deploy/scripts/deploy.sh --env production   # Deploy to production
    ./deploy/scripts/deploy.sh --env staging      # Deploy to staging
    ./deploy/scripts/deploy.sh --status           # Check status
    ./deploy/scripts/deploy.sh --rollback         # Rollback
    ./deploy/scripts/deploy.sh --setup-ssl        # Setup SSL

Configuration:
    Copy deploy/config/.env.example to .env.deploy (or .env.deploy.<env>)
    and configure your deployment settings.

First-time setup:
    1. Configure .env.deploy with your server details
    2. Run: ./deploy/scripts/server-init.sh
    3. Run: ./deploy/scripts/deploy.sh
    4. Run: ./deploy/scripts/deploy.sh --setup-ssl

EOF
}

sanitize_domain() {
    echo "$1" | sed 's/\./-/g'
}

# =============================================================================
# Parse Arguments
# =============================================================================

ENV_NAME=""
FORCE_MODE=false
ACTION="deploy"

while [[ $# -gt 0 ]]; do
    case $1 in
        --env)
            ENV_NAME="$2"
            shift 2
            ;;
        --force)
            FORCE_MODE=true
            shift
            ;;
        --status)
            ACTION="status"
            shift
            ;;
        --rollback)
            ACTION="rollback"
            shift
            ;;
        --setup-ssl)
            ACTION="setup-ssl"
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# =============================================================================
# Load Configuration
# =============================================================================

if [ -n "$ENV_NAME" ]; then
    ENV_FILE="$PROJECT_ROOT/.env.deploy.$ENV_NAME"
else
    ENV_FILE="$PROJECT_ROOT/.env.deploy"
fi

if [ ! -f "$ENV_FILE" ]; then
    print_error "Configuration file not found: $ENV_FILE"
    echo ""
    echo "Please create the configuration file:"
    echo "  cp deploy/config/.env.example $ENV_FILE"
    echo ""
    echo "Then edit it with your deployment settings."
    exit 1
fi

print_info "Loading configuration from: $ENV_FILE"

# Export all variables from env file
set -a
source "$ENV_FILE"
set +a

# Validate required variables
REQUIRED_VARS=(
    "SERVER_USER"
    "SERVER_HOST"
    "DOMAIN"
    "APP_KEY"
    "APP_URL"
)

for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        print_error "Required variable $var is not set in $ENV_FILE"
        exit 1
    fi
done

# Set defaults
SERVER_PORT="${SERVER_PORT:-22}"
SERVER_DEPLOY_PATH="${SERVER_DEPLOY_PATH:-/opt/deployments}"
BLUE_PORT="${BLUE_PORT:-8080}"
GREEN_PORT="${GREEN_PORT:-8081}"
HEALTH_CHECK_TIMEOUT="${HEALTH_CHECK_TIMEOUT:-60}"
HEALTH_CHECK_INTERVAL="${HEALTH_CHECK_INTERVAL:-2}"
KEEP_OLD_IMAGES="${KEEP_OLD_IMAGES:-3}"

# Derived variables
SANITIZED_DOMAIN=$(sanitize_domain "$DOMAIN")
IMAGE_NAME="${SANITIZED_DOMAIN}-app"
IMAGE_TAG=$(date +%Y%m%d-%H%M%S)
NETWORK_NAME="${SANITIZED_DOMAIN}-network"
DEPLOY_PATH="${SERVER_DEPLOY_PATH}/${SANITIZED_DOMAIN}"

# Build SSH options
SSH_OPTS="-p $SERVER_PORT"
SCP_OPTS="-P $SERVER_PORT"
if [ -n "$SSH_KEY_PATH" ]; then
    # Expand ~ to $HOME
    SSH_KEY_PATH="${SSH_KEY_PATH/#\~/$HOME}"
    if [ ! -f "$SSH_KEY_PATH" ]; then
        print_error "SSH key not found: $SSH_KEY_PATH"
        exit 1
    fi
    SSH_OPTS="$SSH_OPTS -i $SSH_KEY_PATH"
    SCP_OPTS="$SCP_OPTS -i $SSH_KEY_PATH"
fi

# SSH command
SSH_CMD="ssh $SSH_OPTS $SERVER_USER@$SERVER_HOST"
SCP_CMD="scp $SCP_OPTS"

# =============================================================================
# Status Action
# =============================================================================

if [ "$ACTION" = "status" ]; then
    print_header "Checking Deployment Status"
    print_info "Domain: $DOMAIN"
    print_info "Server: $SERVER_USER@$SERVER_HOST"
    echo ""

    $SSH_CMD "bash ${DEPLOY_PATH}/scripts/status.sh" || {
        print_error "Failed to get status. Is the server initialized?"
        exit 1
    }
    exit 0
fi

# =============================================================================
# Rollback Action
# =============================================================================

if [ "$ACTION" = "rollback" ]; then
    print_header "Rolling Back Deployment"
    print_info "Domain: $DOMAIN"
    print_info "Server: $SERVER_USER@$SERVER_HOST"

    if [ "$FORCE_MODE" = false ]; then
        echo ""
        read -p "Are you sure you want to rollback? (y/N) " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_warning "Rollback cancelled"
            exit 0
        fi
    fi

    echo ""
    $SSH_CMD "sudo bash ${DEPLOY_PATH}/scripts/rollback.sh" || {
        print_error "Rollback failed"
        exit 1
    }

    print_success "Rollback completed successfully"
    exit 0
fi

# =============================================================================
# Setup SSL Action
# =============================================================================

if [ "$ACTION" = "setup-ssl" ]; then
    print_header "Setting Up SSL Certificate"
    print_info "Domain: $DOMAIN"
    print_info "Server: $SERVER_USER@$SERVER_HOST"

    echo ""
    print_warning "Prerequisites:"
    echo "  1. DNS for $DOMAIN must point to $SERVER_HOST"
    echo "  2. Application must be deployed and accessible via HTTP"
    echo "  3. Port 80 must be open for Certbot verification"
    echo ""

    if [ "$FORCE_MODE" = false ]; then
        read -p "Continue with SSL setup? (y/N) " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_warning "SSL setup cancelled"
            exit 0
        fi
    fi

    echo ""
    print_info "Running Certbot..."
    $SSH_CMD "sudo certbot certonly --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN" || {
        print_error "Certbot failed. Check that DNS is configured and port 80 is accessible."
        exit 1
    }

    echo ""
    print_info "Enabling SSL configuration..."
    $SSH_CMD "sudo bash ${DEPLOY_PATH}/scripts/enable-ssl.sh" || {
        print_error "Failed to enable SSL configuration"
        exit 1
    }

    print_success "SSL setup completed successfully"
    echo ""
    print_info "Your site is now available at: https://$DOMAIN"
    exit 0
fi

# =============================================================================
# Deploy Action
# =============================================================================

print_header "Blue-Green Deployment"

echo "Configuration:"
echo "  Environment: ${ENV_NAME:-default}"
echo "  Domain:      $DOMAIN"
echo "  Server:      $SERVER_USER@$SERVER_HOST"
echo "  Image:       $IMAGE_NAME:$IMAGE_TAG"
echo ""

if [ "$FORCE_MODE" = false ]; then
    read -p "Continue with deployment? (y/N) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Deployment cancelled"
        exit 0
    fi
fi

# -----------------------------------------------------------------------------
# Step 1: Build Docker Image
# -----------------------------------------------------------------------------

print_header "Step 1: Building Docker Image"

cd "$PROJECT_ROOT"

print_info "Building image for linux/amd64 architecture..."
docker build \
    --platform linux/amd64 \
    -t "$IMAGE_NAME:$IMAGE_TAG" \
    -t "$IMAGE_NAME:latest" \
    . || {
    print_error "Docker build failed"
    exit 1
}

print_success "Image built: $IMAGE_NAME:$IMAGE_TAG"

# -----------------------------------------------------------------------------
# Step 2: Save Image to Tarball
# -----------------------------------------------------------------------------

print_header "Step 2: Saving Image to Tarball"

TARBALL_PATH="/tmp/${SANITIZED_DOMAIN}-${IMAGE_TAG}.tar.gz"

print_info "Compressing image to: $TARBALL_PATH"
docker save "$IMAGE_NAME:$IMAGE_TAG" | gzip > "$TARBALL_PATH" || {
    print_error "Failed to save Docker image"
    exit 1
}

TARBALL_SIZE=$(du -h "$TARBALL_PATH" | cut -f1)
print_success "Image saved: $TARBALL_SIZE"

# -----------------------------------------------------------------------------
# Step 3: Transfer Files to Server
# -----------------------------------------------------------------------------

print_header "Step 3: Transferring Files to Server"

# Create server scripts directory
$SSH_CMD "sudo mkdir -p ${DEPLOY_PATH}/scripts && sudo chown -R $SERVER_USER:$SERVER_USER ${DEPLOY_PATH}/scripts"

# Transfer server scripts
print_info "Transferring deployment scripts..."
$SCP_CMD -r "$SCRIPT_DIR/server/"* "$SERVER_USER@$SERVER_HOST:${DEPLOY_PATH}/scripts/" || {
    print_error "Failed to transfer scripts"
    exit 1
}

# Make scripts executable
$SSH_CMD "chmod +x ${DEPLOY_PATH}/scripts/*.sh"

# Transfer image tarball
print_info "Transferring Docker image ($TARBALL_SIZE)..."
$SCP_CMD "$TARBALL_PATH" "$SERVER_USER@$SERVER_HOST:/tmp/" || {
    print_error "Failed to transfer Docker image"
    exit 1
}

print_success "Files transferred successfully"

# Clean up local tarball
rm -f "$TARBALL_PATH"

# -----------------------------------------------------------------------------
# Step 4: Execute Server-Side Deployment
# -----------------------------------------------------------------------------

print_header "Step 4: Deploying on Server"

# Build environment variables string for server script
ENV_VARS="IMAGE_NAME='$IMAGE_NAME'"
ENV_VARS="$ENV_VARS IMAGE_TAG='$IMAGE_TAG'"
ENV_VARS="$ENV_VARS TARBALL_PATH='/tmp/${SANITIZED_DOMAIN}-${IMAGE_TAG}.tar.gz'"
ENV_VARS="$ENV_VARS DOMAIN='$DOMAIN'"
ENV_VARS="$ENV_VARS SANITIZED_DOMAIN='$SANITIZED_DOMAIN'"
ENV_VARS="$ENV_VARS DEPLOY_PATH='$DEPLOY_PATH'"
ENV_VARS="$ENV_VARS NETWORK_NAME='$NETWORK_NAME'"
ENV_VARS="$ENV_VARS BLUE_PORT='$BLUE_PORT'"
ENV_VARS="$ENV_VARS GREEN_PORT='$GREEN_PORT'"
ENV_VARS="$ENV_VARS HEALTH_CHECK_TIMEOUT='$HEALTH_CHECK_TIMEOUT'"
ENV_VARS="$ENV_VARS HEALTH_CHECK_INTERVAL='$HEALTH_CHECK_INTERVAL'"
ENV_VARS="$ENV_VARS KEEP_OLD_IMAGES='$KEEP_OLD_IMAGES'"
ENV_VARS="$ENV_VARS APP_NAME='$APP_NAME'"
ENV_VARS="$ENV_VARS APP_ENV='$APP_ENV'"
ENV_VARS="$ENV_VARS APP_KEY='$APP_KEY'"
ENV_VARS="$ENV_VARS APP_DEBUG='$APP_DEBUG'"
ENV_VARS="$ENV_VARS APP_TIMEZONE='$APP_TIMEZONE'"
ENV_VARS="$ENV_VARS APP_URL='$APP_URL'"
ENV_VARS="$ENV_VARS LOG_CHANNEL='$LOG_CHANNEL'"
ENV_VARS="$ENV_VARS LOG_LEVEL='$LOG_LEVEL'"
ENV_VARS="$ENV_VARS SESSION_DRIVER='$SESSION_DRIVER'"
ENV_VARS="$ENV_VARS SESSION_LIFETIME='$SESSION_LIFETIME'"
ENV_VARS="$ENV_VARS CACHE_STORE='$CACHE_STORE'"
ENV_VARS="$ENV_VARS QUEUE_CONNECTION='$QUEUE_CONNECTION'"
ENV_VARS="$ENV_VARS REDIS_HOST='$REDIS_HOST'"
ENV_VARS="$ENV_VARS REDIS_PORT='$REDIS_PORT'"
ENV_VARS="$ENV_VARS REDIS_PASSWORD='$REDIS_PASSWORD'"
ENV_VARS="$ENV_VARS FILESYSTEM_DISK='$FILESYSTEM_DISK'"
ENV_VARS="$ENV_VARS MAIL_MAILER='$MAIL_MAILER'"
ENV_VARS="$ENV_VARS MAIL_HOST='$MAIL_HOST'"
ENV_VARS="$ENV_VARS MAIL_PORT='$MAIL_PORT'"
ENV_VARS="$ENV_VARS MAIL_USERNAME='$MAIL_USERNAME'"
ENV_VARS="$ENV_VARS MAIL_PASSWORD='$MAIL_PASSWORD'"
ENV_VARS="$ENV_VARS MAIL_ENCRYPTION='$MAIL_ENCRYPTION'"
ENV_VARS="$ENV_VARS MAIL_FROM_ADDRESS='$MAIL_FROM_ADDRESS'"
ENV_VARS="$ENV_VARS MAIL_FROM_NAME='$MAIL_FROM_NAME'"

$SSH_CMD "sudo $ENV_VARS bash ${DEPLOY_PATH}/scripts/deploy.sh" || {
    print_error "Server-side deployment failed"
    exit 1
}

# -----------------------------------------------------------------------------
# Step 5: Verify Deployment
# -----------------------------------------------------------------------------

print_header "Deployment Complete"

print_success "Application deployed successfully!"
echo ""
echo "Details:"
echo "  Domain:    $DOMAIN"
echo "  Image:     $IMAGE_NAME:$IMAGE_TAG"
echo "  URL:       $APP_URL"
echo ""
echo "Commands:"
echo "  Check status:  ./deploy/scripts/deploy.sh --status"
echo "  Rollback:      ./deploy/scripts/deploy.sh --rollback"
if [[ "$APP_URL" == http://* ]]; then
    echo "  Setup SSL:     ./deploy/scripts/deploy.sh --setup-ssl"
fi
