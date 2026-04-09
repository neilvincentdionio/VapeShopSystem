# Security Implementation Guide

## Laboratory Exercise 5: Advanced User Management and Security Enhancements

This document outlines the security features implemented in the Vape Shop System as part of Laboratory Exercise 5.

## Overview

The system has been enhanced with advanced security features including:
- JWT Token-based Authentication
- Multi-Factor Authentication (MFA) with OTP
- Database Encryption for Sensitive Data
- Automated Backup & Restore System
- Enhanced Audit Logging
- Advanced Role-Based Access Control (RBAC)

## Security Features Implemented

### 1. Authentication System

#### JWT Token-based Authentication
- **Location**: `app/Libraries/JwtService.php`, `app/Controllers/ApiAuth.php`
- **Features**:
  - Access tokens (1-hour expiry)
  - Refresh tokens (7-day expiry)
  - Secure token storage with hashing
  - Token refresh mechanism
- **API Endpoints**:
  - `POST /api/auth/login` - User login with JWT
  - `POST /api/auth/refresh` - Refresh access token
  - `POST /api/auth/logout` - Logout and revoke token
  - `GET /api/auth/me` - Get current user info

#### Multi-Factor Authentication (MFA)
- **Location**: `app/Controllers/Auth.php`, `app/Models/OtpCodeModel.php`
- **Features**:
  - Email-based OTP (6-digit code)
  - 5-minute expiry
  - Maximum 3 attempts
  - Admin exemption (direct login)
  - OTP resend functionality

#### Password Security
- **Hashing**: bcrypt with PHP's `password_hash()`
- **Reset**: Secure token-based password reset
- **Validation**: Minimum 8 characters, complexity requirements

### 2. Role-Based Access Control (RBAC)

#### Advanced Permission System
- **Location**: `app/Libraries/PermissionService.php`, `app/Models/PermissionModel.php`
- **Features**:
  - Granular permissions (read, write, update, delete)
  - Resource-based permissions
  - Role-permission mapping
  - User-role assignment
- **Tables**:
  - `permissions` - Available permissions
  - `role_permissions` - Role-permission mapping
  - `user_roles` - User-role assignments

#### Default Roles
- **Admin**: Full system access
- **Staff**: Limited operational access
- **Customer**: Basic customer access

### 3. Database Security & Encryption

#### Data Encryption
- **Location**: `app/Libraries/EncryptionService.php`
- **Encrypted Fields**:
  - Phone numbers
  - Email addresses (optional)
  - Address information
  - Personal data
- **Encryption**: AES-256 with secure key management

#### Secure Schema
- **Tables**: `users`, `permissions`, `role_permissions`, `user_roles`, `audit_logs`
- **Foreign Keys**: Proper referential integrity
- **Indexes**: Optimized for security queries

### 4. Backup & Restore System

#### Automated Backup
- **Location**: `app/Libraries/BackupService.php`, `app/Controllers/BackupController.php`
- **Features**:
  - Full database backup
  - Compression (gzip)
  - Timestamp-based naming
  - Backup management interface
- **Web Interface**: `/backup` (Admin only)

#### Backup Scripts
- **Windows**: `scripts/backup_database.bat`
- **Linux/Mac**: `scripts/backup_database.sh`
- **Features**:
  - Scheduled execution
  - Automatic cleanup (keep last N backups)
  - Logging and error handling

#### Restore Functionality
- **Web Interface**: Restore from admin panel
- **Command Line**: `scripts/restore_database.bat/sh`
- **Safety**: Confirmation prompts and validation

### 5. Audit Logging

#### Comprehensive Logging
- **Location**: `app/Models/AuditLogModel.php`
- **Logged Events**:
  - Login attempts (success/failure)
  - MFA events
  - Password resets
  - User management actions
  - Backup operations
  - Role changes
  - System access

#### Audit Features
- **Details**: IP address, user agent, timestamps
- **Filtering**: By user, action, date range
- **Statistics**: Activity summaries and trends
- **Retention**: 90-day automatic cleanup

### 6. Security Enhancements

#### Input Validation & Sanitization
- **Location**: `app/Controllers/Auth.php`
- **Features**:
  - Email validation and sanitization
  - Phone number validation
  - Address field validation
  - XSS protection

#### Rate Limiting & Account Lockout
- **Features**:
  - IP-based rate limiting
  - Account lockout after 5 failed attempts
  - 30-minute lockout duration
  - Automatic unlock

#### Session Security
- **Features**:
  - 30-minute timeout
  - Session regeneration on login
  - Secure cookies (httpOnly, secure, sameSite)
  - Activity tracking

#### CSRF Protection
- **Implementation**: CodeIgniter built-in CSRF protection
- **Token**: Per-request CSRF tokens
- **Validation**: Automatic token verification

## Installation & Setup

### 1. Database Setup
```bash
# Run migrations
php spark migrate

# Seed permissions and roles
php spark db:seed PermissionSeeder
```

### 2. Configuration
```bash
# Set encryption key in .env
encryption.key=your-super-secret-encryption-key-32-chars

# Configure email for OTP
email.protocol=smtp
email.SMTPHost=your-smtp-host
email.SMTPUser=your-email
email.SMTPPass=your-password
```

### 3. Backup Scripts
```bash
# Windows (Task Scheduler)
C:\path\to\scripts\backup_database.bat

# Linux (cron job)
0 2 * * * /path/to/scripts/backup_database.sh
```

## API Documentation

### Authentication Endpoints

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "admin"
    }
  }
}
```

#### Refresh Token
```http
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### Logout
```http
POST /api/auth/logout
Content-Type: application/json

{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

## Security Best Practices

### 1. Password Security
- Use strong passwords (minimum 8 characters)
- Enable MFA for all user accounts
- Regular password changes for admin accounts

### 2. Session Management
- Monitor session timeout
- Logout from all devices when needed
- Clear browser cache regularly

### 3. Data Protection
- Regular database backups
- Encrypt sensitive data at rest
- Monitor audit logs for suspicious activity

### 4. Access Control
- Principle of least privilege
- Regular review of user permissions
- Immediate revocation of access for terminated users

## Monitoring & Maintenance

### 1. Backup Monitoring
- Check backup logs daily
- Verify backup file integrity
- Test restore procedures monthly

### 2. Audit Log Review
- Review failed login attempts
- Monitor unusual activity patterns
- Investigate security alerts

### 3. System Updates
- Keep dependencies updated
- Apply security patches promptly
- Monitor for vulnerabilities

## Troubleshooting

### Common Issues

#### JWT Token Issues
- Check system clock synchronization
- Verify token expiration
- Ensure proper key configuration

#### MFA OTP Issues
- Check email configuration
- Verify SMTP settings
- Check spam folder

#### Backup Issues
- Verify database permissions
- Check disk space
- Review backup logs

### Support
For technical support:
1. Check application logs
2. Review audit logs
3. Verify configuration
4. Contact system administrator

## Compliance & Standards

This implementation addresses:
- **OWASP Top 10**: Authentication, session management, access control
- **Data Protection**: Encryption at rest and in transit
- **Audit Requirements**: Comprehensive logging and monitoring
- **Backup Standards**: Automated backup and recovery procedures

## Future Enhancements

- LDAP/Active Directory integration
- Biometric authentication
- Advanced threat detection
- Automated security scanning
- Compliance reporting dashboard
