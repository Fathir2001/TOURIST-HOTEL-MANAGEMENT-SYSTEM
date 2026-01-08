-- ===================================================================
-- CLEAN DATABASE SETUP - NO DUPLICATE ERRORS
-- ===================================================================
-- This will drop all existing data and recreate everything fresh
-- Use this if you keep getting duplicate entry errors
-- ===================================================================

-- Drop database if exists and recreate
DROP DATABASE IF EXISTS tourist_hotel_db;
CREATE DATABASE tourist_hotel_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE tourist_hotel_db;

-- ===================================================================
-- TABLE 1: ADMINS
-- ===================================================================
CREATE TABLE admins (
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

-- Default admin (Username: admin, Password: Admin@123)
INSERT INTO admins (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@touristhotel.lk', '$2y$10$95zu.K1w92MsO7gM8J1biuMai6LD2aJx4FXZg2SXS6jBafXJOEi1a', 'System Administrator', 'admin');

-- ===================================================================
-- TABLE 2: ROOM TYPES
-- ===================================================================
CREATE TABLE room_types (
    room_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    max_occupancy INT NOT NULL,
    size_sqm DECIMAL(6, 2),
    bed_type VARCHAR(50),
    amenities TEXT,
    badge_label VARCHAR(50),
    features TEXT,
    image_url VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_type_name (type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert room types
INSERT INTO room_types (type_name, description, base_price, max_occupancy, size_sqm, bed_type, amenities, badge_label, features, image_url) VALUES
('Presidential Suite', 'Spanning a majestic 623 square feet, these opulent retreats offer a haven of personalized service and top-tier amenities.', 350.00, 4, 57.88, 'King Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Premium', '["Separate Sitting Area", "Garden Views", "Luxury Bathroom", "King Size Bed"]', '../images/presidential-suite.jpg'),
('The Heritage Suite', 'Vintage Luxury - Luxury Suites highlighting Ceylon\'s Cultural Heritage.', 280.00, 3, 51.10, 'King/Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Heritage', '["Cultural Decor", "Vintage Furnishings", "Modern Amenities", "Premium Linens"]', '../images/heritage-suite.jpg'),
('Deluxe Double Room', 'Indulge in timeless luxury with spacious layouts offering a haven of comfort.', 180.00, 2, 37.16, 'Queen Size Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Deluxe', '["Queen Size Bed", "Elegant Furnishings", "Modern Amenities", "City/Garden View"]', '../images/Deluxe Room.jfif'),
('Premium Garden Wing', 'Serenity in Nature - Our larger rooms with easy access to our Garden and Spa.', 220.00, 3, 41.81, 'Queen Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Garden Wing', '["Garden Access", "Spa Proximity", "Spacious Layout", "Nature Views"]', '../images/garden-wing.jpg'),
('Standard Triple Room', 'Unwind in spacious comfort at your Bandarawela hideaway.', 150.00, 3, 35.30, 'Three Single Beds', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Three Beds", "Family Friendly", "Cotton Linens", "Scenic Views"]', '../images/triple-room.jpg'),
('Standard Double Room', 'Bathe in the soft glow of Bandarawela\'s rolling hills.', 120.00, 2, 29.73, 'Double Bed', '["Free WiFi", "Flat Screen TV", "Air Conditioning", "Tea/Coffee Maker", "Safe Deposit Box", "Room Service", "Mini Bar", "Bathroom Toiletries"]', 'Standard', '["Double Bed", "Natural Light", "Cozy Atmosphere", "Hill Views"]', '../images/double-room.jpg');

-- ===================================================================
-- TABLE 3: ROOMS
-- ===================================================================
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type_id INT NOT NULL,
    floor_number INT,
    view_type VARCHAR(50),
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id) ON DELETE RESTRICT,
    INDEX idx_room_number (room_number),
    INDEX idx_status (status),
    INDEX idx_room_type (room_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert rooms (30 total)
INSERT INTO rooms (room_number, room_type_id, floor_number, view_type) VALUES
('401', 1, 4, 'Garden'), ('402', 1, 4, 'Garden'),
('301', 2, 3, 'Mountain'), ('302', 2, 3, 'Mountain'), ('303', 2, 3, 'City'),
('201', 3, 2, 'Garden'), ('202', 3, 2, 'City'), ('203', 3, 2, 'Garden'), ('204', 3, 2, 'Mountain'), ('205', 3, 2, 'City'),
('G01', 4, 1, 'Garden'), ('G02', 4, 1, 'Garden'), ('G03', 4, 1, 'Garden'), ('G04', 4, 1, 'Garden'),
('101', 5, 1, 'City'), ('102', 5, 1, 'Garden'), ('103', 5, 1, 'Mountain'), ('104', 5, 1, 'City'), ('105', 5, 1, 'Garden'), ('106', 5, 1, 'Mountain'),
('107', 6, 1, 'City'), ('108', 6, 1, 'Garden'), ('109', 6, 1, 'Hill'), ('110', 6, 1, 'Hill'),
('206', 6, 2, 'Hill'), ('207', 6, 2, 'Garden'), ('208', 6, 2, 'Hill'), ('209', 6, 2, 'City'), ('210', 6, 2, 'Hill'), ('211', 6, 2, 'Garden');

-- ===================================================================
-- TABLE 4: BOOKINGS
-- ===================================================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    guest_name VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    guest_address TEXT,
    room_type_id INT NOT NULL,
    room_id INT,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    adults INT NOT NULL DEFAULT 1,
    children INT DEFAULT 0,
    number_of_rooms INT NOT NULL DEFAULT 1,
    total_nights INT NOT NULL,
    room_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    service_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
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
