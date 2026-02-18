#!/usr/bin/env bash

# =============================================================================
# Server Initialization Script
# =============================================================================
# One-time setup script for a new deployment server.
# Run this on your LOCAL machine - it will SSH to the server and configure it.
#
# Usage:
#   ./deploy/scripts/server-init.sh
#   ./deploy/scripts/server-init.sh --env staging
#   ./deploy/scripts/server-init.sh --env production
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

sanitize_domain() {
    echo "$1" | sed 's/\./-/g'
}

# =============================================================================
# Parse Arguments
# =============================================================================

ENV_NAME=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --env)
            ENV_NAME="$2"
            shift 2
            ;;
        --help)
            echo "Usage: ./deploy/scripts/server-init.sh [--env <name>]"
            echo ""
            echo "Options:"
            echo "    --env <name>    Use .env.deploy.<name> configuration"
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
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
    echo "Please create the configuration file first:"
    echo "  cp deploy/config/.env.example $ENV_FILE"
    exit 1
fi

print_info "Loading configuration from: $ENV_FILE"

set -a
source "$ENV_FILE"
set +a

# Validate required variables
for var in SERVER_USER SERVER_HOST DOMAIN; do
    if [ -z "${!var}" ]; then
        print_error "Required variable $var is not set"
        exit 1
    fi
done

# Set defaults
SERVER_PORT="${SERVER_PORT:-22}"
SERVER_DEPLOY_PATH="${SERVER_DEPLOY_PATH:-/opt/deployments}"
BLUE_PORT="${BLUE_PORT:-8080}"
GREEN_PORT="${GREEN_PORT:-8081}"

# Derived variables
SANITIZED_DOMAIN=$(sanitize_domain "$DOMAIN")
NETWORK_NAME="${SANITIZED_DOMAIN}-network"
DEPLOY_PATH="${SERVER_DEPLOY_PATH}/${SANITIZED_DOMAIN}"

# SSH command
SSH_CMD="ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST"

# =============================================================================
# Server Initialization
# =============================================================================

print_header "Server Initialization"

echo "Configuration:"
echo "  Server:      $SERVER_USER@$SERVER_HOST:$SERVER_PORT"
echo "  Domain:      $DOMAIN"
echo "  Deploy Path: $DEPLOY_PATH"
echo "  Network:     $NETWORK_NAME"
echo ""

read -p "Continue with server initialization? (y/N) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Initialization cancelled"
    exit 0
fi

# Create the initialization script to run on the server
INIT_SCRIPT=$(cat << 'REMOTE_SCRIPT'
#!/usr/bin/env bash
set -e

# Variables passed from local script
DOMAIN="__DOMAIN__"
SANITIZED_DOMAIN="__SANITIZED_DOMAIN__"
DEPLOY_PATH="__DEPLOY_PATH__"
NETWORK_NAME="__NETWORK_NAME__"
BLUE_PORT="__BLUE_PORT__"
GREEN_PORT="__GREEN_PORT__"
SERVER_USER="__SERVER_USER__"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_info() { echo -e "${BLUE}→ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

echo ""
echo "Starting server initialization..."
echo ""

# =============================================================================
# Install Docker
# =============================================================================

if command -v docker &> /dev/null; then
    print_success "Docker already installed: $(docker --version)"
else
    print_info "Installing Docker..."
    
    # Install prerequisites
    apt-get update
    apt-get install -y ca-certificates curl gnupg lsb-release

    # Add Docker's official GPG key
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    # Add Docker repository
    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
        $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    # Install Docker
    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    # Start and enable Docker
    systemctl start docker
    systemctl enable docker

    print_success "Docker installed: $(docker --version)"
fi

# Add user to docker group
if id -nG "$SERVER_USER" | grep -qw docker; then
    print_success "User $SERVER_USER already in docker group"
else
    print_info "Adding $SERVER_USER to docker group..."
    usermod -aG docker "$SERVER_USER"
    print_success "User $SERVER_USER added to docker group"
fi

# =============================================================================
# Install Nginx
# =============================================================================

if command -v nginx &> /dev/null; then
    print_success "Nginx already installed: $(nginx -v 2>&1)"
else
    print_info "Installing Nginx..."
    apt-get install -y nginx
    systemctl start nginx
    systemctl enable nginx
    print_success "Nginx installed"
fi

# =============================================================================
# Install Redis
# =============================================================================

if command -v redis-server &> /dev/null; then
    print_success "Redis already installed: $(redis-server --version)"
else
    print_info "Installing Redis..."
    apt-get install -y redis-server
    
    # Configure Redis to listen on localhost only
    sed -i 's/^bind .*/bind 127.0.0.1 ::1/' /etc/redis/redis.conf
    
    # Enable and start Redis
    systemctl start redis-server
    systemctl enable redis-server
    print_success "Redis installed"
fi

# =============================================================================
# Install Certbot
# =============================================================================

if command -v certbot &> /dev/null; then
    print_success "Certbot already installed: $(certbot --version)"
else
    print_info "Installing Certbot..."
    apt-get install -y certbot python3-certbot-nginx
    print_success "Certbot installed"
fi

# =============================================================================
# Configure Firewall
# =============================================================================

if command -v ufw &> /dev/null; then
    print_info "Configuring firewall..."
    
    # Enable UFW if not already enabled
    ufw --force enable
    
    # Allow SSH, HTTP, HTTPS
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    
    print_success "Firewall configured (ports 22, 80, 443)"
else
    print_warning "UFW not found, skipping firewall configuration"
fi

# =============================================================================
# Create Directory Structure
# =============================================================================

print_info "Creating deployment directory structure..."

mkdir -p "${DEPLOY_PATH}/data/database"
mkdir -p "${DEPLOY_PATH}/data/storage"
mkdir -p "${DEPLOY_PATH}/data/storage/app/public"
mkdir -p "${DEPLOY_PATH}/data/storage/framework/sessions"
mkdir -p "${DEPLOY_PATH}/data/storage/framework/views"
mkdir -p "${DEPLOY_PATH}/data/storage/framework/cache"
mkdir -p "${DEPLOY_PATH}/data/storage/logs"
mkdir -p "${DEPLOY_PATH}/scripts"
mkdir -p "${DEPLOY_PATH}/nginx"

# Set ownership
chown -R "$SERVER_USER:$SERVER_USER" "$DEPLOY_PATH"

# Set permissions for data directories (need to be writable by container user UID 1337)
chmod -R 775 "${DEPLOY_PATH}/data"
chown -R 1337:1337 "${DEPLOY_PATH}/data/database"
chown -R 1337:1337 "${DEPLOY_PATH}/data/storage"

print_success "Directory structure created: $DEPLOY_PATH"

# =============================================================================
# Create Docker Network
# =============================================================================

if docker network inspect "$NETWORK_NAME" &> /dev/null; then
    print_success "Docker network already exists: $NETWORK_NAME"
else
    print_info "Creating Docker network: $NETWORK_NAME"
    docker network create "$NETWORK_NAME"
    print_success "Docker network created: $NETWORK_NAME"
fi

# =============================================================================
# Configure Nginx
# =============================================================================

print_info "Configuring Nginx..."

# Create upstream configuration
cat > "/etc/nginx/conf.d/${SANITIZED_DOMAIN}-upstream.conf" << EOF
# Upstream configuration for ${DOMAIN}
# Managed by deployment scripts - do not edit manually

upstream ${SANITIZED_DOMAIN}-backend {
    # Initially pointing to blue slot - will be updated by deploy script
    server 127.0.0.1:${BLUE_PORT};
}
EOF

# Create site configuration (HTTP only initially)
cat > "/etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf" << EOF
# Site configuration for ${DOMAIN}
# SSL will be added by enable-ssl.sh after running certbot

server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Proxy to application
    location / {
        proxy_pass http://${SANITIZED_DOMAIN}-backend;
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
        proxy_buffering off;
    }

    # Health check endpoint (used by certbot and monitoring)
    location /up {
        proxy_pass http://${SANITIZED_DOMAIN}-backend;
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }
}
EOF

# Enable site
ln -sf "/etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf" "/etc/nginx/sites-enabled/"

# Remove default site if it exists
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Reload Nginx
systemctl reload nginx

print_success "Nginx configured for $DOMAIN"

# =============================================================================
# Summary
# =============================================================================

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Server Initialization Complete"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Installed services:"
echo "  - Docker:  $(docker --version)"
echo "  - Nginx:   $(nginx -v 2>&1)"
echo "  - Redis:   $(redis-server --version | head -1)"
echo "  - Certbot: $(certbot --version)"
echo ""
echo "Created:"
echo "  - Deploy path:    $DEPLOY_PATH"
echo "  - Docker network: $NETWORK_NAME"
echo "  - Nginx config:   /etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf"
echo ""
echo "Next steps:"
echo "  1. Deploy your application:"
echo "     ./deploy/scripts/deploy.sh"
echo ""
echo "  2. After deployment, setup SSL:"
echo "     ./deploy/scripts/deploy.sh --setup-ssl"
echo ""
print_warning "Note: You may need to re-login for docker group changes to take effect"
echo ""

REMOTE_SCRIPT
)

# Replace placeholders in the script
INIT_SCRIPT="${INIT_SCRIPT//__DOMAIN__/$DOMAIN}"
INIT_SCRIPT="${INIT_SCRIPT//__SANITIZED_DOMAIN__/$SANITIZED_DOMAIN}"
INIT_SCRIPT="${INIT_SCRIPT//__DEPLOY_PATH__/$DEPLOY_PATH}"
INIT_SCRIPT="${INIT_SCRIPT//__NETWORK_NAME__/$NETWORK_NAME}"
INIT_SCRIPT="${INIT_SCRIPT//__BLUE_PORT__/$BLUE_PORT}"
INIT_SCRIPT="${INIT_SCRIPT//__GREEN_PORT__/$GREEN_PORT}"
INIT_SCRIPT="${INIT_SCRIPT//__SERVER_USER__/$SERVER_USER}"

# Execute on server
echo ""
print_info "Connecting to server and running initialization..."
echo ""

$SSH_CMD "sudo bash -c '$INIT_SCRIPT'"

print_header "Server Initialization Complete"

print_success "Server is ready for deployment!"
echo ""
echo "Next steps:"
echo "  1. Deploy: ./deploy/scripts/deploy.sh"
echo "  2. Setup SSL: ./deploy/scripts/deploy.sh --setup-ssl"
