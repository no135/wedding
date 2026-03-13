# Wedding Invitation System - Quick Reference Guide

## Admin Panel Navigation

```
┌─────────────────────────────────────────────┐
│ Admin Dashboard                             │
├─────────────────────────────────────────────┤
│ Menu Items:                                 │
│ • Dashboard          → Statistics & Overview│
│ • Invitations        → Create & Manage      │
│ • Users              → User Management      │
│ • Payments           → Approve/Reject       │
│ • Guests & RSVPs     → Check-ins            │
│ • Packages           → Features & Pricing   │
│ • Themes             → Colors & Styling    │
│ • Settings           → System Config        │
│ • Logout             → Exit Admin           │
└─────────────────────────────────────────────┘
```

## Key URLs

### Admin Pages
```
/admin/dashboard.php              - Main dashboard
/admin/invitations.php            - Invitations list
/admin/invitation-detail.php?id=X - View invitation
/admin/invitation-create.php      - Create invitation
/admin/users.php                  - Users list
/admin/payments.php               - Payments list
/admin/payment-detail.php?id=X    - View payment
/admin/guests.php                 - Guest list
/admin/packages.php               - Package list
/admin/themes.php                 - Theme list
/admin/logout.php                 - Logout
```

### Public Pages
```
/login.php                        - Login page
/register.php                     - Registration
/public/invitation.php?slug=X     - Guest view invitation
/404.php                          - Error page
```

### API Endpoints
```
/api/invitation-detail.php?id=X   - Get invitation JSON
/api/payment-approve.php?id=X     - Approve payment
/api/payment-reject.php?id=X      - Reject payment
/api/checkin-guest.php?token=X    - QR code check-in
```

## Database Tables Quick Lookup

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| `users` | User accounts | id, email, password, role |
| `invitations` | Weddings/events | id, user_id, host_name_1/2 |
| `packages` | Service tiers | id, package_name, price |
| `rsvps` | Guest responses | id, invitation_id, guest_name |
| `payments` | Transactions | id, user_id, amount, status |
| `media` | Photos/videos | id, invitation_id, file_path |
| `locations` | Venues | id, invitation_id, address |
| `events` | Timeline | id, invitation_id, time |
| `guestbook` | Messages | id, invitation_id, message |

## Common Admin Tasks

### Create New Invitation
1. Click "Invitations" in sidebar
2. Click "New Invitation" button
3. Fill form:
   - Host Name 1 (required)
   - Host Name 2 (optional)
   - Invitation Type
   - Package
   - Event Date/Time
4. Click "Create Invitation"

### Approve Payment
1. Click "Payments" in sidebar
2. Find pending payment
3. Click "View" button
4. Add optional admin note
5. Click "Approve Payment"

### Check in Guest
1. Click "Guests & RSVPs"
2. Find guest in list
3. Click QR code or use check-in link
4. Status updates to "Checked In"

### Manage Packages
1. Click "Packages"
2. View all packages in cards
3. Click "Edit" to change
4. Click "Delete" to remove

### View Invitation Stats
1. Click "Invitations"
2. Click invitation row
3. See detailed view with:
   - Guest statistics
   - Media gallery
   - Locations
   - Timeline events

## User Roles

```
┌──────────────────────────────────────────┐
│ User Roles                               │
├──────────────────────────────────────────┤
│ Admin                                    │
│ ├─ Access all admin pages                │
│ ├─ Manage all invitations                │
│ ├─ Approve/reject payments               │
│ ├─ Manage users                          │
│ └─ Configure system                      │
│                                          │
│ User                                     │
│ ├─ Create own invitations                │
│ ├─ Manage own invitations                │
│ └─ Submit payments                       │
└──────────────────────────────────────────┘
```

## Payment Status Workflow

```
┌─────────────┐
│   Pending   │ ← Initial state when uploaded
└─────┬───────┘
      │
      ├─────────────────────────┐
      │                         │
      ▼                         ▼
 ┌─────────┐           ┌──────────┐
 │Approved │           │ Rejected │
 └─────────┘           └──────────┘
   ✓ Money received      ✗ Not received
   ✓ Invitation active   ✗ Payment denied
```

## Invitation Status Workflow

```
Pending Payment → Pending Approval → Active → Expired
     ↓                ↓               ↓
  Upload proof    Admin review    Published
  Pay amount      Admin approves   Live
                                   viewable
```

## File Upload Handling

```
Allowed Image Types:  jpg, jpeg, png, gif, webp
Allowed Video Types:  mp4, avi, mov, webm
Max File Size:        10 MB
Upload Directory:     /public/uploads/
Naming:               UUID based (auto)
```

## RSVP Guest Sides

```
┌─────────────────────────────────┐
│ Guest Side Options              │
├─────────────────────────────────┤
│ • Groom's side (customizable)  │
│ • Bride's side (customizable)  │
│ • General (neutral)            │
└─────────────────────────────────┘
```

## Dashboard Statistics

```
┌────────────────────────────────────────┐
│ Real-Time Stats on Dashboard           │
├────────────────────────────────────────┤
│ • Total Invitations                    │
│ • Active Invitations                   │
│ • Pending Approval Count               │
│ • Total Users                          │
│ • Approved Revenue                     │
│ • Pending Revenue                      │
│ • Pending Payments Count               │
│ • System Information                   │
└────────────────────────────────────────┘
```

## Default Credentials (Development)

```
URL:      http://localhost/wedding/login.php
Email:    admin@example.com
Password: password123
Role:     Admin
```

⚠️ Change immediately in production!

## Quick Customizations

### Change Primary Color
File: `/admin/layout/header.php`
Find: `--primary-color: #6366f1;`
Change to your hex color

### Change App Name
File: `/config/config.php`
Find: `define('APP_NAME', 'Wedding Invitation System');`
Update text

### Change Max File Size
File: `/config/config.php`
Find: `define('MAX_FILE_SIZE', 10 * 1024 * 1024);`
Adjust bytes

### Add New Menu Item
File: `/admin/layout/header.php`
Find: `<li><a href=...>` sections
Add new link in sidebar

## Troubleshooting Quick Fixes

| Problem | Solution |
|---------|----------|
| Can't login | Check credentials, ensure DB connected |
| Upload fails | Check permissions: `chmod 755 public/uploads` |
| Blank page | Check error logs, enable error display |
| DB error | Verify credentials in config.php |
| 404 error | Check file path, file exists? |
| Session timeout | Configure timeout in config.php |

## Performance Tips

- ✓ Clear old uploaded files monthly
- ✓ Archive past invitations
- ✓ Enable query caching
- ✓ Add database indexes
- ✓ Use CDN for media files
- ✓ Enable GZIP compression
- ✓ Minify CSS/JS files
- ✓ Optimize images before upload

## Security Checklist

- ✓ Change default admin password
- ✓ Keep PHP updated
- ✓ Keep MySQL updated
- ✓ Use HTTPS in production
- ✓ Back up database regularly
- ✓ Restrict admin access (firewall)
- ✓ Monitor error logs
- ✓ Test payment workflow
- ✓ Review user accounts monthly
- ✓ Set up automated backups

## Useful Queries

### Get Payment Statistics
```sql
SELECT payment_status, COUNT(*) as count, SUM(amount) as total
FROM payments
GROUP BY payment_status;
```

### Get Invitation Statistics
```sql
SELECT status, COUNT(*) as count
FROM invitations
GROUP BY status;
```

### Get User Activity
```sql
SELECT u.fullname, COUNT(i.id) as invitations, COUNT(p.id) as payments
FROM users u
LEFT JOIN invitations i ON u.id = i.user_id
LEFT JOIN payments p ON u.id = p.user_id
GROUP BY u.id
ORDER BY invitations DESC;
```

### Get Guest Statistics
```sql
SELECT i.host_name_1, COUNT(r.id) as guests, 
       COUNT(CASE WHEN r.attendance_status='Yes' THEN 1 END) as confirmed
FROM invitations i
LEFT JOIN rsvps r ON i.id = r.invitation_id
GROUP BY i.id;
```

## Backup Commands

### Backup Database
```bash
mysqldump -u root -p wedding > wedding_backup.sql
```

### Restore Database
```bash
mysql -u root -p wedding < wedding_backup.sql
```

### Backup Files
```bash
tar -czf wedding_backup.tar.gz /path/to/wedding
```

## File Permissions Reference

```
755 → rwxr-xr-x  (directories)
644 → rw-r--r--  (files)
700 → rwx------  (sensitive files)
```

Set all:
```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 public public/uploads
```

## API Response Format

All API endpoints return JSON:

```json
{
  "success": true,           // Boolean
  "message": "Operation...", // String
  "data": {                  // Optional object
    // Response data
  }
}
```

Example:
```json
{
  "success": true,
  "message": "Guest checked in successfully",
  "data": {
    "guest": {
      "id": 1,
      "guest_name": "John Doe",
      "attendance_status": "Yes"
    }
  }
}
```

## Module Dependencies

```
Core:
├─ config.php      → ALL
├─ Database.php    → Models
├─ Auth.php        → Pages
├─ Middleware.php  → Admin Pages
└─ Helpers.php     → ALL

Models:
├─ Model.php       → Base class
├─ User.php        → User pages
├─ Invitation.php  → Invitation pages
├─ Payment.php     → Payment pages
└─ RSVP.php        → Guest pages
```

---

## For More Information

- **Setup**: Read `SETUP.md`
- **Deployment**: Read `DEPLOYMENT.md`
- **Features**: Read `README_IMPLEMENTATION.md`
- **Full Summary**: Read `PROJECT_SUMMARY.txt`

---

**Last Updated**: 2026
**Version**: 1.0
**Status**: Production Ready ✅
