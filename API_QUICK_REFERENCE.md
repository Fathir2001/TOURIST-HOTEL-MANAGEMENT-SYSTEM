# 🚀 REST API Quick Reference Card
## Tourist Hotel Management System

**Last Updated:** January 10, 2026  
**Version:** 2.0

---

## 📍 Base URL
```
http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/
```

---

## 🔐 Authentication
- **Admin endpoints** require active session (`$_SESSION['admin_id']`)
- **Public endpoints** can be accessed without authentication
- **Login first at:** `html/ADMIN.HTML` (username: admin, password: Admin@123)

---

## 📦 BOOKINGS API (`bookings.php`)

### Get All Bookings 🔒
```http
GET /bookings.php
GET /bookings.php?status=pending
GET /bookings.php?search=john&limit=10
```
**Requires:** Admin session  
**Returns:** Array of all bookings with room type details

### Get Booking by ID 🔒
```http
GET /bookings.php?id=123
```
**Requires:** Admin session  
**Returns:** Single booking object

### Get Booking by Reference 🌐
```http
GET /booking_public.php?ref=BK20260110-7236
```
**Requires:** None (public)  
**Returns:** Single booking with room type info  
**Used by:** Track Booking feature, Confirmation page

### Create Booking 🌐
```http
POST /bookings.php
Content-Type: application/json

{
  "room_type_id": 3,
  "check_in_date": "2026-01-15",
  "check_out_date": "2026-01-18",
  "adults": 2,
  "children": 1,
  "number_of_rooms": 1,
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+94771234567",
  "special_requests": "Late check-in"
}
```
**Requires:** None (public endpoint)  
**Returns:** 
```json
{
  "success": true,
  "booking_reference": "BK20260110-7236",
  "booking_id": 123
}
```

### Update Booking 🔒
```http
PATCH /bookings.php
Content-Type: application/json

{
  "booking_id": 123,
  "status": "confirmed",
  "room_id": 5
}
```
**Requires:** Admin session  
**Available Status Values:** `pending`, `confirmed`, `cancelled`, `completed`  
**Timestamps:** Automatically sets `confirmed_at` when status → confirmed, `cancelled_at` when status → cancelled

### Cancel Booking 🔒
```http
PATCH /bookings.php
Content-Type: application/json

{
  "booking_id": 123,
  "status": "cancelled",
  "cancellation_reason": "Guest requested change of dates"
}
```
**Requires:** Admin session  
**Returns:** Success message with cancellation timestamp

---

## 🛏️ ROOMS API

### Get All Rooms
```http
GET /rooms.php
GET /rooms.php?status=available
GET /rooms.php?room_type_id=3&floor=2
```

### Get Room by ID
```http
GET /rooms.php?id=5
```

### Create Room
```http
POST /rooms.php
Content-Type: application/json

{
  "room_number": "301",
  "room_type_id": 3,
  "floor_number": 3,
  "view_type": "Mountain"
}
```

### Update Room
```http
PATCH /rooms.php
Content-Type: application/json

{
  "room_id": 5,
  "status": "maintenance"
}
```

### Delete Room
```http
DELETE /rooms.php?id=5
```

---

## 🏨 ROOM TYPES API (`room_types.php`)

### Get All Room Types 🌐
```http
GET /room_types.php
```
**Requires:** None (public)

### Get Room Type by ID 🌐
```http
GET /room_types.php?id=3
```
**Requires:** None (public)

### Create Room Type 🔒
```http
POST /room_types.php
Content-Type: application/json

{
  "type_name": "Presidential Suite",
  "description": "Luxury suite...",
  "base_price": 250.00,
  "max_occupancy": 4,
  "size_sqm": 65,
  "amenities": "WiFi, TV, Mini Bar",
  "image_url": "../images/room-types/suite.jpg"
}
```
**Requires:** Admin session

### Update Room Type 🔒
```http
PATCH /room_types.php
Content-Type: application/json

{
  "room_type_id": 3,
  "base_price": 275.00,
  "image_url": "../images/room-types/room_type_3_1767854863.jfif"
}
```
**Requires:** Admin session  
**Note:** Update image using upload_room_image.php first

### Delete Room Type 🔒
```http
DELETE /room_types.php?id=3
```
**Requires:** Admin session

---

## 📤 IMAGE UPLOAD API (`upload_room_image.php`)

### Upload Room Type Image 🔒
```http
POST /upload_room_image.php
Content-Type: multipart/form-data

room_type_id: 3
image: <file>
```
**Requires:** Admin session  
**Accepts:** JPG, PNG, GIF, WEBP (max 5MB)  
**Returns:**
```json
{
  "success": true,
  "image_url": "../images/room-types/room_type_3_1767854863.jfif"
}
```
**Note:** Images stored in `images/room-types/` with unique filenames

---

## 📱 JavaScript Usage

```javascript
// Load API integration
<script src="../js/api-integration.js"></script>

// Get all bookings
const bookings = await getAllBookings();

// Get bookings with filters
const pending = await getAllBookings({ status: 'pending' });

// Get booking by reference (public)
const booking = await getBookingByReference('BK20260110-7236');

// Create booking (public)
const result = await createBooking({
  roomTypeId: 3,
  checkIn: '2026-01-15',
  checkOut: '2026-01-18',
  adults: 2,
  children: 1,
  numberOfRooms: 1,
  guestName: 'John Doe',
  guestEmail: 'john@example.com',
  guestPhone: '+94771234567'
});
console.log(result.booking_reference); // BK20260110-7236

// Update booking status (admin only)
await updateBookingStatus(123, 'confirmed', 5);

// Cancel booking with reason (admin only)
await updateBooking(123, { 
  status: 'cancelled',
  cancellation_reason: 'Guest requested refund' 
});

// Get all rooms
const rooms = await getAllRooms();

// Get available rooms
const available = await getAllRooms({ status: 'available' });

// Create room (admin only)
await createRoom({
  roomNumber: '301',
  roomTypeId: 3,
  floor: 3,
  view: 'Mountain'
});

// Update room status (admin only)
await updateRoomStatus(5, 'maintenance');

// Delete room (admin only)
await deleteRoom(5);

// Upload room type image (admin only)
const formData = new FormData();
formData.append('room_type_id', 3);
formData.append('image', fileInput.files[0]);
const imageResult = await fetch('../php/upload_room_image.php', {
  method: 'POST',
  body: formData
});
const imageData = await imageResult.json();
console.log(imageData.image_url); // Use for room type update
```

---

## 🎯 Response Format

### Success Response (Standard)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Success Response (Booking Public API)
```json
{
  "success": true,
  "booking": {
    "booking_id": 123,
    "booking_reference": "BK20260110-7236",
    "status": "confirmed",
    "room_type_name": "Deluxe Room",
    "confirmed_at": "2026-01-10 14:30:00",
    "cancelled_at": null,
    "cancellation_reason": null
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message"
}
```

---

## 📊 HTTP Status Codes

- `200 OK` - Successful GET, PUT, PATCH
- `201 Created` - Successful POST
- `400 Bad Request` - Invalid data
- `401 Unauthorized` - Not logged in
- `404 Not Found` - Resource not found
- `409 Conflict` - Duplicate resource
- `500 Internal Server Error` - Server error

---

## 🧪 Testing with cURL

```bash
# Get bookings
curl http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php

# Create booking
curl -X POST http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php \
  -H "Content-Type: application/json" \
  -d '{"room_type_id":3,"check_in_date":"2026-01-15",...}'

# Update booking
curl -X PATCH http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php \
  -H "Content-Type: application/json" \
  -d '{"booking_id":123,"booking_status":"confirmed"}'

# Delete booking
curl -X DELETE "http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php?id=123"
```

---

## 📚 Documentation Files

- **[RESTFUL_API_IMPLEMENTATION_GUIDE.md](RESTFUL_API_IMPLEMENTATION_GUIDE.md)** - Full implementation guide
- **[API_MIGRATION_TEST_GUIDE.md](API_MIGRATION_TEST_GUIDE.md)** - Testing procedures
- **[MIGRATION_COMPLETE.md](MIGRATION_COMPLETE.md)** - Migration summary

---

**Last Updated:** January 10, 2026  
**Version:** 2.0 (RESTful API)
