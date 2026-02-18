# Blue-Green Docker Deployment

Zero-downtime deployment system for the Find Missa Laravel application.

## Quick Start

```bash
# 1. Create config for your environment
cp deploy/config/.env.example .env.deploy.staging

# 2. Edit with your server details
nano .env.deploy.staging

# 3. Initialize server (one-time)
./deploy/scripts/server-init.sh --env staging

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

# Domain Configuration
DOMAIN=staging.findmissa.org

# Application Settings
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.findmissa.org

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

# Domain Configuration
DOMAIN=findmissa.org

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://findmissa.org

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
./deploy/scripts/server-init.sh --env staging

# For production server
./deploy/scripts/server-init.sh --env production
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
    ├── server-init.sh        # Server initialization (run locally)
    └── server/
        ├── deploy.sh         # Server-side deployment
        ├── enable-ssl.sh     # Enable SSL after certbot
        ├── health-check.sh   # Container health verification
        ├── rollback.sh       # Rollback to previous version
        └── status.sh         # Check deployment status
```

### Server

```
/opt/deployments/{domain}/
├── data/
│   ├── database/             # SQLite database (persistent)
│   │   └── database.sqlite
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

| Slot  | Container Name   | Port |
| ----- | ---------------- | ---- |
| Blue  | `{domain}-blue`  | 8080 |
| Green | `{domain}-green` | 8081 |

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
   ssh user@server "docker logs {domain}-blue"
   ```

2. Verify the `/up` endpoint works:

   ```bash
   ssh user@server "curl -f http://localhost:8080/up"
   ```

3. Common causes:
   - Missing environment variables
   - Database migration issues
   - Permission problems on mounted volumes

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
   ssh user@server "sudo ufw status"
   ```

### Permission Errors on Volumes

The container runs as user `findMissa` (UID 1337). Ensure volume directories have correct ownership:

```bash
ssh user@server "sudo chown -R 1337:1337 /opt/deployments/{domain}/data"
```

### Redis Connection Issues

Verify Redis is running on the server:

```bash
ssh user@server "redis-cli ping"
# Should return: PONG
```

Containers access Redis via `host.docker.internal`. This is configured automatically.

### Rollback Not Working

Rollback requires the previous container image to still exist. By default, the last 3 images are kept. If the previous image was cleaned up, you'll need to redeploy an older version.

## Security Notes

- **SSH Keys:** Use key-based authentication, not passwords
- **Secrets:** Never commit `.env.deploy.*` files (they're in `.gitignore`)
- **Firewall:** Only ports 22, 80, 443 should be open
- **Container User:** Containers run as non-root user (`findMissa`)
- **SSL:** Use `--setup-ssl` to enable HTTPS with Let's Encrypt

## Architecture Decisions

For detailed explanations of architectural choices (why SQLite, why blue-green, why host Redis, etc.), see [DECISIONS.md](./DECISIONS.md).
