# REST API Migration Test Script
## Tourist Hotel Management System

**Last Updated:** January 10, 2026  
**Version:** 2.0 (With Track Booking & Enhancements)

---

## 🧪 Comprehensive Testing Guide

This guide provides step-by-step testing procedures to verify the RESTful API migration and all new features.

---

## ✅ Pre-Testing Checklist

Before starting tests, ensure:
- [ ] WAMP/XAMPP server is running
- [ ] MySQL database is running (`hotel_management`)
- [ ] Browser Developer Tools are open (F12)
- [ ] Admin account is available (username: admin, password: Admin@123)
- [ ] Database has booking data (run test_bookings.php if needed)
- [ ] All new database columns exist (confirmed_at, cancelled_at, cancellation_reason)

---

## 📋 Test Cases

### **Test 1: Track Booking Feature (Public API)**

**Objective:** Verify Track Booking modal works on all pages

**Steps:**
1. Open any page (e.g., `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/HOME.HTML`)
2. Wait for navbar to load dynamically
3. Look for "Track Booking" link in navbar (yellow/gold color)
4. Click "Track Booking"
5. Enter a valid booking reference (e.g., `BK20260110-7236`)
6. Click "Check Status"
7. Check Network tab for: `php/api/booking_public.php?ref=BK20260110-7236`

**Expected Results:**
- ✅ Modal opens with slideDown animation
- ✅ Request method: GET
- ✅ Status code: 200
- ✅ Response format: `{success: true, booking: {...}}`
- ✅ Modal displays: booking reference, status badge, room type, dates, guest info
- ✅ If confirmed: Shows confirmation timestamp
- ✅ If cancelled: Shows cancellation reason and timestamp
- ✅ Close button works
- ✅ Click outside modal closes it

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 2: Accommodation Page - Load Room Types**

**Objective:** Verify room types load via REST API

**Steps:**
1. Open browser and navigate to: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/ACCOMMODATION.PHP`
2. Open Developer Tools → Network tab
3. Refresh the page
4. Look for request to: `php/api/room_types.php`

**Expected Results:**
- ✅ Request method: GET
- ✅ Status code: 200
- ✅ Response contains `{success: true, data: [...]}`
- ✅ Room cards display on page
- ✅ Each card shows: image (from images/room-types/), name, price, occupancy, description

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 3: Accommodation Page - Create Booking**

**Objective:** Verify booking creation via REST API

**Steps:**
1. On ACCOMMODATION.PHP, click "Book Now" on any room
2. Fill in the booking form:
   - Check-in: (select tomorrow)
   - Check-out: (select day after tomorrow)
   - Adults: 2
   - Children: 1
   - Number of Rooms: 1
   - Guest Name: Test User
   - Email: test@example.com
   - Phone: +94771234567
3. Click "Confirm Booking"
4. Check Network tab for: `php/api/bookings.php`

**Expected Results:**
- ✅ Request method: POST
- ✅ Status code: 201
- ✅ Request body includes: room_type_id, check_in_date, check_out_date, adults, children, number_of_rooms, guest_name, guest_email, guest_phone
- ✅ Response contains `{success: true, booking_reference: "BK...", booking_id: ...}`
- ✅ Alert shows success message with booking reference
- ✅ Redirect to confirmation page

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 4: Dashboard - Login**

**Objective:** Verify admin authentication

**Steps:**
1. Navigate to: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/ADMIN.HTML`
2. Enter credentials:
   - Username: admin
   - Password: Admin@123
3. Click "Login"

**Expected Results:**
- ✅ Redirect to DASHBOARD.PHP
- ✅ Dashboard loads successfully
- ✅ Welcome message shows admin name
- ✅ Track Booking link visible in navbar

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 5: Dashboard - Load Bookings**

**Objective:** Verify bookings load via REST API

**Steps:**
1. On dashboard, click "Bookings" in sidebar
2. Check Network tab for: `php/api/bookings.php`
3. Verify bookings table displays

**Expected Results:**
- ✅ Request method: GET
- ✅ Status code: 200
- ✅ Response contains booking array with pagination
- ✅ Table displays all bookings
- ✅ Each row shows: reference, guest name, email, room type, dates, status

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 5: Dashboard - Update Booking Status**

**Objective:** Verify booking status update via REST API

**Steps:**
1. On Bookings page, click edit icon (pencil) for a "pending" booking
2. Change status to "confirmed"
3. Select a room from dropdown
4. Click "Update Status"
5. Check Network tab for: `php/api/bookings.php` (PATCH request)

**Expected Results:**
- ✅ Request method: PATCH
- ✅ Status code: 200
- ✅ Response: `{success: true, message: "Booking updated successfully"}`
- ✅ Alert shows success message
- ✅ Modal closes
- ✅ Bookings table refreshes
- ✅ Booking status badge updated to "CONFIRMED"

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 6: Dashboard - Load Rooms**

**Objective:** Verify rooms load via REST API

**Steps:**
1. On dashboard, click "Rooms" in sidebar
2. Check Network tab for: `php/api/rooms.php`
3. Verify rooms display in tree view

**Expected Results:**
- ✅ Request method: GET
- ✅ Status code: 200
- ✅ Response contains rooms array
- ✅ Rooms grouped by room type
- ✅ Each room shows: number, floor, view, status

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 7: Dashboard - Create Room**

**Objective:** Verify room creation via REST API

**Steps:**
1. On Rooms page, click "+ Add New Room" button
2. Fill in form:
   - Room Number: 999
   - Room Type: (select any)
   - Floor: 3
   - View Type: Mountain
3. Click "Add Room"
4. Check Network tab for: `php/api/rooms.php` (POST request)

**Expected Results:**
- ✅ Request method: POST
- ✅ Status code: 201
- ✅ Response: `{success: true, data: {room_id, room_number}}`
- ✅ Alert shows success message
- ✅ Modal closes
- ✅ Rooms list refreshes
- ✅ New room appears in list

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 8: Dashboard - Update Room Status**

**Objective:** Verify room status update via REST API

**Steps:**
1. On Rooms page, click status badge of any "available" room
2. Change status to "maintenance"
3. Click "Update Status"
4. Check Network tab for: `php/api/rooms.php` (PATCH request)

**Expected Results:**
- ✅ Request method: PATCH
- ✅ Status code: 200
- ✅ Response: `{success: true, message: "Room updated successfully"}`
- ✅ Alert shows success message
- ✅ Modal closes
- ✅ Rooms list refreshes
- ✅ Room status badge updated to "MAINTENANCE"

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 9: Dashboard - Delete Room**

**Objective:** Verify room deletion via REST API

**Steps:**
1. On Rooms page, click trash icon for room 999 (created in Test 7)
2. Confirm deletion in alert dialog
3. Check Network tab for: `php/api/rooms.php?id=999` (DELETE request)

**Expected Results:**
- ✅ Request method: DELETE
- ✅ Status code: 200
- ✅ Response: `{success: true, message: "Room deleted successfully"}`
- ✅ Alert shows success message
- ✅ Rooms list refreshes
- ✅ Room 999 removed from list

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 10: Dashboard - Cancel Booking with Reason**

**Objective:** Verify booking cancellation with reason tracking

**Steps:**
1. On Bookings page, click edit icon for a confirmed booking
2. Change status to "cancelled"
3. Modal should prompt for cancellation reason
4. Enter reason: "Guest requested refund due to emergency"
5. Click "Update Status"
6. Check Network tab for: `php/api/bookings.php` (PATCH request)
7. Verify request body includes `cancellation_reason`

**Expected Results:**
- ✅ Request method: PATCH
- ✅ Status code: 200
- ✅ Request body: `{booking_id, status: "cancelled", cancellation_reason: "..."}`
- ✅ Response: `{success: true, message: "Booking cancelled successfully"}`
- ✅ Alert shows success message
- ✅ Booking status updated to "CANCELLED"
- ✅ Cancellation timestamp recorded in database (cancelled_at column)
- ✅ If room was assigned, room status returns to "available"

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 11: Track Booking - Cancelled Status**

**Objective:** Verify cancelled bookings show cancellation details

**Steps:**
1. After cancelling a booking in Test 10, note the booking reference
2. Click "Track Booking" in navbar
3. Enter the cancelled booking reference
4. Click "Check Status"

**Expected Results:**
- ✅ Modal displays booking details
- ✅ Status badge shows "CANCELLED" in red
- ✅ Shows cancellation timestamp
- ✅ Shows cancellation reason entered in Test 10
- ✅ No confirmation timestamp displayed

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 12: Room Type Image Upload**

**Objective:** Verify room type image upload functionality

**Steps:**
1. On dashboard, click "Room Types" in sidebar
2. Click edit icon (pencil) for any room type
3. In the update form, click "Choose File" for image
4. Select an image file (JPG, PNG, GIF, or WEBP, under 5MB)
5. Update base_price or another field (to trigger update)
6. Click "Update Room Type"
7. Check Network tab for two requests:
   - `upload_room_image.php` (POST)
   - `php/api/room_types.php` (PATCH)

**Expected Results:**
- ✅ First request: POST to upload_room_image.php
- ✅ Response: `{success: true, image_url: "../images/room-types/room_type_X_TIMESTAMP.ext"}`
- ✅ Second request: PATCH to php/api/room_types.php
- ✅ Request body includes new image_url from upload response
- ✅ Room type updated successfully
- ✅ New image displays in ACCOMMODATION.PHP when refreshed
- ✅ Image file exists in images/room-types/ directory

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 13: Authentication Check**

**Objective:** Verify API endpoints require authentication

**Steps:**
1. Log out from dashboard
2. Open new browser tab (or incognito)
3. Try to access directly: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php`

**Expected Results:**
- ✅ Status code: 401
- ✅ Response: `{success: false, error: "Unauthorized. Please log in."}`
- ✅ No booking data returned

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 14: Public Endpoint - No Auth Required**

**Objective:** Verify booking_public.php is accessible without login

**Steps:**
1. Without logging in (or in incognito mode)
2. Access: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/booking_public.php?ref=BK20260110-7236`
3. Or open any page and use Track Booking modal

**Expected Results:**
- ✅ Status code: 200 (not 401)
- ✅ Response: `{success: true, booking: {...}}`
- ✅ Booking data returned without authentication
- ✅ Track Booking modal works on public pages

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 15: Error Handling - Invalid Data**

**Objective:** Verify API validation works

**Steps:**
1. Use Postman or browser console to send invalid booking:
```javascript
fetch('http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/api/bookings.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        room_type_id: 999999, // Invalid ID
        check_in_date: '2026-01-15'
        // Missing required fields
    })
});
```

**Expected Results:**
- ✅ Status code: 400
- ✅ Response: `{success: false, error: "Missing required fields: ..."}`
- ✅ No booking created

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

## 🔍 Browser Console Tests

Run these in browser console while on dashboard:

### Test: Get All Bookings
```javascript
getAllBookings().then(console.log);
// Expected: Returns bookings array with pagination info
```

### Test: Get Specific Booking
```javascript
getBooking(1).then(console.log);
// Expected: Returns single booking object
```

### Test: Get All Rooms
```javascript
getAllRooms().then(console.log);
// Expected: Returns rooms array
```

### Test: Get Available Rooms for Room Type
```javascript
getAllRooms({ status: 'available', room_type_id: 3 }).then(console.log);
// Expected: Returns filtered rooms array
```

### Test: Get All Room Types
```javascript
getAllRoomTypes().then(console.log);
// Expected: Returns room types array with availability info
```

---

## 🐛 Common Issues & Solutions

### Issue: "getAllBookings is not defined"
**Solution:** Ensure `api-integration.js` is loaded:
```html
<script src="../js/api-integration.js"></script>
```

### Issue: 401 Unauthorized errors
**Solution:** Log in to dashboard first. API requires authentication.

### Issue: CORS errors
**Solution:** Ensure you're accessing via `localhost`, not `127.0.0.1` or file://

### Issue: Network tab shows no requests
**Solution:** 
1. Clear browser cache
2. Hard refresh (Ctrl+F5)
3. Check browser console for JavaScript errors

### Issue: Rooms not loading on accommodation page
**Solution:**
1. Check `php/api/room_types.php` exists
2. Check database has room_types data
3. Verify room_types table has `image_url` column

---

## 📊 Test Summary

Total Tests: 12

Passed: ____ / 12
Failed: ____ / 12

**Overall Status:** PASS / FAIL

**Tested By:** ________________  
**Date:** ________________  
**Browser:** ________________  
**Notes:** _______________________________________________________________________

---

## 🚀 Performance Tests (Optional)

### Load Time Test
1. Clear cache
2. Open Network tab
3. Load ACCOMMODATION.PHP
4. Check total load time

**Expected:** < 2 seconds  
**Actual:** ________

### API Response Time Test
1. Open Network tab
2. Load dashboard bookings
3. Check API response time for `php/api/bookings.php`

**Expected:** < 500ms  
**Actual:** ________

---

## ✅ Sign-Off

After all tests pass:
- [ ] All 12 core tests passed
- [ ] No console errors
- [ ] All CRUD operations work
- [ ] Authentication works properly
- [ ] Error handling works correctly

**QA Approval:** ________________  
**Date:** ________________

**Ready for Production:** YES / NO

---

**Test Completion Date:** January 10, 2026  
**Version:** 2.0 (RESTful API)

For issues or questions, refer to RESTFUL_API_IMPLEMENTATION_GUIDE.md
