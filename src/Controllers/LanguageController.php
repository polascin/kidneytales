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
  public static function setCurrentLanguage(string $langCode): void
  {
    // Validate the language code, fallback to default if unsupported
    $lang = LanguageModel::isSupported($langCode) ? $langCode : (defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en');
    // Start a secure session using SessionManager
    SessionManager::StartSession();
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
    LanguageModel::loadLanguageTranslations($langCode);
  }

    /**
     * Detect the current language code using URL, POST, session, cookie, browser, or geo headers.
     * @return string The detected language code
     */
    public static function detectCurrentLanguage(): string
    {
        // Ensure session is started before accessing $_SESSION
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sanitize = function ($code) {
            $code = strtolower(trim($code));
            return preg_match('/^[a-z0-9_-]+$/i', $code) ? $code : '';
        };

        // 1. URL param (?lang=)
        if (isset($_GET['lang'])) {
            $candidate = $sanitize($_GET['lang']);
            if (LanguageModel::isSupported($candidate)) {
                LanguageModel::$currentLanguageCode = $candidate;
                return LanguageModel::$currentLanguageCode;
            }
        }
        // 2. POST param
        elseif (
            isset($_POST['lang'])
            && isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            && (time() - $_SESSION['csrf_token_time'] <= 3600)
        ) {
            $candidate = $sanitize($_POST['lang']);
            if (LanguageModel::isSupported($candidate)) {
                LanguageModel::$currentLanguageCode = $candidate;
                return LanguageModel::$currentLanguageCode;
            }
        }
        // 3. Session
        elseif (isset($_SESSION['lang'])) {
            $candidate = $sanitize($_SESSION['lang']);
            if (LanguageModel::isSupported($candidate)) {
                LanguageModel::$currentLanguageCode = $candidate;
                return LanguageModel::$currentLanguageCode;
            }
        }
        // 4. Cookie
        elseif (isset($_COOKIE['lang'])) {
            $candidate = $sanitize($_COOKIE['lang']);
            if (LanguageModel::isSupported($candidate)) {
                LanguageModel::$currentLanguageCode = $candidate;
                return LanguageModel::$currentLanguageCode;
            }
        }
        // 5. Browser Accept-Language
        elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($langs as $lang) {
                $lang = explode(';', $lang)[0];
                $candidate = $sanitize($lang);
                if (LanguageModel::isSupported($candidate)) {
                    LanguageModel::$currentLanguageCode = $candidate;
                    return LanguageModel::$currentLanguageCode;
                }
                // Try just the primary subtag (e.g., 'en' from 'en-US')
                if (strpos($candidate, '-') !== false) {
                    $primary = explode('-', $candidate)[0];
                    if (LanguageModel::isSupported($primary)) {
                        LanguageModel::$currentLanguageCode = $primary;
                        return LanguageModel::$currentLanguageCode;
                    }
                }
            }
        }
        // 6. GeoIP-based detection (optional, basic implementation)
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            // Example: Use a simple GeoIP lookup (requires a GeoIP library or service)
            // Here, we use a free API for demonstration. In production, use a local DB or a more robust service.
            $ip = $_SERVER['REMOTE_ADDR'];
            $geoLang = null;
            try {
                // This is a simple, rate-limited, privacy-weak example. Replace with your own service as needed.
                $geoData = @file_get_contents("https://ipapi.co/{$ip}/json/");
                if ($geoData) {
                    $geoJson = json_decode($geoData, true);
                    if (!empty($geoJson['country_code'])) {
                        // Map country code to language code (customize as needed)
                        $countryToLang = [
                            'SK' => 'sk', 'CZ' => 'cs', 'DE' => 'de', 'FR' => 'fr', 'ES' => 'es', 'IT' => 'it',
                            'PL' => 'pl', 'UA' => 'uk', 'RU' => 'ru', 'GB' => 'en', 'US' => 'en', 'CA' => 'en',
                            'CN' => 'zh', 'JP' => 'ja', 'KR' => 'ko', 'TR' => 'tr', 'RO' => 'ro', 'HU' => 'hu',
                            // ...add more as needed
                        ];
                        $cc = strtoupper($geoJson['country_code']);
                        if (isset($countryToLang[$cc])) {
                            $geoLang = $countryToLang[$cc];
                            if (LanguageModel::isSupported($geoLang)) {
                                LanguageModel::$currentLanguageCode = $geoLang;
                                return LanguageModel::$currentLanguageCode;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore GeoIP errors and continue to fallback
            }
        }
        // 7. Fallback to default
        LanguageModel::$currentLanguageCode = $sanitize(LanguageModel::$defaultLanguage);
        return LanguageModel::$currentLanguageCode;
    }
}
