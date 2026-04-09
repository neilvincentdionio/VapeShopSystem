# Vape Shop System - Security Enhanced Version

## Laboratory Exercise 5: Advanced User Management and Security Enhancements

This enhanced version of the Vape Shop System implements comprehensive security features as part of Laboratory Exercise 5.

## Quick Start

### Prerequisites
- XAMPP (or similar) with Apache, MySQL, PHP 8.0+
- Composer
- MySQL 5.7+

### Installation

1. **Setup Database**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE vapeshop_db;
   
   # Run migrations
   php spark migrate
   
   # Seed permissions and users
   php spark db:seed UserSeeder
   php spark db:seed PermissionSeeder
   ```

2. **Configure Environment**
   ```bash
   # Copy and configure .env
   cp .env.example .env
   
   # Set encryption key (generate 32-character key)
   encryption.key=your-super-secret-encryption-key-32-chars
   
   # Configure email for OTP
   email.protocol=smtp
   email.SMTPHost=your-smtp-host
   email.SMTPUser=your-email
   email.SMTPPass=your-password
   email.fromEmail=noreply@vapeshop.com
   ```

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Start Server**
   ```bash
   php spark serve
   ```

5. **Access Application**
   - URL: `http://localhost:8080`
   - Admin Login: `admin@vapeshop.com` / `password`
   - Customer Login: `customer@vapeshop.com` / `password`

## Security Features Overview

### 1. Authentication System
- **JWT Token-based Authentication** for API access
- **Multi-Factor Authentication (MFA)** with email OTP
- **Secure Password Hashing** using bcrypt
- **Account Lockout** after failed attempts
- **Session Management** with timeout protection

### 2. Access Control
- **Role-Based Access Control (RBAC)** with granular permissions
- **Advanced Permission System** (read/write/update/delete)
- **Route Protection** with authentication filters
- **API Security** with JWT authentication

### 3. Data Protection
- **Database Encryption** for sensitive data (phones, addresses)
- **Input Validation & Sanitization** against XSS and injection
- **CSRF Protection** on all forms
- **Secure Headers** and cookies

### 4. Backup & Recovery
- **Automated Database Backup** with compression
- **Backup Management Interface** for admins
- **Scheduled Backup Scripts** for Windows/Linux
- **Database Restore** functionality

### 5. Audit & Monitoring
- **Comprehensive Audit Logging** of all security events
- **Login Attempt Tracking** with IP monitoring
- **Activity Monitoring** and statistics
- **Security Event Alerts**

## New Security Endpoints

### Authentication API
```bash
# JWT Login
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

# Refresh Token
POST /api/auth/refresh
{
  "refresh_token": "token_here"
}

# Logout
POST /api/auth/logout
{
  "refresh_token": "token_here"
}

# Get Current User
GET /api/auth/me
Authorization: Bearer access_token_here
```

### Backup Management (Admin Only)
```bash
# View backup interface
GET /backup

# Create backup
POST /backup/create

# Restore backup
POST /backup/restore
{
  "backup_file": "backup_2026-04-09_14-00-00.sql.gz"
}

# Download backup
GET /backup/download/filename.sql.gz

# Delete backup
POST /backup/delete
{
  "backup_file": "filename.sql.gz"
}
```

## User Roles & Permissions

### Default Roles
- **Admin**: Full system access, user management, backup operations
- **Staff**: Limited operational access (products, orders, reports)
- **Customer**: Basic customer access (dashboard, orders, profile)

### Permission Categories
- **Users**: read, write, update, delete
- **Products**: read, write, update, delete
- **Orders**: read, write, update, delete
- **Reports**: read, write
- **Backup**: read, write, delete
- **System**: read, update
- **Audit**: read

## Security Configuration

### JWT Configuration
```php
// app/Libraries/JwtService.php
private static $secretKey = 'your-secret-key';
private static $accessTokenExpiry = 3600; // 1 hour
private static $refreshTokenExpiry = 604800; // 7 days
```

### MFA Configuration
```php
// OTP Settings
$otpLength = 6; // digits
$otpExpiry = 300; // 5 minutes
$maxAttempts = 3; // maximum OTP attempts
```

### Encryption Configuration
```php
// app/Libraries/EncryptionService.php
// Uses CodeIgniter's built-in encryption
// AES-256 encryption with secure key management
```

## Automated Backups

### Windows Setup
```bash
# Schedule with Task Scheduler
# Run daily at 2:00 AM
C:\path\to\scripts\backup_database.bat
```

### Linux Setup
```bash
# Add to crontab
0 2 * * * /path/to/scripts/backup_database.sh
```

### Manual Backup
```bash
# Create backup
php spark backup:create --name=my_backup

# Cleanup old backups (keep last 10)
php spark backup:cleanup --keep=10

# View backup stats
php spark backup:stats
```

## Security Monitoring

### Audit Log Review
```bash
# View recent security events
php spark audit:recent --limit=50

# View failed login attempts
php spark audit:failed --days=7

# Generate security report
php spark audit:report --start=2026-04-01 --end=2026-04-30
```

### Security Statistics
- Total login attempts
- Failed authentication rate
- MFA usage statistics
- Backup execution status
- Permission access patterns

## Testing Security Features

### Authentication Testing
```bash
# Test login with valid credentials
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vapeshop.com","password":"password"}'

# Test MFA flow
1. Login with customer credentials
2. Check email for OTP
3. Submit OTP for verification
```

### Security Testing Checklist
See `TESTING_CHECKLIST.md` for comprehensive testing procedures.

## Security Best Practices

### For Administrators
1. **Use strong passwords** and enable MFA
2. **Regularly review audit logs** for suspicious activity
3. **Backup database daily** and test restore procedures
4. **Monitor failed login attempts** and investigate patterns
5. **Keep software updated** with security patches

### For Developers
1. **Validate all input** and sanitize output
2. **Use parameterized queries** to prevent SQL injection
3. **Implement least privilege** access control
4. **Log security events** comprehensively
5. **Test security features** regularly

### For Users
1. **Use unique passwords** for each account
2. **Enable MFA** when available
3. **Report suspicious activity** immediately
4. **Logout properly** when finished
5. **Keep contact information** updated for MFA

## Troubleshooting

### Common Issues

#### MFA Not Working
- Check email configuration in `.env`
- Verify SMTP settings are correct
- Check spam folder for OTP emails
- Ensure email service is running

#### JWT Token Issues
- Verify system clock synchronization
- Check token expiration settings
- Ensure proper key configuration
- Clear browser cache and cookies

#### Backup Issues
- Check database permissions
- Verify disk space availability
- Review backup logs for errors
- Test manual backup creation

#### Performance Issues
- Check database indexes
- Monitor server resources
- Review audit log retention
- Optimize backup schedules

### Getting Help
1. Check application logs: `writable/logs/`
2. Review audit logs for security events
3. Verify configuration settings
4. Consult `SECURITY_GUIDE.md` for detailed information

## Security Compliance

### Standards Implemented
- **OWASP Top 10** security controls
- **Data Protection** encryption at rest
- **Access Control** principle of least privilege
- **Audit Trail** comprehensive logging
- **Backup & Recovery** automated procedures

### Documentation
- `SECURITY_GUIDE.md` - Detailed security implementation
- `TESTING_CHECKLIST.md` - Comprehensive testing procedures
- `scripts/` - Automated backup and restore scripts
- `app/Libraries/` - Security service implementations

## Version History

### Version 2.0 - Security Enhanced
- Added JWT token authentication
- Implemented MFA with email OTP
- Added database encryption
- Created automated backup system
- Enhanced audit logging
- Implemented advanced RBAC
- Added security monitoring tools

### Version 1.0 - Base System
- Basic user authentication
- Simple role-based access
- Core e-commerce functionality
- Basic inventory management

## Support & Maintenance

### Regular Tasks
- **Daily**: Monitor backup execution
- **Weekly**: Review audit logs
- **Monthly**: Update security patches
- **Quarterly**: Test restore procedures
- **Annually**: Security audit

### Emergency Procedures
1. **Security Breach**: Review audit logs, lock accounts, notify users
2. **Data Loss**: Restore from latest backup, investigate cause
3. **System Failure**: Check logs, restart services, verify integrity

---

## License

This project is part of Laboratory Exercise 5 for educational purposes. Please ensure compliance with your institution's security policies and data protection regulations.

For technical support or questions about security features, please refer to the documentation files or contact your system administrator.
