<?php
/**
 * Update Room Status
 * Changes the status of a room (available, occupied, maintenance)
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
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // Validate room ID
    if ($room_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid room ID'
        ]);
        exit;
    }
    
    // Validate status
    $valid_statuses = ['available', 'occupied', 'maintenance'];
    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status. Valid options: available, occupied, maintenance'
        ]);
        exit;
    }
    
    // Check if room exists and get current status
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
    
    // Check if room has active bookings when trying to set to maintenance
    if ($new_status === 'maintenance') {
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
                'message' => 'Cannot set room to maintenance. Room has active bookings. Please complete or cancel bookings first.'
            ]);
            exit;
        }
    }
    
    // Update room status
    $updateStmt = $conn->prepare("UPDATE rooms SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
    $result = $updateStmt->execute([$new_status, $room_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Room ' . $room['room_number'] . ' status updated to ' . ucfirst($new_status),
            'room_id' => $room_id,
            'new_status' => $new_status,
            'room_number' => $room['room_number']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update room status. Please try again.'
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
