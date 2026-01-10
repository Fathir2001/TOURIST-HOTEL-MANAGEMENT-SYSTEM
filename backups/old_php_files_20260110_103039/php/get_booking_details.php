<?php
/**
 * Get Booking Details for Admin
 * Retrieves complete booking information for admin dashboard
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Get booking reference from query parameter
    $bookingRef = isset($_GET['ref']) ? trim($_GET['ref']) : '';
    
    if (empty($bookingRef)) {
        echo json_encode([
            'success' => false,
            'message' => 'Booking reference is required'
        ]);
        exit;
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Fetch booking details with room type and room information
    $query = "SELECT 
                b.*,
                rt.type_name,
                rt.base_price,
                rt.max_occupancy,
                rt.bed_type,
                rt.size_sqm,
                rt.image_url,
                r.room_number,
                r.floor_number,
                r.view_type
              FROM bookings b
              LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
              LEFT JOIN rooms r ON b.room_id = r.room_id
              WHERE b.booking_reference = :booking_ref
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':booking_ref', $bookingRef, PDO::PARAM_STR);
    $stmt->execute();
    
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode([
            'success' => false,
            'message' => 'Booking not found'
        ]);
        exit;
    }
    
    // Calculate nights
    $checkIn = new DateTime($booking['check_in_date']);
    $checkOut = new DateTime($booking['check_out_date']);
    $nights = $checkIn->diff($checkOut)->days;
    
    // Format dates
    $booking['check_in_formatted'] = $checkIn->format('F d, Y');
    $booking['check_out_formatted'] = $checkOut->format('F d, Y');
    $booking['created_at_formatted'] = (new DateTime($booking['created_at']))->format('F d, Y g:i A');
    
    if ($booking['confirmed_at']) {
        $booking['confirmed_at_formatted'] = (new DateTime($booking['confirmed_at']))->format('F d, Y g:i A');
    }
    
    $booking['total_nights'] = $nights;
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error in get_booking_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in get_booking_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching booking details'
    ]);
}
?>
