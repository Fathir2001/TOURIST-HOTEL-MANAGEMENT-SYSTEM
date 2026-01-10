/**
 * DASHBOARD REST API Migration Script
 * This script updates all fetch calls in DASHBOARD.PHP to use the new REST API
 */

// Include the API integration library
document.write('<script src="../js/api-integration.js"></script>');

// Override existing functions to use REST API

// Load dashboard statistics - NO CHANGES NEEDED (already uses dedicated endpoint)
// get_dashboard_stats.php will remain

// Load bookings - UPDATE TO USE REST API
async function loadBookings() {
    try {
        const data = await getAllBookings();
        allBookings = data.bookings || data;
        displayBookings(allBookings);
    } catch (error) {
        console.error('Error loading bookings:', error);
        document.getElementById('bookingsTableBody').innerHTML = 
            '<tr><td colspan="11" style="text-align: center; padding: 40px; color: #dc2626;"><i class="fas fa-exclamation-circle"></i> Error loading bookings</td></tr>';
    }
}

// Update booking status - UPDATE TO USE REST API
function updateBookingStatus(event) {
    event.preventDefault();
    
    const bookingId = document.getElementById('status_booking_id').value;
    const newStatus = document.getElementById('new_status').value;
    const assignedRoomId = document.getElementById('assigned_room_id').value;
    const roomAssignmentVisible = document.getElementById('roomAssignmentSection').style.display !== 'none';
    
    if (!bookingId || !newStatus) {
        alert('Please select a status');
        return;
    }

    if (roomAssignmentVisible && !assignedRoomId) {
        alert('Please select a room to assign to this booking');
        return;
    }

    const submitBtn = event.target.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;

    // Use REST API
    updateBookingStatus(parseInt(bookingId), newStatus, assignedRoomId ? parseInt(assignedRoomId) : null)
        .then(result => {
            alert('✓ Booking status updated successfully!' + (assignedRoomId ? '\\nRoom assigned.' : ''));
            closeStatusModal();
            loadBookings();
            if (assignedRoomId && typeof loadRooms === 'function') {
                loadRooms();
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
}

// Load rooms - UPDATE TO USE REST API
async function loadRooms() {
    try {
        const rooms = await getAllRooms();
        displayRooms(rooms);
    } catch (error) {
        console.error('Error loading rooms:', error);
        document.getElementById('roomsTableBody').innerHTML = 
            '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #dc2626;"><i class="fas fa-exclamation-circle"></i> Error loading rooms</td></tr>';
    }
}

// Add room - UPDATE TO USE REST API
async function addRoom(event) {
    event.preventDefault();
    
    const roomData = {
        roomNumber: document.getElementById('room_number').value,
        roomTypeId: parseInt(document.getElementById('room_type_id').value),
        floor: parseInt(document.getElementById('floor_number').value),
        view: document.getElementById('view_type').value
    };
    
    const submitBtn = event.target.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Adding...';
    submitBtn.disabled = true;
    
    try {
        await createRoom(roomData);
        alert('✓ Room added successfully!');
        closeAddRoomModal();
        loadRooms();
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

// Update room status - UPDATE TO USE REST API
async function changeRoomStatus(roomId, newStatus) {
    try {
        await updateRoomStatus(roomId, newStatus);
        loadRooms();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Delete room - UPDATE TO USE REST API
async function deleteRoomById(roomId) {
    if (!confirm('Are you sure you want to delete this room?')) {
        return;
    }
    
    try {
        await deleteRoom(roomId);
        alert('✓ Room deleted successfully!');
        loadRooms();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Load guests - NO CHANGES (uses dedicated endpoint)
// get_guests.php will remain

// Load available rooms for booking - UPDATE TO USE REST API  
async function loadAvailableRooms(roomTypeId) {
    try {
        const rooms = await getAllRooms({ status: 'available', room_type_id: roomTypeId });
        
        const dropdown = document.getElementById('assigned_room_id');
        const currentRoomId = document.getElementById('status_current_room_id').value;
        
        dropdown.innerHTML = '<option value="">-- Select Room --</option>';
        
        if (rooms && rooms.length > 0) {
            rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.room_id;
                option.textContent = `Room ${room.room_number} - ${room.view_type} View (Floor ${room.floor_number})`;
                
                if (currentRoomId && room.room_id == currentRoomId) {
                    option.selected = true;
                }
                
                dropdown.appendChild(option);
            });
        } else {
            dropdown.innerHTML = '<option value="">No available rooms</option>';
        }
    } catch (error) {
        console.error('Error loading available rooms:', error);
        document.getElementById('assigned_room_id').innerHTML = '<option value="">Error loading rooms</option>';
    }
}
