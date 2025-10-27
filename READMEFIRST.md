# Online Bookstore - Setup Guide

## Database Management

### phpMyAdmin
- Access the database management interface at: http://localhost:8080
- Login credentials:
  - Server: db
  - Username: user
  - Password: password
  - Root password: rootpassword (for administrative tasks)

### MySQL Health Checks
The system includes automatic health monitoring:
- Database health is checked every 10 seconds
- Dependent services wait for database to be healthy before starting
- Maximum 5 retries with 5-second timeout

### Database Backup and Restore

#### Using PowerShell (Windows)
```powershell
# Create a backup
.\scripts\backup-db.ps1

# Restore from a specific backup
.\scripts\restore-db.ps1 -BackupFile ".\backups\bookstore_20251027_120000.sql"

# Restore from most recent backup
.\scripts\restore-db.ps1 -BackupFile (Get-ChildItem .\backups | Sort-Object LastWriteTime | Select-Object -Last 1).FullName
```

#### Using Bash (Linux/Mac)
```bash
# Create a backup
./scripts/backup-db.sh

# Restore from backup
./scripts/restore-db.sh ./backups/bookstore_20251027_120000.sql
```

2. Verify everything is running:
- Frontend: http://localhost:3000
- phpMyAdmin: http://localhost:8080
- Backend API: http://localhost:8000




## Admin Panel Access

The admin panel is available at: http://localhost:3000/admin.php

Default admin credentials:
- Username: admin
- Password: admin123

**Important:** Change these credentials in production by editing `frontend/public/admin.php`





For any questions or issues, please check the container logs first and ensure all services are healthy using `docker-compose ps`.