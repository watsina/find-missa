#!/bin/bash

# ==============================================================================
# SQLite to PostgreSQL Migration Script
# ==============================================================================
# Usage: ./scripts/migrate-sqlite-to-postgres.sh [sqlite-file] [postgres-url]
#
# Examples:
#   ./scripts/migrate-sqlite-to-postgres.sh database/database.sqlite "postgresql://user:pass@host:5432/dbname"
#   ./scripts/migrate-sqlite-to-postgres.sh database/database.sqlite "postgresql://find_mass:secret@localhost:5432/find_mass"
#
# Requirements:
#   - sqlite3 (command line tool)
#   - psql (PostgreSQL client)
#
# This script is an alternative to the artisan commands:
#   php artisan db:export-sqlite
#   php artisan db:import-to-postgres
# ==============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
SQLITE_FILE="${1:-database/database.sqlite}"
POSTGRES_URL="${2:-}"
TEMP_DIR=$(mktemp -d)
EXPORT_FILE="${TEMP_DIR}/export.sql"

# Tables to exclude (transient/regenerated)
EXCLUDED_TABLES=(
    "migrations"
    "cache"
    "cache_locks"
    "sessions"
    "jobs"
    "failed_jobs"
    "pulse_aggregates"
    "pulse_entries"
    "pulse_values"
)

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

cleanup() {
    rm -rf "${TEMP_DIR}"
}
trap cleanup EXIT

usage() {
    echo "Usage: $0 <sqlite-file> <postgres-url>"
    echo ""
    echo "Arguments:"
    echo "  sqlite-file   Path to SQLite database file"
    echo "  postgres-url  PostgreSQL connection URL"
    echo ""
    echo "Example:"
    echo "  $0 database/database.sqlite \"postgresql://user:pass@host:5432/dbname\""
    echo ""
    exit 1
}

check_requirements() {
    log_info "Checking requirements..."

    if ! command -v sqlite3 &> /dev/null; then
        log_error "sqlite3 is not installed. Please install it first."
        exit 1
    fi

    if ! command -v psql &> /dev/null; then
        log_error "psql is not installed. Please install PostgreSQL client."
        exit 1
    fi

    log_success "All requirements met."
}

validate_inputs() {
    log_info "Validating inputs..."

    if [ -z "${SQLITE_FILE}" ]; then
        log_error "SQLite file path is required."
        usage
    fi

    if [ ! -f "${SQLITE_FILE}" ]; then
        log_error "SQLite file not found: ${SQLITE_FILE}"
        exit 1
    fi

    if [ -z "${POSTGRES_URL}" ]; then
        log_error "PostgreSQL URL is required."
        usage
    fi

    # Test PostgreSQL connection
    if ! psql "${POSTGRES_URL}" -c "SELECT 1" &> /dev/null; then
        log_error "Cannot connect to PostgreSQL. Check your connection URL."
        exit 1
    fi

    log_success "Inputs validated."
}

get_tables() {
    sqlite3 "${SQLITE_FILE}" "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';" | sort
}

is_excluded() {
    local table=$1
    for excluded in "${EXCLUDED_TABLES[@]}"; do
        if [ "${table}" == "${excluded}" ]; then
            return 0
        fi
    done
    return 1
}

export_table() {
    local table=$1
    local count=$(sqlite3 "${SQLITE_FILE}" "SELECT COUNT(*) FROM \"${table}\";")

    if [ "${count}" -eq 0 ]; then
        log_warning "Skipping ${table} (empty)"
        return
    fi

    log_info "Exporting ${table} (${count} rows)..."

    # Get column names
    local columns=$(sqlite3 "${SQLITE_FILE}" "PRAGMA table_info(\"${table}\");" | cut -d'|' -f2 | tr '\n' ',' | sed 's/,$//')

    # Export data as INSERT statements
    echo "-- Table: ${table}" >> "${EXPORT_FILE}"
    
    sqlite3 "${SQLITE_FILE}" <<EOF >> "${EXPORT_FILE}"
.mode insert ${table}
SELECT * FROM "${table}";
EOF

    echo "" >> "${EXPORT_FILE}"

    # Reset sequence if table has id column
    local has_id=$(sqlite3 "${SQLITE_FILE}" "PRAGMA table_info(\"${table}\");" | grep -c '^0|id|')
    if [ "${has_id}" -gt 0 ]; then
        local max_id=$(sqlite3 "${SQLITE_FILE}" "SELECT MAX(id) FROM \"${table}\";")
        if [ -n "${max_id}" ] && [ "${max_id}" != "" ]; then
            echo "SELECT setval('${table}_id_seq', ${max_id}, true);" >> "${EXPORT_FILE}"
            echo "" >> "${EXPORT_FILE}"
        fi
    fi
}

transform_sql() {
    log_info "Transforming SQL for PostgreSQL compatibility..."

    # Create transformed file
    local transformed="${TEMP_DIR}/transformed.sql"

    # Add header
    cat > "${transformed}" << 'HEADER'
-- Disable foreign key checks during import
SET session_replication_role = 'replica';

HEADER

    # Transform SQLite INSERT syntax to PostgreSQL
    # - Replace INSERT INTO "table" with INSERT INTO table
    # - Handle boolean values (SQLite uses 0/1)
    sed -e 's/INSERT INTO \([^ ]*\)/INSERT INTO \1/g' \
        -e "s/'t'/true/g" \
        -e "s/'f'/false/g" \
        "${EXPORT_FILE}" >> "${transformed}"

    # Add footer
    cat >> "${transformed}" << 'FOOTER'

-- Re-enable foreign key checks
SET session_replication_role = 'origin';
FOOTER

    mv "${transformed}" "${EXPORT_FILE}"
    log_success "SQL transformed."
}

import_to_postgres() {
    log_info "Importing to PostgreSQL..."

    if psql "${POSTGRES_URL}" -f "${EXPORT_FILE}" 2>&1 | grep -i error; then
        log_warning "Some errors occurred during import. Check output above."
    else
        log_success "Import completed."
    fi
}

# ==============================================================================
# Main Script
# ==============================================================================

echo ""
echo "=============================================="
echo "SQLite to PostgreSQL Migration"
echo "=============================================="
echo ""

# Check arguments
if [ "$#" -lt 2 ]; then
    usage
fi

check_requirements
validate_inputs

# Initialize export file
echo "-- SQLite to PostgreSQL Export" > "${EXPORT_FILE}"
echo "-- Generated: $(date)" >> "${EXPORT_FILE}"
echo "-- Source: ${SQLITE_FILE}" >> "${EXPORT_FILE}"
echo "" >> "${EXPORT_FILE}"

# Get and process tables
log_info "Finding tables to export..."
tables=$(get_tables)
table_count=0
skipped_count=0

for table in ${tables}; do
    if is_excluded "${table}"; then
        log_warning "Excluding ${table} (in exclusion list)"
        ((skipped_count++))
        continue
    fi

    export_table "${table}"
    ((table_count++))
done

echo ""
log_info "Exported ${table_count} tables, skipped ${skipped_count} tables."

# Transform for PostgreSQL
transform_sql

# Ask for confirmation
echo ""
read -p "Ready to import to PostgreSQL. Continue? (y/N) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log_info "Aborted. Export file saved at: ${EXPORT_FILE}"
    # Copy export file to permanent location before cleanup
    cp "${EXPORT_FILE}" "database/exports/sqlite-export-$(date +%Y%m%d-%H%M%S).sql"
    exit 0
fi

# Import
import_to_postgres

echo ""
echo "=============================================="
log_success "Migration complete!"
echo "=============================================="
echo ""
echo "Next steps:"
echo "  1. Verify data in PostgreSQL"
echo "  2. Run: php artisan activity-times:migrate (if needed)"
echo ""
