# 🚀 REST API Quick Reference Card
## Tourist Hotel Management System

---

## 📍 Base URL
```
http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/
```

---

## 🔐 Authentication
All endpoints require admin session (except GET room_types)
**Login first at:** `html/ADMIN.HTML`

---

## 📦 BOOKINGS API

### Get All Bookings
```http
GET /bookings.php
GET /bookings.php?status=pending
GET /bookings.php?search=john&limit=10
```

### Get Booking by ID
```http
GET /bookings.php?id=123
```

### Create Booking
```http
POST /bookings.php
Content-Type: application/json

{
  "room_type_id": 3,
  "check_in_date": "2026-01-15",
  "check_out_date": "2026-01-18",
  "adults": 2,
  "children": 1,
  "guest_name": "John Doe",
  "guest_email": "john@example.com",
  "guest_phone": "+94771234567",
  "special_requests": "Late check-in"
}
```

### Update Booking
```http
PATCH /bookings.php
Content-Type: application/json

{
  "booking_id": 123,
  "booking_status": "confirmed",
  "room_id": 5
}
```

### Cancel Booking
```http
DELETE /bookings.php?id=123
```

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

## 🏨 ROOM TYPES API

### Get All Room Types
```http
GET /room_types.php
```

### Get Room Type by ID
```http
GET /room_types.php?id=3
```

### Create Room Type
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
  "image_url": "../images/suite.jpg"
}
```

### Update Room Type
```http
PATCH /room_types.php
Content-Type: application/json

{
  "room_type_id": 3,
  "base_price": 275.00
}
```

### Delete Room Type
```http
DELETE /room_types.php?id=3
```

---

## 📱 JavaScript Usage

```javascript
// Load API integration
<script src="../js/api-integration.js"></script>

// Get all bookings
const bookings = await getAllBookings();

// Get bookings with filters
const pending = await getAllBookings({ status: 'pending' });

// Create booking
const result = await createBooking({
  roomTypeId: 3,
  checkIn: '2026-01-15',
  checkOut: '2026-01-18',
  adults: 2,
  guestName: 'John Doe',
  guestEmail: 'john@example.com',
  guestPhone: '+94771234567'
});

// Update booking status
await updateBookingStatus(123, 'confirmed', 5);

// Cancel booking
await cancelBooking(123);

// Get all rooms
const rooms = await getAllRooms();

// Get available rooms
const available = await getAllRooms({ status: 'available' });

// Create room
await createRoom({
  roomNumber: '301',
  roomTypeId: 3,
  floor: 3,
  view: 'Mountain'
});

// Update room status
await updateRoomStatus(5, 'maintenance');

// Delete room
await deleteRoom(5);
```

---

## 🎯 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
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
