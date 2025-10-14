<?php
// Registration View for Kidney Tales
// File: resources/views/register.php

declare(strict_types=1);

// Include bootstrap to load language and session
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use KidneyTales\Controllers\SessionManager;
use KidneyTales\Models\LanguageModel;

// Start session if not already started
SessionManager::StartSession();

// Load current language
$currentLanguageCode = LanguageModel::getCurrentLanguageCode();
$t = LanguageModel::$t;

// Load available languages for dropdown
$languages = require APP_ROOT . DS . 'resources' . DS . 'languages.php';

// Define password requirements
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}

// Get CSRF token from session
$csrf_token = $_SESSION['csrf_token'] ?? '';

// Check if user is already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

// Initialize flash messages array
$flash_messages = $_SESSION['flash_messages'] ?? [];
unset($_SESSION['flash_messages']);

$pageTitle = $t['register'] ?? 'Register';
$pageDescription = $t['register_description'] ?? 'Create your Kidney Tales account';
$current_language = $currentLanguageCode;
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars(APP_NAME) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/colors.css">
    <link rel="stylesheet" href="/assets/css/font-families.css">
    <link rel="stylesheet" href="/assets/css/language.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    
    <style>
        .register-container {
            max-width: 500px;
            margin: 1rem auto 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0,124,186,0.2);
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            background: #007cba;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .flash-messages {
            margin-bottom: 1rem;
        }
        
        .flash-message {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .flash-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .flash-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .auth-links {
            text-align: center;
            margin-top: 1rem;
        }
        
        .auth-links a {
            color: #007cba;
            text-decoration: none;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .language-selector {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
        
        .password-requirements {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        .form-help {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Header Container -->
    <?php include APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'header.php'; ?>

    <!-- Language Selector -->
    <div class="language-selector">
        <?php include APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'language-selector.php'; ?>
    </div>

    <div class="register-container">
        <div class="header">
            <h1><?= htmlspecialchars(APP_NAME) ?></h1>
            <h2><?= htmlspecialchars($t['create_account'] ?? 'Create Account') ?></h2>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flash_messages)): ?>
            <div class="flash-messages">
                <?php foreach ($flash_messages as $message): ?>
                    <div class="flash-message <?= htmlspecialchars($message['type']) ?>">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="/register" id="register-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <!-- Name Fields -->
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name"><?= htmlspecialchars($t['first_name'] ?? 'First Name') ?> *</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        required 
                        autocomplete="given-name"
                        value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                    >
                </div>
                <div class="form-group">
                    <label for="last_name"><?= htmlspecialchars($t['last_name'] ?? 'Last Name') ?> *</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        required 
                        autocomplete="family-name"
                        value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                    >
                </div>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username"><?= htmlspecialchars($t['username'] ?? 'Username') ?> *</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autocomplete="username"
                    pattern="[a-zA-Z0-9_]+"
                    minlength="3"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                >
                <div class="form-help">
                    <?= htmlspecialchars($t['username_help'] ?? 'Letters, numbers, and underscores only. Minimum 3 characters.') ?>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email"><?= htmlspecialchars($t['email'] ?? 'Email') ?> *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <!-- Preferred Language -->
            <div class="form-group">
                <label for="preferred_language"><?= htmlspecialchars($t['preferred_language'] ?? 'Preferred Language') ?></label>
                <select id="preferred_language" name="preferred_language">
                    <?php 
                    $selectedLang = $_POST['preferred_language'] ?? $current_language ?? DEFAULT_LANGUAGE;
                    if (!empty($languages)):
                        foreach ($languages as $code => $language): 
                    ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $code === $selectedLang ? 'selected' : '' ?>>
                            <?= htmlspecialchars($language[1]) ?> (<?= htmlspecialchars($language[0]) ?>)
                        </option>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </select>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password"><?= htmlspecialchars($t['password'] ?? 'Password') ?> *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                >
                <div class="password-requirements">
                    <?= htmlspecialchars($t['password_requirements'] ?? 'Minimum ' . PASSWORD_MIN_LENGTH . ' characters') ?>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password"><?= htmlspecialchars($t['confirm_password'] ?? 'Confirm Password') ?> *</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    autocomplete="new-password"
                >
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn">
                    <?= htmlspecialchars($t['create_account'] ?? 'Create Account') ?>
                </button>
            </div>
        </form>

        <!-- Auth Links -->
        <div class="auth-links">
            <p>
                <?= htmlspecialchars($t['already_have_account'] ?? "Already have an account?") ?>
                <a href="/login"><?= htmlspecialchars($t['login'] ?? 'Login') ?></a>
            </p>
            <p>
                <a href="/"><?= htmlspecialchars($t['back_to_home'] ?? 'Back to Home') ?></a>
            </p>
        </div>
    </div>

    <script>
        // Focus on first input
        document.getElementById('first_name').focus();
        
        // Password confirmation validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        function validatePasswordMatch() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('<?= htmlspecialchars($t['passwords_must_match'] ?? 'Passwords must match') ?>');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        passwordInput.addEventListener('input', validatePasswordMatch);
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        
        // Form submission validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('<?= htmlspecialchars($t['passwords_must_match'] ?? 'Passwords must match') ?>');
                confirmPasswordInput.focus();
            }
        });
    </script>
</body>
</html>