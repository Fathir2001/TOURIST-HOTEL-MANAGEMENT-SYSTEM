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
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
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
    
    // Check if booking exists
    $checkStmt = $conn->prepare("SELECT booking_id, status FROM bookings WHERE booking_id = ?");
    $checkStmt->execute([$booking_id]);
    $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Update the booking status
    $sql = "UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP";
    
    // If status is confirmed, set confirmed_at timestamp
    if ($new_status === 'confirmed' && $booking['status'] !== 'confirmed') {
        $sql .= ", confirmed_at = CURRENT_TIMESTAMP";
    }
    
    // If status is cancelled, set cancelled_at timestamp
    if ($new_status === 'cancelled' && $booking['status'] !== 'cancelled') {
        $sql .= ", cancelled_at = CURRENT_TIMESTAMP";
    }
    
    $sql .= " WHERE booking_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$new_status, $booking_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'booking_id' => $booking_id,
            'new_status' => $new_status
        ]);
    } else {
        // No rows affected might mean the status was already the same
        echo json_encode([
            'success' => true,
            'message' => 'Status is already set to ' . $new_status,
            'booking_id' => $booking_id,
            'new_status' => $new_status
        ]);
    }
    
} catch (PDOException $e) {
    // Database error
    error_log('Database Error in update_booking_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // General error
    error_log('Error in update_booking_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
