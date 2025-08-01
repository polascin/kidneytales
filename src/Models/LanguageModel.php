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
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
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
      if (self::isSupported($candidate)) {
        self::$currentLanguageCode = $candidate;
        return self::$currentLanguageCode;
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
      if (self::isSupported($candidate)) {
        self::$currentLanguageCode = $candidate;
        return self::$currentLanguageCode;
      }
    }
    // 3. Session
    elseif (isset($_SESSION['lang'])) {
      $candidate = $sanitize($_SESSION['lang']);
      if (self::isSupported($candidate)) {
        self::$currentLanguageCode = $candidate;
        return self::$currentLanguageCode;
      }
    }
    // 4. Cookie
    elseif (isset($_COOKIE['lang'])) {
      $candidate = $sanitize($_COOKIE['lang']);
      if (self::isSupported($candidate)) {
        self::$currentLanguageCode = $candidate;
        return self::$currentLanguageCode;
      }
    }
    // 5. Browser Accept-Language
    elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $acceptLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      foreach ($acceptLangs as $lang) {
        $langCode = $sanitize(substr($lang, 0, 5));
        if (self::isSupported($langCode)) {
          self::$currentLanguageCode = $langCode;
          return self::$currentLanguageCode;
        }
        // Try primary subtag only (e.g., 'en' from 'en-US')
        $primary = $sanitize(substr($langCode, 0, 2));
        if (self::isSupported($primary)) {
          self::$currentLanguageCode = $primary;
          return self::$currentLanguageCode;
        }
      }
    }
    // 6. Geo language code (custom header or env)
    elseif (isset($_SERVER['HTTP_GEO_LANG']) || isset($_SERVER['GEO_LANG'])) {
      $geoLang = $_SERVER['HTTP_GEO_LANG'] ?? $_SERVER['GEO_LANG'] ?? null;
      $candidate = $sanitize($geoLang);
      if ($candidate && self::isSupported($candidate)) {
        self::$currentLanguageCode = $candidate;
        return self::$currentLanguageCode;
      }
    }
    // 7. Fallback to default
    self::$currentLanguageCode = $sanitize(self::$defaultLanguage);
    return self::$currentLanguageCode;
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
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
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
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
    if (file_exists($languagesFile)) {
      $data = include $languagesFile;
      if (is_array($data) && isset($data[$lang][1])) {
        return htmlspecialchars(trim($data[$lang][1]));
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
    $unFlag = APP_ROOT . DS . 'resources' . DS . 'flags' . DS . 'un.webp';
    $countriesFile = APP_ROOT . DS . 'resources' . DS . 'countries.php';
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
    if (file_exists($countriesFile)) {
      $data = include $countriesFile;
      if (is_array($data) && isset($data[$lang])) {
        $country = strtolower(trim($data[$lang][1]));
        $flag = APP_ROOT . DS . 'resources' . DS . 'flags' . DS . $country . '.webp';
        if (!$flag) {
          if (file_exists($languagesFile)) {
            $data = include $languagesFile;
            if (is_array($data) && isset($data[$lang][2])) {
              $country = strtolower(trim($data[$lang][2]));
              $flag = APP_ROOT . DS . 'resources' . DS . 'flags' . DS . $country . '.webp';
              if ($flag) {
                return $flag;
              } else {
                return $unFlag;
              }
            } else {
              return $unFlag;
            }
          } else {
            return $unFlag;
          }
        } else {
          return $flag;
        }
      } else {
        return $unFlag;
      }
    } else {
      return $unFlag;
    }
  }
}
