<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    // Get room type ID
    $room_type_id = filter_input(INPUT_POST, 'room_type_id', FILTER_VALIDATE_INT);
    
    if (!$room_type_id) {
        throw new Exception('Room type ID is required');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['room_image']) || $_FILES['room_image']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('No image file uploaded');
    }
    
    $file = $_FILES['room_image'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.');
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 5MB limit');
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Check if room type exists and get current image
    $checkStmt = $conn->prepare("SELECT room_type_id, type_name, image_url FROM room_types WHERE room_type_id = ?");
    $checkStmt->execute([$room_type_id]);
    $roomType = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$roomType) {
        throw new Exception('Room type not found');
    }
    
    // Create images directory if it doesn't exist
    $upload_dir = '../images/room-types/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $sanitized_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($roomType['type_name']));
    $new_filename = $sanitized_name . '-' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Delete old image if it exists and is not the default
    if (!empty($roomType['image_url']) && file_exists('../' . $roomType['image_url'])) {
        @unlink('../' . $roomType['image_url']);
    }
    
    // Update database with new image path (relative from html folder)
    $new_image_url = '../images/room-types/' . $new_filename;
    $updateStmt = $conn->prepare("UPDATE room_types SET image_url = ?, updated_at = CURRENT_TIMESTAMP WHERE room_type_id = ?");
    $updateStmt->execute([$new_image_url, $room_type_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Room type image updated successfully',
        'image_url' => $new_image_url,
        'room_type_id' => $room_type_id
    ]);
    
} catch (Exception $e) {
    // Clean up uploaded file if it exists
    if (isset($upload_path) && file_exists($upload_path)) {
        @unlink($upload_path);
    }
    
    error_log('Error in update_room_type_image.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
