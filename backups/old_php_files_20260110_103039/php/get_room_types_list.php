<?php
/**
 * Get Room Types List
 * Returns all room types for dropdown selection
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
    
    // Prepare SQL query to fetch all active room types
    $sql = "SELECT 
                room_type_id,
                type_name,
                base_price,
                max_occupancy,
                bed_type
            FROM room_types
            WHERE status = 1
            ORDER BY base_price DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'room_types' => $roomTypes
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'room_types' => []
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'room_types' => []
    ]);
}
