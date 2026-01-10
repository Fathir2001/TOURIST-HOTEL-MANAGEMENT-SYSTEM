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
    
    // Prepare SQL query to fetch all bookings with room type information
    $sql = "SELECT 
                b.*,
                rt.type_name
            FROM bookings b
            INNER JOIN room_types rt ON b.room_type_id = rt.room_type_id
            ORDER BY b.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response with bookings data
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ]);
    
} catch (PDOException $e) {
    // Database error
    error_log('Database Error in get_bookings.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // General error
    error_log('Error in get_bookings.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching bookings',
        'error' => $e->getMessage()
    ]);
}
?>
