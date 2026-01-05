-- ===================================================================
-- TOURIST HOTEL MANAGEMENT SYSTEM - DATABASE SCHEMA
-- ===================================================================
-- Database Name: tourist_hotel_db
-- Version: 1.0
-- Date: January 2026
-- Description: Complete database schema for hotel management system
--              including admin management, room inventory, and bookings
-- ===================================================================

-- Create database with proper character encoding
CREATE DATABASE IF NOT EXISTS tourist_hotel_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE tourist_hotel_db;

-- ===================================================================
-- TABLE 1: ADMINS
-- ===================================================================
-- Purpose: Store administrator accounts for dashboard access
-- Features: Username/password authentication, role-based access,
--           session tracking, account status management
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

-- Default admin account
-- Username: admin
-- Password: Admin@123
INSERT INTO admins (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@touristhotel.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');


-- ===================================================================
-- TABLE 2: ROOM TYPES
-- ===================================================================
-- Purpose: Define different room categories and their properties
-- Features: Pricing, occupancy limits, amenities, images, badges
-- Notes: All room types displayed on accommodation page
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
    
    INDEX idx_status (status),
    INDEX idx_type_name (type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room type data (6 types)
INSERT INTO room_types (type_name, description, base_price, max_occupancy, size_sqm, bed_type, amenities, badge_label, features, image_url) VALUES
('Presidential Suite', 'Spanning a majestic 623 square feet, these opulent retreats offer a haven of personalized service and top-tier amenities. Unwind in a separate sitting area, designed for relaxation, and rejuvenate in the exquisite bathroom. Wake up to breathtaking garden views from the master bedroom.', 350.00, 4, 57.88, 'King Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Premium', '["Separate Sitting Area", "Garden Views", "Luxury Bathroom", "King Size Bed"]', '../images/presidential-suite.jpg'),

('The Heritage Suite', 'Vintage Luxury - Luxury Suites highlighting Ceylon\'s Cultural Heritage. Experience the perfect blend of traditional elegance and modern comfort in these thoughtfully designed suites that pay homage to Sri Lanka\'s rich cultural legacy.', 280.00, 3, 51.10, 'King/Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Heritage', '["Cultural Decor", "Vintage Furnishings", "Modern Amenities", "Premium Linens"]', '../images/heritage-suite.jpg'),

('Deluxe Double Room', 'Indulge in timeless luxury with spacious layouts offering a haven of comfort. Featuring plush queen-sized beds and adorned with beautiful materials, modern conveniences and elegant furnishings ensure a truly unforgettable stay.', 180.00, 2, 37.16, 'Queen Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Deluxe', '["Queen Size Bed", "Elegant Furnishings", "Modern Amenities", "City/Garden View"]', '../images/Deluxe Room.jfif'),

('Premium Garden Wing', 'Serenity in Nature - Our larger rooms with easy access to our Garden and Spa. Perfect for guests seeking tranquility and natural beauty, these rooms offer direct access to lush gardens and wellness facilities.', 220.00, 3, 41.81, 'Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Garden Wing', '["Garden Access", "Spa Proximity", "Spacious Layout", "Nature Views"]', '../images/garden-wing.jpg'),

('Standard Triple Room', 'Unwind in spacious comfort at your Bandarawela hideaway. Our Standard Triple Room offers ample space for families or groups of three, featuring revitalized beds with crisp white cotton sheets, plump pillows, and a cozy duvet for a peaceful night\'s sleep.', 150.00, 3, 35.30, 'Three Single Beds', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Three Beds", "Family Friendly", "Cotton Linens", "Scenic Views"]', '../images/triple-room.jpg'),

('Standard Double Room', 'Bathe in the soft glow of Bandarawela\'s rolling hills with our Standard Double Bedrooms. Natural light streams in, creating a warm and inviting atmosphere perfect for unwinding after a day exploring this charming hill station.', 120.00, 2, 29.73, 'Double Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Double Bed", "Natural Light", "Cozy Atmosphere", "Hill Views"]', '../images/double-room.jpg');


-- ===================================================================
-- TABLE 3: ROOMS
-- ===================================================================
-- Purpose: Individual room inventory and availability tracking
-- Features: Room number assignment, floor/view details, status management
-- Statuses: available, occupied, maintenance
-- Notes: Total 30 rooms across 6 types
-- ===================================================================

CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type_id INT NOT NULL,
    floor_number INT,
    view_type VARCHAR(50) COMMENT 'Garden, Mountain, City, Hill',
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id) ON DELETE RESTRICT,
    INDEX idx_room_number (room_number),
    INDEX idx_status (status),
    INDEX idx_room_type (room_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room inventory data (30 rooms total)

-- Presidential Suites (2 rooms) - Floor 4
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('401', (SELECT room_type_id FROM room_types WHERE type_name = 'Presidential Suite'), 4, 'Garden'),
('402', (SELECT room_type_id FROM room_types WHERE type_name = 'Presidential Suite'), 4, 'Garden');

-- Heritage Suites (3 rooms) - Floor 3
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('301', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'Mountain'),
('302', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'Mountain'),
('303', (SELECT room_type_id FROM room_types WHERE type_name = 'The Heritage Suite'), 3, 'City');

-- Deluxe Double Rooms (5 rooms) - Floor 2
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('201', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Garden'),
('202', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'City'),
('203', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Garden'),
('204', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'Mountain'),
('205', (SELECT room_type_id FROM room_types WHERE type_name = 'Deluxe Double Room'), 2, 'City');

-- Premium Garden Wing (4 rooms) - Ground Floor
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('G01', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G02', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G03', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden'),
('G04', (SELECT room_type_id FROM room_types WHERE type_name = 'Premium Garden Wing'), 1, 'Garden');

-- Standard Triple Rooms (6 rooms) - Floor 1
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('101', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'City'),
('102', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Garden'),
('103', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Mountain'),
('104', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'City'),
('105', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Garden'),
('106', (SELECT room_type_id FROM room_types WHERE type_name = 'Standard Triple Room'), 1, 'Mountain');

-- Standard Double Rooms (10 rooms) - Floors 1 & 2
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
-- TABLE 4: BOOKINGS
-- ===================================================================
-- Purpose: Store all room reservations and booking details
-- Features: Guest info, room assignment, payment tracking, status management
-- Workflow: pending → confirmed (room assigned) → checked_in → checked_out
-- Auto-update: System automatically updates status based on check-in/out dates
-- ===================================================================

CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    guest_address TEXT,
    room_type_id INT NOT NULL,
    room_id INT COMMENT 'Assigned during confirmation',
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    adults INT NOT NULL DEFAULT 1,
    children INT DEFAULT 0,
    total_nights INT NOT NULL,
    room_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    cancellation_reason TEXT,
    cancelled_at DATETIME,
    confirmed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE SET NULL,
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_guest_email (guest_email),
    INDEX idx_check_in (check_in_date),
    INDEX idx_check_out (check_out_date),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ===================================================================
-- SCHEMA NOTES
-- ===================================================================
-- 
-- BOOKING WORKFLOW:
-- 1. Guest books from accommodation page → Status: pending
-- 2. Admin confirms & assigns room → Status: confirmed, room_id set
-- 3. Auto check-in on check-in date → Status: checked_in, room: occupied
-- 4. Auto check-out on check-out date → Status: checked_out, room: available
--
-- ROOM STATUS MANAGEMENT:
-- - available: Ready for booking
-- - occupied: Currently booked and in use
-- - maintenance: Under maintenance, cannot be assigned
--
-- ADMIN FEATURES:
-- - Dashboard with statistics and analytics
-- - Real-time room availability tracking
-- - Guest management with booking history
-- - Revenue tracking (confirmed bookings only)
-- - Booking status management with room assignment
-- - Settings for username/password changes
--
-- AUTOMATIC UPDATES:
-- - Booking status automatically updates based on check-in/check-out dates
-- - Room status syncs with booking status changes
-- - Statistics refresh in real-time on dashboard
--
-- ===================================================================
-- END OF SCHEMA
