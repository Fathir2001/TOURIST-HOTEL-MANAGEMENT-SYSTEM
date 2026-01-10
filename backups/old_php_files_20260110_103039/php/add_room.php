<?php
/**
 * Add New Room
 * Creates a new room in the database
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Get POST data
    $room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : 0;
    $room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
    $floor_number = isset($_POST['floor_number']) ? intval($_POST['floor_number']) : 1;
    $view_type = isset($_POST['view_type']) ? trim($_POST['view_type']) : 'City';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'available';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Validate required fields
    if (empty($room_number)) {
        echo json_encode([
            'success' => false,
            'message' => 'Room number is required'
        ]);
        exit;
    }
    
    if ($room_type_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid room type'
        ]);
        exit;
    }
    
    // Validate room type exists
    $checkType = $conn->prepare("SELECT room_type_id, type_name FROM room_types WHERE room_type_id = ?");
    $checkType->execute([$room_type_id]);
    $roomType = $checkType->fetch(PDO::FETCH_ASSOC);
    
    if (!$roomType) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid room type selected'
        ]);
        exit;
    }
    
    // Check if room number already exists
    $checkRoom = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
    $checkRoom->execute([$room_number]);
    
    if ($checkRoom->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Room number already exists. Please use a different room number.'
        ]);
        exit;
    }
    
    // Validate status
    $validStatuses = ['available', 'occupied', 'maintenance'];
    if (!in_array($status, $validStatuses)) {
        $status = 'available';
    }
    
    // Insert new room
    $sql = "INSERT INTO rooms (room_number, room_type_id, floor_number, view_type, status, notes, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        $room_number,
        $room_type_id,
        $floor_number,
        $view_type,
        $status,
        $notes
    ]);
    
    if ($result) {
        $newRoomId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Room added successfully',
            'room_id' => $newRoomId,
            'room_number' => $room_number,
            'room_type' => $roomType['type_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add room. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
