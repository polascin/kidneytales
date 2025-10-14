# Kidney Tales - Comprehensive User Management System Documentation

## Overview

This document describes the comprehensive user management system implemented for Kidney Tales, a multilingual web application for sharing kidney health stories. The system includes role-based access control, subscription management, and secure authentication features.

## Architecture

### Core Components

1. **User Model** (`src/Models/User.php`)
2. **Role & Permission System** (`src/Models/Role.php`)
3. **Subscription Management** (`src/Models/Subscription.php`)
4. **User Controller** (`src/Controllers/UserController.php`)
5. **Authentication Manager** (`src/Controllers/AuthenticationManager.php`)
6. **Database Schema** (`database/schema.sql` & `database/seed_data.sql`)

## User Roles and Permissions

### Role Hierarchy

1. **Reader** (Default Role)
   - Basic access to published content
   - Can read articles and stories
   - Limited interaction capabilities

2. **Translator**
   - All Reader permissions
   - Can translate content between languages
   - Access to translation tools
   - Can work on translation projects

3. **Creator**
   - All Reader permissions
   - Can create and manage their own content
   - Can translate content
   - Limited article creation based on subscription

4. **Editor**
   - All Creator permissions
   - Can edit and review content from other users
   - Can approve translations
   - Can publish content
   - Access to analytics

5. **Administrator**
   - Full system access
   - User management capabilities
   - System configuration
   - Subscription management
   - Analytics and reporting

### Permission System

The system uses granular permissions organized into categories:

#### Content Permissions
- `read_content` - View published content
- `create_content` - Create new articles
- `edit_content` - Edit others' content
- `delete_content` - Delete content
- `publish_content` - Publish content
- `view_draft_content` - View unpublished drafts

#### Translation Permissions
- `translate_content` - Create translations
- `review_translations` - Review translations
- `approve_translations` - Approve translations
- `manage_translation_projects` - Manage translation projects

#### User Management Permissions
- `view_user_profiles` - View user profiles
- `edit_user_profiles` - Edit user profiles
- `manage_users` - Full user management
- `assign_roles` - Assign/change user roles
- `view_user_activity` - View activity logs

#### Administrative Permissions
- `admin_dashboard` - Access admin dashboard
- `system_settings` - Modify system settings
- `view_analytics` - View analytics and reports
- `manage_languages` - Manage language settings
- `view_system_logs` - View system logs

#### Subscription Permissions
- `manage_subscriptions` - Manage user subscriptions
- `view_subscription_reports` - View subscription analytics
- `create_subscription_plans` - Create/modify plans

## Subscription Plans

### Available Plans

1. **Free Plan**
   - Price: $0/month
   - 2 articles per month
   - 5 translations per month
   - Basic content access

2. **Basic Plan**
   - Price: $9.99/month or $99.99/year
   - 10 articles per month
   - 25 translations per month
   - Premium content access
   - Translation tools

3. **Premium Plan**
   - Price: $19.99/month or $199.99/year
   - 50 articles per month
   - 100 translations per month
   - Priority support
   - Analytics access

4. **Professional Plan**
   - Price: $39.99/month or $399.99/year
   - 200 articles per month
   - 500 translations per month
   - API access
   - Advanced features

5. **Enterprise Plan**
   - Price: $99.99/month or $999.99/year
   - Unlimited articles and translations
   - Full feature access
   - Priority support

### Subscription Features

- **Usage Tracking**: Monthly limits on article creation and translations
- **Auto-renewal**: Automatic subscription renewal
- **Billing Cycles**: Monthly, yearly, or lifetime options
- **Grace Periods**: Configurable grace periods for expired subscriptions
- **Usage Reset**: Monthly usage counters reset automatically

## Security Features

### Authentication
- **Secure Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **Session Management**: Secure session handling with CSRF protection
- **Login Attempt Limiting**: Configurable lockout after failed attempts
- **Session Binding**: Sessions bound to user agent and IP address

### CSRF Protection
- Unique tokens for each session
- 1-hour token expiry
- Token validation on all POST requests
- Token regeneration on login

### Rate Limiting
- Configurable rate limits for various actions
- IP-based tracking
- Automatic reset after time windows

### Password Security
- Configurable minimum length (default: 8 characters)
- Password strength validation
- Secure password reset functionality

## Database Schema

### Core Tables

#### `users`
- User account information
- Authentication data
- Profile details
- Security settings

#### `roles`
- User role definitions
- Role hierarchy information

#### `permissions`
- Granular permission definitions
- Categorized permissions

#### `role_permissions`
- Many-to-many relationship
- Links roles to permissions

#### `subscription_plans`
- Available subscription plans
- Pricing and feature information

#### `user_subscriptions`
- User subscription records
- Usage tracking
- Billing information

### Optional Tables

#### `user_sessions`
- Advanced session management
- Session tracking and cleanup

#### `security_logs`
- Security event logging
- Audit trail maintenance

## API Integration

### Authentication Manager Methods

```php
// Check user permissions
AuthenticationManager::requirePermission('create_content');
AuthenticationManager::canCreateContent();
AuthenticationManager::canTranslate();

// Role-based access
AuthenticationManager::requireRole('editor');
AuthenticationManager::hasRole('administrator');

// Subscription limits
UserSubscription::canCreateArticle($userId);
UserSubscription::incrementArticleUsage($userId);
```

### User Controller Methods

```php
// User management
UserController::register();
UserController::login();
UserController::logout();
UserController::dashboard();
UserController::profile();

// Authentication helpers
UserController::isLoggedIn();
UserController::getCurrentUser();
UserController::hasPermission($permission);
```

## Installation and Setup

### 1. Database Setup

```sql
-- Create database
CREATE DATABASE kidneytales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Run schema
mysql -u username -p kidneytales < database/schema.sql

-- Seed initial data
mysql -u username -p kidneytales < database/seed_data.sql
```

### 2. Configuration

Update `config/setconstants.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kidneytales');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Default Admin Account

The system creates a default admin account:
- **Username**: `admin`
- **Email**: `admin@kidneytales.com`
- **Password**: `KidneyTales2025!`

**Important**: Change this password immediately in production!

### 4. File Permissions

Ensure proper file permissions for:
- Session storage directory
- Log files
- Upload directories (if applicable)

## Usage Examples

### Basic Authentication

```php
// Protect a page
AuthenticationManager::requireLogin();

// Check specific permission
AuthenticationManager::requirePermission('create_content');

// Get current user
$user = UserController::getCurrentUser();
if ($user) {
    echo "Welcome, " . $user->getFullName();
}
```

### Role-Based Access

```php
// Admin-only section
AuthenticationManager::requireRole('administrator');

// Multiple roles allowed
AuthenticationManager::requireAnyRole(['editor', 'administrator']);

// Check subscription limits
if (UserSubscription::canCreateArticle($userId)) {
    // Allow article creation
    UserSubscription::incrementArticleUsage($userId);
}
```

### User Registration

```php
// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    UserController::register();
}
```

## Multilingual Support

The user management system is fully integrated with Kidney Tales' multilingual capabilities:

- All user interface elements support translation
- User language preferences are stored and respected
- Registration forms adapt to selected language
- Email notifications use user's preferred language

## Security Best Practices

### Implementation Guidelines

1. **Always validate CSRF tokens** on POST requests
2. **Use prepared statements** for all database queries
3. **Implement rate limiting** for sensitive operations
4. **Log security events** for audit trails
5. **Regularly update** password hashing algorithms
6. **Monitor** failed login attempts and suspicious activity

### Production Deployment

1. **Change default passwords** immediately
2. **Enable HTTPS** for all user-facing pages
3. **Configure secure session** settings
4. **Set up proper logging** and monitoring
5. **Implement backup** and recovery procedures
6. **Regular security audits** and updates

## Customization

### Adding New Roles

1. Insert new role in `roles` table
2. Define permissions in `role_permissions` table
3. Update role constants in `Role.php`
4. Add role-specific logic in controllers

### Custom Permissions

1. Add permission to `permissions` table
2. Update permission constants in `Permission.php`
3. Implement permission checks in relevant controllers
4. Update UI based on permission availability

### Subscription Plan Modifications

1. Update `subscription_plans` table
2. Modify plan constants in `SubscriptionPlan.php`
3. Update pricing and feature logic
4. Refresh subscription management UI

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify database credentials
   - Check database server status
   - Ensure proper database permissions

2. **Session Issues**
   - Check session directory permissions
   - Verify session configuration
   - Clear browser cookies

3. **Permission Denied Errors**
   - Verify user role assignments
   - Check permission configurations
   - Review role-permission mappings

4. **CSRF Token Errors**
   - Ensure forms include CSRF tokens
   - Check token expiration settings
   - Verify POST request validation

### Debugging

Enable detailed error logging in development:

```php
// In config/setconstants.php
define('APP_ENV', 'development');
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

## Future Enhancements

### Planned Features

1. **Two-Factor Authentication** (2FA)
2. **OAuth Integration** (Google, Facebook, etc.)
3. **Advanced User Analytics**
4. **Automated User Onboarding**
5. **Role-Based Content Filtering**
6. **Advanced Subscription Analytics**

### Extension Points

The system is designed for extensibility:

- **Custom User Fields**: Easily add profile fields
- **Additional Roles**: Framework supports unlimited roles
- **Custom Permissions**: Granular permission system
- **Plugin Architecture**: Hooks for custom functionality
- **API Extensions**: RESTful API endpoints

## Support and Maintenance

### Regular Maintenance Tasks

1. **Clean up expired sessions**
2. **Archive old security logs**
3. **Update user statistics**
4. **Backup user data**
5. **Monitor subscription renewals**

### Performance Optimization

1. **Database indexing** on frequently queried fields
2. **Session storage optimization**
3. **Caching user permissions**
4. **Query optimization** for large user bases

---

**Note**: This documentation should be updated as the system evolves. Keep track of changes and maintain version compatibility when updating components.