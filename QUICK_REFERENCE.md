# QUICK REFERENCE GUIDE
## Tourist Hotel Management System

---

## 🚀 Quick Start (5 Minutes)

### 1. Setup (First Time)
```bash
1. Install WAMP Server
2. Copy project to C:\wamp64\www\TOURIST-HOTEL-MANAGEMENT-SYSTEM\
3. Start WAMP (All services green)
4. Open phpMyAdmin: http://localhost/phpmyadmin
5. Create database: tourist_hotel_db
6. Import: php/schema.sql
7. Done!
```

### 2. Access URLs
- **Website:** http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/
- **Admin Login:** http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/html/ADMIN.HTML
- **Username:** admin
- **Password:** Admin@123

---

## 📄 Page Reference

| Page | File | Purpose |
|------|------|---------|
| Home | html/HOME.HTML | Landing page with slideshow |
| Accommodation | html/ACCOMMODATION.PHP | Room booking page |
| Admin Dashboard | html/DASHBOARD.PHP | Management control panel |
| Admin Login | html/ADMIN.HTML | Admin authentication |
| About Us | html/ABOUTUS.HTML | Hotel information |
| Services | html/SERVICES.HTML | Hotel services list |
| Things To Do | html/THINGS TO DO.HTML | Activities & excursions |
| Gallery | html/GALLARY.HTML | Photo gallery |
| Testimonials | html/TESTIMONIALS.HTML | Customer reviews |
| FAQ | html/FAQ.HTML | Frequently asked questions |
| Contact | html/CONTACTUS.HTML | Contact information |
| Booking Confirm | html/BOOKING_CONFIRMATION.PHP | Success page after booking |

---

## 🗄️ Database Tables Quick Reference

| Table | Records | Purpose |
|-------|---------|---------|
| admins | 1 | Admin accounts |
| room_types | 6 | Room categories |
| rooms | 30 | Physical room inventory |
| bookings | Variable | Reservation records |

**Note:** Total 4 tables. No contact_messages or users tables exist.

---

## 🔑 Key Functions by Task

### Customer Books a Room
1. Customer visits `ACCOMMODATION.PHP`
2. Views available rooms (from `get_rooms.php`)
3. Clicks "Book Now" → Modal opens
4. Fills form (dates, guests, contact info)
5. Submits → `process_booking.php` processes
6. Redirects to `BOOKING_CONFIRMATION.PHP`
7. Booking stored with status = "pending"

### Admin Manages Booking
1. Login to `DASHBOARD.PHP`
2. Click "Bookings" in sidebar
3. View all bookings table
4. Click booking row → details modal
5. Actions available:
   - **Confirm:** Assign room, status → "confirmed"
   - **Check-In:** Status → "checked_in", room → "occupied"
   - **Check-Out:** Status → "checked_out", room → "available"
   - **Cancel:** Status → "cancelled"

### Admin Adds New Room
1. Dashboard → Rooms section
2. Click "Add New Room"
3. Fill form:
   - Room number (unique)
   - Room type (dropdown)
   - Floor number
   - View type
4. Submit → `add_room.php`
5. New room appears in inventory

### Admin Changes Room Price
1. Dashboard → Rooms section
2. Click "Manage Room Types"
3. Click edit icon on room type
4. Update price field
5. Save → `update_room_type.php`
6. Price updated, reflected immediately

---

## 🔧 Common Admin Tasks

### Change Admin Password
1. Dashboard → Settings
2. "Change Password" section
3. Enter current password
4. Enter new password (min 8 chars, upper, lower, number, special)
5. Confirm new password
6. Submit → `update_password.php`

### Update Room Status
1. Dashboard → Rooms
2. Find room in table
3. Click status dropdown
4. Select: Available / Occupied / Maintenance
5. Save → `update_room_status.php`
6. Status updated

### View Revenue Report
1. Dashboard → Revenue
2. View charts and statistics
3. Filter by date range
4. See breakdown by room type
5. Export data (optional)

### Search Bookings
1. Dashboard → Bookings
2. Use search box (booking ref, name, email)
3. Or filter by status dropdown
4. Or select date range
5. Click "Apply Filters"
6. Results update instantly

---

## 🐛 Troubleshooting Quick Fixes

| Problem | Quick Fix |
|---------|-----------|
| Can't login as admin | Run `php/fix_admin_password.php` |
| Database connection error | Check WAMP MySQL is running |
| Images not showing | Run `php/fix_image_paths.php` |
| Booking not processing | Check browser console, verify PHP errors |
| Dashboard blank | Clear browser cache, check session |
| Old bookings not updating | Run `php/auto_update_booking_status.php` |

---

## 📊 Room Type Quick Reference

| Room Type | Price | Max Guests | Rooms | Floors |
|-----------|-------|------------|-------|--------|
| Presidential Suite | $350 | 4 | 2 | 4 |
| Heritage Suite | $280 | 3 | 3 | 3 |
| Deluxe Double | $180 | 2 | 5 | 2 |
| Premium Garden Wing | $220 | 3 | 4 | G |
| Standard Triple | $150 | 3 | 6 | 1 |
| Standard Double | $120 | 2 | 10 | 1-2 |
| **TOTAL** | - | - | **30** | - |

---

## 💰 Pricing Calculation

```
Subtotal = Room Price × Number of Nights
Tax (10%) = Subtotal × 0.10
Service Charge (5%) = Subtotal × 0.05
Total Amount = Subtotal + Tax + Service Charge
```

**Example:**
- Room: Deluxe Double ($180/night)
- Nights: 3
- Subtotal: $180 × 3 = $540
- Tax: $540 × 0.10 = $54
- Service: $540 × 0.05 = $27
- **Total: $621**

---

## 🔐 Security Checklist

- [ ] Change default admin password
- [ ] Use strong passwords (8+ chars, mixed case, numbers, symbols)
- [ ] Enable HTTPS in production
- [ ] Delete `fix_admin_password.php` in production
- [ ] Restrict admin IP addresses (optional)
- [ ] Regular database backups
- [ ] Keep PHP and MySQL updated
- [ ] Monitor error logs
- [ ] Set proper file permissions (755/644)
- [ ] Validate all user inputs

---

## 📁 Important File Locations

### Configuration
- Database: `php/config/database.php`
- Global Config: `php/config/config.php`

### Styling
- Global CSS: `css/STYLE.CSS`
- Page-specific: `css/[page-name].css`

### Components
- Navbar: `includes/navbar.html`
- Footer: `includes/footer.html`

### Images
- General: `images/`
- Room Types: `images/room-types/`

### Backups
- HTML/CSS Backups: `backups/`

---

## 🔄 Booking Status Flow

```
NEW BOOKING
    ↓
┌─────────────┐
│  pending    │ ← Customer submits booking
└─────────────┘
    ↓ (Admin assigns room)
┌─────────────┐
│ confirmed   │ ← Room assigned, confirmation sent
└─────────────┘
    ↓ (Check-in date arrives / Manual)
┌─────────────┐
│ checked_in  │ ← Guest arrives, room status = occupied
└─────────────┘
    ↓ (Check-out date arrives / Manual)
┌─────────────┐
│ checked_out │ ← Guest departs, room status = available
└─────────────┘

(At any point)
┌─────────────┐
│ cancelled   │ ← Booking cancelled
└─────────────┘
```

---

## 🔌 API Endpoints Cheat Sheet

### GET Requests (Fetch Data)
```javascript
// Get all room types
fetch('php/get_rooms.php')

// Get all rooms inventory
fetch('php/get_all_rooms.php')

// Get bookings (with filters)
fetch('php/get_bookings.php?status=pending')

// Get dashboard stats
fetch('php/get_dashboard_stats.php')

// Get guests
fetch('php/get_guests.php')

// Get revenue data
fetch('php/get_revenue.php?period=monthly')
```

### POST Requests (Submit Data)
```javascript
// Create booking
fetch('php/process_booking.php', {
  method: 'POST',
  body: formData
})

// Update booking status
fetch('php/update_booking_status.php', {
  method: 'POST',
  body: JSON.stringify({
    booking_id: 123,
    new_status: 'confirmed',
    room_id: 5
  })
})

// Update room status
fetch('php/update_room_status.php', {
  method: 'POST',
  body: JSON.stringify({
    room_id: 5,
    status: 'available'
  })
})
```

---

## 📞 Emergency Procedures

### Lost Admin Access
1. Navigate to: `php/fix_admin_password.php` in browser
2. Password reset to: `Admin@123`
3. Login and change password immediately

### Database Corrupted
1. Stop WAMP MySQL
2. Restore from backup:
   ```bash
   mysql -u root -p tourist_hotel_db < backup.sql
   ```
3. Restart MySQL

### Site Down
1. Check WAMP services (both green)
2. Check Apache error log: `C:\wamp64\logs\apache_error.log`
3. Check PHP error log: `C:\wamp64\logs\php_error.log`
4. Verify database connection in `php/config/database.php`

---

## 🎯 Performance Tips

### Speed Up Dashboard
1. Limit bookings display (pagination)
2. Cache dashboard stats (5 min)
3. Index database tables
4. Optimize images

### Reduce Load Time
1. Compress images (WebP format)
2. Minify CSS/JS
3. Enable browser caching
4. Use CDN for Font Awesome

### Database Optimization
```sql
-- Run monthly
OPTIMIZE TABLE bookings;
OPTIMIZE TABLE rooms;
OPTIMIZE TABLE room_types;

-- Add indexes
CREATE INDEX idx_booking_dates ON bookings(check_in_date, check_out_date);
CREATE INDEX idx_status ON bookings(booking_status);
```

---

## 📅 Maintenance Schedule

### Daily
- Check WAMP services running
- Monitor new bookings
- Review error logs

### Weekly
- Backup database
- Check disk space
- Review pending bookings
- Update room statuses

### Monthly
- Full database backup (export)
- Optimize database tables
- Review analytics
- Archive old data
- Update prices (seasonal)

### Quarterly
- Security audit
- Password changes
- Software updates
- Performance review

---

## 💡 Useful SQL Queries

### View Today's Bookings
```sql
SELECT * FROM bookings 
WHERE DATE(created_at) = CURDATE();
```

### Check Room Availability
```sql
SELECT r.room_number, r.status, rt.type_name
FROM rooms r
JOIN room_types rt ON r.room_type_id = rt.room_type_id
WHERE r.status = 'available';
```

### Revenue This Month
```sql
SELECT SUM(total_amount) as monthly_revenue
FROM bookings
WHERE MONTH(created_at) = MONTH(CURDATE())
AND YEAR(created_at) = YEAR(CURDATE())
AND payment_status = 'paid';
```

### Top Customers
```sql
SELECT guest_name, guest_email, COUNT(*) as booking_count, SUM(total_amount) as total_spent
FROM bookings
GROUP BY guest_email
ORDER BY booking_count DESC
LIMIT 10;
```

### Upcoming Check-ins (Next 7 Days)
```sql
SELECT * FROM bookings
WHERE check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
AND booking_status IN ('confirmed', 'pending')
ORDER BY check_in_date;
```

---

## 🎨 Customization Quick Guide

### Change Hotel Name
1. Edit `includes/navbar.html` → Update brand name
2. Edit page titles in all HTML files
3. Update logo image: `images/logo.png`

### Change Color Scheme
1. Edit `css/STYLE.CSS` → CSS variables:
```css
:root {
  --primary-color: #dc2626;  /* Main red */
  --secondary-color: #1e293b; /* Dark blue */
  --accent-color: #f59e0b;    /* Gold accent */
}
```

### Add New Room Type
1. Dashboard → Rooms → Manage Room Types
2. Click "Add New Room Type"
3. Fill details, upload image
4. Save
5. Add physical rooms for this type

### Modify Email Templates
1. Edit email functions in `php/process_booking.php`
2. Customize HTML email templates
3. Add logo and branding

---

## 📖 Code Structure Quick Reference

### Frontend (HTML/CSS/JS)
```
html/          → All pages
css/           → Styling
js/            → JavaScript
includes/      → Reusable components (navbar, footer)
images/        → Static assets
```

### Backend (PHP)
```
php/
├── config/             → Configuration
│   ├── config.php      → Global settings
│   └── database.php    → DB connection
├── Connect.php         → Legacy handler
├── process_booking.php → Booking processor
├── get_*.php          → Data fetchers (GET)
├── update_*.php       → Data updaters (POST)
├── add_*.php          → Create operations
├── delete_*.php       → Delete operations
└── schema.sql         → Database schema
```

---

## 🚨 When to Call for Help

### Critical Issues
- Database completely down
- Cannot access admin dashboard at all
- All bookings disappeared
- Payment processing failures
- Security breach suspected

### Before Calling
1. Check error logs
2. Try restarting WAMP
3. Verify database backup exists
4. Note exact error messages
5. Document steps to reproduce

---

## ✅ Pre-Launch Checklist

### Before Going Live
- [ ] Change admin password from default
- [ ] Test booking process end-to-end
- [ ] Verify all images load correctly
- [ ] Test on mobile devices
- [ ] Check all forms work
- [ ] Set up email notifications
- [ ] Configure payment gateway (if applicable)
- [ ] Set up SSL certificate (HTTPS)
- [ ] Create database backup procedure
- [ ] Test admin dashboard all features
- [ ] Set up automated status updates (cron job)
- [ ] Add contact information to contact page
- [ ] Update hotel details on about page
- [ ] Test all navigation links
- [ ] Verify SEO metadata
- [ ] Set up analytics (Google Analytics)
- [ ] Create 404 error page
- [ ] Test error handling
- [ ] Review privacy policy
- [ ] Add terms and conditions
- [ ] Train staff on dashboard usage

---

**Quick Reference Version 1.0**  
**Last Updated:** January 8, 2026

---

*For complete details, see PROJECT_DOCUMENTATION.md*
