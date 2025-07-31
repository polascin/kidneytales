<?php

declare(strict_types=1);

namespace KidneyTales\Models;

/**
 * LanguageModel for Kidney Tales - multilingual web application
 *
 * @package KidneyTales
 * @author Ľubomír Polaščín
 * @version 2025.08.01.01
 */

// File: src/models/LanguageModel.php

class LanguageModel
{

  /**
   * @var array<string, string> Holds merged translations for the current language
   */
  public static $t = [];

  /**
   * @var string Path to the directory containing language files
   */
  public static $languageFilesPath = LANGUAGES_PATH;

  /**
   * @var string File extension for language files
   */
  public static $languageFileExtension = '.php';

  /**
   * @var string Default language code (e.g., 'en')
   */
  public static $defaultLanguage = DEFAULT_LANGUAGE;

  /**
   * @var string|null Current language code in use
   */
  public static $currentLanguageCode;


  /**
   * Get all supported language codes from the languages.php file.
   * @return array List of supported language codes (e.g., ['en', 'sk'])
   */
  public static function getSupportedLanguageCodes(): array
  {
    static $codes = null;
    if ($codes !== null) {
      return $codes;
    }
    $languagesFile = APP_ROOT . DS . 'src' . DS . 'Models' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data)) {
        $codes = array_keys($data);
        return $codes;
      }
    }
    // Fallback: just return default language
    $codes = [self::$defaultLanguage];
    return $codes;
  }

  /**
   * Load translations for the given language code into the static $t property.
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return void
   */
  public static function loadLanguageTranslations(string $lang): void
  {
    $lang = strtolower(trim($lang));
    $file = APP_ROOT . DS . 'languages' . DS . $lang . self::$languageFileExtension;
    if (file_exists($file)) {
      $translations = include $file;
      if (is_array($translations)) {
        self::$t = $translations;
        return;
      }
    }
    // Fallback: empty translations
    self::$t = [];
  }

  /**
   * Detect the current language code using URL, POST, session, cookie, browser, or geo headers.
   * @return string The detected language code
   */
  public static function detectCurrentLanguage(): string
  {
    // 1. URL param (?lang=)
    if (isset($_GET['lang'])) {
      self::$currentLanguageCode = htmlspecialchars(trim($_GET['lang']));
      if (!self::isSupported(self::$currentLanguageCode)) {
        self::$currentLanguageCode = self::$defaultLanguage;
      }
    }
    // 2. POST param
    if (
      isset($_POST['lang'])
      && isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])
      && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
      && (time() - $_SESSION['csrf_token_time'] <= 3600)
    ) {
      self::$currentLanguageCode = htmlspecialchars(trim($_POST['lang']));
      if (!self::isSupported(self::$currentLanguageCode)) {
        self::$currentLanguageCode = self::$defaultLanguage;
      }
    }
    // 3. Session
    if (isset($_SESSION['lang'])) {
      self::$currentLanguageCode = htmlspecialchars(trim($_SESSION['lang']));
      if (!self::isSupported(self::$currentLanguageCode)) {
        self::$currentLanguageCode = self::$defaultLanguage;
      }
    }
    // 4. Cookie
    if (isset($_COOKIE['lang'])) {
      self::$currentLanguageCode = htmlspecialchars(trim($_COOKIE['lang']));
      if (!self::isSupported(self::$currentLanguageCode)) {
        self::$currentLanguageCode = self::$defaultLanguage;
      }
    }
    // 5. Browser Accept-Language
    if (empty(self::$currentLanguageCode) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $acceptLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      foreach ($acceptLangs as $lang) {
        $langCode = strtolower(substr(trim($lang), 0, 5));
        if (self::isSupported($langCode)) {
          self::$currentLanguageCode = $langCode;
          break;
        }
        // Try primary subtag only (e.g., 'en' from 'en-US')
        $primary = strtolower(substr($langCode, 0, 2));
        if (self::isSupported($primary)) {
          self::$currentLanguageCode = $primary;
          break;
        }
      }
    }
    // 6. Geo language code (custom header or env)
    if (empty(self::$currentLanguageCode)) {
      $geoLang = $_SERVER['HTTP_GEO_LANG'] ?? ($_SERVER['GEO_LANG'] ?? null);
      if ($geoLang && self::isSupported($geoLang)) {
        self::$currentLanguageCode = $geoLang;
      }
    }
    // 7. Fallback to default
    if (empty(self::$currentLanguageCode)) {
      self::$currentLanguageCode = self::$defaultLanguage;
    }
    return htmlspecialchars(trim(self::$currentLanguageCode));
  }

  /**
   * Check if a language code is supported
   * @param string $lang Language code to check
   */
  public static function isSupported(string $lang): bool
  {
    $supported = self::getSupportedLanguageCodes();
    return in_array($lang, $supported, true);
  }

  /**
   * Get the English name for a given language code from languages.php
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string|null English name if found, null otherwise
   */
  public static function getLanguageEnglishName(string $lang): ?string
  {
    $languagesFile = APP_ROOT . DS . 'src' . DS . 'Models' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data) && isset($data[$lang][0])) {
        return htmlspecialchars(trim($data[$lang][0]));
      }
    }
    return null;
  }

  /**
   * Get the native name for a given language code from languages.php
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string|null Native name if found, null otherwise
   */
  public static function getLanguageNativeName(string $lang): ?string
  {
    $languagesFile = APP_ROOT . DS . 'src' . DS . 'Models' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data) && isset($data[$lang][1])) {
        return htmlspecialchars(trim($data[$lang][1]));
      }
    }
    return null;
  }

  /**
   * Get simple language code languages.php
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string|null Simple language code if found, null otherwise
   */
  public static function getLanguage(string $lang): ?string
  {
    $languagesFile = APP_ROOT . DS . 'src' . DS . 'Models' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data) && isset($data[$lang][2])) {
        return htmlspecialchars(trim($data[$lang][2]));
      }
    }
    return null;
  }

  /**
   * Get the path to the flag image for a given language code, using the country name and .webp extension.
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string|null Path to the flag image if country is found, null otherwise
   */
  public static function getFlagPath(string $lang): ?string
  {
    $languagesFile = APP_ROOT . DS . 'src' . DS . 'Models' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data) && isset($data[$lang]['country'])) {
        $country = $data[$lang]['country'];
        // Sanitize country name for file path (replace spaces with underscores, lowercase)
        $fileName = strtolower(str_replace(' ', '_', $country)) . '.webp';
        // Example: /public/assets/flags/united_kingdom.webp
        return 'assets' . DS . 'flags' . DS . $fileName;
      }
    }
    return null;
  }

  /**
   * Set the current language code in session and cookie
   * @param string $lang Language code to set
   * @return void
   */
  public static function setCurrentLanguage(string $lang): void
  {
    if (!self::isSupported($lang)) {
      $lang = self::$defaultLanguage;
    }
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, [
      'expires' => time() + 60 * 60 * 24 * 30, // 30 days
      'path' => '/',
      'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
  }
}
