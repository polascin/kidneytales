<?php

declare(strict_types=1);

namespace KidneyTales\Controllers;

class SessionManager
{

  /**
   * Starts a secure session and manages CSRF token lifecycle.
   *
   * - Sets a custom session name for the application.
   * - Configures secure session cookie parameters (secure, httponly, samesite).
   * - Starts the session if not already active.
   * - Regenerates the session ID only on new sessions for security.
   * - Generates a CSRF token and timestamp if missing or expired (1 hour).
   * - Uses cryptographically secure random_bytes for CSRF token, with fallback.
   *
   * @return void
   */
  public static function StartSession(): void
  {
    // Only set session parameters if session is not active
    if (session_status() === PHP_SESSION_NONE) {
      session_name('KIDNEYTALESSESSID');
      ini_set('session.use_strict_mode', '1');
      ini_set('session.cookie_httponly', '1');
      ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
      ini_set('session.cookie_samesite', 'Lax');
      // Optionally, set session.cookie_lifetime and session.gc_maxlifetime for custom session duration

      $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
      session_set_cookie_params([
        'lifetime' => 0, // Session cookie (until browser closes)
        'path' => '/',
        'domain' => '',
        'secure' => $secure, // Only send cookie over HTTPS
        'httponly' => true,  // Prevent JavaScript access
        'samesite' => 'Lax', // Mitigate CSRF
      ]);
      session_start();
      $isNewSession = true;
    } else {
      // Session already active, do not change parameters, just continue
      $isNewSession = false;
    }
    // Regenerate session ID only if this is a new session (prevents fixation)
    if (isset($isNewSession) && $isNewSession) {
      session_regenerate_id(true);
    }
    // Bind session to user agent and IP address to prevent hijacking
    if (!isset($_SESSION['user_agent'])) {
      $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
      session_unset();
      session_destroy();
      session_start();
      $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $isNewSession = true;
    }
    if (!isset($_SESSION['ip_address'])) {
      $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    } elseif ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
      session_unset();
      session_destroy();
      session_start();
      $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
      $isNewSession = true;
    }
    // Set or refresh CSRF token and timestamp if missing or expired (1 hour)
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time'] > 3600)) {
      try {
        // Use cryptographically secure random_bytes for CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      } catch (\Exception $e) {
        // Fallback: use less secure uniqid if random_bytes fails
        $_SESSION['csrf_token'] = bin2hex(uniqid((string)mt_rand(), true));
      }
      $_SESSION['csrf_token_time'] = time();
      // Bind CSRF token to user agent and IP for extra security
      $_SESSION['csrf_token_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $_SESSION['csrf_token_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    }
  }

  /**
   * Validates the CSRF token for POST requests.
   *
   * - Checks that the request method is POST.
   * - Ensures CSRF token and timestamp are present in both POST and session.
   * - Uses hash_equals to prevent timing attacks.
   * - Token is valid for 1 hour.
   *
   * @return bool True if CSRF token is valid, false otherwise.
   */
  public static function isValidCsrfToken(): bool
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $valid = isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        && (time() - $_SESSION['csrf_token_time'] <= 3600);
      // Check user agent and IP binding for CSRF token
      $uaMatch = !isset($_SESSION['csrf_token_user_agent']) || ($_SESSION['csrf_token_user_agent'] === ($_SERVER['HTTP_USER_AGENT'] ?? ''));
      $ipMatch = !isset($_SESSION['csrf_token_ip']) || ($_SESSION['csrf_token_ip'] === ($_SERVER['REMOTE_ADDR'] ?? ''));
      return $valid && $uaMatch && $ipMatch;
    }
    return false;
  }
}
