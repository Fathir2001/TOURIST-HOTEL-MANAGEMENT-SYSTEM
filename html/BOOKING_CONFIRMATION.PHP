<?php
// Get booking reference from URL
$booking_reference = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : null;

if (!$booking_reference) {
    header('Location: ACCOMMODATION.PHP');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/STYLE.CSS">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Booking Confirmation - TOURIST HOTEL MANAGEMENT SYSTEM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .success-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: checkmark 0.8s ease;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.2) rotate(180deg);
            }
            100% {
                transform: scale(1) rotate(360deg);
                opacity: 1;
            }
        }

        .success-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 18px;
            opacity: 0.95;
        }

        .booking-details {
            padding: 40px;
        }

        .booking-ref-card {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            border: 2px solid #fecaca;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .booking-ref-card h3 {
            color: #dc2626;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .booking-ref {
            font-size: 28px;
            font-weight: bold;
            color: #991b1b;
            letter-spacing: 2px;
        }

        .details-section {
            margin-bottom: 30px;
        }

        .details-section h2 {
            color: #dc2626;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #fecaca;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 600;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
            text-align: right;
        }

        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            color: #666;
        }

        .price-row.total {
            border-top: 2px solid #dc2626;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
        }

        .info-message {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-message i {
            color: #3b82f6;
            margin-right: 10px;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
        }

        .btn-secondary:hover {
            background: #dc2626;
            color: white;
        }

        .error-container {
            text-align: center;
            padding: 60px 40px;
        }

        .error-icon {
            font-size: 60px;
            color: #dc2626;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .confirmation-container {
                margin: 0;
                border-radius: 0;
            }

            .success-header {
                padding: 30px 20px;
            }

            .success-header h1 {
                font-size: 24px;
            }

            .booking-details {
                padding: 30px 20px;
            }

            .booking-ref {
                font-size: 22px;
            }

            .actions {
                flex-direction: column;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>

<div id="loadingContainer" style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div style="text-align: center;">
        <i class="fas fa-spinner fa-spin" style="font-size: 50px; color: #dc2626;"></i>
        <p style="margin-top: 20px; color: #666; font-size: 18px;">Loading booking details...</p>
    </div>
</div>

<div id="errorContainer" class="confirmation-container" style="display: none;">
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 style="color: #dc2626; margin-bottom: 15px;">Booking Not Found</h1>
        <p id="errorMessage" style="color: #666; margin-bottom: 30px;"></p>
        <a href="ACCOMMODATION.PHP" class="btn btn-primary">Back to Accommodation</a>
    </div>
</div>

<div id="confirmationContainer" class="confirmation-container" style="display: none;">
    <div class="success-header">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Booking Confirmed!</h1>
        <p>Thank you for your reservation</p>
    </div>

    <div class="booking-details">
        <div class="booking-ref-card">
            <h3>Your Booking Reference</h3>
            <div class="booking-ref" id="bookingRef"></div>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                <i class="fas fa-info-circle"></i> Please save this reference number for future correspondence
            </p>
        </div>

        <div class="details-section">
            <h2><i class="fas fa-user"></i> Guest Information</h2>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value" id="guestName"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value" id="guestEmail"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value" id="guestPhone"></span>
            </div>
        </div>

        <div class="details-section">
            <h2><i class="fas fa-bed"></i> Room Details</h2>
            <div class="detail-row">
                <span class="detail-label">Room Type:</span>
                <span class="detail-value" id="roomType"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guests:</span>
                <span class="detail-value" id="guestCount"></span>
            </div>
        </div>

        <div class="details-section">
            <h2><i class="fas fa-calendar-alt"></i> Stay Details</h2>
            <div class="detail-row">
                <span class="detail-label">Check-in:</span>
                <span class="detail-value" id="checkIn"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out:</span>
                <span class="detail-value" id="checkOut"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Number of Nights:</span>
                <span class="detail-value" id="totalNights"></span>
            </div>
            <div id="specialRequestsRow" class="detail-row" style="display: none;">
                <span class="detail-label">Special Requests:</span>
                <span class="detail-value" id="specialRequests"></span>
            </div>
        </div>

        <div class="details-section">
            <h2><i class="fas fa-dollar-sign"></i> Payment Summary</h2>
            <div class="price-summary">
                <div class="price-row">
                    <span id="roomRateLabel"></span>
                    <span id="subtotal"></span>
                </div>
                <div class="price-row">
                    <span>Tax (10%):</span>
                    <span id="tax"></span>
                </div>
                <div class="price-row">
                    <span>Service Charge (5%):</span>
                    <span id="serviceCharge"></span>
                </div>
                <div class="price-row total">
                    <span>Total Amount:</span>
                    <span id="totalAmount"></span>
                </div>
            </div>
        </div>

        <div class="info-message">
            <i class="fas fa-info-circle"></i>
            <strong>Important:</strong> A confirmation email has been sent to <span id="confirmEmail"></span>. 
            Please contact us at <strong>057 22 22 501</strong> if you need to make any changes to your booking.
        </div>

        <div class="actions">
            <a href="HOME.HTML" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="ACCOMMODATION.PHP" class="btn btn-primary">
                <i class="fas fa-bed"></i> Book Another Room
            </a>
        </div>
    </div>
</div>

<script>
    const bookingReference = '<?php echo $booking_reference; ?>';
    
    async function loadBookingDetails() {
        try {
            // Fetch booking by reference using public endpoint
            const response = await fetch(`../php/api/booking_public.php?ref=${encodeURIComponent(bookingReference)}`);
            
            if (!response.ok) {
                throw new Error('Failed to load booking details');
            }
            
            const result = await response.json();
            
            if (!result.success || !result.data) {
                throw new Error(result.error || 'Booking not found');
            }
            
            const booking = result.data;
            
            // Populate booking details
            document.getElementById('bookingRef').textContent = booking.booking_reference;
            document.getElementById('guestName').textContent = booking.guest_name;
            document.getElementById('guestEmail').textContent = booking.guest_email;
            document.getElementById('guestPhone').textContent = booking.guest_phone;
            document.getElementById('roomType').textContent = booking.type_name;
            
            const adults = booking.adults;
            const children = booking.children || 0;
            let guestText = adults + ' Adult' + (adults > 1 ? 's' : '');
            if (children > 0) {
                guestText += ', ' + children + ' Child' + (children > 1 ? 'ren' : '');
            }
            document.getElementById('guestCount').textContent = guestText;
            
            document.getElementById('checkIn').textContent = formatDate(booking.check_in_date);
            document.getElementById('checkOut').textContent = formatDate(booking.check_out_date);
            document.getElementById('totalNights').textContent = booking.total_nights + ' Night' + (booking.total_nights > 1 ? 's' : '');
            
            if (booking.special_requests) {
                document.getElementById('specialRequests').textContent = booking.special_requests;
                document.getElementById('specialRequestsRow').style.display = 'flex';
            }
            
            document.getElementById('roomRateLabel').textContent = `Room Rate (${booking.total_nights} nights × $${parseFloat(booking.room_price).toFixed(2)}):`;
            document.getElementById('subtotal').textContent = '$' + parseFloat(booking.subtotal).toFixed(2);
            document.getElementById('tax').textContent = '$' + parseFloat(booking.tax_amount).toFixed(2);
            document.getElementById('serviceCharge').textContent = '$' + parseFloat(booking.service_charge).toFixed(2);
            document.getElementById('totalAmount').textContent = '$' + parseFloat(booking.total_amount).toFixed(2);
            document.getElementById('confirmEmail').textContent = booking.guest_email;
            
            // Hide loading, show confirmation
            document.getElementById('loadingContainer').style.display = 'none';
            document.getElementById('confirmationContainer').style.display = 'block';
            
        } catch (error) {
            console.error('Error loading booking:', error);
            document.getElementById('loadingContainer').style.display = 'none';
            document.getElementById('errorMessage').textContent = error.message;
            document.getElementById('errorContainer').style.display = 'block';
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }
    
    // Load booking details when page loads
    loadBookingDetails();
</script>

</body>
</html>
