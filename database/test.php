<?php
/**
 * Test script for Kidney Tales User Management System
 * 
 * This script tests the basic functionality of the user management system
 * to ensure all components are working correctly.
 */

declare(strict_types=1);

// Include bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Load required classes
use KidneyTales\Controllers\UserController;
use KidneyTales\Controllers\AuthenticationManager;
use KidneyTales\Models\User;
use KidneyTales\Models\Role;
use KidneyTales\Models\SubscriptionPlan;

// HTML output for web interface
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kidney Tales User Management Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .status-pass { background-color: #d4edda; }
        .status-fail { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h1>🏥 Kidney Tales User Management System Test</h1>
    
    <?php
    $testResults = [];
    
    function testResult(string $testName, bool $passed, string $message = ''): void {
        global $testResults;
        $testResults[] = [
            'name' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
        
        $status = $passed ? 'PASS' : 'FAIL';
        $class = $passed ? 'success' : 'error';
        echo "<p class=\"{$class}\">[{$status}] {$testName}";
        if ($message) {
            echo " - {$message}";
        }
        echo "</p>\n";
    }
    
    // Test 1: Database Connection
    echo '<div class="test-section"><h2>🔗 Database Connection Test</h2>';
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        testResult('Database Connection', true, 'Connected to ' . DB_NAME);
    } catch (PDOException $e) {
        testResult('Database Connection', false, $e->getMessage());
    }
    echo '</div>';
    
    // Test 2: Table Existence
    echo '<div class="test-section"><h2>📋 Database Tables Test</h2>';
    try {
        $requiredTables = ['users', 'roles', 'permissions', 'role_permissions', 'subscription_plans', 'user_subscriptions', 'user_usage'];
        
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $exists = $stmt->rowCount() > 0;
            testResult("Table '{$table}'", $exists);
        }
    } catch (PDOException $e) {
        testResult('Table Check', false, $e->getMessage());
    }
    echo '</div>';
    
    // Test 3: Model Classes
    echo '<div class="test-section"><h2>🏗️ Model Classes Test</h2>';
    
    try {
        $user = new User();
        testResult('User Model', true, 'User class instantiated');
    } catch (Throwable $e) {
        testResult('User Model', false, $e->getMessage());
    }
    
    try {
        $role = new Role();
        testResult('Role Model', true, 'Role class instantiated');
    } catch (Throwable $e) {
        testResult('Role Model', false, $e->getMessage());
    }
    
    try {
        $subscription = new SubscriptionPlan();
        testResult('SubscriptionPlan Model', true, 'SubscriptionPlan class instantiated');
    } catch (Throwable $e) {
        testResult('SubscriptionPlan Model', false, $e->getMessage());
    }
    
    echo '</div>';
    
    // Test 4: Controller Classes
    echo '<div class="test-section"><h2>🎮 Controller Classes Test</h2>';
    
    try {
        $userController = new UserController();
        testResult('UserController', true, 'UserController class instantiated');
    } catch (Throwable $e) {
        testResult('UserController', false, $e->getMessage());
    }
    
    try {
        $authManager = new AuthenticationManager();
        testResult('AuthenticationManager', true, 'AuthenticationManager class instantiated');
    } catch (Throwable $e) {
        testResult('AuthenticationManager', false, $e->getMessage());
    }
    
    echo '</div>';
    
    // Test 5: Data Verification
    echo '<div class="test-section"><h2>📊 Data Verification Test</h2>';
    try {
        // Check roles
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
        $roleCount = $stmt->fetch()['count'];
        testResult('Roles Data', $roleCount >= 5, "Found {$roleCount} roles");
        
        // Check permissions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
        $permissionCount = $stmt->fetch()['count'];
        testResult('Permissions Data', $permissionCount >= 20, "Found {$permissionCount} permissions");
        
        // Check subscription plans
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM subscription_plans");
        $planCount = $stmt->fetch()['count'];
        testResult('Subscription Plans', $planCount >= 5, "Found {$planCount} plans");
        
        // Check admin user
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id = 5");
        $adminCount = $stmt->fetch()['count'];
        testResult('Admin User', $adminCount >= 1, "Found {$adminCount} admin user(s)");
        
    } catch (PDOException $e) {
        testResult('Data Verification', false, $e->getMessage());
    }
    echo '</div>';
    
    // Test 6: Role Permissions
    echo '<div class="test-section"><h2>🔐 Role Permissions Test</h2>';
    try {
        if (class_exists('KidneyTales\Models\Role')) {
            $roleModel = new Role();
            
            // Test admin permissions
            $hasAdminPerms = $roleModel->userHasPermission(1, 'admin_dashboard'); // Assuming user ID 1 is admin
            testResult('Admin Permissions', $hasAdminPerms, 'Admin has dashboard access');
            
            // Show role summary
            $stmt = $pdo->query("
                SELECT r.display_name, COUNT(rp.permission_id) as perm_count 
                FROM roles r 
                LEFT JOIN role_permissions rp ON r.id = rp.role_id 
                GROUP BY r.id, r.display_name 
                ORDER BY r.id
            ");
            
            echo '<table>';
            echo '<tr><th>Role</th><th>Permissions Count</th></tr>';
            while ($row = $stmt->fetch()) {
                echo "<tr><td>{$row['display_name']}</td><td>{$row['perm_count']}</td></tr>";
            }
            echo '</table>';
        }
    } catch (Throwable $e) {
        testResult('Role Permissions', false, $e->getMessage());
    }
    echo '</div>';
    
    // Test Summary
    echo '<div class="test-section"><h2>📈 Test Summary</h2>';
    $totalTests = count($testResults);
    $passedTests = array_sum(array_column($testResults, 'passed'));
    $failedTests = $totalTests - $passedTests;
    
    echo "<p><strong>Total Tests:</strong> {$totalTests}</p>";
    echo "<p class=\"success\"><strong>Passed:</strong> {$passedTests}</p>";
    echo "<p class=\"error\"><strong>Failed:</strong> {$failedTests}</p>";
    
    if ($failedTests === 0) {
        echo '<p class="success"><strong>🎉 All tests passed! The user management system is ready to use.</strong></p>';
        echo '<p><strong>Next Steps:</strong></p>';
        echo '<ul>';
        echo '<li>Change the default admin password (admin123)</li>';
        echo '<li>Configure your web server to point to the public/ directory</li>';
        echo '<li>Test user registration and login functionality</li>';
        echo '<li>Configure email settings for user verification</li>';
        echo '</ul>';
    } else {
        echo '<p class="warning"><strong>⚠️ Some tests failed. Please review the issues above before using the system.</strong></p>';
    }
    echo '</div>';
    ?>
    
    <div class="test-section">
        <h2>🔗 Quick Links</h2>
        <ul>
            <li><a href="../public/index.php">Main Application</a></li>
            <li><a href="../resources/views/login.php">Login Page</a></li>
            <li><a href="../resources/views/register.php">Registration Page</a></li>
            <li><a href="setup.php">Database Setup</a></li>
        </ul>
    </div>
    
</body>
</html>