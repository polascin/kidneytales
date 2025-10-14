# Kidney Tales User Management System - Quick Start Guide

## 🚀 Getting Started

This guide will help you set up the comprehensive user management system for Kidney Tales.

### Prerequisites

- PHP 8.4 or higher
- MySQL/MariaDB database server
- Web server (Apache/Nginx) or PHP development server
- Composer (for dependencies)

### Installation Steps

#### 1. Configure Database Connection

Edit `config/setconstants.php` and update the database configuration:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kidneytales');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

#### 2. Install Dependencies

```bash
composer install
```

#### 3. Set Up Database

Option A: **Web Interface** (Recommended)
```
Navigate to: http://your-site.com/database/setup.php
```

Option B: **Command Line**
```bash
php database/setup.php
```

Option C: **Manual Setup**
```bash
mysql -u your_user -p kidneytales < database/schema.sql
mysql -u your_user -p kidneytales < database/seed_data.sql
```

#### 4. Test Installation

Navigate to: `http://your-site.com/database/test.php`

This will run comprehensive tests to verify your installation.

#### 5. Configure Web Server

Point your web server document root to the `public/` directory.

**For PHP Development Server:**
```bash
php -S localhost:8000 -t public/
```

### Default Admin Account

After setup, you can log in with:
- **Username:** `admin`
- **Email:** `admin@kidneytales.local`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change this password immediately after first login!

### User Roles & Permissions

The system includes 5 user roles with hierarchical permissions:

1. **Reader** - Basic content access
2. **Translator** - Can translate content
3. **Creator** - Can create and translate content
4. **Editor** - Can edit and review content
5. **Administrator** - Full system access

### Subscription Plans

5 subscription tiers are available:

1. **Free** - $0/month (2 articles, 5 translations)
2. **Basic** - $9.99/month (10 articles, 25 translations)
3. **Premium** - $19.99/month (50 articles, 100 translations)
4. **Professional** - $39.99/month (200 articles, 500 translations)
5. **Enterprise** - $99.99/month (unlimited)

### Key Features

- ✅ **Multilingual Support** - Integrated with 200+ language system
- ✅ **Role-Based Access Control** - Granular permissions system
- ✅ **Subscription Management** - Usage tracking and limits
- ✅ **Security Features** - CSRF protection, rate limiting, password security
- ✅ **Session Management** - Secure session handling with IP binding
- ✅ **User Profiles** - Comprehensive user profile management

### File Structure

```
src/
├── Controllers/
│   ├── UserController.php        # User registration, authentication
│   └── AuthenticationManager.php # Security and access control
├── Models/
│   ├── User.php                  # User data and authentication
│   ├── Role.php                  # Role and permission management
│   └── Subscription.php          # Subscription and usage tracking
└── Views/
    └── HomePageView.php          # View controller

resources/views/
├── login.php                     # Login page
├── register.php                  # Registration page
├── dashboard.php                 # User dashboard
├── profile.php                   # User profile management
└── components/
    └── user-info.php            # Header user information component

database/
├── schema.sql                    # Database structure
├── seed_data.sql                # Initial data
├── setup.php                    # Database setup script
└── test.php                     # Installation verification
```

### Integration with Existing System

The user management system is designed to integrate seamlessly with the existing Kidney Tales multilingual system:

- Uses existing language detection and translation system
- Follows established MVC patterns
- Integrates with session management
- Maintains security standards

### Troubleshooting

#### Common Issues

1. **Database Connection Failed**
   - Verify database credentials in `config/setconstants.php`
   - Ensure MySQL service is running
   - Check database exists and user has proper permissions

2. **Permission Denied Errors**
   - Ensure web server has read permissions on all files
   - Check file ownership and permissions

3. **Class Not Found Errors**
   - Run `composer install` to ensure autoloader is updated
   - Verify `bootstrap.php` is included

4. **Session Issues**
   - Ensure session directory is writable
   - Check session configuration in PHP

#### Getting Help

- Check the test script: `/database/test.php`
- Review error logs in your web server
- Ensure all file paths use `APP_ROOT . DS` pattern

### Next Steps

After successful installation:

1. **Change Admin Password** - Update the default admin account
2. **Configure Email** - Set up email verification (optional)
3. **Customize Views** - Modify templates to match your design
4. **Test User Flow** - Register test users and verify functionality
5. **Production Setup** - Configure proper SSL, backups, and monitoring

### Security Considerations

- Change default admin credentials
- Use strong database passwords
- Enable HTTPS in production
- Regular security updates
- Monitor login attempts and user activity
- Configure proper session timeouts

### Support

For issues or questions:
- Review the documentation in `/docs/USER_MANAGEMENT_SYSTEM.md`
- Check the test results in `/database/test.php`
- Verify system requirements and configuration

---

**Kidney Tales User Management System v2025.10.1**  
**Author:** Ľubomír Polaščín