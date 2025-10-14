<?php

declare(strict_types=1);

/**
 * Demo page for User Information Header Component
 * This page demonstrates the user information display in different states
 */

define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

require_once APP_ROOT . DS . 'bootstrap.php';

use KidneyTales\Controllers\SessionManager;
use KidneyTales\Controllers\LanguageController;
use KidneyTales\Models\LanguageModel;

// Initialize session
SessionManager::StartSession();

// Load language
try {
    $currentLanguage = LanguageController::detectCurrentLanguage();
    LanguageController::setCurrentLanguage($currentLanguage);
} catch (Throwable $e) {
    LanguageController::setCurrentLanguage(DEFAULT_LANGUAGE);
}

$currentLanguageCode = LanguageModel::getCurrentLanguageCode();
$t = LanguageModel::$t;

// Demo: Simulate different user states
$demoMode = $_GET['demo'] ?? 'guest';

switch ($demoMode) {
    case 'admin':
        // Simulate admin user
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role_id'] = 5;
        $_SESSION['login_time'] = time();
        break;
        
    case 'creator':
        // Simulate content creator
        $_SESSION['user_id'] = 2;
        $_SESSION['username'] = 'creator';
        $_SESSION['role_id'] = 3;
        $_SESSION['login_time'] = time();
        break;
        
    case 'translator':
        // Simulate translator
        $_SESSION['user_id'] = 3;
        $_SESSION['username'] = 'translator';
        $_SESSION['role_id'] = 2;
        $_SESSION['login_time'] = time();
        break;
        
    case 'logout':
        // Clear session to show guest state
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role_id']);
        unset($_SESSION['login_time']);
        break;
        
    default:
        // Guest mode - no session data
        break;
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLanguageCode) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information Demo - <?= htmlspecialchars(APP_NAME) ?></title>
    
    <!-- Include existing styles -->
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/colors.css">
    <link rel="stylesheet" href="/assets/css/font-families.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/language.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .demo-controls {
            background: #fff;
            padding: 1rem;
            margin: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .demo-controls h2 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        
        .demo-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .demo-btn {
            padding: 0.5rem 1rem;
            background: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .demo-btn:hover {
            background: #005a87;
        }
        
        .demo-btn.active {
            background: #198754;
        }
        
        .demo-btn.logout {
            background: #dc3545;
        }
        
        .demo-btn.logout:hover {
            background: #c82333;
        }
        
        .demo-content {
            background: #fff;
            margin: 1rem;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .demo-note {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            padding: 1rem;
            margin: 1rem;
            border-radius: 8px;
            color: #004085;
        }
        
        /* Make header visible in demo */
        .header-container {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1rem;
        }
        
        .main-header-content-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-left {
            flex: 1;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
        }
        
        .header-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1rem;
        }
        
        .flag {
            width: 32px;
            height: auto;
        }
        
        .logoimg {
            width: 64px;
            height: auto;
            float: left;
            margin-right: 1rem;
        }
        
        .description {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="demo-controls">
        <h2>User Information Demo</h2>
        <p>Use the buttons below to simulate different user states and see how the user information displays in the header:</p>
        <div class="demo-buttons">
            <a href="?demo=guest" class="demo-btn <?= $demoMode === 'guest' ? 'active' : '' ?>">Guest User</a>
            <a href="?demo=translator" class="demo-btn <?= $demoMode === 'translator' ? 'active' : '' ?>">Translator</a>
            <a href="?demo=creator" class="demo-btn <?= $demoMode === 'creator' ? 'active' : '' ?>">Creator</a>
            <a href="?demo=admin" class="demo-btn <?= $demoMode === 'admin' ? 'active' : '' ?>">Administrator</a>
            <a href="?demo=logout" class="demo-btn logout">Logout</a>
        </div>
    </div>

    <!-- Include the header component with user information -->
    <?php require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'header.php'; ?>

    <div class="demo-content">
        <h1>User Information Header Demo</h1>
        <p>Look at the top-right corner of the header to see the user information display.</p>
        
        <h3>Current Demo Mode: <strong><?= htmlspecialchars(ucfirst($demoMode)) ?></strong></h3>
        
        <?php if ($demoMode !== 'guest'): ?>
            <p>✅ User is logged in as: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Unknown') ?></strong></p>
            <p>🎭 Role ID: <strong><?= htmlspecialchars($_SESSION['role_id'] ?? 'None') ?></strong></p>
        <?php else: ?>
            <p>👤 Guest user (not logged in)</p>
        <?php endif; ?>
        
        <h3>Features Demonstrated:</h3>
        <ul style="text-align: left; display: inline-block;">
            <li><strong>Guest State:</strong> Shows login/register buttons</li>
            <li><strong>Logged-in State:</strong> Shows user avatar, name, role, and quick actions</li>
            <li><strong>Role-based Display:</strong> Different roles show different colors and quick actions</li>
            <li><strong>Quick Actions:</strong> Role-based quick access buttons (dashboard, profile, create, admin, etc.)</li>
            <li><strong>Responsive Design:</strong> Adapts to different screen sizes</li>
            <li><strong>Multilingual Support:</strong> All text respects current language settings</li>
        </ul>
    </div>

    <div class="demo-note">
        <strong>Note:</strong> This is a demonstration page. In a real application, user authentication would be handled through proper login forms and database verification. The user information component integrates seamlessly with the existing Kidney Tales authentication system.
    </div>

    <script>
        // Auto-refresh demo every 30 seconds to show time updates
        setTimeout(function() {
            if (!window.location.search.includes('norefresh')) {
                window.location.reload();
            }
        }, 30000);
    </script>
</body>
</html>