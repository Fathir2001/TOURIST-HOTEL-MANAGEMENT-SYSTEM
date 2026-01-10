<?php
/**
 * RESTful API Helper Functions
 * Provides utilities for handling REST requests
 */

/**
 * Get the HTTP method (handles method override)
 */
function getRequestMethod() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Support method override for browsers that don't support PUT/DELETE
    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    }
    
    // Check for X-HTTP-Method-Override header
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }
    
    return $method;
}

/**
 * Get request body data (works for all methods)
 */
function getRequestData() {
    $method = getRequestMethod();
    
    if ($method === 'GET') {
        return $_GET;
    }
    
    if ($method === 'POST' && !empty($_POST)) {
        return $_POST;
    }
    
    // For PUT, DELETE, PATCH - read from php://input
    $input = file_get_contents('php://input');
    
    // Try to parse as JSON
    $data = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }
    
    // Try to parse as form data
    parse_str($input, $data);
    return $data;
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function sendErrorResponse($message, $statusCode = 400, $details = null) {
    $response = [
        'success' => false,
        'error' => $message
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Send success response
 */
function sendSuccessResponse($data = null, $message = 'Success', $statusCode = 200) {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Validate required fields
 */
function validateRequiredFields($data, $requiredFields) {
    $missing = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (empty($data[$field]) && $data[$field] !== 0 && $data[$field] !== '0')) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendErrorResponse('Missing required fields: ' . implode(', ', $missing), 400);
    }
}

/**
 * Set CORS headers
 */
function setCorsHeaders() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        }
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        
        exit(0);
    }
}

/**
 * Require authentication
 */
function requireAuth() {
    session_start();
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        sendErrorResponse('Unauthorized. Please log in.', 401);
    }
}

/**
 * Check rate limit (simple implementation)
 */
function checkRateLimit($maxRequests = 60, $period = 60) {
    session_start();
    $key = 'api_calls_' . session_id();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + $period];
    }
    
    if (time() > $_SESSION[$key]['reset']) {
        $_SESSION[$key] = ['count' => 0, 'reset' => time() + $period];
    }
    
    $_SESSION[$key]['count']++;
    
    if ($_SESSION[$key]['count'] > $maxRequests) {
        sendErrorResponse('Rate limit exceeded. Try again later.', 429);
    }
}
?>
