<?php
/**
 * Database Configuration and Connection
 * This file handles the database connection using PDO
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'tourist_hotel_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection using PDO
 * @return PDO|null Returns PDO connection object or null on failure
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error (in production, log to file instead of displaying)
        error_log("Database Connection Error: " . $e->getMessage());
        
        // Display user-friendly error in development
        if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
            die("Database Connection Failed: " . $e->getMessage());
        } else {
            die("Database connection error. Please contact the administrator.");
        }
        
        return null;
    }
}

/**
 * Test database connection
 * @return bool Returns true if connection is successful
 */
function testDBConnection() {
    $conn = getDBConnection();
    if ($conn !== null) {
        return true;
    }
    return false;
}

/**
 * Alias for getDBConnection() for compatibility
 * @return PDO|null Returns PDO connection object or null on failure
 */
function getDatabaseConnection() {
    return getDBConnection();
}

// Initialize connection (optional - remove if you want lazy loading)
// $db = getDBConnection();
?>
