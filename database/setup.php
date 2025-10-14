<?php
/**
 * Database Setup Script for Kidney Tales User Management System
 * 
 * This script helps set up the database schema and initial data.
 * Run this script once after configuring your database connection.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.10.1
 */

declare(strict_types=1);

// Include the bootstrap to load constants and autoloader
require_once __DIR__ . '/../bootstrap.php';

// HTML output for web interface
$isWebRequest = !empty($_SERVER['HTTP_HOST']);

if ($isWebRequest) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidney Tales Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>🏥 Kidney Tales Database Setup</h1>';
}

/**
 * Log message with optional color coding for web output
 */
function logMessage(string $message, string $type = 'info'): void {
    global $isWebRequest;
    
    if ($isWebRequest) {
        echo "<p class=\"{$type}\">{$message}</p>\n";
    } else {
        echo "[" . strtoupper($type) . "] {$message}\n";
    }
}

/**
 * Test database connection
 */
function testDatabaseConnection(): bool {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        logMessage("✅ Database connection successful", 'success');
        return true;
    } catch (PDOException $e) {
        logMessage("❌ Database connection failed: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Create database if it doesn't exist
 */
function createDatabase(): bool {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        logMessage("✅ Database '" . DB_NAME . "' created or already exists", 'success');
        return true;
    } catch (PDOException $e) {
        logMessage("❌ Failed to create database: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Execute SQL file
 */
function executeSqlFile(string $filePath, string $description): bool {
    if (!file_exists($filePath)) {
        logMessage("❌ SQL file not found: {$filePath}", 'error');
        return false;
    }
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $sql = file_get_contents($filePath);
        $pdo->exec($sql);
        
        logMessage("✅ {$description} completed successfully", 'success');
        return true;
    } catch (PDOException $e) {
        logMessage("❌ Failed to execute {$description}: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Verify installation by checking key tables
 */
function verifyInstallation(): bool {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Check for required tables
        $requiredTables = ['roles', 'permissions', 'users', 'subscription_plans'];
        $existingTables = [];
        
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            $existingTables[] = current($row);
        }
        
        $missingTables = array_diff($requiredTables, $existingTables);
        
        if (empty($missingTables)) {
            // Check data counts
            foreach ($requiredTables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
                $count = $stmt->fetch()['count'];
                logMessage("📊 Table '{$table}': {$count} records", 'info');
            }
            
            logMessage("✅ Installation verification successful!", 'success');
            return true;
        } else {
            logMessage("❌ Missing tables: " . implode(', ', $missingTables), 'error');
            return false;
        }
    } catch (PDOException $e) {
        logMessage("❌ Verification failed: " . $e->getMessage(), 'error');
        return false;
    }
}

// Main setup process
function runSetup(): bool {
    global $isWebRequest;
    
    if ($isWebRequest) {
        echo '<div class="step"><h2>🔧 Database Setup Process</h2></div>';
    }
    
    logMessage("🚀 Starting Kidney Tales database setup...", 'info');
    
    // Step 1: Test connection
    if ($isWebRequest) echo '<div class="step"><h3>Step 1: Testing Database Connection</h3>';
    if (!testDatabaseConnection()) {
        logMessage("Please check your database configuration in config/setconstants.php", 'warning');
        return false;
    }
    if ($isWebRequest) echo '</div>';
    
    // Step 2: Create database
    if ($isWebRequest) echo '<div class="step"><h3>Step 2: Creating Database</h3>';
    if (!createDatabase()) {
        return false;
    }
    if ($isWebRequest) echo '</div>';
    
    // Step 3: Create schema
    if ($isWebRequest) echo '<div class="step"><h3>Step 3: Creating Database Schema</h3>';
    $schemaPath = __DIR__ . '/schema.sql';
    if (!executeSqlFile($schemaPath, 'Database schema creation')) {
        return false;
    }
    if ($isWebRequest) echo '</div>';
    
    // Step 4: Insert seed data
    if ($isWebRequest) echo '<div class="step"><h3>Step 4: Inserting Initial Data</h3>';
    $seedPath = __DIR__ . '/seed_data.sql';
    if (!executeSqlFile($seedPath, 'Initial data insertion')) {
        return false;
    }
    if ($isWebRequest) echo '</div>';
    
    // Step 5: Verify installation
    if ($isWebRequest) echo '<div class="step"><h3>Step 5: Verifying Installation</h3>';
    if (!verifyInstallation()) {
        return false;
    }
    if ($isWebRequest) echo '</div>';
    
    logMessage("🎉 Database setup completed successfully!", 'success');
    
    if ($isWebRequest) {
        echo '<div class="step">
                <h3>✅ Setup Complete!</h3>
                <p><strong>Default Admin Account:</strong></p>
                <ul>
                    <li>Username: <code>admin</code></li>
                    <li>Email: <code>admin@kidneytales.local</code></li>
                    <li>Password: <code>admin123</code></li>
                </ul>
                <p class="warning"><strong>⚠️ Important:</strong> Change the admin password immediately after first login!</p>
              </div>';
    }
    
    return true;
}

// Execute setup if this script is run directly
if ($isWebRequest) {
    // Show form to confirm setup
    if (!isset($_POST['confirm_setup'])) {
        echo '<div class="step">
                <h3>⚠️ Database Setup Confirmation</h3>
                <p>This will create the database schema and insert initial data.</p>
                <p><strong>Current Configuration:</strong></p>
                <ul>
                    <li>Host: ' . DB_HOST . '</li>
                    <li>Database: ' . DB_NAME . '</li>
                    <li>User: ' . DB_USER . '</li>
                </ul>
                <form method="post">
                    <button type="submit" name="confirm_setup" value="1" style="background:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">
                        🚀 Start Database Setup
                    </button>
                </form>
              </div>';
    } else {
        runSetup();
    }
    
    echo '</body></html>';
} else {
    // Command line execution
    runSetup();
}