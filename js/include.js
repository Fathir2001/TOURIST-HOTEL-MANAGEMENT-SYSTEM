// Function to load CSS file into head
function loadCSS(href) {
    const existingLink = document.querySelector(`link[href="${href}"]`);
    if (!existingLink) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }
}

// Load HTML includes from files
document.addEventListener('DOMContentLoaded', async function() {
    loadCSS('../css/navbar.css');
    loadCSS('../css/footer.css');
    
    const navbarPlaceholder = document.getElementById('navbar-placeholder');
    const footerPlaceholder = document.getElementById('footer-placeholder');
    
    // Load navbar from navbar.html file
    if (navbarPlaceholder) {
        try {
            const response = await fetch('../includes/navbar.html');
            const html = await response.text();
            navbarPlaceholder.innerHTML = html;
            initNavbar();
        } catch (error) {
            console.error('Error loading navbar:', error);
        }
    }
    
    // Load footer from footer.html file
    if (footerPlaceholder) {
        try {
            const response = await fetch('../includes/footer.html');
            const html = await response.text();
            footerPlaceholder.innerHTML = html;
        } catch (error) {
            console.error('Error loading footer:', error);
        }
    }
});

// Initialize navbar functionality
function initNavbar() {
    const currentPage = decodeURIComponent(window.location.pathname.split('/').pop()).toLowerCase();
    const navLinks = document.querySelectorAll('.navbar-menu a');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').toLowerCase();
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });
    
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }
}

// Booking modal functions
window.openCheckBookingModal = function() {
    document.getElementById('checkBookingModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('bookingRefInput').value = '';
    document.getElementById('bookingDetailsDisplay').style.display = 'none';
    document.getElementById('bookingErrorDisplay').style.display = 'none';
};

window.closeCheckBookingModal = function() {
    document.getElementById('checkBookingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
};

window.checkBookingStatus = async function() {
    const refInput = document.getElementById('bookingRefInput');
    const bookingRef = refInput.value.trim();
    const detailsDiv = document.getElementById('bookingDetailsDisplay');
    const errorDiv = document.getElementById('bookingErrorDisplay');
    
    detailsDiv.style.display = 'none';
    errorDiv.style.display = 'none';
    
    if (!bookingRef) {
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Please enter your booking reference number.';
        errorDiv.style.display = 'block';
        return;
    }
    
    try {
        const response = await fetch('../php/api/booking_public.php?ref=' + encodeURIComponent(bookingRef));
        const result = await response.json();
        
        if (result.success && result.booking) {
            const booking = result.booking;
            const checkIn = new Date(booking.check_in_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            const checkOut = new Date(booking.check_out_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            let statusColor = '#6b7280';
            if (booking.status === 'confirmed') statusColor = '#059669';
            else if (booking.status === 'pending') statusColor = '#d97706';
            else if (booking.status === 'checked_in') statusColor = '#2563eb';
            else if (booking.status === 'checked_out') statusColor = '#8b5cf6';
            else if (booking.status === 'cancelled') statusColor = '#dc2626';
            
            detailsDiv.innerHTML = '<h3 style="margin: 0 0 15px 0; color: #111827; font-size: 18px; font-weight: 600;"><i class="fas fa-check-circle" style="color: #059669;"></i> Booking Found</h3><div style="display: grid; gap: 12px;"><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Reference:</span><span style="color: #111827; font-weight: 600;">' + booking.booking_reference + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Status:</span><span style="background-color: ' + statusColor + '; color: white; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600; text-transform: uppercase;">' + booking.status.replace('_', ' ') + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Guest Name:</span><span style="color: #111827; font-weight: 600;">' + booking.guest_name + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Room Type:</span><span style="color: #111827; font-weight: 600;">' + booking.type_name + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Check-in:</span><span style="color: #111827; font-weight: 600;">' + checkIn + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Check-out:</span><span style="color: #111827; font-weight: 600;">' + checkOut + '</span></div><div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;"><span style="color: #6b7280; font-weight: 500;">Total Amount:</span><span style="color: #dc2626; font-weight: 700; font-size: 18px;">Rs. ' + parseFloat(booking.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) + '</span></div>' + (booking.status === 'confirmed' && booking.room_number ? '<div style="margin-top: 10px; padding: 12px; background-color: #dcfce7; border-radius: 6px; border-left: 3px solid #059669;"><i class="fas fa-door-open" style="color: #059669;"></i> <strong>Room ' + booking.room_number + '</strong> assigned.</div>' : '') + (booking.status === 'cancelled' && booking.cancellation_reason ? '<div style="margin-top: 10px; padding: 12px; background-color: #fee2e2; border-radius: 6px; border-left: 3px solid #dc2626;"><strong style="color: #991b1b;"><i class="fas fa-info-circle"></i> Cancellation:</strong><br><span style="color: #7f1d1d;">' + booking.cancellation_reason + '</span></div>' : '') + '</div>';
            
            detailsDiv.style.display = 'block';
        } else {
            errorDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + (result.error || 'Booking not found.');
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred.';
        errorDiv.style.display = 'block';
    }
};

window.onclick = function(event) {
    const modal = document.getElementById('checkBookingModal');
    if (event.target === modal) {
        window.closeCheckBookingModal();
    }
};

setTimeout(function() {
    const input = document.getElementById('bookingRefInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') window.checkBookingStatus();
        });
    }
}, 1000);
