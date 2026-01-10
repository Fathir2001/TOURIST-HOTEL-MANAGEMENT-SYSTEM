# TOURIST HOTEL MANAGEMENT SYSTEM - COMPLETE DOCUMENTATION

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Installation & Setup](#installation--setup)
4. [Database Structure](#database-structure)
5. [Frontend Pages Documentation](#frontend-pages-documentation)
6. [Backend PHP Scripts](#backend-php-scripts)
7. [Admin Dashboard](#admin-dashboard)
8. [Features & Functionality](#features--functionality)
9. [Security Implementation](#security-implementation)
10. [API Endpoints](#api-endpoints)
11. [Maintenance & Troubleshooting](#maintenance--troubleshooting)

---

## 🎯 Project Overview

**Project Name:** Tourist Hotel Management System (Titania Hotel)  
**Location:** Bandarawela, Sri Lanka  
**Type:** Full-stack web application for hotel management  
**Technology Stack:**
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla JS)
- **Backend:** PHP 7.4+
- **Database:** MySQL (MariaDB compatible)
- **Server:** Apache (WAMP/XAMPP)

**Purpose:** A comprehensive hotel management system for Titania Hotel in Bandarawela that handles:
- Public website with hotel information
- Online room booking system
- Admin dashboard for hotel management
- Real-time availability tracking
- Booking and revenue management

---

## 🏗️ System Architecture

### Directory Structure

```
TOURIST-HOTEL-MANAGEMENT-SYSTEM/
├── index.html                 # Root redirect to HOME.HTML
├── backups/                   # Backup files for HTML/CSS
├── css/                       # All stylesheet files
│   ├── STYLE.CSS             # Global styles
│   ├── home.css              # Home page specific
│   ├── accommodation.css     # Accommodation page
│   ├── dashboard.css         # Admin dashboard
│   ├── navbar.css            # Navigation bar
│   ├── footer.css            # Footer styles
│   └── [other page styles]
├── html/                      # All HTML/PHP pages
│   ├── HOME.HTML             # Landing page
│   ├── ACCOMMODATION.PHP     # Room booking page
│   ├── DASHBOARD.PHP         # Admin control panel
│   ├── ADMIN.HTML            # Admin login
│   ├── ABOUTUS.HTML          # About page
│   ├── SERVICES.HTML         # Services listing
│   ├── THINGS TO DO.HTML     # Activities page
│   ├── GALLARY.HTML          # Photo gallery
│   ├── TESTIMONIALS.HTML     # Customer reviews
│   ├── FAQ.HTML              # Frequently asked questions
│   ├── CONTACTUS.HTML        # Contact information
│   └── BOOKING_CONFIRMATION.PHP  # Booking success
├── images/                    # All image assets
│   └── room-types/           # Room type images
├── includes/                  # Reusable components
│   ├── navbar.html           # Navigation menu
│   └── footer.html           # Footer component
├── js/                        # JavaScript files
│   ├── include.js            # Component loader
│   ├── hero-slideshow.js     # Homepage slideshow
│   └── hero-effects.js       # Animation effects
├── php/                       # Backend PHP scripts
│   ├── Connect.php           # Legacy database connection
│   ├── process_booking.php   # Booking processor
│   ├── get_rooms.php         # Room data fetcher
│   ├── get_dashboard_stats.php  # Dashboard statistics
│   ├── get_bookings.php      # Booking data API
│   ├── get_guests.php        # Guest data API
│   ├── get_revenue.php       # Revenue analytics
│   ├── update_booking_status.php  # Status updater
│   ├── add_room.php          # Add new rooms
│   ├── delete_room.php       # Remove rooms
│   ├── update_room_status.php  # Room availability
│   ├── update_room_type.php  # Edit room types
│   ├── update_password.php   # Admin password change
│   ├── update_username.php   # Admin username change
│   ├── logout.php            # Session termination
│   ├── schema.sql            # Database schema
│   └── config/               # Configuration files
│       ├── config.php        # Global settings
│       └── database.php      # Database connection
└── setup_room_images.ps1     # PowerShell setup script
```

### Technology Stack Details

**Frontend Technologies:**
- HTML5 (Semantic markup)
- CSS3 (Grid, Flexbox, Animations)
- JavaScript ES6+ (Async/Await, Fetch API, REST integration)
- Font Awesome 6.0 (Icons)

**Backend Technologies:**
- PHP 7.4+ (PDO for database, RESTful API)
- MySQL 5.7+ / MariaDB 10.x
- Apache Web Server
- Session-based authentication

**Design Pattern:**
- MVC-inspired architecture with REST API
- Separation of concerns (HTML/CSS/JS/PHP)
- Modular component design
- Dynamic component loading (navbar/footer)

**New Features (v2.0):**
- RESTful API endpoints (GET, POST, PATCH, DELETE)
- Track Booking feature (public booking lookup)
- Image upload system for room types
- Cancellation tracking with reasons and timestamps
- Dynamic HTML component loading

---

## 💾 Database Structure

### Database Name: `hotel_management`

### Tables Overview

#### 1. **admins** - Administrator Accounts
Stores admin user credentials and profiles.

**Columns:**
- `admin_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `username` (VARCHAR(50), UNIQUE, NOT NULL)
- `email` (VARCHAR(100), UNIQUE, NOT NULL)
- `password_hash` (VARCHAR(255), NOT NULL) - Bcrypt hashed
- `full_name` (VARCHAR(100), NOT NULL)
- `role` (ENUM: 'admin', 'staff') - Role-based access
- `phone` (VARCHAR(20))
- `status` (TINYINT(1)) - 1=active, 0=inactive
- `last_login` (DATETIME)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

**Default Admin Account:**
- Username: `admin`
- Password: `Admin@123`
- Email: `admin@touristhotel.lk`

#### 2. **room_types** - Room Categories
Defines available room types with pricing and amenities.

**Columns:**
- `room_type_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `type_name` (VARCHAR(50), UNIQUE, NOT NULL)
- `description` (TEXT)
- `base_price` (DECIMAL(10,2)) - Price per night
- `max_occupancy` (INT) - Maximum guests
- `size_sqm` (DECIMAL(6,2)) - Room size in square meters
- `bed_type` (VARCHAR(50)) - e.g., King, Queen
- `amenities` (TEXT) - JSON array of amenities
- `badge_label` (VARCHAR(50)) - Premium, Heritage, Deluxe, etc.
- `features` (TEXT) - JSON array of features
- `image_url` (VARCHAR(255)) - Stored in images/room-types/
- `status` (TINYINT(1)) - Active/Inactive
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

**Room Types (6 types):**
1. Presidential Suite - $350/night (4 guests max)
2. The Heritage Suite - $280/night (3 guests max)
3. Deluxe Double Room - $180/night (2 guests max)
4. Premium Garden Wing - $220/night (3 guests max)
5. Standard Triple Room - $150/night (3 guests max)
6. Standard Double Room - $120/night (2 guests max)

#### 3. **rooms** - Physical Room Inventory
Individual room units (30 total rooms).

**Columns:**
- `room_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `room_number` (VARCHAR(10), UNIQUE, NOT NULL)
- `room_type_id` (INT, FOREIGN KEY → room_types)
- `floor_number` (INT)
- `view_type` (VARCHAR(50)) - Garden, Mountain, City, Hill
- `status` (ENUM: 'available', 'occupied', 'maintenance')
- `notes` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

**Room Distribution:**
- Floor 4: 2 Presidential Suites (401, 402)
- Floor 3: 3 Heritage Suites (301-303)
- Floor 2: 5 Deluxe Double + 6 Standard Double (201-211)
- Floor 1: 6 Standard Triple + 4 Standard Double (101-110)
- Ground: 4 Premium Garden Wing (G01-G04)

#### 4. **bookings** - Reservation Records
All customer bookings and reservations.

**Columns:**
- `booking_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `booking_reference` (VARCHAR(20), UNIQUE) - Format: BK20260110-XXXX
- `guest_name` (VARCHAR(100), NOT NULL)
- `guest_email` (VARCHAR(100), NOT NULL)
- `guest_phone` (VARCHAR(20), NOT NULL)
- `guest_address` (TEXT)
- `room_type_id` (INT, FOREIGN KEY → room_types)
- `room_id` (INT, FOREIGN KEY → rooms) - Assigned during confirmation
- `check_in_date` (DATE, NOT NULL)
- `check_out_date` (DATE, NOT NULL)
- `adults` (INT, NOT NULL)
- `children` (INT, DEFAULT 0)
- `number_of_rooms` (INT, DEFAULT 1)
- `total_nights` (INT, NOT NULL)
- `room_price` (DECIMAL(10,2)) - Per night price
- `subtotal` (DECIMAL(10,2))
- `tax_amount` (DECIMAL(10,2)) - 10% tax
- `service_charge` (DECIMAL(10,2)) - 5% service charge
- `total_amount` (DECIMAL(10,2))
- `payment_status` (ENUM: 'pending', 'paid', 'cancelled')
- `payment_method` (VARCHAR(50))
- `status` (ENUM: 'pending', 'confirmed', 'cancelled', 'completed')
- `confirmed_at` (DATETIME) - Auto-set when status → confirmed
- `cancelled_at` (DATETIME) - Auto-set when status → cancelled
- `cancellation_reason` (TEXT) - Admin-entered cancellation reason
- `special_requests` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

**Booking Workflow:**
1. pending → Initial booking created (via Track Booking, guests can check status)
2. confirmed → Admin assigns room (timestamp recorded in confirmed_at)
3. cancelled → Booking cancelled (timestamp + reason recorded)
4. completed → Booking completed after checkout

**Track Booking Feature:**
- Guests can check booking status using booking_reference
- Access via "Track Booking" link in navbar (yellow/gold color)
- Shows status, room type, dates, confirmation/cancellation details
- No authentication required (public API endpoint)

---

## 🌐 Frontend Pages Documentation

### 1. **HOME.HTML** - Landing Page
**URL:** `html/HOME.HTML` (Default entry point)

**Features:**
- Full-screen hero section with animated background slideshow
- 3 rotating hero images (5-second intervals)
- Scroll indicator animation
- Dynamic navbar with Track Booking feature
- Welcome section with hotel description
- Gallery preview (8 images)
- Luxurious rooms section with quick links
- Navigation menu integration

**Key Elements:**
- Hero slideshow (powered by `hero-slideshow.js`)
- Responsive image gallery
- Call-to-action buttons
- Smooth scroll animations

**Linked Resources:**
- CSS: `home.css`, `STYLE.CSS`
- JS: `include.js`, `hero-slideshow.js`

---

### 2. **ACCOMMODATION.PHP** - Room Booking Page
**URL:** `html/ACCOMMODATION.PHP`

**Features:**
- Dynamic room display from database
- Room filtering and search
- Real-time availability check
- Interactive booking modal
- Price calculation with taxes
- Responsive room cards

**Room Display:**
- Room type name and description
- High-quality images with fallback
- Price per night display
- Max occupancy information
- Room size in square feet
- Amenity list with icons
- Feature highlights
- "Book Now" buttons

**Booking Modal:**
- Date picker (check-in/check-out)
- Guest count selector (adults/children)
- Guest information form
- Special requests field
- Real-time price calculation
- Validation for all fields

**Backend Integration:**
- Fetches data from `get_rooms.php`
- Submits to `process_booking.php`
- Validates availability

**Linked Resources:**
- CSS: `accommodation.css`, `STYLE.CSS`
- PHP: `get_rooms.php`, `process_booking.php`

---

### 3. **DASHBOARD.PHP** - Admin Control Panel
**URL:** `html/DASHBOARD.PHP` (Requires authentication)

**Access Control:**
- Session-based authentication required
- Redirects to ADMIN.HTML if not logged in
- Stores admin info in session

**Dashboard Sections:**

#### A. Dashboard Overview (Default View)
- Today's bookings count with trend indicator
- Total guests (unique)
- Available rooms count
- Monthly revenue with comparison
- Recent bookings table (latest 5)
- Recent guests list

#### B. Bookings Management
- Complete bookings table with filters
- Filter by status: All, Pending, Confirmed, Checked-In, Checked-Out, Cancelled
- Date range filtering
- Actions:
  - View details
  - Update status
  - Assign room
  - Cancel booking

**Booking Status Updates:**
- Pending → Confirmed (assign room)
- Confirmed → Checked-In (Auto)
- Checked-In → Checked-Out (Auto)
- Any → Cancelled

#### C. Guests Management
- List of all guests from bookings
- View booking history per guest
- Contact information display
- Total bookings per guest
- Guest statistics

#### D. Rooms Management
- Room inventory display (30 rooms)
- Filter by room type, floor, or status
- View room details
- Update room status:
  - Available
  - Occupied
  - Maintenance
- Add new rooms
- Edit room information
- Delete rooms (if no active bookings)
- Room type management:
  - Edit room type details
  - Update pricing
  - Change images
  - Modify amenities

#### E. Revenue Analytics
- Monthly revenue breakdown
- Year-to-date total
- Revenue by room type
- Payment status tracking
- Revenue trends chart
- Occupancy rate calculation
- Average daily rate (ADR)
- Revenue per available room (RevPAR)

#### F. Settings
- Admin profile management
- Change username
- Update password
- Email configuration
- System preferences
- Backup/restore options

**Sidebar Navigation:**
- Dashboard (overview)
- Bookings
- Guests
- Rooms
- Revenue
- Settings
- View Website (return to public site)
- Logout

**Top Bar:**
- Current section title
- Admin profile display
- Admin name and role
- Responsive menu toggle

**Real-time Updates:**
- Auto-refresh statistics
- Live booking status
- Availability tracking

**Linked Resources:**
- CSS: `dashboard.css`, `STYLE.CSS`
- PHP: Multiple backend scripts (detailed in Backend section)
- JS: Inline JavaScript for dashboard interactions

---

### 4. **ADMIN.HTML** - Admin Login Page
**URL:** `html/ADMIN.HTML`

**Features:**
- Secure login form
- Username and password fields
- Password visibility toggle
- "Remember me" checkbox
- Back to website link

**Form Submission:**
- Posts to `Connect.php`
- Session creation on success
- Redirect to DASHBOARD.PHP
- Error handling with messages

**Security:**
- Password input type toggle
- CSRF protection
- Session management
- Bcrypt password hashing

**Admin Features List:**
- Manage Bookings
- Guest Management
- Room Inventory
- Revenue Reports
- Analytics & Insights
- System Settings

**Linked Resources:**
- CSS: `admin.css`, `STYLE.CSS`
- PHP: `Connect.php` (handles authentication)

---

### 5. **ABOUTUS.HTML** - About Hotel Page
**URL:** `html/ABOUTUS.HTML`

**Content:**
- Hotel description and history
- Property highlights
- Comfortable accommodations description
- Dining and leisure facilities
- Location and accessibility info
- Guest satisfaction ratings

**Features Section:**
- Most popular facilities (10 items)
- Amenities list
- Facilities by category:
  - Activities (bicycle rental, cooking class, cycling)
  - Food & Drink (restaurant, bar, tea/coffee, breakfast)
  - Internet (Free WiFi)
  - Parking (Free on-site parking)
  - Services (Room service, 24-hour front desk, currency exchange, tour desk, laundry, dry cleaning, ironing)
  - General (Designated smoking areas, air conditioning, soundproofing, carpeted floors, heating, BBQ facilities)
  - Safety & Security (Fire extinguishers, CCTV, smoke alarms, security alarms, key card access, 24-hour security, safe)

**Linked Resources:**
- CSS: `aboutus.css`, `STYLE.CSS`

---

### 6. **SERVICES.HTML** - Hotel Services
**URL:** `html/SERVICES.HTML`

**Service Categories (12 services):**

1. **Culture Show** - Traditional Sri Lankan cultural performances
2. **Room Service** - 24/7 in-room dining
3. **WiFi** - Free high-speed internet throughout property
4. **Breakfast** - Daily buffet breakfast
5. **Barbecue** - Outdoor BBQ facilities
6. **Laundry Service** - Professional cleaning service
7. **Parking** - Free on-site parking
8. **Swimming Pool** - Temperature-controlled pool
9. **Spa & Wellness** - Ayurvedic treatments and massages
10. **Restaurant** - Multi-cuisine dining
11. **Bar** - Premium beverages and cocktails
12. **24/7 Front Desk** - Round-the-clock assistance

**Each Service Card Includes:**
- Service image
- Icon representation
- Service name
- Detailed description
- Key features list

**Linked Resources:**
- CSS: `services.css`, `STYLE.CSS`

---

### 7. **THINGS TO DO.HTML** - Activities Page
**URL:** `html/THINGS TO DO.HTML`

**Categories:**

#### Excursions (7 activities):
1. **Trek to Ella Rock** - 4-hour mountain trek
2. **Trek to Little Adam's Peak** - Sunrise hike
3. **Trek to Lipton's Seat** - Tea plantation views
4. **Trek to Diyaluma Waterfall** - Waterfall swimming
5. **Visit Nine Arch Bridge** - Historic railway bridge
6. **Tea Factory Tour** - Ceylon tea production
7. **Visit Ravana Falls** - Scenic waterfall

#### Hotel Experiences (7 activities):
1. **Ayurvedic Spa Treatments** - Traditional wellness
2. **Traditional Sri Lankan Cooking Classes** - Hands-on cooking
3. **Cocktail Mixology and Tasting** - Beverage workshop
4. **Coconut Arrack Tasting Experience** - Local spirits
5. **Tour Our Organic Farm** - Farm-to-table tour
6. **Guided Meditation & Yoga** - Wellness sessions
7. **Cultural Evening Performance** - Traditional dances

**Activity Card Details:**
- Activity image
- Activity name
- Duration
- Difficulty level (for treks)
- Description
- What to expect
- "Book Activity" button

**Linked Resources:**
- CSS: `things-to-do.css`, `STYLE.CSS`

---

### 8. **GALLARY.HTML** - Photo Gallery
**URL:** `html/GALLARY.HTML`

**Features:**
- Grid layout photo gallery
- Multiple image categories:
  - Hotel exterior
  - Rooms and suites
  - Restaurant and dining
  - Spa and wellness
  - Pool and garden
  - Activities
  - Surrounding areas
- Responsive grid (3-4 columns)
- Image lightbox/modal view
- High-quality images

**Linked Resources:**
- CSS: `gallary.css`, `STYLE.CSS`

---

### 9. **TESTIMONIALS.HTML** - Customer Reviews
**URL:** `html/TESTIMONIALS.HTML`

**Features:**
- Customer testimonial cards
- Star ratings (1-5 stars)
- Customer name and location
- Review date
- Review text
- Customer photo/avatar
- Overall rating summary
- Categories:
  - Service Quality
  - Room Comfort
  - Location
  - Cleanliness
  - Value for Money

**Linked Resources:**
- CSS: `testimonials.css`, `STYLE.CSS`

---

### 10. **FAQ.HTML** - Frequently Asked Questions
**URL:** `html/FAQ.HTML`

**Question Categories:**

1. **Booking & Reservations**
   - How to book a room
   - Cancellation policy
   - Payment methods
   - Booking modifications

2. **Check-in & Check-out**
   - Check-in time (2:00 PM)
   - Check-out time (12:00 PM)
   - Early check-in/late check-out
   - ID requirements

3. **Room & Amenities**
   - Room types available
   - Room amenities
   - Extra bed requests
   - Room service hours

4. **Hotel Facilities**
   - WiFi availability
   - Parking facilities
   - Restaurant hours
   - Pool access

5. **Policies**
   - Pet policy
   - Smoking policy
   - Child policy
   - Age restrictions

**Features:**
- Accordion-style Q&A
- Expandable/collapsible sections
- Search FAQ functionality
- Category filtering

**Linked Resources:**
- CSS: `faq.css`, `STYLE.CSS`

---

### 11. **CONTACTUS.HTML** - Contact Information
**URL:** `html/CONTACTUS.HTML`

**Contact Information Display:**
- Hotel address: Bandarawela, Sri Lanka
- Phone: 057 22 22 501 / 057 22 31 190
- Email: info@touristhotel.lk
- Operating hours: 24/7

**Features:**
- Contact information cards with icons
  - Location with map marker icon
  - Phone numbers with phone icon
  - Email address with envelope icon
- Google Maps integration (embedded interactive map)
- Find Us On The Map section

**Note:** Contact form has been removed. Visitors can contact via phone or email directly.

**Linked Resources:**
- CSS: `contactus.css`, `STYLE.CSS`

---

### 12. **BOOKING_CONFIRMATION.PHP** - Booking Success
**URL:** `html/BOOKING_CONFIRMATION.PHP`

**Displayed After Successful Booking:**
- Booking reference number
- Guest information summary
- Room type and details
- Check-in and check-out dates
- Number of guests
- Total amount paid
- Payment status
- Booking confirmation message
- Email confirmation notice

**Actions Available:**
- Print confirmation
- Download PDF
- Return to home
- Book another room

**Linked Resources:**
- CSS: `STYLE.CSS`

---

## 🔧 Backend PHP Scripts

### Configuration Files

#### **config/config.php** - Global Settings
**Purpose:** Application-wide configuration constants

**Contents:**
- Site name and title
- Base URL configuration
- File upload settings
- Email configuration
- Timezone settings
- Error reporting levels

---

#### **config/database.php** - Database Connection
**Purpose:** Centralized database connection management

**Functions:**
- `getDBConnection()` - Returns PDO connection
- `getDatabaseConnection()` - Alias function
- Error handling with try-catch
- Connection pooling

**Configuration:**
```php
Host: localhost
Database: tourist_hotel_db
Username: root
Password: (empty for WAMP default)
Charset: utf8mb4
```

---

### Main Backend Scripts

#### **Connect.php** - Legacy Admin Login Handler
**Purpose:** Handles admin authentication (DEPRECATED for new code)

**Handles:**
1. Admin login only

**Functions:**
- `handleAdminLogin($conn)` - Authenticate admin and create session

**Note:** No user registration or contact form functionality exists in this project. The CONTACTUS.HTML page only displays contact information (phone, email, address) without any form submission capability.

**Login Flow:**
1. Receives username and password
2. Queries admins table
3. Verifies bcrypt password hash
4. Creates session with admin data
5. Returns success/error JSON

---

#### **process_booking.php** - Booking Processor
**Purpose:** Handle all room bookings from accommodation page

**Process Flow:**
1. Validate POST request
2. Sanitize all inputs
3. Validate dates (check-in > today, check-out > check-in)
4. Check room availability for date range
5. Generate unique booking reference (BK + date + random)
6. Calculate pricing:
   - Base price × nights = subtotal
   - Tax (10%) = subtotal × 0.10
   - Service charge (5%) = subtotal × 0.05
   - Total = subtotal + tax + service charge
7. Insert into bookings table
8. Redirect to confirmation page

**Validation Rules:**
- Required fields: room_type_id, check_in_date, check_out_date, adults, guest_name, guest_email, guest_phone
- Email must be valid format
- Dates cannot be in the past
- Check-out must be after check-in
- Minimum 1 night stay
- Adults must be ≥ 1 and ≤ max_occupancy

**Error Handling:**
- Database errors
- Invalid dates
- No availability
- Validation failures

---

#### **get_rooms.php** - Room Data Fetcher
**Purpose:** Retrieve room types for accommodation page

**Function:** `getRoomTypes()`

**Returns:** Array of room types with:
- room_type_id
- type_name
- description
- base_price
- max_occupancy
- size_sqft (converted from sqm)
- bed_type
- amenities (decoded from JSON)
- badge_label
- features (decoded from JSON)
- image_url
- status

**Usage:** Called by ACCOMMODATION.PHP to display rooms

---

#### **get_all_rooms.php** - Complete Room Inventory
**Purpose:** Fetch all physical rooms with details

**Returns:** Array of rooms with:
- room_id
- room_number
- room_type_id
- type_name
- floor_number
- view_type
- status (available, occupied, maintenance)
- notes
- room_type details (price, occupancy, etc.)

**Usage:** Admin dashboard rooms management

---

#### **get_available_rooms.php** - Availability Checker
**Purpose:** Check room availability for specific dates

**Parameters:**
- room_type_id
- check_in_date
- check_out_date

**Logic:**
1. Get all rooms of specified type
2. Exclude rooms with overlapping bookings
3. Return available room count

**Usage:** Real-time availability checking during booking

---

#### **get_dashboard_stats.php** - Dashboard Analytics
**Purpose:** Provide real-time statistics for dashboard

**Returns JSON with:**

1. **Today's Bookings**
   - Count of bookings created today
   - Comparison with yesterday
   - Trend indicator (up/down)

2. **Total Guests**
   - Unique guest count (by email)
   - Comparison with last week
   - Growth percentage

3. **Available Rooms**
   - Count of rooms with status = 'available'
   - Total room inventory
   - Occupancy rate

4. **Monthly Revenue**
   - Sum of total_amount for current month
   - Comparison with last month
   - Growth percentage
   - Payment status breakdown

**Usage:** Dashboard overview cards (auto-refreshes)

---

#### **get_bookings.php** - Booking Data API
**Purpose:** Fetch bookings with filtering and search

**Parameters (optional):**
- status (pending, confirmed, checked_in, checked_out, cancelled)
- search (booking_reference, guest_name, guest_email)
- start_date, end_date (date range)
- limit (number of records)

**Returns:** Array of bookings with:
- All booking fields
- Room type name
- Room number (if assigned)
- Calculated nights
- Status

**Query Features:**
- JOIN with room_types and rooms
- WHERE conditions for filters
- ORDER BY created_at DESC
- LIMIT for pagination

---

#### **get_guests.php** - Guest Data API
**Purpose:** Retrieve guest information

**Returns:** Array of unique guests with:
- guest_name
- guest_email
- guest_phone
- Total bookings count
- Last booking date
- Total amount spent

**Query Features:**
- GROUP BY guest_email
- Aggregate functions (COUNT, SUM, MAX)
- Sorted by recent activity

---

#### **get_revenue.php** - Revenue Analytics
**Purpose:** Financial reporting and analytics

**Parameters:**
- period (daily, weekly, monthly, yearly)
- start_date, end_date

**Returns:**
- Revenue by period
- Revenue by room type
- Payment status breakdown
- Occupancy statistics
- Average booking value
- RevPAR (Revenue per Available Room)
- ADR (Average Daily Rate)

---

#### **get_recent_data.php** - Recent Activity
**Purpose:** Fetch latest bookings and guests for dashboard

**Returns:**
- 5 most recent bookings
- 5 most recent guests
- Today's check-ins
- Today's check-outs

---

#### **update_booking_status.php** - Status Updater
**Purpose:** Change booking status

**Parameters:**
- booking_id
- new_status (pending, confirmed, checked_in, checked_out, cancelled)
- room_id (optional, for confirmation)

**Process:**
1. Validate booking exists
2. Check status transition is valid
3. If confirming, assign room_id
4. Update booking status
5. If checking in/out, update room status
6. Log status change
7. Send email notification (optional)

**Status Transitions:**
- pending → confirmed (requires room_id)
- confirmed → checked_in
- checked_in → checked_out
- any → cancelled

---

#### **update_room_status.php** - Room Status Manager
**Purpose:** Update physical room status

**Parameters:**
- room_id
- status (available, occupied, maintenance)

**Validation:**
- Cannot set to available if room has active booking
- Cannot set to occupied without booking

---

#### **add_room.php** - New Room Creator
**Purpose:** Add new physical rooms to inventory

**Parameters:**
- room_number (unique)
- room_type_id
- floor_number
- view_type
- notes (optional)

**Validation:**
- Room number must be unique
- Room type must exist
- Floor number must be positive

---

#### **delete_room.php** - Room Remover
**Purpose:** Delete rooms from inventory

**Parameters:**
- room_id

**Safety Checks:**
- Cannot delete room with active bookings
- Cannot delete if status = occupied
- Soft delete option (set status to inactive)

---

#### **update_room_type.php** - Room Type Editor
**Purpose:** Modify room type details

**Parameters:**
- room_type_id
- type_name
- description
- base_price
- max_occupancy
- size_sqm
- bed_type
- amenities (JSON)
- features (JSON)
- badge_label

**Validation:**
- Price must be positive
- Occupancy must be ≥ 1
- Amenities and features as JSON arrays

---

#### **update_room_type_image.php** - Image Updater
**Purpose:** Change room type images

**Parameters:**
- room_type_id
- image (file upload)

**Process:**
1. Validate file type (jpg, jpeg, png, jfif, avif, webp)
2. Generate unique filename
3. Move to images/room-types/
4. Update image_url in database
5. Delete old image file

**File Naming:** `room_type_{id}_{timestamp}.{ext}`

---

#### **get_next_room_number.php** - Room Number Generator
**Purpose:** Suggest next available room number

**Logic:**
- Analyze existing room numbers
- Suggest based on floor and room type
- Format: {floor}{sequential number} or G0X for ground

---

#### **update_password.php** - Admin Password Changer
**Purpose:** Allow admin to change password

**Parameters:**
- current_password
- new_password
- confirm_password

**Process:**
1. Verify current password
2. Validate new password strength
3. Check confirmation matches
4. Hash new password with bcrypt
5. Update database
6. Return success/error

**Password Requirements:**
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character

---

#### **update_username.php** - Admin Username Changer
**Purpose:** Change admin username

**Parameters:**
- current_username
- new_username
- password (confirmation)

**Validation:**
- Username must be unique
- Password must be correct
- Username 4-50 characters
- Alphanumeric and underscore only

---

#### **logout.php** - Session Terminator
**Purpose:** Log out admin user

**Process:**
1. Start session
2. Destroy all session data
3. Unset session variables
4. Destroy session cookie
5. Redirect to ADMIN.HTML

---

#### **auto_update_booking_status.php** - Automated Status Updater
**Purpose:** Cron job script to auto-update booking statuses

**Logic:**
1. **Check-In Automation:**
   - Find bookings with status = 'confirmed'
   - Where check_in_date = TODAY
   - Update to 'checked_in'
   - Update room status to 'occupied'

2. **Check-Out Automation:**
   - Find bookings with status = 'checked_in'
   - Where check_out_date = TODAY
   - Update to 'checked_out'
   - Update room status to 'available'

**Scheduling:** Should run daily via cron or Windows Task Scheduler

**Cron Example:** `0 0 * * * php /path/to/auto_update_booking_status.php`

---

#### **get_booking_details.php** - Single Booking Fetcher
**Purpose:** Get detailed information for one booking

**Parameters:**
- booking_id

**Returns:** Complete booking record with:
- All booking fields
- Room type details
- Room details (if assigned)
- Payment information
- Guest information
- Calculated totals

---

#### **fix_admin_password.php** - Password Reset Utility
**Purpose:** Emergency admin password reset (run manually)

**Usage:**
1. Access directly: `php/fix_admin_password.php`
2. Resets admin password to default: `Admin@123`
3. Use only if locked out

**Security:** Should be deleted or restricted in production

---

#### **fix_image_paths.php** - Image Path Corrector
**Purpose:** Utility to fix broken image paths in database

**Process:**
1. Scans images/room-types/ directory
2. Matches files to room types
3. Updates image_url in database
4. Reports fixed paths

---

### Database Schema Files

#### **schema.sql** - Complete Database Schema
**Purpose:** Full database creation script

**Contents:**
- Database creation
- All table definitions
- Foreign key relationships
- Indexes for performance
- Default admin account
- Sample room types (6 types)
- Sample rooms (30 rooms)
- Comments and documentation

**Usage:** Import to create fresh database

---

#### **clean_import.sql** - Clean Data Import
**Purpose:** Simplified import without sample data

---

#### **update_bookings_table.sql** - Schema Updates
**Purpose:** ALTER TABLE statements for upgrades

---

## 🎨 JavaScript Functionality

### **include.js** - Component Loader
**Purpose:** Load navbar and footer dynamically

**Functions:**
1. `loadCSS(href)` - Load CSS file into head
2. Load navbar HTML into placeholder
3. Load footer HTML into placeholder
4. Highlight active menu item based on current page

**How It Works:**
- Fetches navbar.html and footer.html
- Injects into `#navbar-placeholder` and `#footer-placeholder`
- Detects current page from URL
- Adds 'active' class to matching menu item

**Usage:** Include in all pages
```html
<div id="navbar-placeholder"></div>
<script src="../js/include.js"></script>
```

---

### **hero-slideshow.js** - Homepage Slideshow
**Purpose:** Animate hero section background images

**Functionality:**
- Rotates through 3 hero images
- 5-second interval
- Smooth fade transitions
- Auto-starts on page load

**Images:**
- img 1.jpg
- img72.jpg
- img73.webp

---

### **hero-effects.js** - Animation Effects
**Purpose:** Add interactive animations

**Features:**
- Scroll reveal animations
- Parallax effects
- Smooth scrolling
- Intersection Observer for lazy loading

---

## 🔐 Security Implementation

### Authentication

**Admin Login:**
- Bcrypt password hashing (cost: 10)
- Session-based authentication
- Session timeout after inactivity
- HTTPS recommended for production

**Session Management:**
```php
$_SESSION['admin_id']
$_SESSION['admin_name']
$_SESSION['admin_role']
$_SESSION['logged_in'] = true
```

**Access Control:**
- Dashboard checks session before rendering
- Redirects unauthorized users to login
- Role-based permissions (admin vs staff)

---

### Data Validation

**Input Sanitization:**
- `filter_input()` for all POST/GET data
- `htmlspecialchars()` for output
- PDO prepared statements (prevents SQL injection)
- Email validation with `filter_var()`

**File Upload Security:**
- Whitelist file extensions
- File size limits
- Unique filename generation
- Secure directory permissions

---

### Database Security

**PDO with Prepared Statements:**
```php
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$booking_id]);
```

**Connection Security:**
- Separate database user for application
- Limit privileges (no DROP, CREATE in production)
- Password-protected database

---

### XSS Prevention

- Output encoding with `htmlspecialchars()`
- Content Security Policy headers
- Escape JSON data

---

### CSRF Protection

**Recommendations:**
- Implement CSRF tokens
- Validate tokens on form submission
- Use same-origin policy

---

## 📡 API Endpoints

The system now uses RESTful API endpoints with proper HTTP methods. See `API_QUICK_REFERENCE.md` for detailed API documentation.

### REST API Endpoints (NEW - v2.0)

**Base URL:** `php/api/`

| Endpoint | Methods | Purpose | Auth Required |
|----------|---------|---------|---------------|
| `bookings.php` | GET, POST, PATCH | CRUD for bookings | Admin |
| `booking_public.php` | GET | Get booking by reference | Public |
| `rooms.php` | GET, POST, PATCH, DELETE | CRUD for rooms | Admin |
| `room_types.php` | GET, POST, PATCH, DELETE | CRUD for room types | GET=Public, Others=Admin |
| `upload_room_image.php` | POST | Upload room type images | Admin |

**Key Features:**
- Proper HTTP methods (GET, POST, PATCH, DELETE)
- JSON request/response format
- Session-based authentication for admin endpoints
- Public endpoint for Track Booking feature
- CORS headers support
- Standardized error responses

**API Helper Functions:** `php/config/api_helpers.php`
- `setCorsHeaders()` - Set CORS headers
- `getRequestMethod()` - Get HTTP method with override support
- `getRequestData()` - Parse request body (all methods)
- `requireAuth()` - Check admin authentication
- `validateRequiredFields()` - Validate input data
- `sendJsonResponse()` - Generic JSON response
- `sendSuccessResponse()` - Success response
- `sendErrorResponse()` - Error response with HTTP status code

**Frontend Integration:** `js/api-integration.js`
- 20+ async wrapper functions for all API operations
- Automatic error handling
- Consistent response format

### Legacy AJAX Endpoints (Backward Compatible)

All PHP scripts in `/php/` directory can still be called via AJAX:

#### GET Endpoints

| Endpoint | Purpose | Parameters |
|----------|---------|------------|
| `get_rooms.php` | Room types list | None |
| `get_all_rooms.php` | Room inventory | None |
| `get_available_rooms.php` | Check availability | room_type_id, check_in_date, check_out_date |
| `get_dashboard_stats.php` | Dashboard statistics | None |
| `get_bookings.php` | Booking list | status, search, start_date, end_date, limit |
| `get_guests.php` | Guest list | None |
| `get_revenue.php` | Revenue data | period, start_date, end_date |
| `get_recent_data.php` | Recent activity | None |
| `get_booking_details.php` | Single booking | booking_id |

#### POST Endpoints

| Endpoint | Purpose | Parameters |
|----------|---------|------------|
| `process_booking.php` | Create booking | room_type_id, dates, guest info, etc. |
| `update_booking_status.php` | Change booking status | booking_id, new_status, room_id |
| `update_room_status.php` | Change room status | room_id, status |
| `add_room.php` | Add new room | room_number, room_type_id, floor, view |
| `delete_room.php` | Remove room | room_id |
| `update_room_type.php` | Edit room type | room_type_id, all room type fields |
| `update_password.php` | Change admin password | current, new, confirm |
| `update_username.php` | Change admin username | current, new, password |
| `Connect.php` | Multi-purpose | Various (registration, login, contact) |

### Response Format

REST API endpoints return JSON:

**Success Response (Standard):**
```json
{
  "success": true,
  "message": "Operation completed",
  "data": {...}
}
```

**Success Response (Booking Public API):**
```json
{
  "success": true,
  "booking": {
    "booking_reference": "BK20260110-7236",
    "status": "confirmed",
    "room_type_name": "Deluxe Room",
    "confirmed_at": "2026-01-10 14:30:00",
    "cancellation_reason": null
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Error description"
}
```

---

## 🛠️ Installation & Setup

### Prerequisites

1. **Web Server:** Apache 2.4+
2. **PHP:** 7.4 or higher
3. **Database:** MySQL 5.7+ or MariaDB 10.x
4. **WAMP/XAMPP/LAMP:** Recommended for local development

---

### Step-by-Step Installation

#### 1. Install WAMP Stack (Windows)

Download and install from: https://www.wampserver.com/

**Default Paths:**
- Installation: `C:\wamp64\`
- Web Root: `C:\wamp64\www\`
- MySQL: Port 3306
- Apache: Port 80

---

#### 2. Copy Project Files

Extract project to: `C:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\`

**Verify Structure:**
```
C:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\
├── index.html
├── html/
├── css/
├── js/
├── php/
├── images/
└── includes/
```

---

#### 3. Create Database

**Option A: Using phpMyAdmin**

1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `tourist_hotel_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Go to "Import" tab
7. Choose file: `php/schema.sql`
8. Click "Go"

**Option B: Using MySQL Command Line**

```bash
mysql -u root -p
CREATE DATABASE tourist_hotel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tourist_hotel_db;
SOURCE C:/wamp64/www/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/schema.sql;
EXIT;
```

---

#### 4. Configure Database Connection

Edit `php/config/database.php`:

```php
<?php
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'tourist_hotel_db';
    $username = 'root';
    $password = ''; // Empty for default WAMP
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
```

---

#### 5. Set Permissions (Linux/Mac)

```bash
chmod -R 755 /path/to/TOURIST-HOTEL-MANAGEMENT-SYSTEM
chmod -R 777 /path/to/TOURIST-HOTEL-MANAGEMENT-SYSTEM/images/room-types
```

---

#### 6. Start Services

**WAMP:**
- Click WAMP icon in system tray
- Ensure all services are green
- Apache and MySQL must be running

**Verify:**
- Apache: http://localhost (should show WAMP homepage)
- MySQL: http://localhost/phpmyadmin (should open phpMyAdmin)

---

#### 7. Access Application

**Public Website:**
- URL: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/`
- Redirects to: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/HOME.HTML`

**Admin Dashboard:**
- URL: `http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/ADMIN.HTML`
- Username: `admin`
- Password: `Admin@123`

---

### Production Deployment

#### Additional Steps for Live Server:

1. **Update Database Configuration:**
   - Change database credentials in `php/config/database.php`
   - Use secure passwords

2. **Update Base URL:**
   - Edit `php/config/config.php`
   - Set production URL

3. **Enable HTTPS:**
   - Install SSL certificate
   - Force HTTPS redirection in `.htaccess`

4. **Secure Admin Area:**
   - Change default admin password immediately
   - Restrict IP access to admin pages
   - Implement additional authentication

5. **File Permissions:**
   - Set proper permissions (755 for directories, 644 for files)
   - Writable: `images/room-types/` (777 or 755 with owner)

6. **Error Handling:**
   - Disable error display: `display_errors = Off` in php.ini
   - Enable error logging: `log_errors = On`

7. **Backup Strategy:**
   - Regular database backups
   - File backups (images, uploads)
   - Backup `/backups/` directory

8. **Performance:**
   - Enable PHP OpCache
   - Use CDN for static assets
   - Database query optimization
   - Image optimization

9. **Security Hardening:**
   - Delete `fix_admin_password.php`
   - Remove `setup_room_images.ps1`
   - Implement rate limiting
   - Add CSRF tokens
   - Configure CSP headers

10. **Monitoring:**
    - Set up error monitoring
    - Track booking funnel
    - Monitor server resources

---

## 🔧 Maintenance & Troubleshooting

### Common Issues

#### 1. Cannot Connect to Database

**Symptoms:**
- "Connection failed" error
- Pages show blank or errors

**Solutions:**
- Verify MySQL is running (WAMP icon → MySQL → Service running)
- Check database credentials in `php/config/database.php`
- Ensure database `tourist_hotel_db` exists
- Check MySQL port (default: 3306)

```bash
# Test MySQL connection
mysql -u root -p -h localhost
SHOW DATABASES;
```

---

#### 2. Admin Login Not Working

**Symptoms:**
- Incorrect username/password error
- Redirects back to login

**Solutions:**
- Reset password using `fix_admin_password.php`
- Verify admin account exists in database
- Check sessions are enabled in PHP
- Clear browser cookies/cache

```sql
-- Check admin account
SELECT * FROM admins WHERE username = 'admin';

-- Reset password manually
UPDATE admins 
SET password_hash = '$2y$10$95zu.K1w92MsO7gM8J1biuMai6LD2aJx4FXZg2SXS6jBafXJOEi1a' 
WHERE username = 'admin';
```

---

#### 3. Images Not Displaying

**Symptoms:**
- Broken image icons
- Default placeholder images

**Solutions:**
- Check image file paths are correct
- Verify images exist in `images/` and `images/room-types/`
- Check file permissions (readable)
- Run `fix_image_paths.php` to correct paths
- Verify image URLs in database

```sql
-- Check room type images
SELECT room_type_id, type_name, image_url FROM room_types;

-- Update image path
UPDATE room_types 
SET image_url = '../images/room-types/room_type_1.jpg' 
WHERE room_type_id = 1;
```

---

#### 4. Booking Not Processing

**Symptoms:**
- Form submission fails
- Stays on booking page
- No confirmation

**Solutions:**
- Check JavaScript console for errors
- Verify `process_booking.php` is accessible
- Check database connection
- Ensure all required fields filled
- Validate date format (YYYY-MM-DD)
- Check room availability

**Debug:**
```php
// Add to process_booking.php temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

#### 5. Dashboard Not Loading

**Symptoms:**
- Blank dashboard
- Redirects to login repeatedly
- Statistics not showing

**Solutions:**
- Verify session is active
- Check admin is logged in
- Ensure all API endpoints accessible
- Check browser console for AJAX errors
- Verify database has data

**Debug:**
```javascript
// Browser console
fetch('http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/php/get_dashboard_stats.php')
  .then(res => res.json())
  .then(data => console.log(data));
```

---

#### 6. Auto Status Update Not Working

**Symptoms:**
- Bookings not auto-checking in/out
- Status remains "confirmed"

**Solutions:**
- Manually run `auto_update_booking_status.php`
- Set up cron job or Windows Task Scheduler
- Check script has database access
- Verify date comparisons work

**Windows Task Scheduler:**
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily at 12:00 AM
4. Action: Start a program
5. Program: `C:\wamp64\bin\php\php7.x.x\php.exe`
6. Arguments: `C:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\php\auto_update_booking_status.php`

---

### Database Maintenance

#### Backup Database

**Using phpMyAdmin:**
1. Open phpMyAdmin
2. Select `tourist_hotel_db`
3. Click "Export" tab
4. Choose "Custom" method
5. Select all tables
6. Format: SQL
7. Click "Go"
8. Save file with date: `tourist_hotel_db_20260108.sql`

**Using Command Line:**
```bash
mysqldump -u root -p tourist_hotel_db > backup_20260108.sql
```

---

#### Restore Database

**Using phpMyAdmin:**
1. Open phpMyAdmin
2. Select `tourist_hotel_db`
3. Click "Import" tab
4. Choose file
5. Click "Go"

**Using Command Line:**
```bash
mysql -u root -p tourist_hotel_db < backup_20260108.sql
```

---

#### Clean Old Data

```sql
-- Delete old cancelled bookings (older than 1 year)
DELETE FROM bookings 
WHERE booking_status = 'cancelled' 
AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archive old checked-out bookings
CREATE TABLE bookings_archive LIKE bookings;
INSERT INTO bookings_archive 
SELECT * FROM bookings 
WHERE booking_status = 'checked_out' 
AND check_out_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

---

### Performance Optimization

#### Database Indexing

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_booking_dates ON bookings(check_in_date, check_out_date);
CREATE INDEX idx_booking_status ON bookings(booking_status);
CREATE INDEX idx_guest_email ON bookings(guest_email);
CREATE INDEX idx_created_at ON bookings(created_at);
```

---

#### Image Optimization

1. Compress images before upload
2. Use WebP format for better compression
3. Implement lazy loading
4. Use responsive images

---

#### Caching

**Browser Caching (.htaccess):**
```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

### Monitoring

#### Check Disk Space

Monitor `images/room-types/` directory size

#### Database Size

```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'tourist_hotel_db'
ORDER BY (data_length + index_length) DESC;
```

---

#### Log Files

**Check Apache Error Log:**
- WAMP: `C:\wamp64\logs\apache_error.log`
- Review for PHP errors and warnings

**Check PHP Error Log:**
- WAMP: `C:\wamp64\logs\php_error.log`

---

## 📞 Support & Contact

### For Technical Issues:
- Check this documentation first
- Review error logs
- Check database connectivity
- Verify file permissions

### For Feature Requests:
- Document desired functionality
- Consider database impacts
- Plan UI/UX changes

---

## 📝 Change Log

### Version 1.0 (January 2026)
- Initial release
- Core booking system
- Admin dashboard
- 6 room types, 30 rooms
- Revenue analytics
- Automated status updates

---

## 🔮 Future Enhancements

### Planned Features:
1. **User Registration & Login** (Not Currently Implemented)
   - Customer account creation
   - Login portal for guests
   - Booking history tracking
   - Profile management
   - Users database table

2. **Online Payment Integration**
   - PayPal, Stripe integration
   - Credit card processing
   - Payment gateway

3. **Email Notifications**
   - Booking confirmation emails
   - Reminder emails before check-in
   - Admin notifications

4. **Multi-language Support**
   - English, Sinhala, Tamil
   - Language switcher
   - Translated content

5. **Mobile App**
   - Native iOS/Android apps
   - Push notifications
   - Mobile check-in

6. **Advanced Analytics**
   - Predictive analytics
   - Demand forecasting
   - Price optimization
   - Customer segmentation

7. **Review System**
   - Customer reviews and ratings
   - Photo uploads
   - Response management

8. **Loyalty Program**
   - Points system
   - Member discounts
   - Special offers

9. **Housekeeping Module**
   - Room cleaning status
   - Maintenance requests
   - Staff assignments

10. **Inventory Management**
    - Stock tracking
    - Supplier management
    - Purchase orders

---

## 📄 License & Credits

**Developed for:** Titania Hotel, Bandarawela, Sri Lanka  
**Technology Stack:** HTML5, CSS3, JavaScript, PHP, MySQL  
**Framework:** None (Vanilla/Custom built)  
**Icons:** Font Awesome 6.0  

---

## 🎓 Handover Checklist

### For the New Developer:

- [ ] Read this entire documentation
- [ ] Set up local development environment (WAMP)
- [ ] Import database using `schema.sql`
- [ ] Test admin login (username: admin, password: Admin@123)
- [ ] Test booking process from accommodation page
- [ ] Review all HTML pages
- [ ] Familiarize with dashboard features
- [ ] Check all PHP backend scripts
- [ ] Test room management
- [ ] Test booking management
- [ ] Verify email configuration (if applicable)
- [ ] Set up automated backup routine
- [ ] Configure Windows Task Scheduler for auto-updates
- [ ] Review security best practices
- [ ] Change default admin password
- [ ] Test on different browsers
- [ ] Verify mobile responsiveness
- [ ] Check image upload functionality
- [ ] Review API endpoints
- [ ] Test error handling
- [ ] Set up version control (Git recommended)

### Important Files to Review First:
1. `index.html` - Entry point
2. `html/HOME.HTML` - Main landing page
3. `html/ACCOMMODATION.PHP` - Booking page
4. `html/DASHBOARD.PHP` - Admin dashboard
5. `php/config/database.php` - Database connection
6. `php/process_booking.php` - Booking logic
7. `php/schema.sql` - Database structure

### Key Credentials:
- **Database:** tourist_hotel_db
- **Admin Username:** admin
- **Admin Password:** Admin@123 (CHANGE THIS!)
- **Database User:** root
- **Database Password:** (empty for WAMP default)

---

## 📧 Questions?

If you have questions about this system:
1. Refer to this documentation
2. Check the inline code comments
3. Review the database schema
4. Test in local environment first
5. Keep backup before making changes

**Remember:** Always backup database and files before making changes!

---

**End of Documentation**  
**Last Updated:** January 8, 2026  
**Document Version:** 1.0  
**Total Pages:** Comprehensive Guide

---

*This documentation covers all aspects of the Tourist Hotel Management System. For specific implementation details, refer to individual file comments and code documentation.*
