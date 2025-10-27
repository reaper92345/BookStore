#!/bin/bash

# Check if backup file is provided
if [ -z "$1" ]; then
    echo "Error: Backup file not specified"
    echo "Usage: $0 <backup_file>"
    exit 1
fi

BACKUP_FILE="$1"
CONTAINER="online-bookstore-db-1"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "Restoring from backup: $BACKUP_FILE"

# Restore MySQL backup using docker exec
cat "$BACKUP_FILE" | docker exec -i $CONTAINER mysql -u user -ppassword bookstore

if [ $? -eq 0 ]; then
    echo "Restore completed successfully"
else
    echo "Restore failed with exit code: $?"
    exit 1
fi