<?php

declare(strict_types=1);

/**
 * Kidney Tales - multilingual web application entry point
 *
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2025.08.1.1
 */

// File: /public/index.php

// --- Secure Session and CSRF Setup ---
// Set a custom session name for extra security
session_name('KIDNEYTALESSESSID');

// Start a secure session if none is started
if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    // Regenerate session ID on new session for security
    session_regenerate_id(true);
}

// Set CSRF token and timestamp for the session, regenerate if missing or expired (1 hour)
if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time'] > 3600)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

/**
 * Validate CSRF token for POST requests
 * @return bool
 */
function isValidCsrfToken(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            && (time() - $_SESSION['csrf_token_time'] <= 3600);
    }
    return true;
}
// Usage: if (!isValidCsrfToken()) { http_response_code(403); exit('Invalid CSRF token.'); }
// --- End Secure Session and CSRF Setup ---

define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

include_once APP_ROOT . DS . 'bootstrap.php';




// --- Language Loading and Error Handling ---
require_once APP_ROOT . DS . 'vendor' . DS . 'autoload.php';
use KidneyTales\Models\LanguageModel;
try {
    $currentLanguageCode = LanguageModel::detectCurrentLanguage();
    LanguageModel::loadLanguageTranslations($currentLanguageCode);
    LanguageModel::setCurrentLanguage($currentLanguageCode);
} catch (Throwable $e) {
    error_log('[index.php] Language loading error: ' . $e->getMessage());
    $currentLanguageCode = 'en';
    $t = [];
}
// --- End Language Loading ---

include_once APP_ROOT . DS . 'src' . DS . 'Views' . DS . 'HomePageView.php';

?>
