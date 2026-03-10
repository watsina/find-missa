# Blue-Green Docker Deployment

Zero-downtime deployment system for Laravel applications.

## Quick Start

```bash
# 1. Create config for your environment
cp deploy/config/.env.example .env.deploy.staging

# 2. Edit with your server details
nano .env.deploy.staging

# 3. Initialize server (one-time)
./deploy/scripts/server-init-laravel.sh --env staging

# 4. Deploy
./deploy/scripts/deploy.sh --env staging

# 5. Setup SSL (after DNS is configured)
./deploy/scripts/deploy.sh --env staging --setup-ssl
```

## Prerequisites

### Local Machine

- Docker Desktop installed and running
- SSH access to deployment server (key-based authentication)
- Bash shell (macOS/Linux or WSL on Windows)

### Server

- Ubuntu 22.04+ (fresh install recommended)
- SSH access with sudo privileges
- Ports 22, 80, 443 open in firewall
- DNS configured to point to server IP (for SSL)

## SSH Key Setup

The deployment scripts use SSH key-based authentication. You can either use your existing SSH key or create a dedicated deploy key for each environment.

### Option 1: Use Existing SSH Key (Simplest)

If you already have SSH access to your server, leave `SSH_KEY_PATH` empty in your config:

```bash
# In .env.deploy.staging
SSH_KEY_PATH=
```

The scripts will use your default SSH key (`~/.ssh/id_rsa` or `~/.ssh/id_ed25519`).

### Option 2: Create Dedicated Deploy Keys (Recommended for Teams)

Create a separate key for each environment:

```bash
# Generate staging deploy key
ssh-keygen -t ed25519 -C "deploy-staging" -f ~/.ssh/deploy_staging_key

# Generate production deploy key
ssh-keygen -t ed25519 -C "deploy-production" -f ~/.ssh/deploy_production_key
```

Add the public key to your server:

```bash
# Copy staging key to staging server
ssh-copy-id -i ~/.ssh/deploy_staging_key.pub ubuntu@staging.example.com

# Copy production key to production server
ssh-copy-id -i ~/.ssh/deploy_production_key.pub ubuntu@production.example.com
```

Configure the key path in your env files:

```bash
# In .env.deploy.staging
SSH_KEY_PATH=~/.ssh/deploy_staging_key

# In .env.deploy.production
SSH_KEY_PATH=~/.ssh/deploy_production_key
```

### Verify SSH Access

Test your connection before deploying:

```bash
# Test with default key
ssh ubuntu@your-server-ip "echo 'SSH connection successful'"

# Test with specific key
ssh -i ~/.ssh/deploy_staging_key ubuntu@your-server-ip "echo 'SSH connection successful'"
```

## Environment Setup

The deployment system supports multiple environments (staging, production) via separate configuration files.

### Configuration Files

| Environment | Config File              | Usage                                         |
| ----------- | ------------------------ | --------------------------------------------- |
| Default     | `.env.deploy`            | `./deploy/scripts/deploy.sh`                  |
| Staging     | `.env.deploy.staging`    | `./deploy/scripts/deploy.sh --env staging`    |
| Production  | `.env.deploy.production` | `./deploy/scripts/deploy.sh --env production` |

### Creating Environment Configs

**For Staging:**

```bash
cp deploy/config/.env.example .env.deploy.staging
```

Edit `.env.deploy.staging`:

```bash
# Server Connection
SERVER_USER=ubuntu
SERVER_HOST=staging.example.com   # Your staging server IP or hostname
SERVER_PORT=22
SSH_KEY_PATH=~/.ssh/deploy_staging_key   # Optional: path to SSH key

# Domain Configuration
DOMAIN=staging.example.com

# Application Settings
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.example.com

# Generate a unique key for this environment
APP_KEY=base64:your-staging-key-here
```

**For Production:**

```bash
cp deploy/config/.env.example .env.deploy.production
```

Edit `.env.deploy.production`:

```bash
# Server Connection
SERVER_USER=ubuntu
SERVER_HOST=production.example.com   # Your production server IP or hostname
SERVER_PORT=22
SSH_KEY_PATH=~/.ssh/deploy_production_key   # Optional: path to SSH key

# Domain Configuration
DOMAIN=example.com

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

# Generate a unique key for this environment
APP_KEY=base64:your-production-key-here
```

### Generating APP_KEY

```bash
php artisan key:generate --show
```

Copy the output (including `base64:`) to your config file.

## Server Initialization

Run this **once per server** to install Docker, Nginx, Redis, and Certbot:

```bash
# For staging server
./deploy/scripts/server-init-laravel.sh --env staging

# For production server
./deploy/scripts/server-init-laravel.sh --env production
```

This script will:

1. Install Docker and configure it
2. Install Nginx with optimized settings
3. Install Redis for caching and queues
4. Install Certbot for SSL certificates
5. Create deployment directories with correct permissions
6. Configure firewall (UFW)

## Deployment Commands

### Deploy

```bash
# Deploy to staging
./deploy/scripts/deploy.sh --env staging

# Deploy to production
./deploy/scripts/deploy.sh --env production

# Deploy without confirmation prompts
./deploy/scripts/deploy.sh --env production --force
```

### Check Status

```bash
# Check staging deployment status
./deploy/scripts/deploy.sh --env staging --status

# Check production deployment status
./deploy/scripts/deploy.sh --env production --status
```

Output shows:

- Active container (blue/green)
- Container health status
- Current image version
- Nginx upstream configuration

### Rollback

```bash
# Rollback staging to previous version
./deploy/scripts/deploy.sh --env staging --rollback

# Rollback production to previous version
./deploy/scripts/deploy.sh --env production --rollback
```

Rollback switches traffic to the previous container slot. This is instant and zero-downtime.

### Setup SSL

**Prerequisites:**

1. Application must be deployed and accessible via HTTP
2. DNS must be configured and propagated
3. Port 80 must be accessible for Certbot verification

```bash
# Setup SSL for staging
./deploy/scripts/deploy.sh --env staging --setup-ssl

# Setup SSL for production
./deploy/scripts/deploy.sh --env production --setup-ssl
```

## Deployment Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        LOCAL MACHINE                            │
├─────────────────────────────────────────────────────────────────┤
│  1. Load .env.deploy.{env}                                      │
│  2. Build Docker image (--platform linux/amd64)                 │
│  3. Compress image to tarball                                   │
│  4. SCP tarball + scripts to server                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                          SERVER                                 │
├─────────────────────────────────────────────────────────────────┤
│  5. Load Docker image from tarball                              │
│  6. Start new container on INACTIVE slot                        │
│     (if blue is active, start green)                            │
│  7. Health check: poll /up endpoint                             │
│  8. Update Nginx upstream to new container                      │
│  9. Reload Nginx (zero-downtime traffic switch)                 │
│ 10. Stop old container                                          │
│ 11. Cleanup old images (keep last 3)                            │
└─────────────────────────────────────────────────────────────────┘
```

## Directory Structure

### Local (Repository)

```
deploy/
├── README.md                 # This file
├── DECISIONS.md              # Architecture decisions
├── config/
│   └── .env.example          # Configuration template
├── nginx/
│   ├── site.conf.template    # HTTP site config
│   ├── ssl.conf.template     # HTTPS site config
│   └── upstream.conf.template
└── scripts/
    ├── deploy.sh             # Main deployment script (run locally)
    ├── server-init-laravel.sh        # Server initialization (run locally)
    └── server/
        ├── deploy.sh         # Server-side deployment
        ├── enable-ssl.sh     # Enable SSL after certbot
        ├── health-check.sh   # Container health verification
        ├── rollback.sh       # Rollback to previous version
        └── status.sh         # Check deployment status
```

### Server

```
${SERVER_DEPLOY_PATH}/${DOMAIN}/
├── data/
│   ├── database.sqlite       # SQLite database (persistent)
│   └── storage/              # Laravel storage (persistent)
│       ├── app/
│       ├── framework/
│       └── logs/
├── scripts/                  # Deployment scripts (from local)
├── nginx/                    # Nginx configs
└── current-slot.txt          # Tracks active slot (blue/green)
```

## Blue-Green Architecture

The system maintains two container "slots":

| Slot  | Container Name    | Port |
| ----- | ----------------- | ---- |
| Blue  | `${DOMAIN}-blue`  | 8080 |
| Green | `${DOMAIN}-green` | 8081 |

**Only one container runs at a time.** This is important for SQLite, which doesn't handle concurrent writes well.

### How It Works

1. **Current state:** Blue container is active, serving traffic on port 8080
2. **Deploy:** New green container starts on port 8081
3. **Health check:** Script polls `/up` endpoint on green
4. **Switch:** Nginx upstream updated to point to green (port 8081)
5. **Cleanup:** Blue container stopped
6. **Result:** Green is now active

Next deployment reverses the process (green → blue).

## Troubleshooting

### Build Fails on Apple Silicon

Ensure you're building with the correct platform:

```bash
docker build --platform linux/amd64 -t myapp .
```

The deployment script handles this automatically.

### Container Fails Health Check

1. Check container logs:

   ```bash
   ssh ${SERVER_USER}@${SERVER_HOST} "docker logs ${DOMAIN}-blue"
   ```

2. Verify the `/up` endpoint works:

   ```bash
   ssh ${SERVER_USER}@${SERVER_HOST} "curl -f http://localhost:8080/up"
   ```

3. Common causes:
   - Missing environment variables
   - Database migration issues
   - Permission problems on mounted volumes
   - Wrong health endpoint (default is `/up`, Laravel 11+ convention — change in `scripts/server/health-check.sh` line 24 if your app uses a different endpoint)

### SSL Setup Fails

1. Verify DNS is configured:

   ```bash
   dig +short yourdomain.com
   ```

2. Check HTTP is working:

   ```bash
   curl -I http://yourdomain.com
   ```

3. Ensure port 80 is open:
   ```bash
   ssh ${SERVER_USER}@${SERVER_HOST} "sudo ufw status"
   ```

### Permission Errors on Volumes

The container runs as the non-root user defined by `CONTAINER_USER` in your `.env.deploy` file (default: `app`, UID 1337). Ensure volume directories have correct ownership:

```bash
ssh ${SERVER_USER}@${SERVER_HOST} "sudo chown -R 1337:1337 ${SERVER_DEPLOY_PATH}/${DOMAIN}/data"
```

### Redis Connection Issues

Verify Redis is running on the server:

```bash
ssh ${SERVER_USER}@${SERVER_HOST} "redis-cli ping"
# Should return: PONG
```

Containers access Redis via `host.docker.internal`. This is configured automatically.

### Rollback Not Working

Rollback requires the previous container image to still exist. By default, the last 3 images are kept. If the previous image was cleaned up, you'll need to redeploy an older version.

## Accessing the Running Container

To run artisan commands or debug directly inside the container:

### 1. SSH to the Server

```bash
# Using default SSH key
ssh ${SERVER_USER}@${SERVER_HOST}

# Using a specific key (if SSH_KEY_PATH is set)
ssh -i ${SSH_KEY_PATH} ${SERVER_USER}@${SERVER_HOST}
```

### 2. Find the Active Container

```bash
# Check which container is running
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Or check the current slot file
cat ${SERVER_DEPLOY_PATH}/${DOMAIN}/current-slot.txt
```

### 3. Execute Artisan Commands

```bash
# Run any artisan command
docker exec -it ${DOMAIN}-blue php artisan migrate:status
docker exec -it ${DOMAIN}-blue php artisan cache:clear
docker exec -it ${DOMAIN}-blue php artisan tinker
docker exec -it ${DOMAIN}-blue php artisan queue:restart
```

### 4. Open an Interactive Shell

```bash
# Get a bash shell inside the container (runs as non-root app user, UID 1337)
docker exec -it ${DOMAIN}-blue bash

# Or as root (for debugging only)
docker exec -it --user root ${DOMAIN}-blue bash
```

### 5. View Logs

```bash
# Laravel logs (from mounted volume)
tail -f ${SERVER_DEPLOY_PATH}/${DOMAIN}/data/storage/logs/laravel.log

# Container logs (supervisor output)
docker logs -f ${DOMAIN}-blue
```

**Note:** Replace `${DOMAIN}` with your sanitized domain (dots replaced with dashes, e.g., `staging-example-com`). Replace `blue` with `green` if the green container is currently active.

**Environment Variables Reference:**

| Variable                | Description                       | Example                     |
| ----------------------- | --------------------------------- | --------------------------- |
| `${SERVER_USER}`        | SSH username                      | `ubuntu`                    |
| `${SERVER_HOST}`        | Server hostname or IP             | `staging.example.com`       |
| `${SSH_KEY_PATH}`       | Path to SSH private key           | `~/.ssh/deploy_staging_key` |
| `${SERVER_DEPLOY_PATH}` | Base deployment directory         | `/opt/deployments`          |
| `${DOMAIN}`             | Sanitized domain (dots to dashes) | `staging-example-com`       |

## Security Notes

- **SSH Keys:** Use key-based authentication, not passwords
- **Secrets:** Never commit `.env.deploy.*` files (they're in `.gitignore`)
- **Firewall:** Only ports 22, 80, 443 should be open
- **Container User:** Containers run as non-root user (configured via `CONTAINER_USER` in `.env.deploy`)
- **SSL:** Use `--setup-ssl` to enable HTTPS with Let's Encrypt

## Architecture Decisions

For detailed explanations of architectural choices (why SQLite, why blue-green, why host Redis, etc.), see [DECISIONS.md](./DECISIONS.md).
