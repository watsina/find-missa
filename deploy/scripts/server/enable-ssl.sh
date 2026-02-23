#!/usr/bin/env bash

# =============================================================================
# Enable SSL Script
# =============================================================================
# Enables SSL configuration for Nginx after Certbot has generated certificates.
# Run this after: certbot certonly --nginx -d <domain>
#
# Usage: enable-ssl.sh
#
# Environment variables (read from parent directory):
#   - Uses the Nginx config to determine domain
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

# Determine domain from script location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_PATH="$(dirname "$SCRIPT_DIR")"
SANITIZED_DOMAIN="$(basename "$DEPLOY_PATH")"
DOMAIN="${SANITIZED_DOMAIN//-/.}"

# If domain has only one part, it might be wrong - try to get from nginx config
NGINX_CONF="/etc/nginx/sites-available/${SANITIZED_DOMAIN}.conf"
if [ -f "$NGINX_CONF" ]; then
    DOMAIN=$(grep "server_name" "$NGINX_CONF" | head -1 | awk '{print $2}' | tr -d ';')
fi

print_info "Enabling SSL for domain: $DOMAIN"

# Check if certificates exist
CERT_PATH="/etc/letsencrypt/live/${DOMAIN}"
if [ ! -d "$CERT_PATH" ]; then
    print_error "SSL certificates not found at: $CERT_PATH"
    echo ""
    echo "Please run Certbot first:"
    echo "  sudo certbot certonly --nginx -d $DOMAIN"
    exit 1
fi

print_success "SSL certificates found"

# Backup existing config
if [ -f "$NGINX_CONF" ]; then
    cp "$NGINX_CONF" "${NGINX_CONF}.backup"
    print_info "Backed up existing config"
fi

# Create new SSL-enabled configuration
print_info "Creating SSL configuration..."

cat > "$NGINX_CONF" << EOF
# Site configuration for ${DOMAIN}
# SSL enabled - managed by deployment scripts

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    
    # Allow ACME challenge for certificate renewal
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }
    
    # Redirect all other traffic to HTTPS
    location / {
        return 301 https://\$server_name\$request_uri;
    }
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN};

    # SSL certificates
    ssl_certificate ${CERT_PATH}/fullchain.pem;
    ssl_certificate_key ${CERT_PATH}/privkey.pem;
    
    # SSL settings (Mozilla Intermediate configuration)
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;
    
    # Modern configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # HSTS (optional - uncomment if you want to enforce HTTPS)
    # add_header Strict-Transport-Security "max-age=63072000" always;
    
    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate ${CERT_PATH}/chain.pem;
    resolver 8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout 5s;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Maximum upload size
    client_max_body_size 50m;

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
        
        # WebSocket support
        proxy_cache_bypass \$http_upgrade;
    }

    # Health check endpoint
    location /up {
        proxy_pass http://${SANITIZED_DOMAIN}-backend;
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
EOF

# Test Nginx configuration
print_info "Testing Nginx configuration..."
if ! nginx -t; then
    print_error "Nginx configuration test failed"
    print_info "Restoring backup..."
    cp "${NGINX_CONF}.backup" "$NGINX_CONF"
    exit 1
fi

# Reload Nginx
print_info "Reloading Nginx..."
systemctl reload nginx

# Clean up backup
rm -f "${NGINX_CONF}.backup"

print_success "SSL enabled successfully!"
echo ""
echo "Your site is now available at:"
echo "  https://${DOMAIN}"
echo ""
echo "Certificate renewal is handled automatically by Certbot's systemd timer."
echo "To check renewal status: sudo certbot certificates"
