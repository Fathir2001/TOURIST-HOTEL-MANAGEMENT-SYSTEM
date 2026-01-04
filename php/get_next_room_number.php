<?php
/**
 * Get Next Room Number
 * Auto-generates the next room number based on existing rooms of selected type
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
            'message' => 'Invalid room type ID'
        ]);
        exit;
    }
    
    // Get room type info
    $typeQuery = $conn->prepare("SELECT type_name FROM room_types WHERE room_type_id = ?");
    $typeQuery->execute([$room_type_id]);
    $roomType = $typeQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$roomType) {
        echo json_encode([
            'success' => false,
            'message' => 'Room type not found'
        ]);
        exit;
    }
    
    // Get existing room numbers for this room type
    $roomsQuery = $conn->prepare("SELECT room_number FROM rooms WHERE room_type_id = ? ORDER BY room_number ASC");
    $roomsQuery->execute([$room_type_id]);
    $existingRooms = $roomsQuery->fetchAll(PDO::FETCH_COLUMN);
    
    $nextRoomNumber = '';
    $message = '';
    
    if (empty($existingRooms)) {
        // No rooms exist for this type - suggest based on room type
        $typeName = $roomType['type_name'];
        
        // Logic for suggesting first room number based on room type
        if (stripos($typeName, 'Presidential') !== false) {
            $nextRoomNumber = '401';
            $message = 'First Presidential Suite room - starting from 401';
        } elseif (stripos($typeName, 'Heritage') !== false) {
            $nextRoomNumber = '301';
            $message = 'First Heritage Suite room - starting from 301';
        } elseif (stripos($typeName, 'Garden Wing') !== false) {
            $nextRoomNumber = 'G01';
            $message = 'First Garden Wing room - starting from G01';
        } elseif (stripos($typeName, 'Deluxe') !== false) {
            $nextRoomNumber = '201';
            $message = 'First Deluxe room - starting from 201';
        } elseif (stripos($typeName, 'Triple') !== false) {
            $nextRoomNumber = '101';
            $message = 'First Triple room - starting from 101';
        } else {
            // Default for Standard Double or other types
            $nextRoomNumber = '107';
            $message = 'First room of this type - starting from 107';
        }
    } else {
        // Rooms exist - analyze pattern and increment
        $lastRoom = end($existingRooms);
        
        // Check if room number has letter prefix (like G01, G02)
        if (preg_match('/^([A-Z]+)(\d+)$/', $lastRoom, $matches)) {
            $prefix = $matches[1];
            $number = intval($matches[2]);
            $nextNumber = $number + 1;
            $nextRoomNumber = $prefix . str_pad($nextNumber, strlen($matches[2]), '0', STR_PAD_LEFT);
            $message = "Auto-generated from last room: {$lastRoom}";
        } 
        // Check if room number is purely numeric (like 101, 201, 401)
        elseif (preg_match('/^\d+$/', $lastRoom)) {
            $number = intval($lastRoom);
            $nextNumber = $number + 1;
            $nextRoomNumber = (string)$nextNumber;
            $message = "Auto-generated from last room: {$lastRoom}";
        }
        // Mixed or unknown pattern - try to increment
        else {
            // Extract any numbers from the room number
            preg_match_all('/\d+/', $lastRoom, $matches);
            if (!empty($matches[0])) {
                $number = intval(end($matches[0]));
                $nextNumber = $number + 1;
                $nextRoomNumber = preg_replace('/\d+/', $nextNumber, $lastRoom, 1);
                $message = "Auto-generated from last room: {$lastRoom}";
            } else {
                // Fallback if no pattern detected
                $nextRoomNumber = $lastRoom . '-1';
                $message = "Could not detect pattern - manual adjustment may be needed";
            }
        }
    }
    
    // Check if generated room number already exists (shouldn't happen, but double-check)
    $checkQuery = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
    $checkQuery->execute([$nextRoomNumber]);
    if ($checkQuery->fetch()) {
        // Room number exists, add suffix
        $nextRoomNumber .= 'A';
        $message .= ' (adjusted to avoid duplicate)';
    }
    
    echo json_encode([
        'success' => true,
        'next_room_number' => $nextRoomNumber,
        'message' => $message,
        'existing_count' => count($existingRooms),
        'room_type' => $roomType['type_name']
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
