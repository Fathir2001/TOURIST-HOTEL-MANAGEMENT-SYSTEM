<?php
/**
 * Update Room Type
 * Handles updating room type details including name, price, specifications, and image
 */

header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get and validate input
    $roomTypeId = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : 0;
    $typeName = isset($_POST['type_name']) ? trim($_POST['type_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $basePrice = isset($_POST['base_price']) ? floatval($_POST['base_price']) : 0;
    $maxOccupancy = isset($_POST['max_occupancy']) ? intval($_POST['max_occupancy']) : 0;
    $bedType = isset($_POST['bed_type']) ? trim($_POST['bed_type']) : '';
    $sizeSqm = isset($_POST['size_sqm']) ? floatval($_POST['size_sqm']) : 0;
    $badgeLabel = isset($_POST['badge_label']) ? trim($_POST['badge_label']) : '';
    
    // Validation
    if ($roomTypeId <= 0) {
        throw new Exception('Invalid room type ID');
    }
    
    if (empty($typeName)) {
        throw new Exception('Room type name is required');
    }
    
    if ($basePrice <= 0) {
        throw new Exception('Base price must be greater than 0');
    }
    
    if ($maxOccupancy <= 0) {
        throw new Exception('Max occupancy must be greater than 0');
    }
    
    if (empty($bedType)) {
        throw new Exception('Bed type is required');
    }
    
    if ($sizeSqm <= 0) {
        throw new Exception('Room size must be greater than 0');
    }
    
    // Check if room type exists
    $stmt = $conn->prepare("SELECT room_type_id, image_url FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    $existingRoomType = $stmt->fetch();
    
    if (!$existingRoomType) {
        throw new Exception('Room type not found');
    }
    
    // Handle image upload if provided
    $imageUrl = $existingRoomType['image_url']; // Keep existing by default
    
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['room_image'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds 5MB limit');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'room_type_' . $roomTypeId . '_' . time() . '.' . $extension;
        $uploadDir = __DIR__ . '/../images/room-types/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Delete old image if exists
            if (!empty($existingRoomType['image_url'])) {
                // Remove ../ prefix if it exists for the file path
                $oldImagePath = __DIR__ . '/../' . str_replace('../', '', $existingRoomType['image_url']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Store with ../ prefix to match existing format
            $imageUrl = '../images/room-types/' . $filename;
        } else {
            throw new Exception('Failed to upload image');
        }
    }
    
    // Update room type in database
    $stmt = $conn->prepare("
        UPDATE room_types 
        SET type_name = ?,
            description = ?,
            base_price = ?,
            max_occupancy = ?,
            bed_type = ?,
            size_sqm = ?,
            badge_label = ?,
            image_url = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE room_type_id = ?
    ");
    
    $stmt->execute([
        $typeName,
        $description,
        $basePrice,
        $maxOccupancy,
        $bedType,
        $sizeSqm,
        $badgeLabel,
        $imageUrl,
        $roomTypeId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Room type updated successfully',
        'room_type_id' => $roomTypeId,
        'type_name' => $typeName,
        'image_url' => $imageUrl
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
