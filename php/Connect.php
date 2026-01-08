<?php
/**
 * Connect.php - Legacy Connection File (DEPRECATED)
 * This file is kept for backward compatibility
 * New code should use config/database.php instead
 */

// Include configuration files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get database connection
    $conn = getDBConnection();
    
    if (!$conn) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }
    
    // Determine which form was submitted based on available fields
    if (isset($_POST['FirstName']) && isset($_POST['LastName'])) {
        // User Registration Form
        handleRegistration($conn);
    } elseif (isset($_POST['username']) && isset($_POST['password'])) {
        // Admin Login Form
        handleAdminLogin($conn);
    } elseif (isset($_POST['name']) && isset($_POST['message'])) {
        // Contact Form
        handleContactForm($conn);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid form submission']);
    }
}

/**
 * Handle user registration
 */
function handleRegistration($conn) {
    try {
        // Sanitize and validate input
        $firstName = trim($_POST['FirstName']);
        $lastName = trim($_POST['LastName']);
        $gender = trim($_POST['Gender']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $phone = trim($_POST['Number']);
        
        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            throw new Exception('All required fields must be filled');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            throw new Exception('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, gender, email, password_hash, phone, status) 
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([$firstName, $lastName, $gender, $email, $passwordHash, $phone]);
        
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Handle admin login
 */
function handleAdminLogin($conn) {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required');
        }
        
        // Fetch admin user
        $stmt = $conn->prepare("
            SELECT admin_id, username, password_hash, full_name, role, status 
            FROM admins 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            throw new Exception('Invalid username or password');
        }
        
        if ($admin['status'] != 1) {
            throw new Exception('Account is inactive. Please contact administrator.');
        }
        
        // Verify password
        if (!password_verify($password, $admin['password_hash'])) {
            throw new Exception('Invalid username or password');
        }
        
        // Start session and set session variables
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = 'admin';
        
        // Update last login
        $updateStmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
        $updateStmt->execute([$admin['admin_id']]);
        
        // Show success message and redirect
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Login Successful</title>
            <style>
                body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
                .message { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                .success { color: #28a745; font-size: 50px; }
                h2 { color: #333; }
                .info { color: #666; margin: 20px 0; }
                .btn { display: inline-block; padding: 12px 30px; background: green; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .btn:hover { background: darkgreen; }
            </style>
        </head>
        <body>
            <div class='message'>
                <div class='success'>✅</div>
                <h2>Login Successful!</h2>
                <p class='info'><strong>Welcome back, " . htmlspecialchars($admin['full_name']) . "!</strong></p>
                <p class='info'>Redirecting to Admin Dashboard...</p>
                <p class='info'>Role: " . htmlspecialchars($admin['role']) . "</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = '../html/DASHBOARD.PHP';
                }, 1500);
            </script>
        </body>
        </html>";
        exit;
        
    } catch (Exception $e) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Login Failed</title>
            <style>
                body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
                .message { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                .error { color: #dc3545; font-size: 50px; }
                h2 { color: #333; }
                .info { color: #666; margin: 20px 0; }
                .btn { display: inline-block; padding: 12px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .btn:hover { background: #c82333; }
            </style>
        </head>
        <body>
            <div class='message'>
                <div class='error'>❌</div>
                <h2>Login Failed</h2>
                <p class='info'>" . htmlspecialchars($e->getMessage()) . "</p>
                <a href='../html/ADMIN.HTML' class='btn'>Try Again</a>
            </div>
        </body>
        </html>";
        exit;
        
    } catch (Exception $e) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Login Failed</title>
            <style>
                body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
                .message { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                .error { color: #dc3545; font-size: 50px; }
                h2 { color: #333; }
                .info { color: #666; margin: 20px 0; }
                .btn { display: inline-block; padding: 12px 30px; background: green; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .btn:hover { background: darkgreen; }
            </style>
        </head>
        <body>
            <div class='message'>
                <div class='error'>❌</div>
                <h2>Login Failed</h2>
                <p class='info'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p class='info'>Backend test: Failed</p>
                <a href='../html/ADMIN.HTML' class='btn'>Try Again</a>
            </div>
        </body>
        </html>";
        exit;
    }
}

/**
 * Handle contact form submission
 */
function handleContactForm($conn) {
    try {
        // Sanitize input
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        
        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Get client info
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Insert message
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'new')
        ");
        
        $stmt->execute([$name, $email, $subject, $message, $ipAddress, $userAgent]);
        
        // Send success response
        $_SESSION['success'] = 'Thank you for contacting us! We will get back to you soon.';
        header('Location: ../html/CONTACTUS.HTML?success=1');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../html/CONTACTUS.HTML?error=' . urlencode($e->getMessage()));
        exit;
    }
}
