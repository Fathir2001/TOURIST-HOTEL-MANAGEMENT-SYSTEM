# Clean Up Old PHP Files
# This script removes old PHP backend files that have been replaced by the REST API
# 
# Run this AFTER testing the new REST API thoroughly!
# Make sure to backup your project before running this script.

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  OLD PHP FILES CLEANUP SCRIPT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "WARNING: This will DELETE old PHP files!" -ForegroundColor Yellow
Write-Host "Make sure you have:" -ForegroundColor Yellow
Write-Host "  1. Tested the new REST API thoroughly" -ForegroundColor Yellow
Write-Host "  2. Created a backup of your project" -ForegroundColor Yellow
Write-Host ""

$confirmation = Read-Host "Type 'YES' to continue or anything else to cancel"

if ($confirmation -ne "YES") {
    Write-Host "Cleanup cancelled." -ForegroundColor Green
    exit
}

Write-Host ""
Write-Host "Starting cleanup..." -ForegroundColor Green
Write-Host ""

# Navigate to project directory
$projectPath = "c:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM"
Set-Location $projectPath

# Create backup directory with timestamp
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = Join-Path $projectPath "backups\old_php_files_$timestamp"

Write-Host "Creating backup at: $backupDir" -ForegroundColor Cyan
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# List of old PHP files to remove (now replaced by REST API)
$oldFiles = @(
    "php\add_room.php",                    # Replaced by: POST php/api/rooms.php
    "php\delete_room.php",                 # Replaced by: DELETE php/api/rooms.php
    "php\get_all_rooms.php",               # Replaced by: GET php/api/rooms.php
    "php\get_available_rooms.php",         # Replaced by: GET php/api/rooms.php?status=available
    "php\get_bookings.php",                # Replaced by: GET php/api/bookings.php
    "php\get_booking_details.php",         # Replaced by: GET php/api/bookings.php?id={id}
    "php\get_next_room_number.php",        # No longer needed (handled by frontend)
    "php\get_room_types_list.php",         # Replaced by: GET php/api/room_types.php
    "php\get_rooms.php",                   # Replaced by: GET php/api/room_types.php
    "php\process_booking.php",             # Replaced by: POST php/api/bookings.php
    "php\update_booking_status.php",       # Replaced by: PATCH php/api/bookings.php
    "php\update_room_status.php",          # Replaced by: PATCH php/api/rooms.php
    "php\update_room_type.php",            # Replaced by: PATCH php/api/room_types.php
    "php\update_room_type_image.php"       # Replaced by: PATCH php/api/room_types.php
)

# Files that will be KEPT (special purpose or aggregated data)
$keptFiles = @(
    "php\Connect.php",                     # Keep - Admin login (not REST)
    "php\get_admin_info.php",              # Keep - Session info
    "php\get_dashboard_stats.php",         # Keep - Aggregated statistics
    "php\get_guests.php",                  # Keep - Aggregated guest data
    "php\get_recent_data.php",             # Keep - Dashboard widgets
    "php\get_revenue.php",                 # Keep - Revenue analytics
    "php\logout.php",                      # Keep - Session management
    "php\update_password.php",             # Keep - Admin settings
    "php\update_username.php",             # Keep - Admin settings
    "php\auto_update_booking_status.php",  # Keep - Scheduled tasks
    "php\fix_admin_password.php",          # Keep - Utility script
    "php\fix_image_paths.php",             # Keep - Utility script
    "php\config\*",                        # Keep - Configuration
    "php\includes\*"                       # Keep - Helper functions
)

Write-Host ""
Write-Host "Files to be removed:" -ForegroundColor Yellow
foreach ($file in $oldFiles) {
    $fullPath = Join-Path $projectPath $file
    if (Test-Path $fullPath) {
        Write-Host "  - $file" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Files that will be KEPT:" -ForegroundColor Green
foreach ($pattern in $keptFiles) {
    Write-Host "  - $pattern" -ForegroundColor Green
}

Write-Host ""
$finalConfirm = Read-Host "Proceed with deletion? Type 'DELETE' to confirm"

if ($finalConfirm -ne "DELETE") {
    Write-Host "Cleanup cancelled." -ForegroundColor Green
    exit
}

Write-Host ""
Write-Host "Backing up and removing old files..." -ForegroundColor Cyan
Write-Host ""

$removedCount = 0
$notFoundCount = 0

foreach ($file in $oldFiles) {
    $fullPath = Join-Path $projectPath $file
    
    if (Test-Path $fullPath) {
        # Backup file before deletion
        $backupPath = Join-Path $backupDir $file
        $backupParent = Split-Path $backupPath -Parent
        
        if (-not (Test-Path $backupParent)) {
            New-Item -ItemType Directory -Path $backupParent -Force | Out-Null
        }
        
        Copy-Item $fullPath $backupPath -Force
        Write-Host "[BACKED UP] $file" -ForegroundColor Cyan
        
        # Remove file
        Remove-Item $fullPath -Force
        Write-Host "[DELETED] $file" -ForegroundColor Red
        $removedCount++
    } else {
        Write-Host "[NOT FOUND] $file" -ForegroundColor Yellow
        $notFoundCount++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  CLEANUP COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  Files backed up and removed: $removedCount" -ForegroundColor Green
Write-Host "  Files not found: $notFoundCount" -ForegroundColor Yellow
Write-Host "  Backup location: $backupDir" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Test your application thoroughly" -ForegroundColor Yellow
Write-Host "  2. If everything works, you can delete the backup folder" -ForegroundColor Yellow
Write-Host "  3. If issues occur, restore files from: $backupDir" -ForegroundColor Yellow
Write-Host ""
Write-Host "Your REST API is now the primary backend!" -ForegroundColor Green
Write-Host ""

# Create a restoration script in the backup directory
$restoreScript = @"
# Restore Old PHP Files
# Run this script if you need to restore the old PHP files

`$backupDir = "$backupDir"
`$projectPath = "$projectPath"

Write-Host "Restoring files from backup..." -ForegroundColor Cyan

Get-ChildItem -Path `$backupDir -Recurse -File | ForEach-Object {
    `$relativePath = `$_.FullName.Substring(`$backupDir.Length + 1)
    `$targetPath = Join-Path `$projectPath `$relativePath
    `$targetDir = Split-Path `$targetPath -Parent
    
    if (-not (Test-Path `$targetDir)) {
        New-Item -ItemType Directory -Path `$targetDir -Force | Out-Null
    }
    
    Copy-Item `$_.FullName `$targetPath -Force
    Write-Host "[RESTORED] `$relativePath" -ForegroundColor Green
}

Write-Host ""
Write-Host "Restoration complete!" -ForegroundColor Green
"@

$restoreScriptPath = Join-Path $backupDir "RESTORE_FILES.ps1"
Set-Content -Path $restoreScriptPath -Value $restoreScript
Write-Host "Restoration script created at: $restoreScriptPath" -ForegroundColor Cyan
Write-Host ""
