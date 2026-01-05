<?php
require_once '../php/get_rooms.php';
$rooms = getRoomTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/STYLE.CSS">
    <link rel="stylesheet" href="../css/accommodation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Accommodation - TOURIST HOTEL MANAGEMENT SYSTEM</title>
</head>

<body>
    <!-- Hero Section with Navigation -->
    <section class="hero-section">
        <div id="navbar-placeholder"></div>
        
        <div class="hero-content">
            <div class="hero-gradient-overlay"></div>
            <img src="../images/img75.webp" alt="Hotel Accommodation" class="hero-image">
            <div class="hero-text-overlay">
                <h1 class="hero-title">Accommodation</h1>
                <p class="hero-tagline">Elegant Rooms & Suites Tailored to Your Needs</p>
            </div>
            <div class="scroll-indicator">
                <div class="mouse">
                    <div class="wheel"></div>
                </div>
                <p>Scroll Down</p>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="intro-section">
        <div class="container">
            <div class="section-header">
                <h2>Luxury Accommodation in Bandarawela</h2>
                <p class="subtitle">Experience Unparalleled Comfort & Elegance</p>
            </div>
            <p class="intro-text">
                Indulge in unparalleled luxury at Bandarawela's finest hotel with exquisite suites and rooms. Our accommodations offer a haven of personalized service and top-tier amenities, ensuring a truly unforgettable stay. Perfect for families, couples, or discerning travelers seeking an exceptional experience.
            </p>
        </div>
    </section>

    <!-- Room Categories Section -->
    <section class="rooms-section">
        <div class="container">
            <h2 class="category-title">Our Room Categories</h2>
            
            <div class="rooms-grid">
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card <?php echo ($room['badge_label'] === 'Premium' || $room['badge_label'] === 'Heritage') ? 'featured' : ''; ?>">
                            <div class="room-image">
                                <img src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                                     onerror="this.src='../images/img75.webp'">
                                <?php if (!empty($room['badge_label'])): ?>
                                    <div class="room-badge"><?php echo htmlspecialchars($room['badge_label']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="room-details">
                                <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                                <div class="price-tag">
                                    <span class="price-label">From</span>
                                    <span class="price-amount">$<?php echo number_format($room['base_price'], 0); ?></span>
                                    <span class="price-period">/night</span>
                                </div>
                                <div class="room-info">
                                    <span><i class="fas fa-expand-arrows-alt"></i> <?php echo $room['size_sqft']; ?> sq ft</span>
                                    <span><i class="fas fa-user-friends"></i> 
                                        <?php 
                                        echo $room['max_occupancy'];
                                        echo $room['max_occupancy'] > 1 ? ' Guests' : ' Guest';
                                        ?>
                                    </span>
                                </div>
                                <p><?php echo htmlspecialchars($room['description']); ?></p>
                                
                                <?php if (!empty($room['features'])): ?>
                                    <div class="room-features">
                                        <?php foreach ($room['features'] as $feature): ?>
                                            <span><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($feature); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <button class="book-room-btn" onclick="bookRoom(<?php echo $room['room_type_id']; ?>)">Book Now</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-rooms-message">
                        <p>No rooms available at the moment. Please check back later.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Amenities Section -->
    <section class="amenities-section">
        <div class="container">
            <h2 class="category-title">All Rooms Include</h2>
            <div class="amenities-grid">
                <div class="amenity-item">
                    <i class="fas fa-wifi"></i>
                    <h4>Free WiFi</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-tv"></i>
                    <h4>Flat Screen TV</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-wind"></i>
                    <h4>Air Conditioning</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-coffee"></i>
                    <h4>Tea/Coffee Maker</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-lock"></i>
                    <h4>Safe Deposit Box</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-concierge-bell"></i>
                    <h4>Room Service</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-shopping-bag"></i>
                    <h4>Mini Bar</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-soap"></i>
                    <h4>Toiletries</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Modal -->
    <div id="bookingModal" class="booking-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeBookingModal()">&times;</span>
            <h2>Book Your Stay</h2>
            <p class="modal-subtitle">Please fill in your details to proceed</p>
            
            <form id="bookingForm" action="../php/process_booking.php" method="POST" onsubmit="return validateBookingForm()">
                <input type="hidden" id="room_type_id" name="room_type_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="room_type_display">Room Type <span class="required">*</span></label>
                        <input type="text" id="room_type_display" readonly>
                    </div>
                    <div class="form-group">
                        <label for="room_price_display">Price per Night</label>
                        <input type="text" id="room_price_display" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="check_in_date">Check-in Date <span class="required">*</span></label>
                        <input type="date" id="check_in_date" name="check_in_date" required onchange="validateCheckInDate(); calculateTotal()">
                        <small id="check_in_error" style="color: #ef4444; display: none; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i> <span id="check_in_error_msg"></span>
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="check_out_date">Check-out Date <span class="required">*</span></label>
                        <input type="date" id="check_out_date" name="check_out_date" required onchange="validateCheckOutDate(); calculateTotal()">
                        <small id="check_out_error" style="color: #ef4444; display: none; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i> <span id="check_out_error_msg"></span>
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="adults">Adults <span class="required">*</span></label>
                        <input type="number" id="adults" name="adults" min="1" max="10" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="children">Children</label>
                        <input type="number" id="children" name="children" min="0" max="10" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="guest_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="guest_name" name="guest_name" required placeholder="Enter your full name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="guest_email">Email Address <span class="required">*</span></label>
                        <input type="email" id="guest_email" name="guest_email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label for="guest_phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="guest_phone" name="guest_phone" required placeholder="+94 77 7183231">
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea id="special_requests" name="special_requests" rows="3" placeholder="Any special requirements or preferences?"></textarea>
                </div>

                <div class="booking-summary">
                    <div class="summary-row">
                        <span>Number of Nights:</span>
                        <span id="nights_display">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Room Rate:</span>
                        <span id="rate_display">-</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <span id="total_display">-</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
        <div id="footer-placeholder"></div>
    </footer>

    <script src="../js/include.js"></script>
    <script>
        const roomsData = <?php echo json_encode($rooms); ?>;

        function bookRoom(roomTypeId) {
            const room = roomsData.find(r => r.room_type_id == roomTypeId);
            if (!room) return;

            // Populate modal with room data
            document.getElementById('room_type_id').value = roomTypeId;
            document.getElementById('room_type_display').value = room.type_name;
            document.getElementById('room_price_display').value = '$' + parseFloat(room.base_price).toFixed(2);
            
            // Set minimum date to today for check-in
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const todayString = today.toISOString().split('T')[0];
            
            const checkInInput = document.getElementById('check_in_date');
            const checkOutInput = document.getElementById('check_out_date');
            
            checkInInput.setAttribute('min', todayString);
            checkInInput.value = '';
            checkOutInput.value = '';
            checkOutInput.removeAttribute('min');
            
            // Clear any validation errors
            document.getElementById('check_in_error').style.display = 'none';
            document.getElementById('check_out_error').style.display = 'none';
            
            // Show modal
            document.getElementById('bookingModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('bookingForm').reset();
            // Reset summary
            document.getElementById('nights_display').textContent = '-';
            document.getElementById('rate_display').textContent = '-';
            document.getElementById('total_display').textContent = '-';
        }

        function calculateTotal() {
            const checkIn = new Date(document.getElementById('check_in_date').value);
            const checkOut = new Date(document.getElementById('check_out_date').value);
            const priceText = document.getElementById('room_price_display').value;
            const pricePerNight = parseFloat(priceText.replace('$', ''));

            if (checkIn && checkOut && checkOut > checkIn && pricePerNight) {
                const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                const total = nights * pricePerNight;
                
                document.getElementById('nights_display').textContent = nights + (nights === 1 ? ' night' : ' nights');
                document.getElementById('rate_display').textContent = '$' + pricePerNight.toFixed(2) + ' × ' + nights;
                document.getElementById('total_display').textContent = '$' + total.toFixed(2);
            } else {
                document.getElementById('nights_display').textContent = '-';
                document.getElementById('rate_display').textContent = '-';
                document.getElementById('total_display').textContent = '-';
            }
        }

        function validateCheckInDate() {
            const checkInInput = document.getElementById('check_in_date');
            const checkOutInput = document.getElementById('check_out_date');
            const errorElement = document.getElementById('check_in_error');
            const errorMsg = document.getElementById('check_in_error_msg');
            
            const checkInValue = checkInInput.value;
            if (!checkInValue) {
                errorElement.style.display = 'none';
                return false;
            }
            
            const checkIn = new Date(checkInValue);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            checkIn.setHours(0, 0, 0, 0);
            
            // Validate check-in is not in the past
            if (checkIn < today) {
                errorMsg.textContent = 'Check-in date cannot be in the past';
                errorElement.style.display = 'block';
                checkInInput.style.borderColor = '#ef4444';
                return false;
            }
            
            // Validate check-in is not more than 1 year in future
            const oneYearFromNow = new Date();
            oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);
            if (checkIn > oneYearFromNow) {
                errorMsg.textContent = 'Check-in date cannot be more than 1 year in advance';
                errorElement.style.display = 'block';
                checkInInput.style.borderColor = '#ef4444';
                return false;
            }
            
            // Clear error
            errorElement.style.display = 'none';
            checkInInput.style.borderColor = '#e5e5e5';
            
            // Update check-out minimum date to day after check-in
            if (checkInValue) {
                const nextDay = new Date(checkIn);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayString = nextDay.toISOString().split('T')[0];
                checkOutInput.setAttribute('min', nextDayString);
                
                // Re-validate check-out if it's already set
                if (checkOutInput.value) {
                    validateCheckOutDate();
                }
            }
            
            return true;
        }

        function validateCheckOutDate() {
            const checkInInput = document.getElementById('check_in_date');
            const checkOutInput = document.getElementById('check_out_date');
            const errorElement = document.getElementById('check_out_error');
            const errorMsg = document.getElementById('check_out_error_msg');
            
            const checkInValue = checkInInput.value;
            const checkOutValue = checkOutInput.value;
            
            if (!checkOutValue) {
                errorElement.style.display = 'none';
                return false;
            }
            
            if (!checkInValue) {
                errorMsg.textContent = 'Please select check-in date first';
                errorElement.style.display = 'block';
                checkOutInput.style.borderColor = '#ef4444';
                return false;
            }
            
            const checkIn = new Date(checkInValue);
            const checkOut = new Date(checkOutValue);
            checkIn.setHours(0, 0, 0, 0);
            checkOut.setHours(0, 0, 0, 0);
            
            // Validate check-out is after check-in
            if (checkOut <= checkIn) {
                errorMsg.textContent = 'Check-out must be at least 1 day after check-in';
                errorElement.style.display = 'block';
                checkOutInput.style.borderColor = '#ef4444';
                return false;
            }
            
            // Validate maximum stay duration (e.g., 30 days)
            const maxStayDays = 30;
            const daysDiff = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            if (daysDiff > maxStayDays) {
                errorMsg.textContent = `Maximum stay is ${maxStayDays} days`;
                errorElement.style.display = 'block';
                checkOutInput.style.borderColor = '#ef4444';
                return false;
            }
            
            // Clear error
            errorElement.style.display = 'none';
            checkOutInput.style.borderColor = '#e5e5e5';
            return true;
        }

        function validateBookingForm() {
            // Validate check-in date
            const checkInValid = validateCheckInDate();
            if (!checkInValid) {
                alert('Please correct the check-in date errors');
                document.getElementById('check_in_date').focus();
                return false;
            }
            
            // Validate check-out date
            const checkOutValid = validateCheckOutDate();
            if (!checkOutValid) {
                alert('Please correct the check-out date errors');
                document.getElementById('check_out_date').focus();
                return false;
            }
            
            const checkIn = new Date(document.getElementById('check_in_date').value);
            const checkOut = new Date(document.getElementById('check_out_date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            checkIn.setHours(0, 0, 0, 0);
            checkOut.setHours(0, 0, 0, 0);
            
            // Double-check dates are valid
            if (checkIn < today) {
                alert('Check-in date cannot be in the past');
                return false;
            }
            
            if (checkOut <= checkIn) {
                alert('Check-out date must be after check-in date');
                return false;
            }
            
            // Validate guest details
            const guestName = document.getElementById('guest_name').value.trim();
            if (guestName.length < 3) {
                alert('Please enter a valid full name (at least 3 characters)');
                document.getElementById('guest_name').focus();
                return false;
            }
            
            const guestEmail = document.getElementById('guest_email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(guestEmail)) {
                alert('Please enter a valid email address');
                document.getElementById('guest_email').focus();
                return false;
            }
            
            const guestPhone = document.getElementById('guest_phone').value.trim();
            if (guestPhone.length < 10) {
                alert('Please enter a valid phone number (at least 10 digits)');
                document.getElementById('guest_phone').focus();
                return false;
            }
            
            return true;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeBookingModal();
            }
        }
    </script>
</body>
</html>
