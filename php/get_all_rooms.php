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
    
    // Prepare SQL query to fetch all rooms with room type information
    $sql = "SELECT 
                r.room_id,
                r.room_number,
                r.floor_number,
                r.view_type,
                r.status,
                r.notes,
                rt.room_type_id,
                rt.type_name,
                rt.base_price,
                rt.max_occupancy,
                rt.bed_type,
                rt.size_sqm,
                rt.badge_label,
                rt.image_url,
                (SELECT COUNT(*) FROM bookings b 
                 WHERE b.room_id = r.room_id 
                 AND b.status IN ('confirmed', 'checked_in')
                 AND b.check_out_date >= CURDATE()) as current_bookings
            FROM rooms r
            INNER JOIN room_types rt ON r.room_type_id = rt.room_type_id
            ORDER BY r.floor_number DESC, r.room_number ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $stats = [
        'total_rooms' => count($rooms),
        'available' => 0,
        'occupied' => 0,
        'maintenance' => 0
    ];
    
    foreach ($rooms as $room) {
        $status = $room['status'];
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }
    
    // Return success response with rooms data
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'stats' => $stats,
        'count' => count($rooms)
    ]);
    
} catch (PDOException $e) {
    // Database error
    error_log('Database Error in get_all_rooms.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // General error
    error_log('Error in get_all_rooms.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching rooms',
        'error' => $e->getMessage()
    ]);
}
?>
