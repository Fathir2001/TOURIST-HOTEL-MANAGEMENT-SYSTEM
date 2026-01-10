<?php
/**
 * Upload Room Type Image
 * Handles image file upload for room types
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if image file is uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$roomTypeId = $_POST['room_type_id'] ?? null;

if (!$roomTypeId) {
    echo json_encode(['success' => false, 'message' => 'Room type ID is required']);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../images/room-types/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'room_type_' . $roomTypeId . '_' . time() . '.' . $extension;
$uploadPath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Return relative URL for database storage
    $imageUrl = '../images/room-types/' . $filename;
    
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'image_url' => $imageUrl
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
}
?>
