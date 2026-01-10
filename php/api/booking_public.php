<?php
/**
 * Public Booking API - Get booking by reference (no auth required)
 * Used by booking confirmation page
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include dependencies
require_once '../config/database.php';
require_once '../config/api_helpers.php';

// Set CORS headers
setCorsHeaders();

// Set JSON content type
header('Content-Type: application/json');

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendErrorResponse('Method not allowed', 405);
    }
    
    // Get booking reference from query parameter
    if (!isset($_GET['ref'])) {
        sendErrorResponse('Booking reference is required', 400);
    }
    
    $bookingReference = $_GET['ref'];
    
    // Fetch booking details
    $stmt = $conn->prepare("
        SELECT b.*, rt.type_name 
        FROM bookings b
        LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
        WHERE b.booking_reference = ?
    ");
    
    $stmt->execute([$bookingReference]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        sendErrorResponse('Booking not found', 404);
    }
    
    sendSuccessResponse($booking);
    
} catch (Exception $e) {
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
}
?>
