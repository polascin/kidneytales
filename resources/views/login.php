<?php
// Login View for Kidney Tales
// File: resources/views/login.php

declare(strict_types=1);

$pageTitle = $t['login'] ?? 'Login';
$pageDescription = $t['login_description'] ?? 'Login to your Kidney Tales account';
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
    
    <style>
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
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
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0,124,186,0.2);
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
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
    </style>
</head>
<body>
    <!-- Language Selector -->
    <div class="language-selector">
        <?php include APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'language-selector.php'; ?>
    </div>

    <div class="login-container">
        <div class="header">
            <h1><?= htmlspecialchars(APP_NAME) ?></h1>
            <h2><?= htmlspecialchars($t['login'] ?? 'Login') ?></h2>
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

        <!-- Login Form -->
        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="identifier"><?= htmlspecialchars($t['username_or_email'] ?? 'Username or Email') ?></label>
                <input 
                    type="text" 
                    id="identifier" 
                    name="identifier" 
                    required 
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password"><?= htmlspecialchars($t['password'] ?? 'Password') ?></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <div class="form-group">
                <button type="submit" class="btn">
                    <?= htmlspecialchars($t['login'] ?? 'Login') ?>
                </button>
            </div>
        </form>

        <!-- Auth Links -->
        <div class="auth-links">
            <p>
                <?= htmlspecialchars($t['dont_have_account'] ?? "Don't have an account?") ?>
                <a href="/register"><?= htmlspecialchars($t['register'] ?? 'Register') ?></a>
            </p>
            <p>
                <a href="/forgot-password"><?= htmlspecialchars($t['forgot_password'] ?? 'Forgot Password?') ?></a>
            </p>
            <p>
                <a href="/"><?= htmlspecialchars($t['back_to_home'] ?? 'Back to Home') ?></a>
            </p>
        </div>
    </div>

    <script>
        // Focus on first input
        document.getElementById('identifier').focus();
        
        // Clear password field on page load for security
        document.getElementById('password').value = '';
    </script>
</body>
</html>