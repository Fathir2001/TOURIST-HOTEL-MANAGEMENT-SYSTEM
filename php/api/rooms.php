<?php
/**
 * RESTful Rooms API
 * Handles: GET (read), POST (create), PUT (update), DELETE (delete)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/database.php';
require_once '../config/api_helpers.php';

setCorsHeaders();
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $method = getRequestMethod();
    
    // Require authentication
    requireAuth();
    
    switch ($method) {
        case 'GET':
            handleGetRooms($conn);
            break;
        case 'POST':
            handleCreateRoom($conn);
            break;
        case 'PUT':
        case 'PATCH':
            handleUpdateRoom($conn);
            break;
        case 'DELETE':
            handleDeleteRoom($conn);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
}

/**
 * GET - Retrieve rooms
 */
function handleGetRooms($conn) {
    // Get specific room by ID
    if (isset($_GET['id'])) {
        $roomId = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT r.*, rt.type_name, rt.base_price, rt.description
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.room_type_id
            WHERE r.room_id = ?
        ");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($room) {
            sendSuccessResponse($room);
        } else {
            sendErrorResponse('Room not found', 404);
        }
        return;
    }
    
    // Filter parameters
    $status = $_GET['status'] ?? null;
    $roomTypeId = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : null;
    $floor = isset($_GET['floor']) ? (int)$_GET['floor'] : null;
    
    // Build query
    $sql = "
        SELECT r.*, 
               rt.type_name, 
               rt.description,
               rt.base_price,
               rt.max_occupancy,
               rt.bed_type,
               rt.size_sqm,
               rt.badge_label,
               rt.image_url,
               (SELECT COUNT(*) FROM bookings b 
                WHERE b.room_id = r.room_id 
                AND b.status IN ('confirmed', 'checked_in')) as current_bookings
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND r.status = ?";
        $params[] = $status;
    }
    
    if ($roomTypeId) {
        $sql .= " AND r.room_type_id = ?";
        $params[] = $roomTypeId;
    }
    
    if ($floor) {
        $sql .= " AND r.floor_number = ?";
        $params[] = $floor;
    }
    
    $sql .= " ORDER BY r.room_number";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendSuccessResponse($rooms);
}

/**
 * POST - Create new room
 */
function handleCreateRoom($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['room_number', 'room_type_id', 'floor_number']);
    
    // Check if room number already exists
    $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
    $stmt->execute([$data['room_number']]);
    
    if ($stmt->fetch()) {
        sendErrorResponse('Room number already exists', 409);
    }
    
    // Verify room type exists
    $stmt = $conn->prepare("SELECT room_type_id FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$data['room_type_id']]);
    
    if (!$stmt->fetch()) {
        sendErrorResponse('Invalid room type', 400);
    }
    
    // Insert room
    $stmt = $conn->prepare("
        INSERT INTO rooms (room_number, room_type_id, floor_number, view_type, status)
        VALUES (?, ?, ?, ?, 'available')
    ");
    
    $stmt->execute([
        $data['room_number'],
        $data['room_type_id'],
        $data['floor_number'],
        $data['view_type'] ?? null
    ]);
    
    $roomId = $conn->lastInsertId();
    
    sendSuccessResponse([
        'room_id' => $roomId,
        'room_number' => $data['room_number']
    ], 'Room created successfully', 201);
}

/**
 * PUT/PATCH - Update room
 */
function handleUpdateRoom($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['room_id']);
    
    $roomId = $data['room_id'];
    
    // Check if room exists
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    
    if (!$stmt->fetch()) {
        sendErrorResponse('Room not found', 404);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    if (isset($data['status'])) {
        // Validate status
        $validStatuses = ['available', 'occupied', 'maintenance', 'cleaning'];
        if (!in_array($data['status'], $validStatuses)) {
            sendErrorResponse('Invalid status. Must be: available, occupied, maintenance, or cleaning', 400);
        }
        $updates[] = "status = ?";
        $params[] = $data['status'];
    }
    
    if (isset($data['room_number'])) {
        // Check if new room number is available
        $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ? AND room_id != ?");
        $stmt->execute([$data['room_number'], $roomId]);
        if ($stmt->fetch()) {
            sendErrorResponse('Room number already exists', 409);
        }
        $updates[] = "room_number = ?";
        $params[] = $data['room_number'];
    }
    
    if (isset($data['room_type_id'])) {
        // Verify room type exists
        $stmt = $conn->prepare("SELECT room_type_id FROM room_types WHERE room_type_id = ?");
        $stmt->execute([$data['room_type_id']]);
        if (!$stmt->fetch()) {
            sendErrorResponse('Invalid room type', 400);
        }
        $updates[] = "room_type_id = ?";
        $params[] = $data['room_type_id'];
    }
    
    if (isset($data['floor_number'])) {
        $updates[] = "floor_number = ?";
        $params[] = $data['floor_number'];
    }
    
    if (isset($data['view_type'])) {
        $updates[] = "view_type = ?";
        $params[] = $data['view_type'];
    }
    
    if (empty($updates)) {
        sendErrorResponse('No updates provided', 400);
    }
    
    $params[] = $roomId;
    $sql = "UPDATE rooms SET " . implode(", ", $updates) . " WHERE room_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    sendSuccessResponse(null, 'Room updated successfully');
}

/**
 * DELETE - Delete room
 */
function handleDeleteRoom($conn) {
    $data = getRequestData();
    $roomId = isset($_GET['id']) ? (int)$_GET['id'] : ($data['room_id'] ?? null);
    
    if (!$roomId) {
        sendErrorResponse('Room ID is required', 400);
    }
    
    // Check if room exists
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    
    if (!$stmt->fetch()) {
        sendErrorResponse('Room not found', 404);
    }
    
    // Check for active bookings
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM bookings 
        WHERE room_id = ? AND booking_status IN ('confirmed', 'checked_in')
    ");
    $stmt->execute([$roomId]);
    
    if ($stmt->fetchColumn() > 0) {
        sendErrorResponse('Cannot delete room with active bookings', 400);
    }
    
    // Delete room
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    
    sendSuccessResponse(null, 'Room deleted successfully');
}
?>
