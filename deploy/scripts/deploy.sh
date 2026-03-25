#!/usr/bin/env bash

# =============================================================================
# Blue-Green Deployment Script
# =============================================================================
# Main deployment script that runs on your LOCAL machine.
# Builds Docker image, transfers to server, and triggers deployment.
#
# Usage:
#   ./deploy/scripts/deploy.sh                  # Deploy using .env.deploy (default)
#   ./deploy/scripts/deploy.sh --env staging    # Deploy using .env.deploy.staging
#   ./deploy/scripts/deploy.sh --env production # Deploy using .env.deploy.production
#
# When --env is not specified, the script uses .env.deploy as the configuration
# file. Use --env to target a specific environment (e.g., staging, production).
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
                      (without this flag, .env.deploy is used as the default)
                      Examples: --env staging, --env production
    --force           Skip all confirmation prompts
    --status          Check current deployment status on server
    --rollback        Rollback to previous deployment
    --setup-ssl       Deploy AND setup SSL certificates with Certbot
    --ssl-only        Setup SSL only (no deployment, requires prior deploy)
    --help            Show this help message

Examples:
    ./deploy/scripts/deploy.sh                    # Deploy using .env.deploy (default)
    ./deploy/scripts/deploy.sh --env production   # Deploy using .env.deploy.production
    ./deploy/scripts/deploy.sh --env staging      # Deploy to staging
    ./deploy/scripts/deploy.sh --setup-ssl        # Deploy + SSL setup
    ./deploy/scripts/deploy.sh --env staging --setup-ssl  # Deploy to staging + SSL
    ./deploy/scripts/deploy.sh --ssl-only         # SSL only (after prior deploy)
    ./deploy/scripts/deploy.sh --status           # Check status
    ./deploy/scripts/deploy.sh --rollback         # Rollback

Configuration:
    Copy deploy/config/.env.example to .env.deploy.<env> (e.g., .env.deploy.production)
    and configure your deployment settings. If you don't use --env, the script
    looks for .env.deploy as the default configuration file.

First-time setup:
    1. Configure .env.deploy.<env> with your server details
    2. Run: ./deploy/scripts/server-init-laravel.sh
    3. Run: ./deploy/scripts/deploy.sh --env <env> --setup-ssl

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
SETUP_SSL_AFTER_DEPLOY=false

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
            SETUP_SSL_AFTER_DEPLOY=true
            shift
            ;;
        --ssl-only)
            ACTION="ssl-only"
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
# SSL-Only Action (no deployment)
# =============================================================================

if [ "$ACTION" = "ssl-only" ]; then
    print_header "Setting Up SSL Certificate (SSL Only)"
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

    # Ensure scripts are transferred to server
    print_info "Ensuring scripts are available on server..."
    $SSH_CMD "sudo mkdir -p ${DEPLOY_PATH}/scripts && sudo chown -R $SERVER_USER:$SERVER_USER ${DEPLOY_PATH}/scripts"
    $SCP_CMD -r "$SCRIPT_DIR/server/"* "$SERVER_USER@$SERVER_HOST:${DEPLOY_PATH}/scripts/" || {
        print_error "Failed to transfer scripts"
        exit 1
    }
    $SSH_CMD "chmod +x ${DEPLOY_PATH}/scripts/*.sh"

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
if [ "$SETUP_SSL_AFTER_DEPLOY" = true ]; then
    echo "  SSL Setup:   Yes (after deployment)"
fi
echo ""

if [ "$SETUP_SSL_AFTER_DEPLOY" = true ]; then
    print_warning "SSL will be configured after deployment. Prerequisites:"
    echo "  1. DNS for $DOMAIN must point to $SERVER_HOST"
    echo "  2. Port 80 must be open for Certbot verification"
    echo ""
fi

if [ "$FORCE_MODE" = false ]; then
    if [ "$SETUP_SSL_AFTER_DEPLOY" = true ]; then
        read -p "Continue with deployment AND SSL setup? (y/N) " -n 1 -r
    else
        read -p "Continue with deployment? (y/N) " -n 1 -r
    fi
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

# Collect all VITE_* env vars as --build-arg flags so Vite can
# inline them into the frontend bundle at build time.
VITE_BUILD_ARGS=()
while IFS='=' read -r key value; do
    VITE_BUILD_ARGS+=(--build-arg "${key}=${value}")
done < <(env | grep '^VITE_' | sort)

if [ ${#VITE_BUILD_ARGS[@]} -gt 0 ]; then
    print_info "Passing ${#VITE_BUILD_ARGS[@]} VITE_* build args to Docker"
fi

docker build \
    --platform linux/amd64 \
    "${VITE_BUILD_ARGS[@]}" \
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
# Step 4: Prepare Application Environment File
# -----------------------------------------------------------------------------

print_header "Step 4: Preparing Application Environment"

# Deployment-only variables that should NOT be passed to the container.
# Everything else from .env.deploy is treated as an application env var.
DEPLOY_ONLY_VARS=(
    "SERVER_USER"
    "SERVER_HOST"
    "SERVER_PORT"
    "SERVER_DEPLOY_PATH"
    "SSH_KEY_PATH"
    "DOMAIN"
    "CONTAINER_USER"
    "HEALTH_CHECK_TIMEOUT"
    "HEALTH_CHECK_INTERVAL"
    "KEEP_OLD_IMAGES"
    "BLUE_PORT"
    "GREEN_PORT"
)

is_deploy_only_var() {
    local var_name="$1"
    for deploy_var in "${DEPLOY_ONLY_VARS[@]}"; do
        if [ "$var_name" = "$deploy_var" ]; then
            return 0
        fi
    done
    return 1
}

# Extract application env vars from the .env.deploy file and write to a
# temporary incoming env file. Only non-comment, non-blank lines with
# valid KEY=VALUE pairs are included, skipping deployment-only variables.
INCOMING_ENV_FILE="/tmp/${SANITIZED_DOMAIN}-env-incoming"
> "$INCOMING_ENV_FILE"

while IFS= read -r line; do
    # Skip blank lines and comments
    [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue

    # Extract variable name (everything before the first '=')
    var_name="${line%%=*}"

    # Skip if not a valid variable name
    [[ ! "$var_name" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] && continue

    # Skip deployment-only variables
    if is_deploy_only_var "$var_name"; then
        continue
    fi

    # Write the variable with its current (resolved) value from the shell
    # This ensures variable references like ${APP_NAME} are expanded
    var_value="${!var_name}"
    echo "${var_name}=${var_value}" >> "$INCOMING_ENV_FILE"
done < "$ENV_FILE"

print_info "Extracted $(wc -l < "$INCOMING_ENV_FILE" | tr -d ' ') application env vars from $ENV_FILE"

# Transfer incoming env file to server
print_info "Transferring environment configuration..."
$SCP_CMD "$INCOMING_ENV_FILE" "$SERVER_USER@$SERVER_HOST:/tmp/${SANITIZED_DOMAIN}-env-incoming" || {
    print_error "Failed to transfer environment file"
    rm -f "$INCOMING_ENV_FILE"
    exit 1
}

# Clean up local temp file
rm -f "$INCOMING_ENV_FILE"

print_success "Environment configuration transferred"

# -----------------------------------------------------------------------------
# Step 5: Execute Server-Side Deployment
# -----------------------------------------------------------------------------

print_header "Step 5: Deploying on Server"

# Only deployment-specific variables are passed inline to the server script.
# Application env vars are handled via the incoming env file and --env-file.
ENV_VARS="IMAGE_NAME='$IMAGE_NAME'"
ENV_VARS="$ENV_VARS IMAGE_TAG='$IMAGE_TAG'"
ENV_VARS="$ENV_VARS TARBALL_PATH='/tmp/${SANITIZED_DOMAIN}-${IMAGE_TAG}.tar.gz'"
ENV_VARS="$ENV_VARS DOMAIN='$DOMAIN'"
ENV_VARS="$ENV_VARS SANITIZED_DOMAIN='$SANITIZED_DOMAIN'"
ENV_VARS="$ENV_VARS DEPLOY_PATH='$DEPLOY_PATH'"
ENV_VARS="$ENV_VARS BLUE_PORT='$BLUE_PORT'"
ENV_VARS="$ENV_VARS GREEN_PORT='$GREEN_PORT'"
ENV_VARS="$ENV_VARS HEALTH_CHECK_TIMEOUT='$HEALTH_CHECK_TIMEOUT'"
ENV_VARS="$ENV_VARS HEALTH_CHECK_INTERVAL='$HEALTH_CHECK_INTERVAL'"
ENV_VARS="$ENV_VARS KEEP_OLD_IMAGES='$KEEP_OLD_IMAGES'"

$SSH_CMD "sudo $ENV_VARS bash ${DEPLOY_PATH}/scripts/deploy.sh" || {
    print_error "Server-side deployment failed"
    exit 1
}

# -----------------------------------------------------------------------------
# Step 6: Setup SSL (if requested)
# -----------------------------------------------------------------------------

if [ "$SETUP_SSL_AFTER_DEPLOY" = true ]; then
    print_header "Step 6: Setting Up SSL Certificate"

    print_info "Running Certbot..."
    $SSH_CMD "sudo certbot certonly --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN" || {
        print_error "Certbot failed. Check that DNS is configured and port 80 is accessible."
        print_warning "Deployment succeeded but SSL setup failed. You can retry with: ./deploy/scripts/deploy.sh --ssl-only"
        exit 1
    }

    echo ""
    print_info "Enabling SSL configuration..."
    $SSH_CMD "sudo bash ${DEPLOY_PATH}/scripts/enable-ssl.sh" || {
        print_error "Failed to enable SSL configuration"
        print_warning "Deployment succeeded but SSL setup failed. You can retry with: ./deploy/scripts/deploy.sh --ssl-only"
        exit 1
    }

    print_success "SSL setup completed successfully"
fi

# -----------------------------------------------------------------------------
# Step 7: Verify Deployment
# -----------------------------------------------------------------------------

print_header "Deployment Complete"

print_success "Application deployed successfully!"
echo ""
echo "Details:"
echo "  Domain:    $DOMAIN"
echo "  Image:     $IMAGE_NAME:$IMAGE_TAG"
if [ "$SETUP_SSL_AFTER_DEPLOY" = true ]; then
    echo "  URL:       https://$DOMAIN"
else
    echo "  URL:       $APP_URL"
fi
echo ""
echo "Commands:"
echo "  Check status:  ./deploy/scripts/deploy.sh --status"
echo "  Rollback:      ./deploy/scripts/deploy.sh --rollback"
if [ "$SETUP_SSL_AFTER_DEPLOY" = false ] && [[ "$APP_URL" == http://* ]]; then
    echo "  Setup SSL:     ./deploy/scripts/deploy.sh --setup-ssl"
fi
