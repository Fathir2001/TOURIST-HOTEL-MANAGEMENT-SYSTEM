# Restore Old PHP Files
# Run this script if you need to restore the old PHP files

$backupDir = "c:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\backups\old_php_files_20260110_103039"
$projectPath = "c:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM"

Write-Host "Restoring files from backup..." -ForegroundColor Cyan

Get-ChildItem -Path $backupDir -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($backupDir.Length + 1)
    $targetPath = Join-Path $projectPath $relativePath
    $targetDir = Split-Path $targetPath -Parent
    
    if (-not (Test-Path $targetDir)) {
        New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    }
    
    Copy-Item $_.FullName $targetPath -Force
    Write-Host "[RESTORED] $relativePath" -ForegroundColor Green
}

Write-Host ""
Write-Host "Restoration complete!" -ForegroundColor Green
