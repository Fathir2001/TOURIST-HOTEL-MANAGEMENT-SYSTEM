# REST API Migration Test Script
## Tourist Hotel Management System

---

## 🧪 Comprehensive Testing Guide

This guide provides step-by-step testing procedures to verify the RESTful API migration.

---

## ✅ Pre-Testing Checklist

Before starting tests, ensure:
- [ ] WAMP/XAMPP server is running
- [ ] MySQL database is running
- [ ] Browser Developer Tools are open (F12)
- [ ] Admin account is available (username: admin, password: Admin@123)

---

## 📋 Test Cases

### **Test 1: Accommodation Page - Load Room Types**

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
- ✅ Each card shows: image, name, price, occupancy, description

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 2: Accommodation Page - Create Booking**

**Objective:** Verify booking creation via REST API

**Steps:**
1. On ACCOMMODATION.PHP, click "Book Now" on any room
2. Fill in the booking form:
   - Check-in: (select tomorrow)
   - Check-out: (select day after tomorrow)
   - Adults: 2
   - Guest Name: Test User
   - Email: test@example.com
   - Phone: +94771234567
3. Click "Confirm Booking"
4. Check Network tab for: `php/api/bookings.php`

**Expected Results:**
- ✅ Request method: POST
- ✅ Status code: 201
- ✅ Response contains `{success: true, data: {booking_reference, ...}}`
- ✅ Alert shows success message with booking reference
- ✅ Redirect to confirmation page

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 3: Dashboard - Login**

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

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 4: Dashboard - Load Bookings**

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

### **Test 10: Dashboard - Cancel Booking**

**Objective:** Verify booking cancellation via REST API

**Steps:**
1. On Bookings page, create a test booking first (or use existing)
2. Click edit icon for the booking
3. Change status to "cancelled"
4. Click "Update Status"
5. Check Network tab for: `php/api/bookings.php` (DELETE or PATCH request)

**Expected Results:**
- ✅ Request method: PATCH or DELETE
- ✅ Status code: 200
- ✅ Response: `{success: true}`
- ✅ Alert shows success message
- ✅ Booking status updated to "CANCELLED"
- ✅ If room was assigned, room status returns to "available"

**Pass/Fail:** ________

**Notes:** _______________________________________________________________________

---

### **Test 11: Authentication Check**

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

### **Test 12: Error Handling - Invalid Data**

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
