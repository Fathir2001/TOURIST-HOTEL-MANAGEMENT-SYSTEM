-- ===================================================================
-- DATABASE UPDATE SCRIPT - Fix Missing Columns in Bookings Table
-- ===================================================================
-- This script adds missing columns to the bookings table
-- Run this in phpMyAdmin to fix the booking insertion issue
-- ===================================================================

USE tourist_hotel_db;

-- Add missing columns to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS user_id INT COMMENT 'Link to registered user if applicable' AFTER booking_reference;

ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS number_of_rooms INT NOT NULL DEFAULT 1 AFTER children;

ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER subtotal;

ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS service_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER tax_amount;

-- Verify the changes
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = 'tourist_hotel_db' 
    AND TABLE_NAME = 'bookings'
ORDER BY 
    ORDINAL_POSITION;
