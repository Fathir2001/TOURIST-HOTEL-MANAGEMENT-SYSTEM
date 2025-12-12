// Function to load CSS file into head
function loadCSS(href) {
    // Check if CSS is already loaded
    const existingLink = document.querySelector(`link[href="${href}"]`);
    if (!existingLink) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }
}

// Navbar HTML
const navbarHTML = `<!-- Navigation Section -->
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <img src="../images/logo.png" alt="Silverleaf" class="logo" onerror="this.style.display='none'">
            <span>Silverleaf</span>
        </div>
        <ul class="navbar-menu">
            <li><a href="HOME.html">Home</a></li>
            <li><a href="ABOUTUS.html">About Us</a></li>
            <li><a href="CONTACTUS.html">Contact Us</a></li>
            <li><a href="SERVICES.html">Services</a></li>
            <li><a href="THINGS TO DO.html">Things To Do</a></li>
            <li><a href="GALLARY.html">Gallery</a></li>
            <li><a href="ACCOMMODATION.html">Accommodation</a></li>
            <li><a href="FAQ.html">FAQ</a></li>
            <li><a href="ADMIN.html">Admin</a></li>
        </ul>
        <div class="navbar-actions">
            <button class="search-btn"><i class="fas fa-search"></i></button>
            <button class="book-btn">Book Now</button>
        </div>
        <button class="navbar-toggle" id="navbar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>`;

// Footer HTML
const footerHTML = `<!-- Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3>Follow Us</h3>
            <ul class="social-icons">
                <li><a href="#"><i class="fab fa-twitter fa-2x"></i></a></li>
                <li><a href="#"><i class="fab fa-facebook fa-2x"></i></a></li>
                <li><a href="#"><i class="fab fa-instagram fa-2x"></i></a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Useful Links</h3>
            <ul>
                <li><a href="#">Blog</a></li>
                <li><a href="#rooms">Rooms</a></li>
                <li><a href="#contact">Contact Us</a></li>
                <li><a href="#">Gift Card</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Navigation</h3>
            <ul>
                <li><a href="HOME.html">Home</a></li>
                <li><a href="ABOUTUS.html">About Us</a></li>
                <li><a href="CONTACTUS.html">Contact Us</a></li>
                <li><a href="SERVICES.html">Services</a></li>
                <li><a href="THINGS TO DO.html">Things To Do</a></li>
                <li><a href="GALLARY.html">Gallery</a></li>
                <li><a href="ACCOMMODATION.html">Accommodation</a></li>
                <li><a href="ADMIN.html">Admin</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Contact Information</h3>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Bandarawela, Sri Lanka</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone-alt"></i>
                <span>+94 XX XXX XXXX</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>info@bandarawelahotel.com</span>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2025 Bandarawela Hotel. All rights reserved.</p>
    </div>
</footer>

<script>
    // Mobile menu toggle
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    if (navbarToggle) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }
</script>`;

// Load navbar and footer when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load CSS files into head
    loadCSS('../css/navbar.css');
    loadCSS('../css/footer.css');
    
    // Insert HTML into placeholders
    const navbarPlaceholder = document.getElementById('navbar-placeholder');
    const footerPlaceholder = document.getElementById('footer-placeholder');
    
    if (navbarPlaceholder) {
        navbarPlaceholder.innerHTML = navbarHTML;
        
        // Highlight active page in navbar
        const currentPage = decodeURIComponent(window.location.pathname.split('/').pop());
        const navLinks = document.querySelectorAll('.navbar-menu a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
    }
    
    if (footerPlaceholder) {
        footerPlaceholder.innerHTML = footerHTML;
    }
});
