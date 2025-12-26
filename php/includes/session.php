<?php
/**
 * Session Management
 * Handles secure session initialization and management
 */

// Prevent direct access
if (!defined('SESSION_INCLUDED')) {
    define('SESSION_INCLUDED', true);
}

/**
 * Initialize secure session
 */
function initSession() {
    // Session configuration for security
    if (session_status() === PHP_SESSION_NONE) {
        
        // Set session cookie parameters
        $cookieParams = [
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS
            'httponly' => true, // Not accessible via JavaScript
            'samesite' => 'Strict' // CSRF protection
        ];
        
        session_set_cookie_params($cookieParams);
        
        // Set session name
        session_name('TOURIST_HOTEL_SESSION');
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            // Regenerate session ID every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Validate session
        validateSession();
    }
}

/**
 * Validate session to prevent hijacking
 */
function validateSession() {
    // Check if session has required validation data
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    // Validate user agent (basic check)
    if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')) {
        destroySession();
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_LIFETIME) {
            destroySession();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Set user session after login
 * @param array $userData User data array
 * @param string $userType Type of user (admin or user)
 */
function setUserSession($userData, $userType = 'user') {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_type'] = $userType;
    $_SESSION['login_time'] = time();
    
    if ($userType === 'admin') {
        $_SESSION['admin_id'] = $userData['admin_id'];
        $_SESSION['admin_username'] = $userData['username'];
        $_SESSION['admin_name'] = $userData['full_name'];
        $_SESSION['admin_role'] = $userData['role'];
    } else {
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isUserLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    if ($_SESSION['user_type'] === 'admin') {
        return $_SESSION['admin_id'] ?? null;
    } else {
        return $_SESSION['user_id'] ?? null;
    }
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    $userData = [];
    
    if ($_SESSION['user_type'] === 'admin') {
        $userData['id'] = $_SESSION['admin_id'] ?? null;
        $userData['username'] = $_SESSION['admin_username'] ?? null;
        $userData['name'] = $_SESSION['admin_name'] ?? null;
        $userData['role'] = $_SESSION['admin_role'] ?? null;
        $userData['type'] = 'admin';
    } else {
        $userData['id'] = $_SESSION['user_id'] ?? null;
        $userData['email'] = $_SESSION['user_email'] ?? null;
        $userData['name'] = $_SESSION['user_name'] ?? null;
        $userData['type'] = 'user';
    }
    
    return $userData;
}

/**
 * Require login - redirect if not logged in
 * @param string $redirectUrl URL to redirect to
 */
function requireUserLogin($redirectUrl = '../html/HOME.HTML') {
    if (!isUserLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error_message'] = 'Please login to access this page';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Require admin login - redirect if not admin
 * @param string $redirectUrl URL to redirect to
 */
function requireAdminLogin($redirectUrl = '../html/ADMIN.HTML') {
    if (!isAdminLoggedIn()) {
        $_SESSION['error_message'] = 'Unauthorized access. Admin login required.';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Destroy session and logout user
 */
function destroySession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
    }
}

/**
 * Logout user
 * @param string $redirectUrl URL to redirect to after logout
 */
function logout($redirectUrl = '../html/HOME.HTML') {
    destroySession();
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Set remember me cookie
 * @param int $userId User ID
 * @param string $token Remember token
 */
function setRememberMeCookie($userId, $token) {
    $cookieValue = $userId . ':' . $token;
    $cookieHash = hash_hmac('sha256', $cookieValue, HASH_ALGORITHM);
    $cookie = $cookieValue . ':' . $cookieHash;
    
    setcookie(
        'remember_me',
        $cookie,
        time() + REMEMBER_ME_LIFETIME,
        '/',
        $_SERVER['HTTP_HOST'] ?? 'localhost',
        isset($_SERVER['HTTPS']),
        true
    );
}

/**
 * Clear remember me cookie
 */
function clearRememberMeCookie() {
    setcookie(
        'remember_me',
        '',
        time() - 3600,
        '/',
        $_SERVER['HTTP_HOST'] ?? 'localhost',
        isset($_SERVER['HTTPS']),
        true
    );
}

/**
 * Check remember me cookie and auto-login
 * @param PDO $conn Database connection
 * @return bool True if auto-logged in, false otherwise
 */
function checkRememberMe($conn) {
    if (!isset($_COOKIE['remember_me'])) {
        return false;
    }
    
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) !== 3) {
        clearRememberMeCookie();
        return false;
    }
    
    list($userId, $token, $hash) = $parts;
    
    // Verify hash
    $cookieValue = $userId . ':' . $token;
    $expectedHash = hash_hmac('sha256', $cookieValue, HASH_ALGORITHM);
    
    if (!hash_equals($expectedHash, $hash)) {
        clearRememberMeCookie();
        return false;
    }
    
    // Verify token in database (you need to add remember_token field to users/admins table)
    // This is a simplified version
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND status = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            setUserSession($user, 'user');
            return true;
        }
    } catch (PDOException $e) {
        error_log("Remember me error: " . $e->getMessage());
    }
    
    clearRememberMeCookie();
    return false;
}

// Initialize session automatically when this file is included
initSession();
?>
