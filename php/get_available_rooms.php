<?php
/**
 * Get Available Rooms by Room Type
 * Returns rooms that are available for booking assignment
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
    
    // Get room type ID from query parameter
    $room_type_id = isset($_GET['room_type_id']) ? intval($_GET['room_type_id']) : 0;
    
    // Validate room type ID
    if ($room_type_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid room type ID',
            'rooms' => []
        ]);
        exit;
    }
    
    // Get available rooms for this room type
    // A room is available if:
    // 1. Status is 'available' (NOT maintenance, NOT occupied)
    // 2. AND it has no active bookings (confirmed/checked_in with future/current dates)
    $sql = "SELECT 
                r.room_id,
                r.room_number,
                r.floor_number,
                r.view_type,
                r.status,
                rt.type_name,
                (SELECT COUNT(*) 
                 FROM bookings b 
                 WHERE b.room_id = r.room_id 
                 AND b.status IN ('confirmed', 'checked_in')
                 AND b.check_out_date >= CURDATE()) as active_bookings
            FROM rooms r
            INNER JOIN room_types rt ON r.room_type_id = rt.room_type_id
            WHERE r.room_type_id = ?
            AND r.status = 'available'
            AND r.status NOT IN ('maintenance', 'occupied')
            AND NOT EXISTS (
                SELECT 1 FROM bookings b2
                WHERE b2.room_id = r.room_id
                AND b2.status IN ('confirmed', 'checked_in')
                AND b2.check_out_date >= CURDATE()
            )
            ORDER BY r.room_number ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$room_type_id]);
    
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'count' => count($rooms)
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'rooms' => []
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'rooms' => []
    ]);
}
