<?php
/**
 * Update Admin Password
 * Changes the password for the logged-in admin
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
    if (!isset($input['current_password']) || !isset($input['new_password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Current password and new password are required'
        ]);
        exit;
    }
    
    $currentPassword = $input['current_password'];
    $newPassword = $input['new_password'];
    
    // Validate password length
    if (strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'New password must be at least 8 characters long'
        ]);
        exit;
    }
    
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Get current admin info
    $query = "SELECT admin_id, password_hash FROM admins WHERE admin_id = ?";
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
    
    // Verify current password
    if (!password_verify($currentPassword, $admin['password_hash'])) {
        $conn->rollBack();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }
    
    // Hash new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $updateQuery = "UPDATE admins SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE admin_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$newPasswordHash, $_SESSION['admin_id']]);
    
    // Commit transaction
    $conn->commit();
    
    // Clear session (user will need to login again)
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Database Error in update_password.php: " . $e->getMessage());
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
    error_log("Error in update_password.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
