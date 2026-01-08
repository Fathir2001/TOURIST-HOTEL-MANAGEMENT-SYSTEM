<?php
/**
 * Get Admin Information
 * Retrieves current admin user details
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
error_log("get_admin_info.php called - Session ID: " . session_id());
error_log("Admin ID in session: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NOT SET'));

// Set header
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        error_log("get_admin_info.php: No admin_id in session");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated',
            'session_data' => array_keys($_SESSION)
        ]);
        exit;
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Get admin information
    $query = "SELECT admin_id, username, email, full_name, role, last_login, created_at
              FROM admins 
              WHERE admin_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Admin not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'admin' => $admin
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error in get_admin_info.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_admin_info.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
