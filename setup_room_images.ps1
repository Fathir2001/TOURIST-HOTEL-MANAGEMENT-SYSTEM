# Copy and rename room images
# Run this script from the project root directory

Write-Host "Creating room images from existing hotel images..." -ForegroundColor Green

$imagesPath = "images"

# Check if images directory exists
if (-not (Test-Path $imagesPath)) {
    Write-Host "Error: Images directory not found!" -ForegroundColor Red
    exit 1
}

# Copy images with new names
$imageMappings = @{
    "img75.webp" = "presidential-suite.jpg"
    "img74.webp" = "heritage-suite.jpg"
    "img73.webp" = "Deluxe Room.jfif"
    "img76.webp" = "garden-wing.jpg"
    "img35.webp" = "triple-room.jpg"
    "img3.webp" = "double-room.jpg"
}

foreach ($source in $imageMappings.Keys) {
    $sourcePath = Join-Path $imagesPath $source
    $destPath = Join-Path $imagesPath $imageMappings[$source]
    
    if (Test-Path $sourcePath) {
        Copy-Item -Path $sourcePath -Destination $destPath -Force
        Write-Host "✓ Copied $source -> $($imageMappings[$source])" -ForegroundColor Cyan
    } else {
        Write-Host "✗ Source file not found: $source" -ForegroundColor Yellow
    }
}

Write-Host "`nRoom images created successfully!" -ForegroundColor Green
Write-Host "You can now use the dynamic accommodation page." -ForegroundColor Green
