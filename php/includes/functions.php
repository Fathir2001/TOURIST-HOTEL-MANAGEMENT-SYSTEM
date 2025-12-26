<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Sanitize user input to prevent XSS attacks
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Sri Lankan format)
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Remove spaces and hyphens
    $phone = preg_replace('/[\s\-]/', '', $phone);
    // Check if it matches Sri Lankan phone format
    return preg_match('/^(?:\+94|0)?[0-9]{9,10}$/', $phone);
}

/**
 * Generate random token
 * @param int $length Length of token
 * @return string Generated token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate unique booking reference
 * @return string Booking reference (e.g., BK20251225001)
 */
function generateBookingReference() {
    $date = date('Ymd');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return 'BK' . $date . $random;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

/**
 * Calculate number of nights between two dates
 * @param string $checkIn Check-in date
 * @param string $checkOut Check-out date
 * @return int Number of nights
 */
function calculateNights($checkIn, $checkOut) {
    $date1 = new DateTime($checkIn);
    $date2 = new DateTime($checkOut);
    $interval = $date1->diff($date2);
    return $interval->days;
}

/**
 * Calculate booking total
 * @param float $roomPrice Room price per night
 * @param int $nights Number of nights
 * @param int $rooms Number of rooms
 * @return array Array with subtotal, tax, service_charge, and total
 */
function calculateBookingTotal($roomPrice, $nights, $rooms = 1) {
    $subtotal = $roomPrice * $nights * $rooms;
    $taxAmount = $subtotal * TAX_RATE;
    $serviceCharge = $subtotal * SERVICE_CHARGE_RATE;
    $total = $subtotal + $taxAmount + $serviceCharge;
    
    return [
        'subtotal' => round($subtotal, 2),
        'tax_amount' => round($taxAmount, 2),
        'service_charge' => round($serviceCharge, 2),
        'total' => round($total, 2)
    ];
}

/**
 * Check if room is available for given dates
 * @param PDO $conn Database connection
 * @param int $roomTypeId Room type ID
 * @param string $checkIn Check-in date
 * @param string $checkOut Check-out date
 * @return bool True if available, false otherwise
 */
function checkRoomAvailability($conn, $roomTypeId, $checkIn, $checkOut) {
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as booked_rooms
            FROM bookings
            WHERE room_type_id = ?
            AND status IN ('confirmed', 'checked_in')
            AND (
                (check_in_date <= ? AND check_out_date > ?)
                OR (check_in_date < ? AND check_out_date >= ?)
                OR (check_in_date >= ? AND check_out_date <= ?)
            )
        ");
        
        $stmt->execute([$roomTypeId, $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
        $result = $stmt->fetch();
        
        // Get total rooms of this type
        $stmtTotal = $conn->prepare("
            SELECT COUNT(*) as total_rooms
            FROM rooms
            WHERE room_type_id = ? AND status = 'available'
        ");
        $stmtTotal->execute([$roomTypeId]);
        $totalRooms = $stmtTotal->fetch();
        
        return ($totalRooms['total_rooms'] - $result['booked_rooms']) > 0;
        
    } catch (PDOException $e) {
        error_log("Error checking availability: " . $e->getMessage());
        return false;
    }
}

/**
 * Get available rooms count for a room type
 * @param PDO $conn Database connection
 * @param int $roomTypeId Room type ID
 * @param string $checkIn Check-in date
 * @param string $checkOut Check-out date
 * @return int Number of available rooms
 */
function getAvailableRoomsCount($conn, $roomTypeId, $checkIn, $checkOut) {
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as booked_rooms
            FROM bookings
            WHERE room_type_id = ?
            AND status IN ('confirmed', 'checked_in')
            AND (
                (check_in_date <= ? AND check_out_date > ?)
                OR (check_in_date < ? AND check_out_date >= ?)
                OR (check_in_date >= ? AND check_out_date <= ?)
            )
        ");
        
        $stmt->execute([$roomTypeId, $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
        $result = $stmt->fetch();
        
        // Get total rooms of this type
        $stmtTotal = $conn->prepare("
            SELECT COUNT(*) as total_rooms
            FROM rooms
            WHERE room_type_id = ? AND status = 'available'
        ");
        $stmtTotal->execute([$roomTypeId]);
        $totalRooms = $stmtTotal->fetch();
        
        return max(0, $totalRooms['total_rooms'] - $result['booked_rooms']);
        
    } catch (PDOException $e) {
        error_log("Error getting available rooms: " . $e->getMessage());
        return 0;
    }
}

/**
 * Send email (basic function - can be enhanced with PHPMailer)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $headers Additional headers
 * @return bool True if sent, false otherwise
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'From: ' . SMTP_FROM_NAME . ' <' . CONTACT_EMAIL . '>',
        'Reply-To: ' . CONTACT_EMAIL,
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8'
    ];
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    $headerString = implode("\r\n", $allHeaders);
    
    return mail($to, $subject, $message, $headerString);
}

/**
 * Log activity to database
 * @param PDO $conn Database connection
 * @param string $userType Type of user (admin, user, guest)
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $description Description of action
 */
function logActivity($conn, $userType, $userId, $action, $description = '') {
    try {
        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_type, user_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->execute([$userType, $userId, $action, $description, $ipAddress, $userAgent]);
        
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Set flash message in session
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function setFlashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message from session
 * @return array|null Flash message array or null
 */
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Require login (redirect if not logged in)
 * @param string $redirectUrl URL to redirect to if not logged in
 */
function requireLogin($redirectUrl = '../html/HOME.HTML') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page');
        redirect($redirectUrl);
    }
}

/**
 * Require admin access (redirect if not admin)
 * @param string $redirectUrl URL to redirect to if not admin
 */
function requireAdmin($redirectUrl = '../html/ADMIN.HTML') {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized access');
        redirect($redirectUrl);
    }
}

/**
 * Upload file
 * @param array $file File from $_FILES
 * @param string $destination Destination directory
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array Result array with success status and message/path
 */
function uploadFile($file, $destination, $allowedTypes = ALLOWED_IMAGE_TYPES, $maxSize = MAX_FILE_SIZE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $destination . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'path' => $filePath, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get time ago string
 * @param string $datetime Datetime string
 * @return string Time ago string (e.g., "2 hours ago")
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'];
    $lengths = [60, 60, 24, 7, 4.35, 12];
    
    for ($i = 0; $difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $difference /= $lengths[$i];
    }
    
    $difference = round($difference);
    
    if ($difference != 1) {
        $periods[$i] .= 's';
    }
    
    return $difference . ' ' . $periods[$i] . ' ago';
}
?>
