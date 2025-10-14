<?php

declare(strict_types=1);

// File: /config/setconstants.php

/**
 * Set Constants for Kidney Tales - multilingual web application
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2005.08.1.0
 */

define('APP_ENV', 'development');
define('DEFAULT_LANGUAGE', 'en');
define('APP_NAME', 'Kidney Tales');
define('LANGUAGES_PATH', dirname(__DIR__) . DS . 'languages' . DS);
define('APP_LOCALE', 'en_US');
define('APP_LANG', 'en');
define('APP_COUNTRY', 'US');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kidneytales');
define('DB_USER', 'kidneytales_user');
define('DB_PASS', 'secure_password_change_in_production');
define('DB_CHARSET', 'utf8mb4');

// User Management Constants
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
define('EMAIL_VERIFICATION_TIMEOUT', 86400); // 24 hours
define('PASSWORD_RESET_TIMEOUT', 3600); // 1 hour
