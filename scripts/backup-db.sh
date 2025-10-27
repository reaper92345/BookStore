#!/bin/bash

# Default values
BACKUP_DIR="./backups"
CONTAINER="online-bookstore-db-1"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Generate backup filename with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/bookstore_$TIMESTAMP.sql"

echo "Creating backup: $BACKUP_FILE"

# Create MySQL backup using docker exec
docker exec $CONTAINER mysqldump -u user -ppassword bookstore > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "Backup completed successfully: $BACKUP_FILE"
else
    echo "Backup failed with exit code: $?"
    exit 1
fi