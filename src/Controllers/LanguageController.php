<?php

declare(strict_types=1);

namespace KidneyTales\Controllers;

use KidneyTales\Models\LanguageModel;
use KidneyTales\Controllers\SessionManager;

/**
 * Controller for managing user language preference.
 *
 * Handles setting the language in both session and cookie securely.
 * Uses SessionManager for robust session security.
 */
class LanguageController
{
  /**
   * Sets the current language code in session and cookie securely.
   *
   * - Validates the language code against supported languages.
   * - Uses SessionManager to ensure a secure session is started.
   * - Stores the language in session and a secure, HTTP-only cookie.
   *
   * @param string $langCode Language code to set (e.g. 'en', 'fr', etc.)
   * @return void
   */
  public function setCurrentLanguage(string $langCode): void
  {
    // Validate the language code, fallback to default if unsupported
    $lang = LanguageModel::isSupported($langCode) ? $langCode : (defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en');

    // Start a secure session using SessionManager
    $sessionManager = new SessionManager();
    $sessionManager->StartSession();

    // Store the language in the session
    $_SESSION['lang'] = $lang;

    // Set a secure, HTTP-only, SameSite cookie for language preference
    setcookie('lang', $lang, [
      'expires' => time() + 60 * 60 * 24 * 30, // 30 days
      'path' => '/',
      'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
  }
}
