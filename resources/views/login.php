<?php
// Login View for Kidney Tales
// File: resources/views/login.php

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

$pageTitle = $t['login'] ?? 'Login';
$pageDescription = $t['login_description'] ?? 'Login to your Kidney Tales account';
$current_language = $currentLanguageCode;

require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'html.php';
require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'head.php';
require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'header.php';
?>

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
        value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="password"><?= htmlspecialchars($t['password'] ?? 'Password') ?></label>
      <input
        type="password"
        id="password"
        name="password"
        required
        autocomplete="current-password">
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

<?php
require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'flags-selector.php';
require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'footer.php';
