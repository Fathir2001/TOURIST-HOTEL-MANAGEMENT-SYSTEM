<?php
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
    
    // Prepare SQL query to fetch unique guest details from bookings table
    // Group by email to get unique guests
    $sql = "SELECT 
                MIN(b.booking_id) as first_booking_id,
                b.guest_name,
                b.guest_email,
                b.guest_phone,
                COUNT(b.booking_id) as total_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN b.status = 'checked_in' THEN 1 ELSE 0 END) as checked_in_bookings,
                SUM(CASE WHEN b.status = 'checked_out' THEN 1 ELSE 0 END) as checked_out_bookings,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                SUM(b.total_amount) as total_spent,
                MAX(b.check_in_date) as last_check_in,
                MIN(b.created_at) as first_booking_date,
                MAX(b.created_at) as last_booking_date
            FROM bookings b
            GROUP BY b.guest_email, b.guest_name, b.guest_phone
            ORDER BY last_booking_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response with guests data
    echo json_encode([
        'success' => true,
        'guests' => $guests,
        'count' => count($guests)
    ]);
    
} catch (PDOException $e) {
    // Database error
    error_log('Database Error in get_guests.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // General error
    error_log('Error in get_guests.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching guests',
        'error' => $e->getMessage()
    ]);
}
?>
