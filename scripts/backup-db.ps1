param(
    [string]$BackupDir = ".\backups",
    [string]$Container = "online-bookstore-db-1"
)

# Ensure backup directory exists
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir
}

# Generate backup filename with timestamp
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = Join-Path $BackupDir "bookstore_${timestamp}.sql"

Write-Host "Creating backup: $backupFile"

# Create MySQL backup using docker exec
docker exec $Container mysqldump -u user -ppassword bookstore > $backupFile

if ($LASTEXITCODE -eq 0) {
    Write-Host "Backup completed successfully: $backupFile"
} else {
    Write-Error "Backup failed with exit code: $LASTEXITCODE"
    exit 1
}