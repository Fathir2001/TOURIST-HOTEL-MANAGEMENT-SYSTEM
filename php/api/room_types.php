<?php
/**
 * RESTful Room Types API
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
    
    // Require authentication for POST, PUT, DELETE
    if ($method !== 'GET') {
        requireAuth();
    }
    
    switch ($method) {
        case 'GET':
            handleGetRoomTypes($conn);
            break;
        case 'POST':
            handleCreateRoomType($conn);
            break;
        case 'PUT':
        case 'PATCH':
            handleUpdateRoomType($conn);
            break;
        case 'DELETE':
            handleDeleteRoomType($conn);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
}

/**
 * GET - Retrieve room types
 */
function handleGetRoomTypes($conn) {
    // Get specific room type by ID
    if (isset($_GET['id'])) {
        $roomTypeId = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT rt.*, COUNT(r.room_id) as total_rooms,
                   SUM(CASE WHEN r.status = 'available' THEN 1 ELSE 0 END) as available_rooms
            FROM room_types rt
            LEFT JOIN rooms r ON rt.room_type_id = r.room_type_id
            WHERE rt.room_type_id = ?
            GROUP BY rt.room_type_id
        ");
        $stmt->execute([$roomTypeId]);
        $roomType = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($roomType) {
            sendSuccessResponse($roomType);
        } else {
            sendErrorResponse('Room type not found', 404);
        }
        return;
    }
    
    // Get all room types with availability info
    $stmt = $conn->query("
        SELECT rt.*, COUNT(r.room_id) as total_rooms,
               SUM(CASE WHEN r.status = 'available' THEN 1 ELSE 0 END) as available_rooms
        FROM room_types rt
        LEFT JOIN rooms r ON rt.room_type_id = r.room_type_id
        GROUP BY rt.room_type_id
        ORDER BY rt.base_price ASC
    ");
    
    $roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendSuccessResponse($roomTypes);
}

/**
 * POST - Create new room type
 */
function handleCreateRoomType($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['type_name', 'base_price', 'max_occupancy']);
    
    // Check if room type name already exists
    $stmt = $conn->prepare("SELECT room_type_id FROM room_types WHERE type_name = ?");
    $stmt->execute([$data['type_name']]);
    
    if ($stmt->fetch()) {
        sendErrorResponse('Room type name already exists', 409);
    }
    
    // Insert room type
    $stmt = $conn->prepare("
        INSERT INTO room_types (type_name, description, base_price, max_occupancy, size_sqm, amenities, image_url)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['type_name'],
        $data['description'] ?? null,
        $data['base_price'],
        $data['max_occupancy'],
        $data['size_sqm'] ?? null,
        $data['amenities'] ?? null,
        $data['image_url'] ?? null
    ]);
    
    $roomTypeId = $conn->lastInsertId();
    
    sendSuccessResponse([
        'room_type_id' => $roomTypeId,
        'type_name' => $data['type_name']
    ], 'Room type created successfully', 201);
}

/**
 * PUT/PATCH - Update room type
 */
function handleUpdateRoomType($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['room_type_id']);
    
    $roomTypeId = $data['room_type_id'];
    
    // Check if room type exists
    $stmt = $conn->prepare("SELECT * FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    
    if (!$stmt->fetch()) {
        sendErrorResponse('Room type not found', 404);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    if (isset($data['type_name'])) {
        // Check if new name is available
        $stmt = $conn->prepare("SELECT room_type_id FROM room_types WHERE type_name = ? AND room_type_id != ?");
        $stmt->execute([$data['type_name'], $roomTypeId]);
        if ($stmt->fetch()) {
            sendErrorResponse('Room type name already exists', 409);
        }
        $updates[] = "type_name = ?";
        $params[] = $data['type_name'];
    }
    
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    
    if (isset($data['base_price'])) {
        if ($data['base_price'] <= 0) {
            sendErrorResponse('Base price must be greater than 0', 400);
        }
        $updates[] = "base_price = ?";
        $params[] = $data['base_price'];
    }
    
    if (isset($data['max_occupancy'])) {
        if ($data['max_occupancy'] <= 0) {
            sendErrorResponse('Max occupancy must be greater than 0', 400);
        }
        $updates[] = "max_occupancy = ?";
        $params[] = $data['max_occupancy'];
    }
    
    if (isset($data['size_sqm'])) {
        $updates[] = "size_sqm = ?";
        $params[] = $data['size_sqm'];
    }
    
    if (isset($data['amenities'])) {
        $updates[] = "amenities = ?";
        $params[] = $data['amenities'];
    }
    
    if (isset($data['image_url'])) {
        $updates[] = "image_url = ?";
        $params[] = $data['image_url'];
    }
    
    if (empty($updates)) {
        sendErrorResponse('No updates provided', 400);
    }
    
    $params[] = $roomTypeId;
    $sql = "UPDATE room_types SET " . implode(", ", $updates) . " WHERE room_type_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    sendSuccessResponse(null, 'Room type updated successfully');
}

/**
 * DELETE - Delete room type
 */
function handleDeleteRoomType($conn) {
    $data = getRequestData();
    $roomTypeId = isset($_GET['id']) ? (int)$_GET['id'] : ($data['room_type_id'] ?? null);
    
    if (!$roomTypeId) {
        sendErrorResponse('Room type ID is required', 400);
    }
    
    // Check if room type exists
    $stmt = $conn->prepare("SELECT * FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    
    if (!$stmt->fetch()) {
        sendErrorResponse('Room type not found', 404);
    }
    
    // Check if there are rooms with this type
    $stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    
    if ($stmt->fetchColumn() > 0) {
        sendErrorResponse('Cannot delete room type that has associated rooms', 400);
    }
    
    // Delete room type
    $stmt = $conn->prepare("DELETE FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    
    sendSuccessResponse(null, 'Room type deleted successfully');
}
?>
