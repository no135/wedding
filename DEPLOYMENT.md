# Wedding Invitation System - Deployment Guide

## Pre-Deployment Checklist

Before deploying to production, ensure:

- [ ] Database configured and migrations applied
- [ ] Environment variables set (.env file created)
- [ ] Admin account created
- [ ] File permissions configured (755 for public, writable uploads)
- [ ] HTTPS certificate installed
- [ ] Backups configured
- [ ] Error logging configured

## Quick Start for Local Development

### 1. Install & Configure

```bash
# Clone or extract project
cd wedding-invitation-system

# Update config/config.php with your database credentials
# Or create .env with variables:
# DB_HOST=localhost
# DB_USER=root
# DB_PASS=your_password
# DB_NAME=wedding
```

### 2. Import Database

```bash
mysql -u root -p wedding < db.sql
```

### 3. Create Admin User

Run this SQL to create an admin:

```sql
INSERT INTO users (fullname, email, password, role) VALUES (
    'Administrator',
    'admin@example.com',
    '$2y$10$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Zxm3.gxFuOb7BwBm',  -- password: password123
    'admin'
);
```

Or use PHP to generate a hash:

```php
<?php
echo password_hash('your_password', PASSWORD_BCRYPT);
?>
```

### 4. Set File Permissions

```bash
chmod 755 public
chmod 755 public/uploads
chmod 644 config/config.php
```

### 5. Start Development Server

For local testing:

```bash
cd /path/to/wedding
php -S localhost:8000
```

Then visit: `http://localhost:8000/login.php`

**Default credentials (change immediately):**
- Email: `admin@example.com`
- Password: `password123`

## Production Deployment

### Option 1: Traditional Web Host (cPanel, Plesk, etc.)

1. **Upload Files**
   - Upload all files via FTP to public_html/

2. **Create Database**
   - Create MySQL database via hosting control panel
   - Import `db.sql` file

3. **Update Configuration**
   - Edit `config/config.php` with production database credentials
   - Set `APP_ENV='production'`
   - Set `APP_URL` to your domain

4. **Set File Permissions**
   ```bash
   chmod 755 public
   chmod 755 public/uploads
   chmod 644 .htaccess
   chmod 644 config/*.php
   ```

5. **Create .htaccess** (if using Apache)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Prevent direct access to sensitive files
    RewriteRule ^config/ - [L,F]
    RewriteRule ^models/ - [L,F]
    RewriteRule ^api/ - [L,F]
    
    # Redirect non-www to www (optional)
    RewriteCond %{HTTP_HOST} !^www\. [NC]
    RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]
    
    # HTTPS redirect (recommended)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</IfModule>
```

### Option 2: Vercel Deployment

Unfortunately, Vercel's serverless architecture doesn't natively support traditional PHP applications. For Vercel hosting, consider:

- Migrate to Next.js/Node.js
- Use a dedicated PHP host (Heroku, DigitalOcean, etc.)
- Use a containerized solution (Docker)

### Option 3: Docker Deployment

Create a `Dockerfile`:

```dockerfile
FROM php:8.1-apache

# Enable required modules
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod 755 /var/www/html/public
RUN chmod 755 /var/www/html/public/uploads

# Enable Apache rewrite
RUN a2enmod rewrite

EXPOSE 80
```

Build and run:

```bash
docker build -t wedding-app .
docker run -p 80:80 -e DB_HOST=db -e DB_USER=root wedding-app
```

### Option 4: DigitalOcean App Platform

1. Push code to GitHub
2. Create App on DigitalOcean
3. Select PHP runtime
4. Set environment variables:
   - `APP_ENV=production`
   - `DB_HOST` (managed database)
   - `DB_USER`, `DB_PASS`, `DB_NAME`
5. Deploy

## Environment Variables (Production)

Create/update environment configuration:

```env
# Application
APP_ENV=production
APP_URL=https://yourdomain.com
APP_NAME=Wedding Invitation System

# Database (use managed database service)
DB_HOST=your-db-host.com
DB_USER=db_user
DB_PASS=very_secure_password_here
DB_NAME=wedding_db
DB_PORT=3306

# Security
JWT_SECRET=generate_a_random_secure_string_here

# Optional: Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
MAIL_FROM=noreply@yourdomain.com
```

## Security Hardening

### 1. Update Permissions

```bash
# Remove write permission from sensitive files
find . -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Make uploads directory writable only by web server
chmod 755 public/uploads
chown www-data:www-data public/uploads
```

### 2. Disable Directory Listing

Add to `.htaccess`:

```apache
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes
```

### 3. Set Security Headers

Update `config/config.php`:

```php
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'');
```

### 4. Database Backups

Set up automated backups:

```bash
# Daily backup script
0 2 * * * mysqldump -u root -p${DB_PASS} ${DB_NAME} > /backups/wedding_$(date +\%Y\%m\%d).sql
```

### 5. SSL/HTTPS

- Install SSL certificate (Let's Encrypt recommended)
- Force HTTPS in `.htaccess`:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
```

## Monitoring & Maintenance

### Log Monitoring

Enable error logging:

```php
// In config/config.php
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/wedding-errors.log');
```

### Regular Maintenance Tasks

1. **Weekly**
   - Check error logs
   - Monitor disk space
   - Review failed payments

2. **Monthly**
   - Backup database
   - Update dependencies
   - Review user activity

3. **Quarterly**
   - Security audit
   - Performance optimization
   - Feature updates

## Scaling & Performance

### Database Optimization

- Add indexes on frequently queried columns
- Enable query caching
- Archive old data

```sql
CREATE INDEX idx_invitation_user ON invitations(user_id);
CREATE INDEX idx_rsvp_invitation ON rsvps(invitation_id);
CREATE INDEX idx_payment_status ON payments(payment_status);
```

### Caching

Implement Redis caching:

```php
// Cache invitation data
$cache_key = 'invitation_' . $invitation_id;
if ($cached = $redis->get($cache_key)) {
    return unserialize($cached);
}

// Store in cache for 1 hour
$redis->setex($cache_key, 3600, serialize($data));
```

### CDN for Media

Serve media files through CDN:

```php
// config/config.php
define('CDN_URL', 'https://cdn.yourdomain.com');

// In templates
<img src="<?php echo CDN_URL . '/' . sanitize($media['file_path']); ?>" />
```

## Troubleshooting Production Issues

### Blank Page / 500 Error

Check error logs:
```bash
tail -f /var/log/apache2/error.log
```

Enable debug in config temporarily:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Database Connection Failed

Verify credentials:
```bash
mysql -h DB_HOST -u DB_USER -p DB_NAME
```

### File Upload Issues

Check permissions:
```bash
ls -la public/uploads/
chmod 755 public/uploads
chown www-data:www-data public/uploads
```

### Session Issues

Ensure /tmp is writable:
```bash
php -r "echo sys_get_temp_dir();"
chmod 777 /tmp
```

## Getting Help

For issues:
1. Check `/var/log/apache2/error.log` (or nginx equivalent)
2. Enable error reporting temporarily
3. Check database connection
4. Verify file permissions
5. Review security logs

## Support Resources

- MySQL documentation: https://dev.mysql.com/
- Apache documentation: https://httpd.apache.org/
- PHP documentation: https://www.php.net/
- Security best practices: https://owasp.org/
