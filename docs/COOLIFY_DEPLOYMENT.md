# Coolify Zero-Downtime Deployment Guide

This guide covers deploying Find Mass to Coolify with zero-downtime rolling updates.

## Overview

### Architecture

The recommended deployment architecture uses **separate Coolify resources** instead of Docker Compose. This enables rolling updates for the Laravel application while databases remain running.

```
┌─────────────────────────────────────────────────────────────────┐
│                      Coolify Resources                          │
│                                                                 │
│  ┌─────────────────────┐   ┌─────────────────────┐             │
│  │   Laravel App       │   │   PostgreSQL        │             │
│  │   (Application)     │   │   (Database)        │             │
│  │   ✓ Rolling updates │   │   ✓ Stays running   │             │
│  │   ✓ Health checks   │   │   ✓ Managed backups │             │
│  └─────────────────────┘   └─────────────────────┘             │
│           │                         │                          │
│           │    ┌─────────────────────┐                         │
│           └────│      Redis          │                         │
│                │   (Database)        │                         │
│                │   ✓ Stays running   │                         │
│                └─────────────────────┘                         │
│                                                                 │
│           All on: coolify predefined network                   │
└─────────────────────────────────────────────────────────────────┘
```

### Benefits

- **Zero-downtime deployments**: App containers roll over while databases stay running
- **Independent scaling**: Scale app separately from databases
- **Managed backups**: Use Coolify's built-in backup features for databases
- **Better resource management**: Each service has dedicated resources

## Prerequisites

- Coolify instance (self-hosted or cloud)
- Domain configured and pointing to Coolify server
- Access to Coolify dashboard
- Git repository access configured in Coolify

---

## Part 1: Setting Up Coolify Resources

### 1.1 Create PostgreSQL Database

1. In Coolify dashboard, go to **Resources** → **+ New** → **Database**
2. Select **PostgreSQL**
3. Configure:
   - **Name**: `findmass-postgres`
   - **Version**: PostgreSQL 16 (or latest)
   - **Database Name**: `find_mass`
   - **Username**: `find_mass`
   - **Password**: Generate a secure password
4. Under **Advanced**:
   - Enable **Persistent Storage**
   - Set appropriate resource limits (recommended: 512MB+ RAM)
5. Click **Create**
6. **Note down** the internal hostname (e.g., `findmass-postgres`) and credentials

### 1.2 Create Redis Database

1. Go to **Resources** → **+ New** → **Database**
2. Select **Redis**
3. Configure:
   - **Name**: `findmass-redis`
   - **Version**: Redis 7 (or latest alpine)
4. Under **Advanced**:
   - Enable **Persistent Storage** (for queue job persistence)
5. Click **Create**
6. **Note down** the internal hostname (e.g., `findmass-redis`)

### 1.3 Network Configuration

All resources on the same Coolify server automatically share a network. The hostnames you noted above are used for inter-service communication.

**Important**: Use the **internal hostnames** (not external URLs) for database connections.

---

## Part 2: Deploy Laravel Application

### 2.1 Create Application Resource

1. Go to **Resources** → **+ New** → **Application**
2. Select your Git provider and repository
3. Configure build:
   - **Build Pack**: Dockerfile
   - **Dockerfile Location**: `Dockerfile` (root)
   - **Branch**: `main` (or your production branch)

### 2.2 Configure Health Check

The application has a health check endpoint at `/up`. Configure in Coolify:

- **Health Check Path**: `/up`
- **Health Check Interval**: `30s`
- **Health Check Timeout**: `10s`
- **Health Check Retries**: `3`
- **Health Check Start Period**: `60s`

### 2.3 Configure Persistent Storage

Add a volume mount for the storage directory:

| Container Path          | Volume Name        | Description                 |
| ----------------------- | ------------------ | --------------------------- |
| `/var/www/html/storage` | `findmass-storage` | User uploads, logs, exports |

**Important**: The `storage` directory contains:

- `app/` - User uploads (profile photos, exports)
- `logs/` - Application logs
- `framework/` - Cache, sessions, views (regenerated if using Redis/database)

### 2.4 Environment Variables

Configure these environment variables in Coolify:

#### Required Variables

| Variable       | Value                     | Notes                                           |
| -------------- | ------------------------- | ----------------------------------------------- |
| `APP_NAME`     | `Find Mass`               | Application name                                |
| `APP_ENV`      | `production`              | Environment                                     |
| `APP_KEY`      | `base64:...`              | Generate with `php artisan key:generate --show` |
| `APP_DEBUG`    | `false`                   | **Must be false in production**                 |
| `APP_URL`      | `https://your-domain.com` | Your production URL                             |
| `APP_TIMEZONE` | `Africa/Nairobi`          | Your timezone                                   |

#### Database (PostgreSQL)

| Variable        | Value                  | Notes                           |
| --------------- | ---------------------- | ------------------------------- |
| `DB_CONNECTION` | `pgsql`                | Use PostgreSQL                  |
| `DB_HOST`       | `findmass-postgres`    | Internal hostname from step 1.1 |
| `DB_PORT`       | `5432`                 | PostgreSQL port                 |
| `DB_DATABASE`   | `find_mass`            | Database name                   |
| `DB_USERNAME`   | `find_mass`            | Database user                   |
| `DB_PASSWORD`   | `your-secure-password` | From step 1.1                   |

#### Redis

| Variable         | Value            | Notes                           |
| ---------------- | ---------------- | ------------------------------- |
| `REDIS_HOST`     | `findmass-redis` | Internal hostname from step 1.2 |
| `REDIS_PORT`     | `6379`           | Redis port                      |
| `REDIS_PASSWORD` | `null`           | Set if Redis has auth           |

#### Cache & Queue

| Variable           | Value      | Notes                        |
| ------------------ | ---------- | ---------------------------- |
| `CACHE_STORE`      | `redis`    | Use Redis for caching        |
| `QUEUE_CONNECTION` | `redis`    | Use Redis for queues         |
| `SESSION_DRIVER`   | `database` | Store sessions in PostgreSQL |

#### Mail (Optional)

| Variable            | Value                     | Notes                    |
| ------------------- | ------------------------- | ------------------------ |
| `MAIL_MAILER`       | `smtp`                    | Or `log` for testing     |
| `MAIL_HOST`         | Your SMTP host            | e.g., `smtp.mailgun.org` |
| `MAIL_PORT`         | `587`                     | TLS port                 |
| `MAIL_USERNAME`     | Your username             | SMTP credentials         |
| `MAIL_PASSWORD`     | Your password             | SMTP credentials         |
| `MAIL_ENCRYPTION`   | `tls`                     | TLS encryption           |
| `MAIL_FROM_ADDRESS` | `noreply@your-domain.com` | Sender address           |
| `MAIL_FROM_NAME`    | `Find Mass`               | Sender name              |

### 2.5 Deploy

1. Click **Deploy** to trigger the first deployment
2. Monitor the build logs for any errors
3. Once deployed, the app will run migrations automatically (via `start-container` script)

---

## Part 3: Migrate Data from SQLite to PostgreSQL

If you have existing data in SQLite, follow these steps to migrate it to PostgreSQL.

### 3.1 Overview

The migration process:

1. Export data from SQLite
2. Import into PostgreSQL
3. Convert old activity data to new schema (if applicable)

**Tables excluded from migration** (transient/regenerated):

- `pulse_*` (monitoring data)
- `cache`, `cache_locks`
- `migrations`
- `sessions`
- `jobs`, `failed_jobs`

### 3.2 Method A: Artisan Commands (Recommended)

#### Step 1: Prepare

1. Place your production `database.sqlite` file in the `database/` folder
2. Configure your `.env` with PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=find_mass
DB_USERNAME=find_mass
DB_PASSWORD=your-password
```

#### Step 2: Export from SQLite (Optional - for backup)

```bash
php artisan db:export-sqlite --output=database/exports/backup.sql
```

This creates a PostgreSQL-compatible SQL file as a backup.

#### Step 3: Import to PostgreSQL

```bash
# Direct import from SQLite to PostgreSQL
php artisan db:import-to-postgres --sqlite=database/database.sqlite

# Or with truncate (clears existing data first)
php artisan db:import-to-postgres --sqlite=database/database.sqlite --truncate

# Dry run to preview what will be imported
php artisan db:import-to-postgres --sqlite=database/database.sqlite --dry-run
```

#### Step 4: Verify Import

```bash
# Check row counts
php artisan tinker --execute="
    \$tables = ['dioceses', 'parishes', 'churches', 'users'];
    foreach (\$tables as \$table) {
        echo \$table . ': ' . DB::table(\$table)->count() . PHP_EOL;
    }
"
```

### 3.3 Method B: Shell Script

An alternative shell script is provided for command-line users:

```bash
# Make executable
chmod +x scripts/migrate-sqlite-to-postgres.sh

# Run migration
./scripts/migrate-sqlite-to-postgres.sh \
    database/database.sqlite \
    "postgresql://find_mass:password@hostname:5432/find_mass"
```

### 3.4 Post-Migration: Convert Activity Data

If migrating from the old schema, convert activity times to the new schedule format:

```bash
# Preview changes (dry run)
php artisan activity-times:migrate --dry-run

# Run migration
php artisan activity-times:migrate

# Or force overwrite existing data
php artisan activity-times:migrate --force
```

---

## Part 4: Database Backups

### 4.1 Coolify Built-in Backups

Coolify provides built-in backup functionality for database resources.

#### Enable Backups

1. Go to your PostgreSQL resource in Coolify
2. Navigate to **Backups** tab
3. Configure:
   - **Enabled**: Yes
   - **Frequency**: Daily (recommended) or custom cron
   - **Retention**: Number of backups to keep (e.g., 7)

#### External Storage (S3)

For off-site backups:

1. Go to **Settings** → **Storage**
2. Add S3-compatible storage credentials
3. In database backup settings, select the S3 destination

### 4.2 Manual Backup Commands

#### PostgreSQL Backup

```bash
# From Coolify server or container
pg_dump -h findmass-postgres -U find_mass -d find_mass > backup.sql

# Compressed
pg_dump -h findmass-postgres -U find_mass -d find_mass | gzip > backup.sql.gz
```

#### PostgreSQL Restore

```bash
# Restore from backup
psql -h findmass-postgres -U find_mass -d find_mass < backup.sql

# From compressed
gunzip -c backup.sql.gz | psql -h findmass-postgres -U find_mass -d find_mass
```

---

## Part 5: Rolling Updates

### 5.1 How Zero-Downtime Works

When you deploy a new version:

1. **New container starts**: Coolify builds and starts the new container
2. **Health check**: Waits for `/up` endpoint to return 200
3. **Traffic switch**: Once healthy, traffic routes to new container
4. **Old container stops**: Previous container receives SIGTERM for graceful shutdown
5. **Cleanup**: Old container is removed

**Timeline**: ~30-60 seconds total, with zero dropped requests.

### 5.2 What Happens During Deployment

| Component          | Behavior                              |
| ------------------ | ------------------------------------- |
| **Laravel App**    | New container replaces old (rolling)  |
| **Queue Workers**  | Graceful shutdown, finish current job |
| **Cron/Scheduler** | Continues in new container            |
| **PostgreSQL**     | Stays running, unaffected             |
| **Redis**          | Stays running, unaffected             |
| **Storage Volume** | Persisted, shared between deployments |

### 5.3 Graceful Shutdown

The application is configured for graceful shutdown:

- Queue workers have 60 seconds to finish current job
- SIGTERM is sent first (graceful), then SIGKILL after timeout
- Supervisor manages process lifecycle

---

## Troubleshooting

### Health Check Failures

**Symptoms**: Deployment fails, container keeps restarting

**Solutions**:

1. Check application logs: `docker logs <container-id>`
2. Verify database connection (correct hostname, credentials)
3. Ensure migrations ran successfully
4. Check if `/up` endpoint is accessible

### Database Connection Errors

**Symptoms**: "Connection refused" or "Host not found"

**Solutions**:

1. Use **internal hostname** (e.g., `findmass-postgres`), not external URL
2. Verify PostgreSQL resource is running
3. Check credentials match between app and database resource
4. Ensure both resources are on same Coolify server/network

### Storage Permission Issues

**Symptoms**: "Permission denied" errors for storage/logs

**Solutions**:

1. The `start-container` script sets permissions automatically
2. If issues persist, check volume mount configuration
3. Ensure volume is writable by container user (UID 1337)

### Migration Import Failures

**Symptoms**: Import command fails or data is missing

**Solutions**:

1. Run with `--dry-run` first to preview
2. Check for tables that don't exist yet (use `--skip-missing`)
3. Verify SQLite file is accessible at specified path
4. Check PostgreSQL has enough disk space

### Useful Commands

```bash
# View application logs (in Coolify)
docker logs <app-container-id> -f

# Connect to PostgreSQL
docker exec -it <postgres-container-id> psql -U find_mass -d find_mass

# Connect to Redis
docker exec -it <redis-container-id> redis-cli

# Run artisan command in container
docker exec -it <app-container-id> php artisan <command>

# Check queue status
docker exec -it <app-container-id> php artisan queue:monitor

# Clear caches
docker exec -it <app-container-id> php artisan optimize:clear
```

---

## Quick Reference

### Deployment Checklist

- [ ] PostgreSQL resource created and running
- [ ] Redis resource created and running
- [ ] Application resource created
- [ ] Environment variables configured
- [ ] Storage volume mounted
- [ ] Health check configured
- [ ] First deployment successful
- [ ] Data migrated (if applicable)
- [ ] Backups configured

### Environment Variables Cheat Sheet

```env
# Application
APP_NAME="Find Mass"
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Africa/Nairobi

# Database
DB_CONNECTION=pgsql
DB_HOST=findmass-postgres
DB_PORT=5432
DB_DATABASE=find_mass
DB_USERNAME=find_mass
DB_PASSWORD=your-secure-password

# Redis
REDIS_HOST=findmass-redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```
