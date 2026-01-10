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
    // Get and validate input
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT); // New: room assignment
    
    // Validate required fields
    if (!$booking_id || !$new_status) {
        throw new Exception('Booking ID and status are required');
    }
    
    // Validate status value
    $valid_statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
    if (!in_array($new_status, $valid_statuses)) {
        throw new Exception('Invalid status value');
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Start transaction for atomic updates
    $conn->beginTransaction();
    
    // Check if booking exists and get current data
    $checkStmt = $conn->prepare("SELECT booking_id, status, room_id, room_type_id FROM bookings WHERE booking_id = ?");
    $checkStmt->execute([$booking_id]);
    $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    $old_room_id = $booking['room_id'];
    
    // Update the booking status
    $sql = "UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP";
    
    // If room_id is provided, assign it to the booking
    $params = [$new_status];
    if ($room_id) {
        // Validate that the room belongs to the correct room type
        $roomCheckStmt = $conn->prepare("SELECT room_id, room_number, status, room_type_id FROM rooms WHERE room_id = ?");
        $roomCheckStmt->execute([$room_id]);
        $room = $roomCheckStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            $conn->rollBack();
            throw new Exception('Selected room not found');
        }
        
        if ($room['room_type_id'] != $booking['room_type_id']) {
            $conn->rollBack();
            throw new Exception('Selected room does not match the booked room type');
        }
        
        $sql .= ", room_id = ?";
        $params[] = $room_id;
    }
    
    // If status is confirmed, set confirmed_at timestamp
    if ($new_status === 'confirmed' && $booking['status'] !== 'confirmed') {
        $sql .= ", confirmed_at = CURRENT_TIMESTAMP";
    }
    
    // If status is cancelled, set cancelled_at timestamp
    if ($new_status === 'cancelled' && $booking['status'] !== 'cancelled') {
        $sql .= ", cancelled_at = CURRENT_TIMESTAMP";
    }
    
    $sql .= " WHERE booking_id = ?";
    $params[] = $booking_id;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    // Update room statuses based on booking status
    if ($room_id) {
        // Set new room to occupied if status is confirmed or checked_in
        if ($new_status === 'confirmed' || $new_status === 'checked_in') {
            $updateRoomStmt = $conn->prepare("UPDATE rooms SET status = 'occupied', updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
            $updateRoomStmt->execute([$room_id]);
        }
    }
    
    // If old room exists and is different from new room, set it back to available
    if ($old_room_id && $old_room_id != $room_id) {
        // Check if the old room has any other active bookings
        $checkOldRoomStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND booking_id != ? AND status IN ('confirmed', 'checked_in') AND check_out_date >= CURDATE()");
        $checkOldRoomStmt->execute([$old_room_id, $booking_id]);
        $oldRoomBookings = $checkOldRoomStmt->fetch(PDO::FETCH_ASSOC);
        
        // If no other active bookings, set room back to available
        if ($oldRoomBookings['count'] == 0) {
            $updateOldRoomStmt = $conn->prepare("UPDATE rooms SET status = 'available', updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
            $updateOldRoomStmt->execute([$old_room_id]);
        }
    }
    
    // If booking is checked_out or cancelled, update room status
    if (($new_status === 'checked_out' || $new_status === 'cancelled') && $booking['room_id']) {
        // Check if there are any other active bookings for this room
        $checkRoomStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND booking_id != ? AND status IN ('confirmed', 'checked_in') AND check_out_date >= CURDATE()");
        $checkRoomStmt->execute([$booking['room_id'], $booking_id]);
        $roomBookings = $checkRoomStmt->fetch(PDO::FETCH_ASSOC);
        
        // If no other active bookings, set room to available
        if ($roomBookings['count'] == 0) {
            $newRoomStatus = 'available';
            $updateRoomStmt = $conn->prepare("UPDATE rooms SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
            $updateRoomStmt->execute([$newRoomStatus, $booking['room_id']]);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    $message = 'Booking status updated successfully';
    if ($room_id && $room_id != $old_room_id) {
        $message .= ' and room assigned';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'booking_id' => $booking_id,
        'new_status' => $new_status,
        'room_id' => $room_id
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // Database error
    error_log('Database Error in update_booking_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    // General error
    error_log('Error in update_booking_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
