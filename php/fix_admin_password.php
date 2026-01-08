<?php
/**
 * Fix Admin Password
 * This script updates the admin password to Admin@123
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        die("❌ Database connection failed!\n");
    }
    
    // Generate correct password hash for "Admin@123"
    $password = 'Admin@123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "🔐 Updating admin password...\n";
    echo "New password hash: " . $passwordHash . "\n\n";
    
    // Update the admin password
    $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$passwordHash]);
    
    // Verify the update
    $stmt = $conn->prepare("SELECT username, full_name, role, status FROM admins WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Admin password updated successfully!\n\n";
        echo "Account Details:\n";
        echo "================\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Full Name: " . $admin['full_name'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Status: " . ($admin['status'] == 1 ? 'Active' : 'Inactive') . "\n\n";
        echo "Login Credentials:\n";
        echo "==================\n";
        echo "Username: admin\n";
        echo "Password: Admin@123\n\n";
        echo "✨ You can now login at: http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/ADMIN.HTML\n";
    } else {
        echo "⚠️ Warning: Could not verify admin account!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
