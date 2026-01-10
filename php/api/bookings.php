<?php
/**
 * RESTful Bookings API
 * Handles: GET (read), POST (create), PUT (update), DELETE (delete)
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
    
    // Get HTTP method
    $method = getRequestMethod();
    
    // Route to appropriate handler
    switch ($method) {
        case 'GET':
            // Require authentication for viewing bookings
            requireAuth();
            handleGetBookings($conn);
            break;
            
        case 'POST':
            // Public endpoint - no auth required for guest bookings
            handleCreateBooking($conn);
            break;
            
        case 'PUT':
        case 'PATCH':
            // Require authentication for updates
            requireAuth();
            handleUpdateBooking($conn);
            break;
            
        case 'DELETE':
            // Require authentication for deletes
            requireAuth();
            handleDeleteBooking($conn);
            break;
            
        default:
            sendErrorResponse('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    sendErrorResponse('Server error: ' . $e->getMessage(), 500);
}

/**
 * GET - Retrieve bookings
 */
function handleGetBookings($conn) {
    // Get query parameters
    $status = $_GET['status'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Get specific booking by ID
    if (isset($_GET['id'])) {
        $bookingId = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT b.*, rt.type_name, r.room_number 
            FROM bookings b
            LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
            LEFT JOIN rooms r ON b.room_id = r.room_id
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            sendSuccessResponse($booking);
        } else {
            sendErrorResponse('Booking not found', 404);
        }
        return;
    }
    
    // Build query for multiple bookings
    $sql = "
        SELECT b.*, rt.type_name, r.room_number 
        FROM bookings b
        LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
        LEFT JOIN rooms r ON b.room_id = r.room_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }
    
    if ($search) {
        $sql .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM bookings b WHERE 1=1";
    $countParams = [];
    
    if ($status) {
        $countSql .= " AND b.status = ?";
        $countParams[] = $status;
    }
    
    if ($search) {
        $countSql .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ?)";
        $searchParam = "%$search%";
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
    }
    
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetchColumn();
    
    sendSuccessResponse([
        'bookings' => $bookings,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
}

/**
 * POST - Create new booking
 */
function handleCreateBooking($conn) {
    $data = getRequestData();
    
    // Validate required fields
    validateRequiredFields($data, [
        'room_type_id', 'check_in_date', 'check_out_date',
        'adults', 'guest_name', 'guest_email', 'guest_phone'
    ]);
    
    // Generate booking reference
    $bookingReference = 'BK' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Calculate nights and pricing
    $checkIn = new DateTime($data['check_in_date']);
    $checkOut = new DateTime($data['check_out_date']);
    $nights = $checkIn->diff($checkOut)->days;
    
    if ($nights <= 0) {
        sendErrorResponse('Check-out date must be after check-in date', 400);
    }
    
    // Get room price
    $stmt = $conn->prepare("SELECT base_price FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$data['room_type_id']]);
    $roomPrice = $stmt->fetchColumn();
    
    if (!$roomPrice) {
        sendErrorResponse('Invalid room type', 400);
    }
    
    $subtotal = $roomPrice * $nights;
    $tax = $subtotal * 0.10;
    $serviceCharge = $subtotal * 0.05;
    $total = $subtotal + $tax + $serviceCharge;
    
    // Insert booking
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            booking_reference, guest_name, guest_email, guest_phone,
            room_type_id, check_in_date, check_out_date, adults, children,
            total_nights, room_price, subtotal, tax_amount, service_charge, total_amount,
            status, payment_status, special_requests
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?)
    ");
    
    $stmt->execute([
        $bookingReference,
        $data['guest_name'],
        $data['guest_email'],
        $data['guest_phone'],
        $data['room_type_id'],
        $data['check_in_date'],
        $data['check_out_date'],
        $data['adults'],
        $data['children'] ?? 0,
        $nights,
        $roomPrice,
        $subtotal,
        $tax,
        $serviceCharge,
        $total,
        $data['special_requests'] ?? null
    ]);
    
    $bookingId = $conn->lastInsertId();
    
    sendSuccessResponse([
        'booking_id' => $bookingId,
        'booking_reference' => $bookingReference,
        'total_amount' => $total,
        'total_nights' => $nights
    ], 'Booking created successfully', 201);
}

/**
 * PUT/PATCH - Update booking
 */
function handleUpdateBooking($conn) {
    $data = getRequestData();
    
    // Validate required fields
    validateRequiredFields($data, ['booking_id']);
    
    $bookingId = $data['booking_id'];
    
    // Check if booking exists
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        sendErrorResponse('Booking not found', 404);
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Update booking status
        if (isset($data['status'])) {
            $sql = "UPDATE bookings SET status = ?";
            $params = [$data['status']];
            
            // If confirming, set confirmed_at timestamp and assign room
            if ($data['status'] === 'confirmed') {
                $sql .= ", confirmed_at = NOW()";
                
                if (isset($data['room_id'])) {
                    $sql .= ", room_id = ?";
                    $params[] = $data['room_id'];
                    
                    // Update room status to occupied
                    $roomStmt = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE room_id = ?");
                    $roomStmt->execute([$data['room_id']]);
                }
            }
            
            // If checking out, free the room
            if ($data['status'] === 'checked_out' && $booking['room_id']) {
                $roomStmt = $conn->prepare("UPDATE rooms SET status = 'maintenance' WHERE room_id = ?");
                $roomStmt->execute([$booking['room_id']]);
            }
            
            // If cancelling, set cancelled_at timestamp
            if ($data['status'] === 'cancelled') {
                $sql .= ", cancelled_at = NOW()";
            }
            
            $sql .= " WHERE booking_id = ?";
            $params[] = $bookingId;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        // Update payment status
        if (isset($data['payment_status'])) {
            $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE booking_id = ?");
            $stmt->execute([$data['payment_status'], $bookingId]);
        }
        
        // Update other fields if needed
        $updateFields = [];
        $updateParams = [];
        
        if (isset($data['special_requests'])) {
            $updateFields[] = "special_requests = ?";
            $updateParams[] = $data['special_requests'];
        }
        
        if (!empty($updateFields)) {
            $updateParams[] = $bookingId;
            $sql = "UPDATE bookings SET " . implode(", ", $updateFields) . " WHERE booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($updateParams);
        }
        
        $conn->commit();
        sendSuccessResponse(null, 'Booking updated successfully');
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * DELETE - Delete/Cancel booking
 */
function handleDeleteBooking($conn) {
    $data = getRequestData();
    
    // Get booking ID from query string or body
    $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : ($data['booking_id'] ?? null);
    
    if (!$bookingId) {
        sendErrorResponse('Booking ID is required', 400);
    }
    
    // Check if booking exists
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        sendErrorResponse('Booking not found', 404);
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Soft delete - set status to cancelled
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        
        // If room was assigned, set it back to available
        if ($booking['room_id']) {
            $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE room_id = ?");
            $stmt->execute([$booking['room_id']]);
        }
        
        $conn->commit();
        sendSuccessResponse(null, 'Booking cancelled successfully');
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
?>
