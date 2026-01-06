# Quick Database Fix Script
# This script will update the bookings table to add missing columns

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DATABASE TABLE UPDATE SCRIPT" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$sqlFile = "c:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\php\update_bookings_table.sql"
$mysqlPath = "c:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"

# Try to find MySQL executable
if (-not (Test-Path $mysqlPath)) {
    $mysqlDir = Get-ChildItem "c:\wamp64\bin\mysql" -Directory | Select-Object -First 1
    if ($mysqlDir) {
        $mysqlPath = "$($mysqlDir.FullName)\bin\mysql.exe"
    }
}

if (Test-Path $mysqlPath) {
    Write-Host "Found MySQL at: $mysqlPath" -ForegroundColor Green
    Write-Host "`nExecuting SQL update script..." -ForegroundColor Yellow
    
    # Execute the SQL file
    $command = "& `"$mysqlPath`" -u root -p tourist_hotel_db < `"$sqlFile`""
    
    Write-Host "`nCommand to run:" -ForegroundColor Cyan
    Write-Host $command -ForegroundColor White
    Write-Host "`nOR import manually in phpMyAdmin:" -ForegroundColor Yellow
    Write-Host "1. Open http://localhost/phpmyadmin/" -ForegroundColor White
    Write-Host "2. Select 'tourist_hotel_db' database" -ForegroundColor White
    Write-Host "3. Go to 'SQL' tab" -ForegroundColor White
    Write-Host "4. Copy and paste contents from: update_bookings_table.sql" -ForegroundColor White
    Write-Host "5. Click 'Go' button`n" -ForegroundColor White
} else {
    Write-Host "MySQL not found automatically." -ForegroundColor Red
    Write-Host "`nManual steps:" -ForegroundColor Yellow
    Write-Host "1. Open http://localhost/phpmyadmin/" -ForegroundColor White
    Write-Host "2. Select 'tourist_hotel_db' database" -ForegroundColor White
    Write-Host "3. Go to 'SQL' tab" -ForegroundColor White
    Write-Host "4. Open and copy: $sqlFile" -ForegroundColor White
    Write-Host "5. Paste and click 'Go' button`n" -ForegroundColor White
}

Write-Host "========================================" -ForegroundColor Cyan
