<?php
/**
 * Get Recent Data (Bookings & Activities)
 * Fetches recent bookings and system activities for dashboard
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
    
    // Get type parameter
    $type = isset($_GET['type']) ? $_GET['type'] : 'bookings';
    
    if ($type === 'bookings') {
        // ========================================
        // GET RECENT BOOKINGS (Last 5)
        // ========================================
        
        $query = "SELECT 
                    b.booking_id,
                    b.booking_reference,
                    b.guest_name,
                    b.check_in_date,
                    b.status,
                    rt.type_name,
                    b.created_at
                  FROM bookings b
                  INNER JOIN room_types rt ON b.room_type_id = rt.room_type_id
                  ORDER BY b.created_at DESC
                  LIMIT 5";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
        
    } elseif ($type === 'activities') {
        // ========================================
        // GET RECENT ACTIVITIES (Last 10)
        // ========================================
        
        $activities = [];
        
        // Get recent booking status changes
        $bookingQuery = "SELECT 
                            booking_reference,
                            guest_name,
                            status,
                            created_at,
                            confirmed_at,
                            updated_at
                         FROM bookings
                         ORDER BY updated_at DESC
                         LIMIT 10";
        
        $stmt = $conn->prepare($bookingQuery);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($bookings as $booking) {
            $timestamp = strtotime($booking['updated_at']);
            $timeAgo = getTimeAgo($timestamp);
            
            switch ($booking['status']) {
                case 'pending':
                    $activities[] = [
                        'type' => 'new_booking',
                        'description' => "<strong>New booking</strong> from {$booking['guest_name']}",
                        'time_ago' => $timeAgo,
                        'timestamp' => $timestamp
                    ];
                    break;
                case 'confirmed':
                    if ($booking['confirmed_at']) {
                        $activities[] = [
                            'type' => 'confirmed',
                            'description' => "<strong>Booking confirmed</strong> - {$booking['booking_reference']}",
                            'time_ago' => getTimeAgo(strtotime($booking['confirmed_at'])),
                            'timestamp' => strtotime($booking['confirmed_at'])
                        ];
                    }
                    break;
                case 'checked_in':
                    $activities[] = [
                        'type' => 'checked_in',
                        'description' => "<strong>Check-in completed</strong> - {$booking['guest_name']}",
                        'time_ago' => $timeAgo,
                        'timestamp' => $timestamp
                    ];
                    break;
                case 'checked_out':
                    $activities[] = [
                        'type' => 'checked_out',
                        'description' => "<strong>Check-out completed</strong> - {$booking['guest_name']}",
                        'time_ago' => $timeAgo,
                        'timestamp' => $timestamp
                    ];
                    break;
                case 'cancelled':
                    $activities[] = [
                        'type' => 'cancelled',
                        'description' => "<strong>Booking cancelled</strong> - {$booking['booking_reference']}",
                        'time_ago' => $timeAgo,
                        'timestamp' => $timestamp
                    ];
                    break;
            }
        }
        
        // Get recent room additions
        $roomQuery = "SELECT room_number, created_at
                      FROM rooms
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                      ORDER BY created_at DESC
                      LIMIT 3";
        
        $stmt = $conn->prepare($roomQuery);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rooms as $room) {
            $timestamp = strtotime($room['created_at']);
            $activities[] = [
                'type' => 'room_added',
                'description' => "<strong>New room added</strong> - Room {$room['room_number']}",
                'time_ago' => getTimeAgo($timestamp),
                'timestamp' => $timestamp
            ];
        }
        
        // Sort by timestamp (most recent first)
        usort($activities, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Return only the first 8 activities
        $activities = array_slice($activities, 0, 8);
        
        echo json_encode([
            'success' => true,
            'activities' => $activities
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid type parameter'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database Error in get_recent_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_recent_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}

/**
 * Calculate time ago from timestamp
 */
function getTimeAgo($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}
