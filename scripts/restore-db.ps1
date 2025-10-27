param(
    [Parameter(Mandatory=$true)]
    [string]$BackupFile,
    [string]$Container = "online-bookstore-db-1"
)

# Check if backup file exists
if (-not (Test-Path $BackupFile)) {
    Write-Error "Backup file not found: $BackupFile"
    exit 1
}

Write-Host "Restoring from backup: $BackupFile"

# Restore MySQL backup using docker exec
Get-Content $BackupFile | docker exec -i $Container mysql -u user -ppassword bookstore

if ($LASTEXITCODE -eq 0) {
    Write-Host "Restore completed successfully"
} else {
    Write-Error "Restore failed with exit code: $LASTEXITCODE"
    exit 1
}