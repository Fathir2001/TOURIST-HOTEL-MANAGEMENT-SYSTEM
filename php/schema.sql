-- ===================================================================
-- Tourist Hotel Management System - Database Schema
-- Database: tourist_hotel_db
-- ===================================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS tourist_hotel_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE tourist_hotel_db;

-- ===================================================================
-- 1. ADMINS TABLE
-- Stores administrator accounts
-- ===================================================================
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    phone VARCHAR(20),
    status TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account (password: Admin@123)
INSERT INTO admins (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@touristhotel.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- ===================================================================
-- 2. USERS TABLE
-- Stores guest/customer accounts
-- ===================================================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(10),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    id_number VARCHAR(50) COMMENT 'Passport or National ID',
    status TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expires DATETIME,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 3. ROOM TYPES TABLE
-- Defines different room categories
-- ===================================================================
CREATE TABLE IF NOT EXISTS room_types (
    room_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    max_occupancy INT NOT NULL,
    size_sqm DECIMAL(6, 2) COMMENT 'Size in square meters',
    bed_type VARCHAR(50) COMMENT 'e.g., King, Queen, Twin',
    amenities TEXT COMMENT 'JSON array of amenities',
    badge_label VARCHAR(50) COMMENT 'Premium, Heritage, Deluxe, Garden Wing, etc.',
    features TEXT COMMENT 'JSON array of room-specific features',
    image_url VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert room types from Accommodation page
INSERT INTO room_types (type_name, description, base_price, max_occupancy, size_sqm, bed_type, amenities, badge_label, features, image_url) VALUES
('Presidential Suite', 'Spanning a majestic 623 square feet, these opulent retreats offer a haven of personalized service and top-tier amenities. Unwind in a separate sitting area, designed for relaxation, and rejuvenate in the exquisite bathroom. Wake up to breathtaking garden views from the master bedroom.', 350.00, 4, 57.88, 'King Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Premium', '["Separate Sitting Area", "Garden Views", "Luxury Bathroom", "King Size Bed"]', '../images/presidential-suite.jpg'),
('The Heritage Suite', 'Vintage Luxury - Luxury Suites highlighting Ceylon\'s Cultural Heritage. Experience the perfect blend of traditional elegance and modern comfort in these thoughtfully designed suites that pay homage to Sri Lanka\'s rich cultural legacy.', 280.00, 3, 51.10, 'King/Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Heritage', '["Cultural Decor", "Vintage Furnishings", "Modern Amenities", "Premium Linens"]', '../images/heritage-suite.jpg'),
('Deluxe Double Room', 'Indulge in timeless luxury with spacious layouts offering a haven of comfort. Featuring plush queen-sized beds and adorned with beautiful materials, modern conveniences and elegant furnishings ensure a truly unforgettable stay.', 180.00, 2, 37.16, 'Queen Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Deluxe', '["Queen Size Bed", "Elegant Furnishings", "Modern Amenities", "City/Garden View"]', '../images/deluxe-room.jpg'),
('Premium Garden Wing', 'Serenity in Nature - Our larger rooms with easy access to our Garden and Spa. Perfect for guests seeking tranquility and natural beauty, these rooms offer direct access to lush gardens and wellness facilities.', 220.00, 3, 41.81, 'Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Garden Wing', '["Garden Access", "Spa Proximity", "Spacious Layout", "Nature Views"]', '../images/garden-wing.jpg'),
('Standard Triple Room', 'Unwind in spacious comfort at your Bandarawela hideaway. Our Standard Triple Room offers ample space for families or groups of three, featuring revitalized beds with crisp white cotton sheets, plump pillows, and a cozy duvet for a peaceful night\'s sleep.', 150.00, 3, 35.30, 'Three Single Beds', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Three Beds", "Family Friendly", "Cotton Linens", "Scenic Views"]', '../images/triple-room.jpg'),
('Standard Double Room', 'Bathe in the soft glow of Bandarawela\'s rolling hills with our Standard Double Bedrooms. Natural light streams in, creating a warm and inviting atmosphere perfect for unwinding after a day exploring this charming hill station.', 120.00, 2, 29.73, 'Double Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Double Bed", "Natural Light", "Cozy Atmosphere", "Hill Views"]', '../images/double-room.jpg');

-- ===================================================================
-- 4. ROOMS TABLE
-- Individual room inventory
-- ===================================================================
CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type_id INT NOT NULL,
    floor_number INT,
    view_type VARCHAR(50) COMMENT 'Garden, Mountain, City, etc.',
    status ENUM('available', 'occupied', 'maintenance', 'cleaning') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id) ON DELETE RESTRICT,
    INDEX idx_room_number (room_number),
    INDEX idx_status (status),
    INDEX idx_room_type (room_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert room inventory (30 rooms total)
-- Presidential Suites (2 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('401', (SELECT room_type_id FROM room_types WHERE type_name = 'Presidential Suite'), 4, 'Garden'),
('402', (SELECT room_type_id FROM room_types WHERE type_name = 'Presidential Suite'), 4, 'Garden');

-- Heritage Suites (3 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('301', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'Mountain'),
('302', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'Mountain'),
('303', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'City');

-- Deluxe Double Rooms (5 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('201', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Garden'),
('202', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'City'),
('203', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Garden'),
('204', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Mountain'),
('205', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'City');

-- Premium Garden Wing (4 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('G01', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G02', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G03', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G04', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden');

-- Standard Triple Rooms (6 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('101', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'City'),
('102', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Garden'),
('103', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Mountain'),
('104', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'City'),
('105', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Garden'),
('106', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Mountain');

-- Standard Double Rooms (10 rooms)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('107', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 1, 'City'),
('108', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 1, 'Garden'),
('109', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 1, 'Hill'),
('110', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 1, 'Hill'),
('206', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'Hill'),
('207', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'Garden'),
('208', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'Hill'),
('209', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'City'),
('210', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'Hill'),
('211', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Double Room'), 2, 'Garden');

-- ===================================================================
-- 5. BOOKINGS TABLE
-- Stores room reservations
-- ===================================================================
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    guest_name VARCHAR(100) NOT NULL COMMENT 'For non-registered guests',
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    room_type_id INT NOT NULL,
    room_id INT,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    adults INT NOT NULL DEFAULT 1,
    children INT DEFAULT 0,
    number_of_rooms INT DEFAULT 1,
    total_nights INT NOT NULL,
    room_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    service_charge DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    cancellation_reason TEXT,
    cancelled_at DATETIME,
    confirmed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE SET NULL,
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_user_id (user_id),
    INDEX idx_check_in (check_in_date),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 6. CONTACT MESSAGES TABLE
-- Stores messages from contact form
-- ===================================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    replied_by INT,
    reply_message TEXT,
    replied_at DATETIME,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (replied_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 7. GALLERY IMAGES TABLE
-- Stores hotel gallery images
-- ===================================================================
CREATE TABLE IF NOT EXISTS gallery_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(50) COMMENT 'rooms, facilities, restaurant, exterior, etc.',
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 8. SERVICES TABLE
-- Hotel services offered
-- ===================================================================
CREATE TABLE IF NOT EXISTS services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon_class VARCHAR(50) COMMENT 'Font Awesome icon class',
    price DECIMAL(10, 2),
    is_free TINYINT(1) DEFAULT 1,
    status TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample services
INSERT INTO services (service_name, description, icon_class, is_free) VALUES
('Free WiFi', 'High-speed wireless internet throughout the property', 'fas fa-wifi', 1),
('Restaurant', '24/7 in-house restaurant serving local and international cuisine', 'fas fa-utensils', 1),
('Room Service', 'Order meals and beverages to your room', 'fas fa-concierge-bell', 1),
('Spa & Wellness', 'Rejuvenating spa treatments and wellness facilities', 'fas fa-spa', 0),
('Swimming Pool', 'Outdoor pool with stunning mountain views', 'fas fa-swimming-pool', 1),
('Parking', 'Free parking for all guests', 'fas fa-parking', 1),
('Laundry', 'Professional laundry and dry cleaning services', 'fas fa-tshirt', 0),
('Airport Transfer', 'Convenient airport pickup and drop-off service', 'fas fa-shuttle-van', 0);

-- ===================================================================
-- 9. FAQs TABLE
-- Frequently Asked Questions
-- ===================================================================
CREATE TABLE IF NOT EXISTS faqs (
    faq_id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50) COMMENT 'booking, payment, policies, facilities, etc.',
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample FAQs
INSERT INTO faqs (question, answer, category, display_order) VALUES
('What time is check-in and check-out?', 'Check-in time is 2:00 PM and check-out time is 12:00 PM (noon). Early check-in and late check-out are subject to availability and may incur additional charges.', 'booking', 1),
('Do you offer airport transfers?', 'Yes, we provide airport transfer services for an additional fee. Please contact us in advance to arrange your pickup.', 'services', 2),
('Is breakfast included?', 'Complimentary breakfast is included with all room bookings. We serve both local and continental breakfast options from 7:00 AM to 10:00 AM.', 'services', 3),
('What is your cancellation policy?', 'Free cancellation is available up to 48 hours before check-in. Cancellations made within 48 hours will incur a charge of one night stay.', 'booking', 4),
('Do you have parking facilities?', 'Yes, we offer free parking for all our guests with 24/7 security.', 'facilities', 5),
('Are pets allowed?', 'Unfortunately, we do not allow pets in our hotel except for registered service animals.', 'policies', 6),
('Do you have WiFi?', 'Yes, we provide complimentary high-speed WiFi throughout the property.', 'facilities', 7),
('What payment methods do you accept?', 'We accept cash (LKR), credit cards (Visa, Mastercard), and bank transfers. Payment can be made at check-in or online during booking.', 'payment', 8);

-- ===================================================================
-- 10. REVIEWS TABLE
-- Guest reviews and ratings
-- ===================================================================
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT,
    guest_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL COMMENT '1-5 stars',
    title VARCHAR(150),
    review_text TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_response TEXT,
    responded_by INT,
    responded_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 11. ACTIVITY LOG TABLE
-- Track admin and user activities
-- ===================================================================
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'user', 'guest') NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL COMMENT 'login, logout, booking_created, etc.',
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_type),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 12. SETTINGS TABLE
-- Store site settings
-- ===================================================================
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'text' COMMENT 'text, number, boolean, json',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Silverleaf Hotel', 'text', 'Hotel name'),
('site_email', 'info@touristhotel.lk', 'text', 'Contact email'),
('site_phone', '057 22 22 501', 'text', 'Contact phone'),
('site_address', 'Bandarawela, Sri Lanka', 'text', 'Hotel address'),
('booking_enabled', '1', 'boolean', 'Enable/disable online booking'),
('maintenance_mode', '0', 'boolean', 'Site maintenance mode'),
('min_booking_days', '1', 'number', 'Minimum booking days'),
('max_booking_days', '30', 'number', 'Maximum booking days');

-- ===================================================================
-- End of Schema
-- ===================================================================
