<?php
/**
 * Update Admin Username
 * Changes the username for the logged-in admin
 */

// Start session
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');

// Include database connection
require_once 'config/database.php';

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated'
        ]);
        exit;
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['new_username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'New username and password are required'
        ]);
        exit;
    }
    
    $newUsername = trim($input['new_username']);
    $password = $input['password'];
    
    // Validate username length
    if (strlen($newUsername) < 4) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username must be at least 4 characters long'
        ]);
        exit;
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Get current admin info
    $query = "SELECT admin_id, username, password_hash FROM admins WHERE admin_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Admin not found'
        ]);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        $conn->rollBack();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect password'
        ]);
        exit;
    }
    
    // Check if username already exists
    $checkQuery = "SELECT admin_id FROM admins WHERE username = ? AND admin_id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$newUsername, $_SESSION['admin_id']]);
    
    if ($checkStmt->fetch()) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit;
    }
    
    // Update username
    $updateQuery = "UPDATE admins SET username = ?, updated_at = CURRENT_TIMESTAMP WHERE admin_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$newUsername, $_SESSION['admin_id']]);
    
    // Commit transaction
    $conn->commit();
    
    // Clear session (user will need to login again)
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Username updated successfully'
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Database Error in update_username.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in update_username.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
