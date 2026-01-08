# TECHNICAL ARCHITECTURE GUIDE
## Tourist Hotel Management System

---

## 🏛️ System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT BROWSER                            │
│  (Chrome, Firefox, Safari, Edge - Desktop & Mobile)             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP/HTTPS
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      APACHE WEB SERVER                           │
│                    (Port 80/443 - WAMP)                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  STATIC CONTENT  │  │   PHP PROCESSOR  │
        │  (HTML/CSS/JS)   │  │    (PHP 7.4+)    │
        │  • Home Page     │  │  • Business Logic│
        │  • About Page    │  │  • API Endpoints │
        │  • Gallery       │  │  • Session Mgmt  │
        │  • Services      │  │  • Authentication│
        └──────────────────┘  └──────────────────┘
                                        │
                                        │ PDO
                                        ▼
                              ┌──────────────────┐
                              │  MySQL DATABASE  │
                              │  (MariaDB 10.x)  │
                              │  tourist_hotel_db│
                              │  • 6 Tables      │
                              │  • Relationships │
                              │  • Indexes       │
                              └──────────────────┘
```

---

## 🗂️ MVC-Inspired Architecture

While not a strict MVC framework, the system follows separation of concerns:

```
┌─────────────────────────────────────────────────────────────────┐
│                            VIEW LAYER                            │
│  HTML Files (html/*.HTML, html/*.PHP templates)                 │
│  CSS Stylesheets (css/*.css)                                    │
│  JavaScript (js/*.js)                                           │
│  Components (includes/navbar.html, includes/footer.html)       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        CONTROLLER LAYER                          │
│  PHP Scripts (php/*.php)                                        │
│  • process_booking.php      → Handle booking creation          │
│  • update_booking_status.php → Modify booking states           │
│  • Connect.php              → Route form submissions           │
│  • update_room_status.php   → Manage room availability         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         MODEL LAYER                              │
│  Database Access (php/config/database.php)                      │
│  Data Structures:                                               │
│  • Admins → Admin authentication                                │
│  • Room Types → Room categories & pricing                       │
│  • Rooms → Physical inventory                                   │
│  • Bookings → Reservations (stores guest info directly)        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Request Flow Diagrams

### 1. Customer Booking Flow

```
   [Customer]
       │
       │ 1. Browse site
       ▼
   [HOME.HTML]
       │
       │ 2. Click "Accommodation"
       ▼
[ACCOMMODATION.PHP] ←──── [get_rooms.php] ←──── [Database]
       │                  (Fetch room types)
       │ 3. View rooms
       │
       │ 4. Click "Book Now"
       ▼
   [Booking Modal]
       │
       │ 5. Fill form (dates, guests, contact)
       │    • Check-in date
       │    • Check-out date
       │    • Number of adults/children
       │    • Guest name, email, phone
       │    • Special requests
       │
       │ 6. Submit
       ▼
[process_booking.php]
       │
       ├─ Validate inputs
       ├─ Check availability
       ├─ Calculate pricing
       ├─ Generate booking reference
       │
       ▼
   [Database]
       │
       │ INSERT INTO bookings
       │
       ▼
[BOOKING_CONFIRMATION.PHP]
       │
       │ Display:
       │ • Booking reference
       │ • Confirmation details
       │ • Payment info
       │
       ▼
   [Customer receives confirmation]
```

---

### 2. Admin Dashboard Flow

```
   [Admin User]
       │
       │ 1. Navigate to admin portal
       ▼
   [ADMIN.HTML]
       │
       │ 2. Enter credentials
       │    Username: admin
       │    Password: Admin@123
       │
       │ 3. Submit login
       ▼
   [Connect.php]
       │
       ├─ Query admins table
       ├─ Verify bcrypt password
       ├─ Create session
       │    $_SESSION['admin_id']
       │    $_SESSION['admin_name']
       │    $_SESSION['logged_in'] = true
       │
       │ 4. Redirect on success
       ▼
[DASHBOARD.PHP]
       │
       ├─ Check session ─────────┐
       │  (If not logged in) ────┘ → Redirect to ADMIN.HTML
       │
       │ 5. Load dashboard
       │
       ├─ AJAX Request → [get_dashboard_stats.php]
       │                      │
       │                      ├─ Today's bookings
       │                      ├─ Total guests
       │                      ├─ Available rooms
       │                      └─ Monthly revenue
       │
       ├─ AJAX Request → [get_recent_data.php]
       │                      │
       │                      ├─ Recent bookings (5)
       │                      └─ Recent guests (5)
       │
       │ 6. Interact with sections
       │
       ├─ [Bookings Section]
       │    │
       │    ├─ AJAX → [get_bookings.php]
       │    │           │
       │    │           └─ Filter by status, search, dates
       │    │
       │    └─ Update Status → [update_booking_status.php]
       │                           │
       │                           ├─ Change status
       │                           └─ Assign room (if confirming)
       │
       ├─ [Rooms Section]
       │    │
       │    ├─ AJAX → [get_all_rooms.php]
       │    │           │
       │    │           └─ Fetch all 30 rooms
       │    │
       │    ├─ Add Room → [add_room.php]
       │    ├─ Delete Room → [delete_room.php]
       │    └─ Update Status → [update_room_status.php]
       │
       ├─ [Revenue Section]
       │    │
       │    └─ AJAX → [get_revenue.php]
       │                  │
       │                  ├─ Monthly breakdown
       │                  ├─ By room type
       │                  └─ Payment status
       │
       └─ [Settings Section]
            │
            ├─ Change Password → [update_password.php]
            └─ Change Username → [update_username.php]
```

---

### 3. Auto Status Update Flow (Cron Job)

```
   [Windows Task Scheduler] / [Cron Job]
       │
       │ Trigger: Daily at 00:00
       ▼
[auto_update_booking_status.php]
       │
       ├─ Connect to database
       │
       ├─ STEP 1: Auto Check-In
       │    │
       │    ├─ SELECT bookings WHERE
       │    │    status = 'confirmed'
       │    │    AND check_in_date = TODAY
       │    │
       │    └─ FOR EACH matching booking:
       │         │
       │         ├─ UPDATE bookings
       │         │    SET status = 'checked_in'
       │         │
       │         └─ UPDATE rooms
       │              SET status = 'occupied'
       │              WHERE room_id = booking.room_id
       │
       ├─ STEP 2: Auto Check-Out
       │    │
       │    ├─ SELECT bookings WHERE
       │    │    status = 'checked_in'
       │    │    AND check_out_date = TODAY
       │    │
       │    └─ FOR EACH matching booking:
       │         │
       │         ├─ UPDATE bookings
       │         │    SET status = 'checked_out'
       │         │
       │         └─ UPDATE rooms
       │              SET status = 'available'
       │              WHERE room_id = booking.room_id
       │
       └─ Log results & send notifications
```

---

## 📊 Database Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                       DATABASE SCHEMA                            │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│     admins       │
├──────────────────┤
│ admin_id (PK)    │
│ username         │
│ email            │
│ password_hash    │
│ full_name        │
│ role             │
│ status           │
│ last_login       │
└──────────────────┘

┌──────────────────┐
│   room_types     │
├──────────────────┤
│ room_type_id(PK) │◄──┐
│ type_name        │   │
│ description      │   │
│ base_price       │   │  ONE room_type
│ max_occupancy    │   │  has MANY rooms
│ size_sqm         │   │
│ bed_type         │   │
│ amenities (JSON) │   │
│ badge_label      │   │
│ features (JSON)  │   │
│ image_url        │   │
└──────────────────┘   │
                       │
                       │
┌──────────────────┐   │
│      rooms       │   │
├──────────────────┤   │
│ room_id (PK)     │   │
│ room_number      │   │
│ room_type_id(FK) │───┘
│ floor_number     │◄──┐
│ view_type        │   │
│ status           │   │  ONE room
│ notes            │   │  has MANY bookings
└──────────────────┘   │  (over time)
                       │
                       │
┌──────────────────┐   │
│    bookings      │   │
├──────────────────┤   │
│ booking_id (PK)  │   │
│ booking_ref      │   │
│ guest_name       │   │
│ guest_email      │   │
│ guest_phone      │   │
│ room_type_id(FK) │───┤
│ room_id (FK)     │───┘
│ check_in_date    │
│ check_out_date   │       │
│ adults           │       │
│ children         │       │
│ total_nights     │       │
│ room_price       │       │
│ subtotal         │       │
│ tax_amount       │       │
│ service_charge   │       │
│ total_amount     │       │
│ payment_status   │       │
│ booking_status   │       │
│ special_requests │
└──────────────────┘

RELATIONSHIPS:
• room_types (1) ─── (M) rooms
• room_types (1) ─── (M) bookings
• rooms (1) ─── (M) bookings

NOTE: Total 4 tables only. No contact_messages or users tables exist.
Contact page displays contact information only without form submission.
Bookings store guest information directly without user accounts.
```

---

## 🔐 Authentication & Session Management

### Session Flow

```
1. LOGIN PROCESS
   ↓
[User submits username & password]
   ↓
[Connect.php receives POST data]
   ↓
[Query: SELECT * FROM admins WHERE username = ?]
   ↓
[Verify: password_verify($input, $db_hash)]
   ↓
   ├─ SUCCESS:
   │   ├─ session_start()
   │   ├─ $_SESSION['admin_id'] = $admin_id
   │   ├─ $_SESSION['admin_name'] = $full_name
   │   ├─ $_SESSION['admin_role'] = $role
   │   ├─ $_SESSION['logged_in'] = true
   │   ├─ UPDATE admins SET last_login = NOW()
   │   └─ Redirect to DASHBOARD.PHP
   │
   └─ FAILURE:
       └─ Return error message

2. PROTECTED PAGE ACCESS
   ↓
[User navigates to DASHBOARD.PHP]
   ↓
[Check: isset($_SESSION['logged_in'])]
   ↓
   ├─ TRUE: Allow access, render dashboard
   │
   └─ FALSE: Redirect to ADMIN.HTML

3. LOGOUT
   ↓
[User clicks logout]
   ↓
[logout.php]
   ↓
   ├─ session_start()
   ├─ session_unset()
   ├─ session_destroy()
   ├─ setcookie(session_name(), '', time()-3600)
   └─ Redirect to ADMIN.HTML
```

### Password Hashing

```php
// Registration / Password Creation
$password = "Admin@123";
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
// Result: $2y$10$95zu.K1w92MsO7gM8J1biuMai6LD2aJx4FXZg2SXS6jBafXJOEi1a

// Login / Password Verification
$input_password = $_POST['password'];
$db_hash = "...$2y$10$..."; // from database
$verified = password_verify($input_password, $db_hash);
// Returns: true if match, false if not
```

---

## 📡 API Architecture

### RESTful-Style Endpoints

```
GET Endpoints (Data Retrieval):
├─ get_rooms.php              → Room types catalog
├─ get_all_rooms.php          → Room inventory
├─ get_available_rooms.php    → Availability check
├─ get_bookings.php           → Booking records
├─ get_guests.php             → Guest list
├─ get_dashboard_stats.php    → Analytics
├─ get_revenue.php            → Financial data
├─ get_recent_data.php        → Recent activity
├─ get_booking_details.php    → Single booking
├─ get_next_room_number.php   → Room number suggestion
└─ get_room_types_list.php    → Room types for dropdown

POST Endpoints (Data Modification):
├─ process_booking.php        → Create booking
├─ update_booking_status.php  → Modify booking
├─ update_room_status.php     → Change room status
├─ add_room.php               → Create room
├─ delete_room.php            → Remove room
├─ update_room_type.php       → Edit room type
├─ update_room_type_image.php → Upload room image
├─ update_password.php        → Change admin password
├─ update_username.php        → Change admin username
└─ Connect.php                → Multi-purpose handler
```

### Request/Response Format

**Request Example (JavaScript):**
```javascript
// GET Request
const response = await fetch('php/get_bookings.php?status=pending');
const data = await response.json();

// POST Request
const formData = new FormData();
formData.append('booking_id', 123);
formData.append('new_status', 'confirmed');

const response = await fetch('php/update_booking_status.php', {
  method: 'POST',
  body: formData
});
const result = await response.json();
```

**Response Format:**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    "booking_id": 123,
    "booking_reference": "BK20260108-1234",
    "status": "confirmed"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "error": "Check-in date cannot be in the past"
}
```

---

## 🎨 Frontend Component Architecture

### Page Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                          PAGE LAYOUT                             │
├─────────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │                 NAVBAR (includes/navbar.html)               │ │
│ │  Logo | Home | About | Services | Gallery | Accommodation  │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │                      HERO SECTION                           │ │
│ │  Background Image | Title | Subtitle | CTA                 │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │                     MAIN CONTENT                            │ │
│ │  • Sections                                                 │ │
│ │  • Cards                                                    │ │
│ │  • Forms                                                    │ │
│ │  • Tables                                                   │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │                 FOOTER (includes/footer.html)               │ │
│ │  Links | Contact | Social Media | Copyright                │ │
│ └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### CSS Architecture

```
STYLE.CSS (Global Styles)
├─ CSS Variables (Colors, Fonts, Spacing)
├─ Reset Styles
├─ Typography
├─ Common Classes (.container, .btn, etc.)
├─ Navbar Styles
├─ Footer Styles
└─ Utility Classes

Page-Specific CSS
├─ home.css          → Homepage slideshow, welcome section
├─ accommodation.css → Room cards, booking modal
├─ dashboard.css     → Admin panel, sidebar, tables
├─ services.css      → Service cards grid
├─ gallary.css       → Photo grid layout
└─ [other pages].css → Page-specific styles

Component CSS
├─ navbar.css        → Navigation bar
├─ footer.css        → Footer section
├─ testimonials.css  → Review cards
└─ faq.css           → Accordion styles
```

### JavaScript Module Structure

```
include.js
├─ loadCSS()           → Dynamically load CSS
├─ loadNavbar()        → Inject navbar HTML
├─ loadFooter()        → Inject footer HTML
└─ setActiveMenu()     → Highlight current page

hero-slideshow.js
├─ DOMContentLoaded    → Wait for page load
├─ slideshow()         → Rotate hero images
└─ interval(5000)      → 5-second transitions

hero-effects.js
├─ scrollReveal()      → Animate on scroll
├─ parallaxEffect()    → Background parallax
└─ smoothScroll()      → Smooth anchor links

Dashboard JavaScript (Inline in DASHBOARD.PHP)
├─ showSection()       → Switch dashboard sections
├─ loadBookings()      → Fetch bookings via AJAX
├─ updateBooking()     → Update booking status
├─ loadRooms()         → Fetch room inventory
├─ updateRoom()        → Update room status
├─ loadRevenue()       → Fetch revenue data
└─ refreshStats()      → Update dashboard cards
```

---

## 🔄 State Management

### Booking Status State Machine

```
┌─────────────────────────────────────────────────────────────────┐
│                    BOOKING STATUS STATES                         │
└─────────────────────────────────────────────────────────────────┘

           pending
              │
              │ (Admin assigns room)
              ▼
          confirmed
              │
              │ (Check-in date OR manual)
              ▼
         checked_in
              │
              │ (Check-out date OR manual)
              ▼
        checked_out
        
        (Any state can transition to)
              │
              ▼
          cancelled

VALID TRANSITIONS:
• pending → confirmed (requires room_id)
• pending → cancelled
• confirmed → checked_in
• confirmed → cancelled
• checked_in → checked_out
• checked_in → cancelled (rare)

INVALID TRANSITIONS:
• checked_out → checked_in (cannot reverse)
• cancelled → any other state (final state)
```

### Room Status States

```
┌─────────────────────────────────────────────────────────────────┐
│                     ROOM STATUS STATES                           │
└─────────────────────────────────────────────────────────────────┘

        available
           │
           │ (Booking confirmed & checked in)
           ▼
        occupied
           │
           │ (Guest checks out)
           ▼
        available
           
        (From any state)
           │
           ▼
       maintenance
           │
           │ (Repairs complete)
           ▼
        available

RULES:
• Room cannot be "available" if has active booking (checked_in)
• Room cannot be "occupied" without assigned booking
• "maintenance" blocks room from new bookings
```

---

## 💾 Data Flow Patterns

### Optimistic vs Pessimistic Locking

**Current Implementation: Soft Locking**

```
Booking Availability Check:
1. User selects dates
2. System queries available rooms
3. User fills form
4. System re-checks availability before insert
5. Insert booking if still available
6. Return error if no longer available

NO HARD LOCKS:
• No row-level locking during check
• Race condition possible (rare)
• Transaction isolation level: READ COMMITTED
```

**Improvement Option: Pessimistic Locking**

```sql
START TRANSACTION;
SELECT * FROM rooms 
WHERE room_type_id = ? 
AND status = 'available'
FOR UPDATE;  -- Lock row

-- Insert booking
INSERT INTO bookings ...

UPDATE rooms SET status = 'occupied' ...

COMMIT;  -- Release lock
```

---

## 🚀 Performance Optimization

### Database Query Optimization

**Indexes:**
```sql
-- Primary Keys (auto-indexed)
admins(admin_id)
room_types(room_type_id)
rooms(room_id)
bookings(booking_id)

-- Secondary Indexes
bookings(check_in_date, check_out_date)
bookings(booking_status)
bookings(guest_email)
bookings(created_at)
rooms(status)
rooms(room_type_id)
admins(username)
```

**Query Patterns:**
```sql
-- GOOD: Uses index
SELECT * FROM bookings 
WHERE booking_status = 'pending'
ORDER BY created_at DESC
LIMIT 10;

-- BAD: Full table scan
SELECT * FROM bookings 
WHERE YEAR(created_at) = 2026;

-- BETTER: Uses index
SELECT * FROM bookings 
WHERE created_at >= '2026-01-01' 
AND created_at < '2027-01-01';
```

### Caching Strategy

```
┌─────────────────────────────────────────────────────────────────┐
│                      CACHING LAYERS                              │
└─────────────────────────────────────────────────────────────────┘

BROWSER CACHE
├─ Static Assets (CSS, JS, Images)
├─ Cache-Control: max-age=31536000 (1 year)
└─ Versioning: ?v=1.0.0

PHP OpCache (Recommended)
├─ Compiled PHP bytecode
├─ Reduces script parsing time
└─ Enabled in php.ini

Application-Level Cache (Future)
├─ Dashboard stats (5-minute cache)
├─ Room types (1-hour cache)
└─ Available rooms (no cache - real-time)
```

---

## 🔧 Deployment Architecture

### Development Environment

```
Local Machine (WAMP)
├─ Windows 10/11
├─ Apache 2.4
├─ PHP 7.4
├─ MySQL 5.7
└─ http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/
```

### Production Environment (Recommended)

```
Web Server
├─ Linux (Ubuntu 20.04 LTS)
├─ Apache 2.4 or Nginx
├─ PHP 8.0+
├─ MySQL 8.0 or MariaDB 10.6
├─ SSL Certificate (Let's Encrypt)
└─ https://www.titaniahotel.lk/

Reverse Proxy (Optional)
├─ Nginx (Front)
└─ Apache (Back)

Load Balancer (Future Scale)
├─ Multiple web servers
└─ Database replication
```

### File Structure in Production

```
/var/www/titaniahotel.lk/
├─ public_html/               ← Document root
│   ├─ index.html
│   ├─ html/
│   ├─ css/
│   ├─ js/
│   ├─ images/
│   └─ includes/
├─ php/                       ← Outside document root (security)
│   ├─ config/
│   └─ [all PHP scripts]
├─ logs/                      ← Application logs
│   ├─ error.log
│   └─ access.log
└─ backups/                   ← Database backups
    └─ tourist_hotel_db_*.sql
```

---

## 🔐 Security Architecture

### Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                      SECURITY LAYERS                             │
└─────────────────────────────────────────────────────────────────┘

LAYER 1: NETWORK SECURITY
├─ Firewall (ports 80, 443 only)
├─ HTTPS/TLS encryption
└─ IP whitelisting for admin (optional)

LAYER 2: APPLICATION SECURITY
├─ Input validation & sanitization
├─ Output encoding (XSS prevention)
├─ Prepared statements (SQL injection prevention)
├─ CSRF tokens (recommended)
└─ Rate limiting (future)

LAYER 3: AUTHENTICATION & AUTHORIZATION
├─ Bcrypt password hashing
├─ Session management
├─ Role-based access (admin/staff)
└─ Password complexity requirements

LAYER 4: DATABASE SECURITY
├─ Separate database user (not root)
├─ Limited privileges (no DROP, no CREATE USER)
├─ Connection over localhost only
└─ Regular backups

LAYER 5: FILE SYSTEM SECURITY
├─ Proper permissions (755/644)
├─ Upload validation
├─ No execution in upload directories
└─ Sensitive files outside document root
```

### Threat Model

```
THREATS & MITIGATIONS:

SQL Injection
├─ Threat: Malicious SQL in user input
├─ Mitigation: PDO prepared statements
└─ Status: ✅ Protected

XSS (Cross-Site Scripting)
├─ Threat: Malicious JavaScript injection
├─ Mitigation: htmlspecialchars() on output
└─ Status: ⚠️ Partial (needs CSP headers)

CSRF (Cross-Site Request Forgery)
├─ Threat: Unauthorized state-changing requests
├─ Mitigation: None currently
└─ Status: ❌ Vulnerable (needs CSRF tokens)

Session Hijacking
├─ Threat: Session ID theft
├─ Mitigation: HTTPS, secure cookies
└─ Status: ⚠️ Partial (needs HTTPS in production)

Brute Force Login
├─ Threat: Password guessing attacks
├─ Mitigation: None currently
└─ Status: ❌ Vulnerable (needs rate limiting)

File Upload Attacks
├─ Threat: Malicious file execution
├─ Mitigation: File type validation, unique names
└─ Status: ✅ Protected
```

---

## 📈 Scalability Considerations

### Current Limitations

```
Single Server Architecture
├─ Single point of failure
├─ Limited concurrent users (~100)
├─ No horizontal scaling
└─ Database bottleneck possible
```

### Scaling Path

```
STAGE 1: Optimization (Current)
├─ Add database indexes ✅
├─ Enable OpCache
├─ Optimize images
└─ Browser caching

STAGE 2: Vertical Scaling
├─ Increase server resources (CPU, RAM)
├─ Optimize database queries
└─ Add application-level caching

STAGE 3: Horizontal Scaling
├─ Load balancer
├─ Multiple web servers
├─ Database read replicas
└─ CDN for static assets

STAGE 4: Microservices (Future)
├─ Separate booking service
├─ Separate payment service
├─ API gateway
└─ Message queue (RabbitMQ)
```

---

## 🧪 Testing Architecture

### Testing Strategy (Recommended)

```
UNIT TESTS
├─ Test individual PHP functions
├─ Mock database connections
└─ Tools: PHPUnit

INTEGRATION TESTS
├─ Test API endpoints
├─ Test database operations
└─ Tools: PHPUnit + test database

FUNCTIONAL TESTS
├─ Test user workflows
├─ Test booking process
└─ Tools: Selenium, Cypress

PERFORMANCE TESTS
├─ Load testing (concurrent users)
├─ Stress testing (breaking point)
└─ Tools: Apache JMeter, k6

SECURITY TESTS
├─ Penetration testing
├─ Vulnerability scanning
└─ Tools: OWASP ZAP, Burp Suite
```

---

## 📊 Monitoring & Logging

### Application Monitoring

```
METRICS TO TRACK:
├─ Response time
├─ Error rate
├─ Booking conversion rate
├─ Database query performance
├─ Server resource usage (CPU, RAM, disk)
└─ User sessions

LOGGING LEVELS:
├─ ERROR: Critical failures
├─ WARNING: Non-critical issues
├─ INFO: Normal operations
└─ DEBUG: Detailed debugging info

LOG FILES:
├─ php_error.log
├─ apache_error.log
├─ application.log (custom)
└─ booking.log (transaction log)
```

---

**Technical Architecture Guide Version 1.0**  
**Last Updated:** January 8, 2026

---

*For implementation details, see PROJECT_DOCUMENTATION.md*  
*For quick reference, see QUICK_REFERENCE.md*
