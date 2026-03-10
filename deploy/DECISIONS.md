# Deployment Architecture Decisions

This document explains the architectural decisions made for the blue-green Docker deployment system.

## Overview

This deployment system provides zero-downtime deployments for Laravel applications using a blue-green deployment strategy with Docker containers.

## Key Decisions

### 1. Build Locally, Deploy via SCP

**Decision:** Build Docker images on the local development machine and transfer to the server via SCP.

**Rationale:**

- Keeps production servers lightweight (no build tools needed)
- Faster deployments (no compilation on server)
- Works for single-server deployments without registry costs
- Simpler setup than maintaining a Docker registry

**Trade-offs:**

- Requires good upload bandwidth
- Image must be built for correct architecture (see #2)

### 2. Cross-Platform Build with `--platform linux/amd64`

**Decision:** Always build with `--platform linux/amd64` flag.

**Rationale:**

- Development machines may be ARM-based (Apple Silicon M1/M2/M3)
- Production servers are typically AMD64/x86_64
- Without this flag, containers crash with "exec format error"

**Implementation:**

```bash
docker build --platform linux/amd64 -t $IMAGE_NAME:$TAG .
```

### 3. Blue-Green Deployment Strategy

**Decision:** Use two container slots (blue/green) with Nginx upstream switching.

**Rationale:**

- True zero-downtime deployments
- Instant rollback capability (just switch Nginx upstream)
- Simpler health verification before traffic switch
- Only one container runs at a time (resource efficient)

**Implementation:**

- Blue container: port 8080
- Green container: port 8081
- Nginx upstream points to active container
- Traffic switch = nginx reload (zero dropped requests)

### 4. SSL Termination on Host Nginx

**Decision:** Handle SSL/TLS on the host Nginx, not inside containers.

**Rationale:**

- Single location for SSL certificates
- Standard Certbot workflow
- Containers stay simple (HTTP only)
- Easier certificate renewal

**Implementation:**

- Host Nginx listens on 443 with SSL
- Proxies to containers on 8080/8081 (HTTP)
- Containers never see HTTPS directly

### 5. Domain-Based Resource Naming

**Decision:** Name all Docker resources based on the deployment domain.

**Rationale:**

- Supports multiple sites on single server
- Clear resource ownership
- Prevents naming conflicts
- Easy to identify resources by domain

**Implementation:**

- Domain `example.com` -> sanitized `example-com`
- Container: `example-com-blue`, `example-com-green`
- Network: `example-com-network`
- Image: `example-com-app`

### 6. SQLite for Database

**Decision:** Use SQLite instead of PostgreSQL for the production database.

**Rationale:**

- Simpler deployment (no separate database container/service)
- Lower resource usage
- Easy backup (single file)
- Sufficient for expected load

**Implementation:**

- SQLite file stored in mounted volume
- WAL mode enabled for concurrent reads
- Production pragmas for performance and safety

**SQLite Production Configuration:**

```sql
PRAGMA journal_mode=WAL;        -- Write-Ahead Logging
PRAGMA synchronous=NORMAL;      -- Balance safety/speed
PRAGMA busy_timeout=5000;       -- 5s timeout on locks
PRAGMA cache_size=-20000;       -- 20MB cache
PRAGMA foreign_keys=ON;         -- Enforce FK constraints
```

### 7. Host-Level Redis

**Decision:** Install Redis directly on the server, not in a container.

**Rationale:**

- Redis persists across deployments
- No coordination needed between Redis and app containers
- Simpler configuration
- Better performance (no container overhead)

**Implementation:**

- Containers access Redis via `host.docker.internal`
- Redis runs as system service on host

### 8. Persistent Volume Mounts

**Decision:** Use host directory mounts for data persistence.

**Rationale:**

- Data survives container replacements
- Easy to backup (standard file system)
- Both blue and green can access same data
- Only one container runs at a time (no SQLite conflicts)

**Mounted Volumes:**

```
Host                                    Container
/opt/deployments/{domain}/data/database → /var/www/html/database
/opt/deployments/{domain}/data/storage  → /var/www/html/storage
```

### 9. Manual SSL Setup (with Helper Script)

**Decision:** SSL setup is a manual one-time step, not automatic.

**Rationale:**

- Requires DNS to be configured first
- Certbot needs interactive verification
- Only done once per domain
- Avoids automation complexity

**Implementation:**

```bash
# After first deploy and DNS setup
./deploy/scripts/deploy.sh --setup-ssl
```

### 10. Health Checks Before Traffic Switch

**Decision:** Always verify new container is healthy before switching traffic.

**Rationale:**

- Prevents deploying broken builds
- Zero-downtime guarantee
- Automatic rollback if health check fails

**Implementation:**

- New container starts on inactive port
- Health check polls `/up` endpoint
- Only switch traffic after health confirmed
- Old container continues serving during check

## Directory Structure

```
Server: /opt/deployments/{sanitized-domain}/
├── data/
│   ├── database/           # SQLite database + WAL files
│   │   └── database.sqlite
│   └── storage/            # Laravel storage (uploads, logs)
├── scripts/                # Server-side deployment scripts
├── nginx/                  # Nginx configuration files
└── current-slot.txt        # Tracks active slot (blue/green)
```

## Deployment Flow

```
Local Machine                          Server
─────────────────                      ──────
1. Load .env.deploy
2. Build image (--platform linux/amd64)
3. Save to tarball (gzip)
4. SCP tarball + scripts ──────────────► 5. Receive files
                                        6. Load image
                                        7. Start new container (inactive slot)
                                        8. Health check (poll /up)
                                        9. Update Nginx upstream
                                        10. Reload Nginx
                                        11. Stop old container
◄────────────────────────────────────── 12. Report success
```

## Rollback Process

```
1. Read current-slot.txt (e.g., "blue")
2. Previous slot = "green"
3. Start green container if stopped
4. Health check green
5. Update Nginx upstream to green
6. Reload Nginx
7. Stop blue container
8. Update current-slot.txt to "green"
```

## Security Considerations

- SSH key authentication (no passwords)
- Containers run as non-root app user (configured via `CONTAINER_USER` in `.env.deploy`)
- Firewall allows only ports 22, 80, 443
- SSL/TLS with modern cipher suites
- No secrets baked into images
- Environment variables for sensitive config
