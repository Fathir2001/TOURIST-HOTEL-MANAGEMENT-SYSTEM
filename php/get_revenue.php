<?php
/**
 * Get Revenue Data
 * Retrieves revenue information from confirmed bookings
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    // ========================================
    // 1. GET REVENUE SUMMARY
    // ========================================
    
    $summaryQuery = "SELECT 
                        COUNT(*) as confirmed_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        COALESCE(AVG(total_amount), 0) as average_booking_value
                     FROM bookings 
                     WHERE status = 'confirmed'";
    
    $stmt = $conn->prepare($summaryQuery);
    $stmt->execute();
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get today's revenue
    $todayQuery = "SELECT COALESCE(SUM(total_amount), 0) as today_revenue
                   FROM bookings 
                   WHERE status = 'confirmed' 
                   AND DATE(confirmed_at) = CURDATE()";
    
    $stmt = $conn->prepare($todayQuery);
    $stmt->execute();
    $todayData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $summary['today_revenue'] = $todayData['today_revenue'];
    
    // ========================================
    // 2. GET REVENUE BY ROOM TYPE
    // ========================================
    
    $byRoomTypeQuery = "SELECT 
                            rt.type_name,
                            COUNT(b.booking_id) as booking_count,
                            COALESCE(SUM(b.total_amount), 0) as total_revenue
                        FROM bookings b
                        INNER JOIN room_types rt ON b.room_type_id = rt.room_type_id
                        WHERE b.status = 'confirmed'
                        GROUP BY rt.room_type_id, rt.type_name
                        ORDER BY total_revenue DESC";
    
    $stmt = $conn->prepare($byRoomTypeQuery);
    $stmt->execute();
    $byRoomType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ========================================
    // 3. GET ALL CONFIRMED BOOKINGS
    // ========================================
    
    $bookingsQuery = "SELECT 
                        b.booking_id,
                        b.booking_reference,
                        b.guest_name,
                        b.guest_email,
                        b.check_in_date,
                        b.check_out_date,
                        b.total_nights,
                        b.total_amount,
                        b.confirmed_at,
                        rt.type_name
                      FROM bookings b
                      INNER JOIN room_types rt ON b.room_type_id = rt.room_type_id
                      WHERE b.status = 'confirmed'
                      ORDER BY b.confirmed_at DESC";
    
    $stmt = $conn->prepare($bookingsQuery);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ========================================
    // RETURN RESPONSE
    // ========================================
    
    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'by_room_type' => $byRoomType,
        'bookings' => $bookings
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error in get_revenue.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_revenue.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
