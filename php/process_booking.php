<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/database.php';

// Function to generate unique booking reference
function generateBookingReference() {
    $date = date('Ymd'); // YYYYMMDD format
    $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return 'BK' . $date . '-' . $random;
}

// Function to check if booking reference already exists
function isBookingReferenceUnique($conn, $reference) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_reference = ?");
    $stmt->execute([$reference]);
    return $stmt->fetchColumn() == 0;
}

// Function to calculate taxes and charges
function calculateCharges($subtotal) {
    $taxRate = 0.10; // 10% tax
    $serviceChargeRate = 0.05; // 5% service charge
    
    $tax = $subtotal * $taxRate;
    $serviceCharge = $subtotal * $serviceChargeRate;
    $total = $subtotal + $tax + $serviceCharge;
    
    return [
        'tax' => round($tax, 2),
        'service_charge' => round($serviceCharge, 2),
        'total' => round($total, 2)
    ];
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/ACCOMMODATION.PHP');
    exit();
}

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Retrieve and sanitize form data
    $room_type_id = filter_input(INPUT_POST, 'room_type_id', FILTER_VALIDATE_INT);
    $check_in_date = filter_input(INPUT_POST, 'check_in_date', FILTER_SANITIZE_STRING);
    $check_out_date = filter_input(INPUT_POST, 'check_out_date', FILTER_SANITIZE_STRING);
    $adults = filter_input(INPUT_POST, 'adults', FILTER_VALIDATE_INT);
    $children = filter_input(INPUT_POST, 'children', FILTER_VALIDATE_INT) ?: 0;
    $guest_name = trim(filter_input(INPUT_POST, 'guest_name', FILTER_SANITIZE_STRING));
    $guest_email = trim(filter_input(INPUT_POST, 'guest_email', FILTER_SANITIZE_EMAIL));
    $guest_phone = trim(filter_input(INPUT_POST, 'guest_phone', FILTER_SANITIZE_STRING));
    $special_requests = trim(filter_input(INPUT_POST, 'special_requests', FILTER_SANITIZE_STRING));
    
    // Validate required fields
    if (!$room_type_id || !$check_in_date || !$check_out_date || !$adults || !$guest_name || !$guest_email || !$guest_phone) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Validate email format
    if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address format.');
    }
    
    // Convert dates to DateTime objects for validation
    $checkInDateTime = new DateTime($check_in_date);
    $checkOutDateTime = new DateTime($check_out_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    // Validate dates
    if ($checkInDateTime < $today) {
        throw new Exception('Check-in date cannot be in the past.');
    }
    
    if ($checkOutDateTime <= $checkInDateTime) {
        throw new Exception('Check-out date must be after check-in date.');
    }
    
    // Calculate total nights
    $interval = $checkInDateTime->diff($checkOutDateTime);
    $total_nights = $interval->days;
    
    if ($total_nights < 1) {
        throw new Exception('Booking must be for at least 1 night.');
    }
    
    // Get room type details to retrieve price
    $stmt = $conn->prepare("SELECT base_price, type_name FROM room_types WHERE room_type_id = ? AND status = 1");
    $stmt->execute([$room_type_id]);
    $room_type = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room_type) {
        throw new Exception('Invalid room type selected.');
    }
    
    $room_price = $room_type['base_price'];
    $subtotal = $room_price * $total_nights;
    
    // Calculate charges
    $charges = calculateCharges($subtotal);
    
    // Generate unique booking reference
    $booking_reference = generateBookingReference();
    $attempts = 0;
    while (!isBookingReferenceUnique($conn, $booking_reference) && $attempts < 10) {
        $booking_reference = generateBookingReference();
        $attempts++;
    }
    
    if ($attempts >= 10) {
        throw new Exception('Unable to generate unique booking reference. Please try again.');
    }
    
    // Prepare SQL statement to insert booking
    $sql = "INSERT INTO bookings (
        booking_reference,
        user_id,
        guest_name,
        guest_email,
        guest_phone,
        room_type_id,
        check_in_date,
        check_out_date,
        adults,
        children,
        number_of_rooms,
        total_nights,
        room_price,
        subtotal,
        tax_amount,
        service_charge,
        total_amount,
        special_requests,
        status,
        payment_status
    ) VALUES (
        :booking_reference,
        NULL,
        :guest_name,
        :guest_email,
        :guest_phone,
        :room_type_id,
        :check_in_date,
        :check_out_date,
        :adults,
        :children,
        1,
        :total_nights,
        :room_price,
        :subtotal,
        :tax_amount,
        :service_charge,
        :total_amount,
        :special_requests,
        'pending',
        'pending'
    )";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':booking_reference', $booking_reference);
    $stmt->bindParam(':guest_name', $guest_name);
    $stmt->bindParam(':guest_email', $guest_email);
    $stmt->bindParam(':guest_phone', $guest_phone);
    $stmt->bindParam(':room_type_id', $room_type_id, PDO::PARAM_INT);
    $stmt->bindParam(':check_in_date', $check_in_date);
    $stmt->bindParam(':check_out_date', $check_out_date);
    $stmt->bindParam(':adults', $adults, PDO::PARAM_INT);
    $stmt->bindParam(':children', $children, PDO::PARAM_INT);
    $stmt->bindParam(':total_nights', $total_nights, PDO::PARAM_INT);
    $stmt->bindParam(':room_price', $room_price);
    $stmt->bindParam(':subtotal', $subtotal);
    $stmt->bindParam(':tax_amount', $charges['tax']);
    $stmt->bindParam(':service_charge', $charges['service_charge']);
    $stmt->bindParam(':total_amount', $charges['total']);
    $stmt->bindParam(':special_requests', $special_requests);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Get the booking ID
        $booking_id = $conn->lastInsertId();
        
        // Redirect to confirmation page with booking reference
        header('Location: ../html/BOOKING_CONFIRMATION.PHP?ref=' . urlencode($booking_reference));
        exit();
    } else {
        throw new Exception('Failed to create booking. Please try again.');
    }
    
} catch (PDOException $e) {
    // Database error
    error_log('Database Error: ' . $e->getMessage());
    $error_message = 'A database error occurred. Please try again later.';
    
} catch (Exception $e) {
    // General error
    $error_message = $e->getMessage();
}

// If we reach here, there was an error
if (isset($error_message)) {
    // Store error in session and redirect back
    session_start();
    $_SESSION['booking_error'] = $error_message;
    header('Location: ../html/ACCOMMODATION.PHP?error=1');
    exit();
}
?>
