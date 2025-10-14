<?php

declare(strict_types=1);

/**
 * Kidney Tales User Management System Setup Script
 * 
 * This script helps set up the user management system by:
 * 1. Checking system requirements
 * 2. Testing database connection
 * 3. Creating database tables
 * 4. Seeding initial data
 * 5. Setting up default admin account
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2025.10.1
 */

// Include bootstrap
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Check if running from command line
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    // Simple web interface
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Kidney Tales Setup</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
    echo ".success{color:green;}.error{color:red;}.info{color:blue;}";
    echo "pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;}";
    echo "</style></head><body>";
    echo "<h1>Kidney Tales User Management System Setup</h1>";
}

/**
 * Log output with formatting
 */
function logOutput(string $message, string $type = 'info'): void
{
    global $isCli;
    
    if ($isCli) {
        $prefix = match($type) {
            'success' => '✓ ',
            'error' => '✗ ',
            'warning' => '⚠ ',
            default => 'ℹ '
        };
        echo $prefix . $message . "\n";
    } else {
        $class = htmlspecialchars($type);
        $message = htmlspecialchars($message);
        echo "<p class=\"{$class}\">{$message}</p>\n";
    }
}

/**
 * Check system requirements
 */
function checkSystemRequirements(): bool
{
    logOutput("Checking system requirements...");
    
    $requirements = [
        'PHP Version >= 8.4' => version_compare(PHP_VERSION, '8.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'MBString Extension' => extension_loaded('mbstring'),
        'OpenSSL Extension' => extension_loaded('openssl'),
    ];
    
    $allGood = true;
    
    foreach ($requirements as $requirement => $met) {
        if ($met) {
            logOutput("✓ {$requirement}", 'success');
        } else {
            logOutput("✗ {$requirement}", 'error');
            $allGood = false;
        }
    }
    
    return $allGood;
}

/**
 * Test database connection
 */
function testDatabaseConnection(): bool
{
    logOutput("Testing database connection...");
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        logOutput("Database server connection successful", 'success');
        
        // Try to connect to the specific database
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        logOutput("Database '{DB_NAME}' connection successful", 'success');
        return true;
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            logOutput("Database '" . DB_NAME . "' does not exist. Please create it first.", 'warning');
            logOutput("Run: CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;", 'info');
        } else {
            logOutput("Database connection failed: " . $e->getMessage(), 'error');
        }
        return false;
    }
}

/**
 * Create database tables
 */
function createDatabaseTables(): bool
{
    logOutput("Creating database tables...");
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $schemaFile = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql';
        
        if (!file_exists($schemaFile)) {
            logOutput("Schema file not found: {$schemaFile}", 'error');
            return false;
        }
        
        $sql = file_get_contents($schemaFile);
        
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        logOutput("Database tables created successfully", 'success');
        return true;
        
    } catch (PDOException $e) {
        logOutput("Failed to create tables: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Seed initial data
 */
function seedInitialData(): bool
{
    logOutput("Seeding initial data...");
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $seedFile = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seed_data.sql';
        
        if (!file_exists($seedFile)) {
            logOutput("Seed file not found: {$seedFile}", 'error');
            return false;
        }
        
        $sql = file_get_contents($seedFile);
        
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        logOutput("Initial data seeded successfully", 'success');
        return true;
        
    } catch (PDOException $e) {
        logOutput("Failed to seed data: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Verify installation
 */
function verifyInstallation(): bool
{
    logOutput("Verifying installation...");
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Check if tables exist
        $tables = ['users', 'roles', 'permissions', 'role_permissions', 'subscription_plans', 'user_subscriptions'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() === 0) {
                logOutput("Table '{$table}' not found", 'error');
                return false;
            }
        }
        
        // Check if roles exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
        $roleCount = $stmt->fetchColumn();
        
        if ($roleCount < 5) {
            logOutput("Expected at least 5 roles, found {$roleCount}", 'error');
            return false;
        }
        
        // Check if permissions exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM permissions");
        $permissionCount = $stmt->fetchColumn();
        
        if ($permissionCount < 10) {
            logOutput("Expected at least 10 permissions, found {$permissionCount}", 'error');
            return false;
        }
        
        // Check if subscription plans exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM subscription_plans");
        $planCount = $stmt->fetchColumn();
        
        if ($planCount < 5) {
            logOutput("Expected at least 5 subscription plans, found {$planCount}", 'error');
            return false;
        }
        
        // Check if admin user exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        $adminExists = $stmt->fetchColumn() > 0;
        
        if (!$adminExists) {
            logOutput("Admin user not found", 'error');
            return false;
        }
        
        logOutput("Installation verification completed successfully", 'success');
        return true;
        
    } catch (PDOException $e) {
        logOutput("Verification failed: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Display setup summary
 */
function displaySetupSummary(): void
{
    global $isCli;
    
    logOutput("\n" . str_repeat("=", 60));
    logOutput("KIDNEY TALES USER MANAGEMENT SYSTEM SETUP COMPLETE");
    logOutput(str_repeat("=", 60));
    
    logOutput("\nDefault Admin Account:");
    logOutput("Username: admin");
    logOutput("Email: admin@kidneytales.com");
    logOutput("Password: KidneyTales2025!");
    logOutput("⚠ IMPORTANT: Change the admin password immediately!");
    
    logOutput("\nSample User Accounts:");
    logOutput("Translator - Username: translator, Password: Translator2025!");
    logOutput("Creator - Username: creator, Password: Creator2025!");
    
    logOutput("\nSubscription Plans Created:");
    logOutput("- Free (0 articles, 5 translations per month)");
    logOutput("- Basic ($9.99/month, 10 articles, 25 translations)");
    logOutput("- Premium ($19.99/month, 50 articles, 100 translations)");
    logOutput("- Professional ($39.99/month, 200 articles, 500 translations)");
    logOutput("- Enterprise ($99.99/month, unlimited)");
    
    logOutput("\nUser Roles Created:");
    logOutput("- Reader (basic access)");
    logOutput("- Translator (can translate content)");
    logOutput("- Creator (can create content)");
    logOutput("- Editor (can edit and publish)");
    logOutput("- Administrator (full access)");
    
    logOutput("\nNext Steps:");
    logOutput("1. Change default admin password");
    logOutput("2. Configure email settings (if applicable)");
    logOutput("3. Set up SSL/HTTPS in production");
    logOutput("4. Configure session settings for production");
    logOutput("5. Set up regular database backups");
    
    if (!$isCli) {
        echo "<h2>Setup Complete!</h2>";
        echo "<p><strong>Your Kidney Tales user management system is now ready to use.</strong></p>";
        echo "<p><a href='/login'>Go to Login Page</a></p>";
    }
}

// Main setup process
try {
    logOutput("Starting Kidney Tales User Management System Setup...\n");
    
    // Step 1: Check system requirements
    if (!checkSystemRequirements()) {
        logOutput("System requirements not met. Please fix the issues above and try again.", 'error');
        exit(1);
    }
    
    // Step 2: Test database connection
    if (!testDatabaseConnection()) {
        logOutput("Database connection failed. Please check your configuration and try again.", 'error');
        exit(1);
    }
    
    // Step 3: Create database tables
    if (!createDatabaseTables()) {
        logOutput("Failed to create database tables. Check the error above.", 'error');
        exit(1);
    }
    
    // Step 4: Seed initial data
    if (!seedInitialData()) {
        logOutput("Failed to seed initial data. Check the error above.", 'error');
        exit(1);
    }
    
    // Step 5: Verify installation
    if (!verifyInstallation()) {
        logOutput("Installation verification failed. Check the errors above.", 'error');
        exit(1);
    }
    
    // Step 6: Display summary
    displaySetupSummary();
    
} catch (Exception $e) {
    logOutput("Setup failed with error: " . $e->getMessage(), 'error');
    exit(1);
}

if (!$isCli) {
    echo "</body></html>";
}