<?php
/**
 * Get Dashboard Statistics
 * Retrieves real-time statistics for dashboard overview cards
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    // ========================================
    // 1. TODAY'S BOOKINGS
    // ========================================
    
    $todayBookingsQuery = "SELECT COUNT(*) as count 
                           FROM bookings 
                           WHERE DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($todayBookingsQuery);
    $stmt->execute();
    $todayBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Yesterday's bookings for comparison
    $yesterdayBookingsQuery = "SELECT COUNT(*) as count 
                                FROM bookings 
                                WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    $stmt = $conn->prepare($yesterdayBookingsQuery);
    $stmt->execute();
    $yesterdayBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // ========================================
    // 2. TOTAL GUESTS (unique guests)
    // ========================================
    
    $totalGuestsQuery = "SELECT COUNT(DISTINCT guest_email) as count 
                         FROM bookings";
    $stmt = $conn->prepare($totalGuestsQuery);
    $stmt->execute();
    $totalGuests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Last week's total for comparison
    $lastWeekGuestsQuery = "SELECT COUNT(DISTINCT guest_email) as count 
                            FROM bookings 
                            WHERE created_at <= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $stmt = $conn->prepare($lastWeekGuestsQuery);
    $stmt->execute();
    $lastWeekGuests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // ========================================
    // 3. AVAILABLE ROOMS & OCCUPANCY
    // ========================================
    
    $roomStatsQuery = "SELECT 
                        COUNT(*) as total_rooms,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
                        SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
                        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_rooms
                       FROM rooms";
    $stmt = $conn->prepare($roomStatsQuery);
    $stmt->execute();
    $roomStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ========================================
    // 4. TODAY'S REVENUE & AVERAGE
    // ========================================
    
    // Today's revenue from confirmed bookings
    $todayRevenueQuery = "SELECT COALESCE(SUM(total_amount), 0) as revenue 
                          FROM bookings 
                          WHERE status IN ('confirmed', 'checked_in', 'checked_out')
                          AND DATE(confirmed_at) = CURDATE()";
    $stmt = $conn->prepare($todayRevenueQuery);
    $stmt->execute();
    $todayRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'];
    
    // Average daily revenue (last 30 days)
    $avgRevenueQuery = "SELECT COALESCE(AVG(daily_revenue), 0) as avg_revenue
                        FROM (
                            SELECT DATE(confirmed_at) as booking_date, SUM(total_amount) as daily_revenue
                            FROM bookings
                            WHERE status IN ('confirmed', 'checked_in', 'checked_out')
                            AND confirmed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            AND confirmed_at < CURDATE()
                            GROUP BY DATE(confirmed_at)
                        ) as daily_stats";
    $stmt = $conn->prepare($avgRevenueQuery);
    $stmt->execute();
    $avgRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_revenue'];
    
    // ========================================
    // RETURN RESPONSE
    // ========================================
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'today_bookings' => (int)$todayBookings,
            'yesterday_bookings' => (int)$yesterdayBookings,
            'total_guests' => (int)$totalGuests,
            'last_week_guests' => (int)$lastWeekGuests,
            'total_rooms' => (int)$roomStats['total_rooms'],
            'available_rooms' => (int)$roomStats['available_rooms'],
            'occupied_rooms' => (int)$roomStats['occupied_rooms'],
            'maintenance_rooms' => (int)$roomStats['maintenance_rooms'],
            'today_revenue' => (float)$todayRevenue,
            'average_daily_revenue' => (float)$avgRevenue
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error in get_dashboard_stats.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_dashboard_stats.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
