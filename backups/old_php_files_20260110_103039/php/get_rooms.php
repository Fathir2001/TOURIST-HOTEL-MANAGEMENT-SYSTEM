<?php
/**
 * Get Rooms Data from Database
 * Returns room types with all details for accommodation page
 */

require_once 'config/database.php';

function getRoomTypes() {
    try {
        $conn = getDatabaseConnection();
        
        $sql = "SELECT 
                    room_type_id,
                    type_name,
                    description,
                    base_price,
                    max_occupancy,
                    size_sqm,
                    bed_type,
                    amenities,
                    badge_label,
                    features,
                    image_url,
                    status
                FROM room_types 
                WHERE status = 1 
                ORDER BY base_price DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($rooms as &$room) {
            $room['amenities'] = json_decode($room['amenities'], true);
            $room['features'] = json_decode($room['features'], true);
            // Convert sqm to sq ft for display
            $room['size_sqft'] = round($room['size_sqm'] * 10.764, 0);
        }
        
        return $rooms;
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return [];
    }
}

function getRoomTypeById($roomTypeId) {
    try {
        $conn = getDatabaseConnection();
        
        $sql = "SELECT 
                    room_type_id,
                    type_name,
                    description,
                    base_price,
                    max_occupancy,
                    size_sqm,
                    bed_type,
                    amenities,
                    badge_label,
                    features,
                    image_url,
                    status
                FROM room_types 
                WHERE room_type_id = ? AND status = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$roomTypeId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($room) {
            $room['amenities'] = json_decode($room['amenities'], true);
            $room['features'] = json_decode($room['features'], true);
            $room['size_sqft'] = round($room['size_sqm'] * 10.764, 0);
        }
        
        return $room;
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return null;
    }
}

function getAvailableRoomCount($roomTypeId) {
    try {
        $conn = getDatabaseConnection();
        
        $sql = "SELECT COUNT(*) as available_count 
                FROM rooms 
                WHERE room_type_id = ? AND status = 'available'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$roomTypeId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['available_count'];
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return 0;
    }
}

// Return as JSON if requested via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_rooms') {
    header('Content-Type: application/json');
    echo json_encode(getRoomTypes());
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get_room' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(getRoomTypeById($_GET['id']));
    exit;
}
?>
