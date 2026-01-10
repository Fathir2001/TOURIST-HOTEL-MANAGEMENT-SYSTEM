/**
 * RESTful API Integration for Dashboard
 * This file contains frontend JavaScript functions for using the new RESTful API
 */

// =============================================================================
// BOOKINGS API
// =============================================================================

/**
 * Get all bookings with optional filters
 */
async function getAllBookings(filters = {}) {
    try {
        const params = new URLSearchParams();
        
        if (filters.status) params.append('status', filters.status);
        if (filters.search) params.append('search', filters.search);
        if (filters.limit) params.append('limit', filters.limit);
        if (filters.offset) params.append('offset', filters.offset);
        
        const url = `../php/api/bookings.php${params.toString() ? '?' + params.toString() : ''}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching bookings:', error);
        throw error;
    }
}

/**
 * Get single booking by ID
 */
async function getBooking(bookingId) {
    try {
        const response = await fetch(`../php/api/bookings.php?id=${bookingId}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching booking:', error);
        throw error;
    }
}

/**
 * Create new booking
 */
async function createBooking(bookingData) {
    try {
        const response = await fetch('../php/api/bookings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                room_type_id: bookingData.roomTypeId,
                check_in_date: bookingData.checkIn,
                check_out_date: bookingData.checkOut,
                adults: bookingData.adults,
                children: bookingData.children || 0,
                guest_name: bookingData.guestName,
                guest_email: bookingData.guestEmail,
                guest_phone: bookingData.guestPhone,
                special_requests: bookingData.specialRequests || ''
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error creating booking:', error);
        throw error;
    }
}

/**
 * Update booking status (PATCH)
 */
async function updateBookingStatus(bookingId, status, roomId = null) {
    try {
        const data = {
            booking_id: bookingId,
            booking_status: status
        };
        
        if (roomId) {
            data.room_id = roomId;
        }
        
        const response = await fetch('../php/api/bookings.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error updating booking:', error);
        throw error;
    }
}

/**
 * Cancel booking (DELETE)
 */
async function cancelBooking(bookingId) {
    try {
        const confirmed = confirm('Are you sure you want to cancel this booking?');
        if (!confirmed) return null;
        
        const response = await fetch(`../php/api/bookings.php?id=${bookingId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error cancelling booking:', error);
        throw error;
    }
}

// =============================================================================
// ROOMS API
// =============================================================================

/**
 * Get all rooms with optional filters
 */
async function getAllRooms(filters = {}) {
    try {
        const params = new URLSearchParams();
        
        if (filters.status) params.append('status', filters.status);
        if (filters.room_type_id) params.append('room_type_id', filters.room_type_id);
        if (filters.floor) params.append('floor', filters.floor);
        
        const url = `../php/api/rooms.php${params.toString() ? '?' + params.toString() : ''}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching rooms:', error);
        throw error;
    }
}

/**
 * Get single room by ID
 */
async function getRoom(roomId) {
    try {
        const response = await fetch(`../php/api/rooms.php?id=${roomId}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching room:', error);
        throw error;
    }
}

/**
 * Create new room
 */
async function createRoom(roomData) {
    try {
        const response = await fetch('../php/api/rooms.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                room_number: roomData.roomNumber,
                room_type_id: roomData.roomTypeId,
                floor_number: roomData.floor,
                view_type: roomData.view || null
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error creating room:', error);
        throw error;
    }
}

/**
 * Update room (PATCH)
 */
async function updateRoom(roomId, updates) {
    try {
        const data = { room_id: roomId, ...updates };
        
        const response = await fetch('../php/api/rooms.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error updating room:', error);
        throw error;
    }
}

/**
 * Update room status only
 */
async function updateRoomStatus(roomId, status) {
    return await updateRoom(roomId, { status: status });
}

/**
 * Delete room
 */
async function deleteRoom(roomId) {
    try {
        const confirmed = confirm('Are you sure you want to delete this room?');
        if (!confirmed) return null;
        
        const response = await fetch(`../php/api/rooms.php?id=${roomId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error deleting room:', error);
        throw error;
    }
}

// =============================================================================
// ROOM TYPES API
// =============================================================================

/**
 * Get all room types
 */
async function getAllRoomTypes() {
    try {
        const response = await fetch('../php/api/room_types.php');
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching room types:', error);
        throw error;
    }
}

/**
 * Get single room type by ID
 */
async function getRoomType(roomTypeId) {
    try {
        const response = await fetch(`../php/api/room_types.php?id=${roomTypeId}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error fetching room type:', error);
        throw error;
    }
}

/**
 * Create new room type
 */
async function createRoomType(roomTypeData) {
    try {
        const response = await fetch('../php/api/room_types.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type_name: roomTypeData.typeName,
                description: roomTypeData.description,
                base_price: roomTypeData.basePrice,
                max_occupancy: roomTypeData.maxOccupancy,
                size_sqm: roomTypeData.size || null,
                amenities: roomTypeData.amenities || null,
                image_url: roomTypeData.imageUrl || null
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error creating room type:', error);
        throw error;
    }
}

/**
 * Update room type (PATCH)
 */
async function updateRoomType(roomTypeId, updates) {
    try {
        const data = { room_type_id: roomTypeId, ...updates };
        
        const response = await fetch('../php/api/room_types.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error updating room type:', error);
        throw error;
    }
}

/**
 * Delete room type
 */
async function deleteRoomType(roomTypeId) {
    try {
        const confirmed = confirm('Are you sure you want to delete this room type?');
        if (!confirmed) return null;
        
        const response = await fetch(`../php/api/room_types.php?id=${roomTypeId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error deleting room type:', error);
        throw error;
    }
}

// =============================================================================
// USAGE EXAMPLES
// =============================================================================

/**
 * Example: Load bookings and display in table
 */
async function loadBookingsTable() {
    try {
        const data = await getAllBookings({ status: 'pending', limit: 10 });
        const bookings = data.bookings;
        
        // Display in your table
        bookings.forEach(booking => {
            console.log(`${booking.booking_reference}: ${booking.guest_name}`);
        });
        
    } catch (error) {
        alert('Failed to load bookings: ' + error.message);
    }
}

/**
 * Example: Confirm booking with room assignment
 */
async function confirmBookingWithRoom(bookingId, roomId) {
    try {
        await updateBookingStatus(bookingId, 'confirmed', roomId);
        alert('Booking confirmed successfully!');
        // Reload bookings
        await loadBookingsTable();
    } catch (error) {
        alert('Failed to confirm booking: ' + error.message);
    }
}

/**
 * Example: Change room status
 */
async function changeRoomStatus(roomId, newStatus) {
    try {
        await updateRoomStatus(roomId, newStatus);
        alert('Room status updated successfully!');
    } catch (error) {
        alert('Failed to update room status: ' + error.message);
    }
}
