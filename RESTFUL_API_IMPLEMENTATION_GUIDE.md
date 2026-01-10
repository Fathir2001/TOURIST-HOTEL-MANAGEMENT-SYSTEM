# RESTful API Implementation Guide
## Tourist Hotel Management System

---

## 🎯 Overview

This guide shows how to implement proper RESTful HTTP methods (GET, POST, PUT, DELETE) in your hotel management system while maintaining **100% backward compatibility** with existing code.

---

## 📋 Implementation Strategy

### Current State (POST for Everything):
```
GET  → php/get_*.php         // Read
POST → php/process_*.php     // Create
POST → php/update_*.php      // Update
POST → php/delete_*.php      // Delete
```

### New RESTful State (Backward Compatible):
```
GET    → php/api/*.php       // Read
POST   → php/api/*.php       // Create
PUT    → php/api/*.php       // Update (full)
PATCH  → php/api/*.php       // Update (partial)
DELETE → php/api/*.php       // Delete
```

---

## 🔧 Step 1: Create API Directory Structure

```
php/
├── api/                      # New RESTful endpoints
│   ├── bookings.php         # Handles GET, POST, PUT, DELETE for bookings
│   ├── rooms.php            # Handles GET, POST, PUT, DELETE for rooms
│   ├── room_types.php       # Handles GET, POST, PUT, DELETE for room types
│   ├── dashboard.php        # Handles GET for dashboard stats
│   └── auth.php             # Handles POST for login
├── config/
│   ├── api_helpers.php      # New: REST helper functions
│   └── cors.php             # New: CORS configuration
└── [existing files remain unchanged]
```

---

## 🛠️ Step 2: Create Helper Functions

### File: `php/config/api_helpers.php`

```php
<?php
/**
 * RESTful API Helper Functions
 * Provides utilities for handling REST requests
 */

/**
 * Get the HTTP method (handles method override)
 */
function getRequestMethod() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Support method override for browsers that don't support PUT/DELETE
    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    }
    
    // Check for X-HTTP-Method-Override header
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }
    
    return $method;
}

/**
 * Get request body data (works for all methods)
 */
function getRequestData() {
    $method = getRequestMethod();
    
    if ($method === 'GET') {
        return $_GET;
    }
    
    if ($method === 'POST' && !empty($_POST)) {
        return $_POST;
    }
    
    // For PUT, DELETE, PATCH - read from php://input
    $input = file_get_contents('php://input');
    
    // Try to parse as JSON
    $data = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }
    
    // Try to parse as form data
    parse_str($input, $data);
    return $data;
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function sendErrorResponse($message, $statusCode = 400, $details = null) {
    $response = [
        'success' => false,
        'error' => $message
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Send success response
 */
function sendSuccessResponse($data = null, $message = 'Success', $statusCode = 200) {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Validate required fields
 */
function validateRequiredFields($data, $requiredFields) {
    $missing = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendErrorResponse('Missing required fields: ' . implode(', ', $missing), 400);
    }
}

/**
 * Set CORS headers
 */
function setCorsHeaders() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        }
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        
        exit(0);
    }
}
?>
```

---

## 📝 Step 3: Create RESTful Endpoints

### Example: `php/api/bookings.php`

```php
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
            handleGetBookings($conn);
            break;
            
        case 'POST':
            handleCreateBooking($conn);
            break;
            
        case 'PUT':
        case 'PATCH':
            handleUpdateBooking($conn);
            break;
            
        case 'DELETE':
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
    
    // Build query
    $sql = "
        SELECT b.*, rt.type_name, r.room_number 
        FROM bookings b
        LEFT JOIN room_types rt ON b.room_type_id = rt.room_type_id
        LEFT JOIN rooms r ON b.room_id = r.room_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND b.booking_status = ?";
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
    
    sendSuccessResponse($bookings);
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
    
    // Get room price
    $stmt = $conn->prepare("SELECT base_price FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$data['room_type_id']]);
    $roomPrice = $stmt->fetchColumn();
    
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
            booking_status, payment_status, special_requests
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
        'total_amount' => $total
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
    
    // Update booking status
    if (isset($data['booking_status'])) {
        $sql = "UPDATE bookings SET booking_status = ? WHERE booking_id = ?";
        $params = [$data['booking_status'], $bookingId];
        
        // If confirming, assign room
        if ($data['booking_status'] === 'confirmed' && isset($data['room_id'])) {
            $sql = "UPDATE bookings SET booking_status = ?, room_id = ? WHERE booking_id = ?";
            $params = [$data['booking_status'], $data['room_id'], $bookingId];
            
            // Update room status to occupied
            $stmt = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE room_id = ?");
            $stmt->execute([$data['room_id']]);
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        sendSuccessResponse(null, 'Booking updated successfully');
    }
    
    sendErrorResponse('No updates provided', 400);
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
    
    // Soft delete - set status to cancelled
    $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    
    // If room was assigned, set it back to available
    if ($booking['room_id']) {
        $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE room_id = ?");
        $stmt->execute([$booking['room_id']]);
    }
    
    sendSuccessResponse(null, 'Booking cancelled successfully');
}
?>
```

---

## 📝 Step 4: Create More API Endpoints

### `php/api/rooms.php`

```php
<?php
require_once '../config/database.php';
require_once '../config/api_helpers.php';

setCorsHeaders();
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $method = getRequestMethod();
    
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

function handleGetRooms($conn) {
    if (isset($_GET['id'])) {
        $roomId = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT r.*, rt.type_name, rt.base_price 
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
    
    $stmt = $conn->query("
        SELECT r.*, rt.type_name, rt.base_price 
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        ORDER BY r.room_number
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendSuccessResponse($rooms);
}

function handleCreateRoom($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['room_number', 'room_type_id', 'floor_number']);
    
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
    
    sendSuccessResponse(['room_id' => $conn->lastInsertId()], 'Room created successfully', 201);
}

function handleUpdateRoom($conn) {
    $data = getRequestData();
    validateRequiredFields($data, ['room_id']);
    
    $roomId = $data['room_id'];
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    if (isset($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
    }
    
    if (isset($data['room_number'])) {
        $updates[] = "room_number = ?";
        $params[] = $data['room_number'];
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

function handleDeleteRoom($conn) {
    $data = getRequestData();
    $roomId = isset($_GET['id']) ? (int)$_GET['id'] : ($data['room_id'] ?? null);
    
    if (!$roomId) {
        sendErrorResponse('Room ID is required', 400);
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
    
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    
    sendSuccessResponse(null, 'Room deleted successfully');
}
?>
```

---

## 🌐 Step 5: Frontend JavaScript Examples

### Using the New RESTful API

```javascript
// ============================================
// GET - Retrieve Data
// ============================================

// Get all bookings
async function getAllBookings() {
    const response = await fetch('php/api/bookings.php');
    const result = await response.json();
    
    if (result.success) {
        console.log('Bookings:', result.data);
        return result.data;
    }
}

// Get specific booking
async function getBooking(bookingId) {
    const response = await fetch(`php/api/bookings.php?id=${bookingId}`);
    const result = await response.json();
    return result.data;
}

// Get bookings with filters
async function getFilteredBookings(status) {
    const response = await fetch(`php/api/bookings.php?status=${status}&limit=10`);
    const result = await response.json();
    return result.data;
}

// ============================================
// POST - Create New Resource
// ============================================

async function createBooking(bookingData) {
    const response = await fetch('php/api/bookings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            room_type_id: bookingData.roomTypeId,
            check_in_date: bookingData.checkIn,
            check_out_date: bookingData.checkOut,
            adults: bookingData.adults,
            children: bookingData.children,
            guest_name: bookingData.guestName,
            guest_email: bookingData.guestEmail,
            guest_phone: bookingData.guestPhone,
            special_requests: bookingData.specialRequests
        })
    });
    
    const result = await response.json();
    
    if (result.success) {
        console.log('Booking created:', result.data.booking_reference);
        return result.data;
    } else {
        console.error('Error:', result.error);
        throw new Error(result.error);
    }
}

// ============================================
// PUT - Full Update (Replace entire resource)
// ============================================

async function updateBookingFull(bookingId, bookingData) {
    const response = await fetch('php/api/bookings.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            booking_status: bookingData.status,
            room_id: bookingData.roomId,
            // ... all fields
        })
    });
    
    const result = await response.json();
    return result;
}

// ============================================
// PATCH - Partial Update (Update specific fields)
// ============================================

async function updateBookingStatus(bookingId, status, roomId = null) {
    const data = {
        booking_id: bookingId,
        booking_status: status
    };
    
    if (roomId) {
        data.room_id = roomId;
    }
    
    const response = await fetch('php/api/bookings.php', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
        console.log('Booking status updated');
        return result;
    } else {
        throw new Error(result.error);
    }
}

// ============================================
// DELETE - Remove Resource
// ============================================

async function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    const response = await fetch(`php/api/bookings.php?id=${bookingId}`, {
        method: 'DELETE'
    });
    
    const result = await response.json();
    
    if (result.success) {
        console.log('Booking cancelled');
        return result;
    } else {
        throw new Error(result.error);
    }
}

// Alternative: Send ID in body
async function cancelBookingAlt(bookingId) {
    const response = await fetch('php/api/bookings.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ booking_id: bookingId })
    });
    
    return await response.json();
}

// ============================================
// Rooms API Examples
// ============================================

// Get all rooms
async function getAllRooms() {
    const response = await fetch('php/api/rooms.php');
    const result = await response.json();
    return result.data;
}

// Create new room
async function createRoom(roomData) {
    const response = await fetch('php/api/rooms.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            room_number: roomData.roomNumber,
            room_type_id: roomData.roomTypeId,
            floor_number: roomData.floor,
            view_type: roomData.view
        })
    });
    
    return await response.json();
}

// Update room status
async function updateRoomStatus(roomId, status) {
    const response = await fetch('php/api/rooms.php', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            room_id: roomId,
            status: status
        })
    });
    
    return await response.json();
}

// Delete room
async function deleteRoom(roomId) {
    const response = await fetch(`php/api/rooms.php?id=${roomId}`, {
        method: 'DELETE'
    });
    
    return await response.json();
}
```

---

## 🔄 Step 6: Backward Compatibility Layer

To maintain backward compatibility, your old files can continue working. You can gradually migrate:

### Option 1: Proxy Old Files to New API

```php
<?php
// php/update_booking_status.php (OLD FILE - proxies to new API)

// Proxy to new RESTful API
$data = [
    'booking_id' => $_POST['booking_id'],
    'booking_status' => $_POST['new_status'],
    'room_id' => $_POST['room_id'] ?? null
];

// Call new API internally
$ch = curl_init('http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
```

### Option 2: Gradual Migration

Keep both old and new systems running:
- **Old:** `php/update_booking_status.php` (POST)
- **New:** `php/api/bookings.php` (PUT/PATCH)

Update frontend code gradually, one feature at a time.

---

## 📊 HTTP Status Codes

Use proper status codes:

```php
// Success
200 OK              // Successful GET, PUT, PATCH
201 Created         // Successful POST (resource created)
204 No Content      // Successful DELETE

// Client Errors
400 Bad Request     // Invalid data
401 Unauthorized    // Not logged in
403 Forbidden       // Logged in but no permission
404 Not Found       // Resource doesn't exist
405 Method Not Allowed  // Wrong HTTP method
409 Conflict        // Duplicate resource

// Server Errors
500 Internal Server Error  // Server-side error
503 Service Unavailable    // Server temporarily down
```

---

## 🧪 Testing the API

### Using cURL:

```bash
# GET - Get all bookings
curl http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php

# GET - Get specific booking
curl http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php?id=1

# POST - Create booking
curl -X POST http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php \
  -H "Content-Type: application/json" \
  -d '{"room_type_id":3,"check_in_date":"2026-01-15","check_out_date":"2026-01-18","adults":2,"guest_name":"John Doe","guest_email":"john@example.com","guest_phone":"+1234567890"}'

# PATCH - Update booking status
curl -X PATCH http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php \
  -H "Content-Type: application/json" \
  -d '{"booking_id":1,"booking_status":"confirmed","room_id":5}'

# DELETE - Cancel booking
curl -X DELETE "http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php?id=1"
```

### Using Postman:
1. Create new request
2. Set method (GET, POST, PUT, DELETE)
3. Enter URL: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php`
4. For POST/PUT/PATCH: Add JSON body in Body → raw → JSON
5. Click Send

---

## 🚀 Complete Implementation Checklist

- [ ] Create `php/config/api_helpers.php`
- [ ] Create `php/api/` directory
- [ ] Create `php/api/bookings.php`
- [ ] Create `php/api/rooms.php`
- [ ] Create `php/api/room_types.php`
- [ ] Create `php/api/dashboard.php`
- [ ] Update frontend JavaScript to use new endpoints
- [ ] Test all CRUD operations
- [ ] Add authentication middleware
- [ ] Add rate limiting (optional)
- [ ] Update documentation

---

## 🔐 Security Enhancements

```php
// Add authentication check
function requireAuth() {
    session_start();
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['logged_in'])) {
        sendErrorResponse('Unauthorized', 401);
    }
}

// Add to each API file
requireAuth(); // Call this before routing

// Rate limiting (simple version)
function checkRateLimit() {
    session_start();
    $key = 'api_calls_' . session_id();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + 60];
    }
    
    if (time() > $_SESSION[$key]['reset']) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + 60];
    }
    
    $_SESSION[$key]['count']++;
    
    if ($_SESSION[$key]['count'] > 60) { // 60 requests per minute
        sendErrorResponse('Rate limit exceeded', 429);
    }
}
```

---

## ✅ Benefits of RESTful Implementation

1. **Semantic Clarity**: HTTP methods describe the action
2. **Better Caching**: GET requests can be cached
3. **Standard Compliance**: Follows REST principles
4. **API Documentation**: Self-documenting endpoints
5. **Frontend Frameworks**: Works better with React, Vue, Angular
6. **Mobile Apps**: Easier to integrate with mobile applications
7. **Backward Compatible**: Old code still works

---

## 📚 Next Steps

1. Implement the helper functions
2. Create API endpoints one by one
3. Test each endpoint thoroughly
4. Update frontend code gradually
5. Add authentication and validation
6. Monitor and optimize performance

---

**Implementation Date:** January 10, 2026  
**Last Updated:** January 10, 2026

---

For questions or issues, refer to TECHNICAL_ARCHITECTURE.md for architectural details.
