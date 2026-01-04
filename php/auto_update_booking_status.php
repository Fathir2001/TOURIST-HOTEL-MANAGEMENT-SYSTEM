<?php
/**
 * Automatic Booking Status Update
 * Runs automatically to update booking statuses based on dates
 * - Changes 'confirmed' to 'checked_in' on check-in date
 * - Changes 'checked_in' to 'checked_out' on check-out date
 * - Sets room to 'available' when checked out
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    $updatedBookings = [];
    $errors = [];
    
    // Start transaction
    $conn->beginTransaction();
    
    // 1. AUTO CHECK-IN: Update bookings from 'confirmed' to 'checked_in' when check-in date arrives
    $checkInQuery = "SELECT booking_id, booking_reference, guest_name, room_id, check_in_date 
                     FROM bookings 
                     WHERE status = 'confirmed' 
                     AND check_in_date <= CURDATE()
                     AND room_id IS NOT NULL";
    
    $checkInStmt = $conn->prepare($checkInQuery);
    $checkInStmt->execute();
    $checkInBookings = $checkInStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($checkInBookings as $booking) {
        try {
            // Update booking status to checked_in
            $updateBooking = $conn->prepare("UPDATE bookings SET status = 'checked_in', updated_at = CURRENT_TIMESTAMP WHERE booking_id = ?");
            $updateBooking->execute([$booking['booking_id']]);
            
            // Ensure room is set to occupied
            if ($booking['room_id']) {
                $updateRoom = $conn->prepare("UPDATE rooms SET status = 'occupied', updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
                $updateRoom->execute([$booking['room_id']]);
            }
            
            $updatedBookings[] = [
                'booking_id' => $booking['booking_id'],
                'booking_reference' => $booking['booking_reference'],
                'guest_name' => $booking['guest_name'],
                'action' => 'checked_in',
                'date' => $booking['check_in_date']
            ];
        } catch (Exception $e) {
            $errors[] = "Error checking in booking {$booking['booking_reference']}: " . $e->getMessage();
        }
    }
    
    // 2. AUTO CHECK-OUT: Update bookings from 'checked_in' to 'checked_out' when check-out date arrives
    $checkOutQuery = "SELECT b.booking_id, b.booking_reference, b.guest_name, b.room_id, b.check_out_date, r.room_number
                      FROM bookings b
                      LEFT JOIN rooms r ON b.room_id = r.room_id
                      WHERE b.status = 'checked_in' 
                      AND b.check_out_date <= CURDATE()";
    
    $checkOutStmt = $conn->prepare($checkOutQuery);
    $checkOutStmt->execute();
    $checkOutBookings = $checkOutStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($checkOutBookings as $booking) {
        try {
            // Update booking status to checked_out
            $updateBooking = $conn->prepare("UPDATE bookings SET status = 'checked_out', updated_at = CURRENT_TIMESTAMP WHERE booking_id = ?");
            $updateBooking->execute([$booking['booking_id']]);
            
            // Set room to available if no other active bookings
            if ($booking['room_id']) {
                $checkOtherBookings = $conn->prepare("SELECT COUNT(*) as count 
                                                       FROM bookings 
                                                       WHERE room_id = ? 
                                                       AND booking_id != ? 
                                                       AND status IN ('confirmed', 'checked_in') 
                                                       AND check_out_date >= CURDATE()");
                $checkOtherBookings->execute([$booking['room_id'], $booking['booking_id']]);
                $otherBookings = $checkOtherBookings->fetch(PDO::FETCH_ASSOC);
                
                // If no other active bookings, set room to available
                if ($otherBookings['count'] == 0) {
                    $updateRoom = $conn->prepare("UPDATE rooms SET status = 'available', updated_at = CURRENT_TIMESTAMP WHERE room_id = ?");
                    $updateRoom->execute([$booking['room_id']]);
                }
            }
            
            $updatedBookings[] = [
                'booking_id' => $booking['booking_id'],
                'booking_reference' => $booking['booking_reference'],
                'guest_name' => $booking['guest_name'],
                'room_number' => $booking['room_number'],
                'action' => 'checked_out',
                'date' => $booking['check_out_date']
            ];
        } catch (Exception $e) {
            $errors[] = "Error checking out booking {$booking['booking_reference']}: " . $e->getMessage();
        }
    }
    
    // Commit all changes
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Automatic status update completed',
        'updates_count' => count($updatedBookings),
        'check_ins' => array_filter($updatedBookings, function($b) { return $b['action'] === 'checked_in'; }),
        'check_outs' => array_filter($updatedBookings, function($b) { return $b['action'] === 'checked_out'; }),
        'errors' => $errors
    ]);
    
} catch (PDOException $e) {
    // Rollback on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Database Error in auto_update_booking_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error during automatic update',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error in auto_update_booking_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error during automatic update',
        'error' => $e->getMessage()
    ]);
}
