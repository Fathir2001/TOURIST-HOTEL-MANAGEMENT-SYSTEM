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

    <!-- Footer Section -->
    <footer class="footer">
        <div id="footer-placeholder"></div>
    </footer>

    <script src="../js/include.js"></script>
    <script>
        function bookRoom(roomTypeId) {
            // TODO: Implement booking functionality
            alert('Booking functionality coming soon!\nRoom Type ID: ' + roomTypeId);
            // Redirect to booking page with room type
            // window.location.href = 'booking.php?room_type=' + roomTypeId;
        }
    </script>
</body>
</html>
