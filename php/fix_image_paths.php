<?php
/**
 * Fix Image Paths in Database
 * Adds ../ prefix to image URLs that are missing it
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        die("❌ Database connection failed!\n");
    }
    
    echo "🔧 Fixing image paths in database...\n\n";
    
    // Get all room types
    $stmt = $conn->prepare("SELECT room_type_id, type_name, image_url FROM room_types");
    $stmt->execute();
    $roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateCount = 0;
    
    foreach ($roomTypes as $roomType) {
        $imageUrl = $roomType['image_url'];
        
        // Check if image URL doesn't start with ../
        if (!empty($imageUrl) && strpos($imageUrl, '../') !== 0) {
            // Add ../ prefix
            $newImageUrl = '../' . $imageUrl;
            
            echo "Updating {$roomType['type_name']}:\n";
            echo "  Old: {$imageUrl}\n";
            echo "  New: {$newImageUrl}\n\n";
            
            $updateStmt = $conn->prepare("UPDATE room_types SET image_url = ? WHERE room_type_id = ?");
            $updateStmt->execute([$newImageUrl, $roomType['room_type_id']]);
            
            $updateCount++;
        }
    }
    
    if ($updateCount > 0) {
        echo "✅ Fixed {$updateCount} image path(s) successfully!\n";
    } else {
        echo "✅ All image paths are already correct!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
