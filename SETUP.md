# Wedding Invitation System - Setup Guide

## Project Overview

This is a fully functional PHP-based wedding invitation system with:
- **Admin Dashboard** with comprehensive management tools
- **Public Viewer Pages** for guests to view invitations
- **RSVP System** with guest management
- **Payment Management** with approval workflow
- **Package & Theme Management**
- **Live Check-in** capability

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Web Server (Apache, Nginx, etc.)
- Composer (optional, for dependency management)

## Installation Steps

### 1. Database Setup

```bash
# Import the database schema
mysql -u root -p wedding < db.sql
```

Or use phpMyAdmin to import the `db.sql` file.

### 2. Configuration

Create a `.env` file in the root directory:

```env
APP_ENV=production
APP_URL=https://yourdomain.com

# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=wedding
DB_PORT=3306

# Security
JWT_SECRET=your-very-secret-key-change-this
```

Or update `config/config.php` directly with your environment variables.

### 3. Directory Permissions

Ensure upload directory is writable:

```bash
mkdir -p public/uploads
chmod 755 public/uploads
chmod 755 public
```

### 4. Initial Admin User

Add an admin user directly to the database or use the registration and then update the role:

```sql
INSERT INTO users (fullname, email, password, role, created_at) VALUES (
    'Admin User',
    'admin@example.com',
    '$2y$10$...bcrypt_hash...',
    'admin',
    NOW()
);
```

Or use the API to create the password hash using PHP:

```php
echo password_hash('your_password', PASSWORD_BCRYPT);
```

## File Structure

```
wedding/
├── config/                      # Configuration & Core Classes
│   ├── config.php              # Main configuration
│   ├── Database.php            # PDO Database wrapper
│   ├── Auth.php                # Authentication system
│   ├── Middleware.php          # Route protection
│   └── Helpers.php             # Utility functions
│
├── models/                      # Data Models (MVC)
│   ├── Model.php               # Base model class
│   ├── User.php
│   ├── Invitation.php
│   ├── Payment.php
│   └── RSVP.php
│
├── admin/                       # Admin Panel
│   ├── dashboard.php           # Main dashboard
│   ├── invitations.php         # Manage invitations
│   ├── users.php               # Manage users
│   ├── payments.php            # Manage payments
│   ├── guests.php              # Manage RSVPs
│   ├── packages.php            # Manage packages
│   ├── themes.php              # Manage themes
│   ├── layout/
│   │   ├── header.php          # Navigation & header
│   │   └── footer.php          # Footer & scripts
│   └── ...detail pages
│
├── api/                         # API Endpoints
│   ├── invitation-detail.php
│   ├── payment-approve.php
│   ├── payment-reject.php
│   ├── checkin-guest.php
│   └── ...more endpoints
│
├── public/                      # Public Pages
│   ├── invitation.php          # Guest view invitation
│   └── uploads/                # Media files
│
├── login.php                    # Admin login
├── 404.php                      # Error page
└── db.sql                       # Database schema
```

## Key Features Implemented

### Admin Dashboard
- System statistics and overview
- Recent invitations and payments
- Quick access to all management areas
- User information display

### Manage Invitations
- List all invitations with filters
- View detailed invitation information
- Edit invitation details
- Delete invitations
- Track guest statistics
- Monitor views and engagement

### Manage Users
- User listing with statistics
- View individual user profiles
- Track user payments and invitations
- Manage user roles

### Manage Payments
- Payment processing workflow
- Approve/reject payments
- Add admin notes
- View payment proofs
- Revenue tracking
- Payment statistics

### Manage Guests & RSVPs
- Guest list management
- RSVP status tracking
- Check-in management
- QR code generation
- Guest statistics

### Manage Packages
- Package creation and editing
- Feature configuration per package
- Price management
- Package visibility

### Manage Themes
- Color scheme customization
- Theme preview
- Package-specific themes
- Global theme management

### Public Invitation Pages
- Beautiful invitation display
- Guest gallery with images/videos
- Event timeline
- Location information with maps
- RSVP form
- Guestbook/comments section
- Social media integration

## Database Tables

### Core Tables
- **users** - User accounts with roles
- **invitations** - Wedding invitation data
- **invitation_type** - Invitation type templates
- **packages** - Service packages with features
- **themes** - Color and styling themes

### Guest Interaction
- **rsvps** - Guest RSVP data
- **guestbook** - Guest messages
- **media** - Photos and videos
- **locations** - Venue information
- **invitation_events** - Timeline events
- **social_links** - Social media links

### Business
- **payments** - Payment transactions
- **gift_registry** - Gift ideas
- **site_settings** - System configuration

## API Endpoints

### Authentication
- `GET /login.php` - Login page
- `POST /login.php` - Process login
- `GET /admin/logout.php` - Logout

### Invitations
- `GET /admin/invitations.php` - List invitations
- `GET /admin/invitation-detail.php?id=X` - View invitation
- `GET /admin/invitation-edit.php?id=X` - Edit invitation
- `GET /api/invitation-detail.php?id=X` - API detail endpoint

### Payments
- `GET /admin/payments.php` - List payments
- `GET /admin/payment-detail.php?id=X` - View payment
- `GET /api/payment-approve.php?id=X` - Approve payment
- `GET /api/payment-reject.php?id=X` - Reject payment

### Guests
- `GET /admin/guests.php` - List guests
- `GET /api/checkin-guest.php?token=X` - Check in guest

### Public
- `GET /public/invitation.php?slug=X` - View invitation
- `POST /public/invitation.php` - Submit RSVP

## Security Features

1. **Authentication**
   - Bcrypt password hashing
   - Session management with timeout
   - Role-based access control

2. **Authorization**
   - Middleware protection on admin pages
   - Role checks (admin/user)
   - CSRF token protection

3. **Data Protection**
   - Prepared statements (SQL injection prevention)
   - Input sanitization
   - Output escaping (XSS prevention)
   - CORS headers

4. **Session Security**
   - HTTP-only cookies (when configured)
   - Session timeout
   - User activity tracking

## Deployment Checklist

- [ ] Update `.env` with production credentials
- [ ] Set `APP_ENV=production` in config
- [ ] Create secure `JWT_SECRET`
- [ ] Set proper directory permissions (755)
- [ ] Enable HTTPS
- [ ] Configure database backups
- [ ] Set up error logging
- [ ] Configure email notifications (optional)
- [ ] Test payment workflow
- [ ] Set up analytics tracking

## Troubleshooting

### Database Connection Error
- Verify database credentials in `config/config.php`
- Check MySQL is running
- Ensure database exists

### Permission Denied on Uploads
```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads  # Linux
```

### Session Issues
- Check PHP session configuration
- Ensure `/tmp` directory is writable
- Clear browser cookies and try again

### Blank Pages
- Check PHP error logs
- Enable error reporting in `config/config.php`
- Verify all required files exist

## Next Steps

1. **Customize Branding**
   - Update logo in header layout
   - Customize color scheme in themes
   - Add company information in settings

2. **Add More Features**
   - Implement email notifications
   - Add QR code generation
   - Create analytics dashboard
   - Add payment gateway integration

3. **Performance**
   - Enable database query caching
   - Implement image optimization
   - Set up CDN for media files
   - Enable gzip compression

4. **Testing**
   - Test user registration flow
   - Test payment approval workflow
   - Test RSVP submission
   - Test public invitation viewing

## Support

For issues or questions, refer to:
- Database schema in `db.sql`
- Model classes in `/models`
- API documentation in individual files

## License

This system is provided as-is for the wedding project.
