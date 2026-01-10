<?php
/**
 * Delete Room
 * Removes a room from the database
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
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    
    // Validate room ID
    if ($room_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid room ID'
        ]);
        exit;
    }
    
    // Check if room exists and get status
    $checkRoom = $conn->prepare("SELECT r.room_id, r.room_number, r.status, rt.type_name 
                                 FROM rooms r 
                                 INNER JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                                 WHERE r.room_id = ?");
    $checkRoom->execute([$room_id]);
    $room = $checkRoom->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        echo json_encode([
            'success' => false,
            'message' => 'Room not found'
        ]);
        exit;
    }
    
    // Check if room is occupied
    if ($room['status'] === 'occupied') {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete occupied room. Room ' . $room['room_number'] . ' is currently occupied. Please check out the guest first.'
        ]);
        exit;
    }
    
    // Check if room has active bookings
    $checkBookings = $conn->prepare("SELECT COUNT(*) as booking_count 
                                     FROM bookings 
                                     WHERE room_id = ? 
                                     AND status IN ('confirmed', 'checked_in')
                                     AND check_out_date >= CURDATE()");
    $checkBookings->execute([$room_id]);
    $bookingData = $checkBookings->fetch(PDO::FETCH_ASSOC);
    
    if ($bookingData['booking_count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete room with active bookings. Please cancel or complete all bookings first.'
        ]);
        exit;
    }
    
    // Delete the room
    $deleteStmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $result = $deleteStmt->execute([$room_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Room ' . $room['room_number'] . ' (' . $room['type_name'] . ') deleted successfully',
            'room_id' => $room_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete room. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    // Check if error is due to foreign key constraint
    if ($e->getCode() == '23000') {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete room because it has associated bookings. Please remove bookings first.'
        ]);
    } else {
        error_log("Database Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
