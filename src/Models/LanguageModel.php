<?php

declare(strict_types=1);

namespace KidneyTales\Models;

use KidneyTales\Controllers\LanguageController;

/**
 * LanguageModel for Kidney Tales - multilingual web application.
 *
 * Handles language detection, translation loading, and language metadata.
 *
 * @package KidneyTales
 * @author Ľubomír Polaščín
 * @version 2025.08.01.01
 */

// File: src/models/LanguageModel.php

class LanguageModel
{
  /**
   * Holds merged translations for the current language.
   * @var array<string, string>
   */
  public static $t = [];

  /**
   * Path to the directory containing language files.
   * @var string
   */
  public static $languageFilesPath = LANGUAGES_PATH;

  /**
   * File extension for language files.
   * @var string
   */
  public static $languageFileExtension = '.php';

  /**
   * Default language code (e.g., 'en').
   * @var string
   */
  public static $defaultLanguage = DEFAULT_LANGUAGE;

  /**
   * Current language code in use.
   * @var string|null
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
        // Set and get locale after loading translations
        $locale = self::getCurrentLocale();
        self::setCurrentLocale($locale);
        return;
      }
    }
    // Fallback: empty translations
    self::$t = [];
    // Set and get locale even if translations are empty
    $locale = self::getCurrentLocale();
    self::setCurrentLocale($locale);
  }

  /**
   * Check if a language code is supported.
   * @param string $lang Language code to check
   * @return bool True if supported, false otherwise
   */
  public static function isSupported(string $lang): bool
  {
    $supported = self::getSupportedLanguageCodes();
    return in_array($lang, $supported, true);
  }

  /**
   * Get the English name for a given language code from languages.php.
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
   * Get the native name for a given language code from languages.php.
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
   * Get the path to the flag image for a given language code, using the country code and .webp extension.
   * If the flag is missing, falls back to the "un" (United Nations) flag.
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string Path to the flag image, or fallback if not found
   */
  public static function getFlagPath(string $lang): ?string
  {
    $unFlag = 'assets' . DS . 'flags' . DS . 'un.webp';
    $countriesFile = APP_ROOT . DS . 'resources' . DS . 'countries.php';
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
    if (file_exists($countriesFile)) {
      $data = include $countriesFile;
      if (is_array($data) && isset($data[$lang])) {
        // Use country code from countries.php
        $country = strtolower(trim($data[$lang]));
        $flag = 'assets' . DS . 'flags' . DS . $country . '.webp';
        if (!file_exists($flag)) {
          // Fallback: try country code from languages.php
          if (file_exists($languagesFile)) {
            $data = include $languagesFile;
            if (is_array($data) && isset($data[$lang][2])) {
              $country = strtolower(trim($data[$lang][2]));
              $flag = 'assets' . DS . 'flags' . DS . $country . '.webp';
              if (file_exists($flag)) {
                return $flag;
              } else {
                // Fallback to UN flag if not found
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

  public static function getCurrentLanguageCode(): string
  {
    return self::$currentLanguageCode = LanguageController::detectCurrentLanguage();
  }

  /**
   * Get the country code for a given language code, using the country code from countries.php.
   * If the country code is missing, falls back to the language code itself.
   * @param string $lang Language code (e.g., 'en', 'sk')
   * @return string|null Country code if found, or language code if not
   */
  public static function getCurrentCountryCode(string $lang): ?string
  {
    $countriesFile = APP_ROOT . DS . 'resources' . DS . 'countries.php';
    $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
    if (file_exists($countriesFile)) {
      $data = include $countriesFile;
      if (is_array($data) && isset($data[$lang])) {
        // Use country code from countries.php
        $country = strtolower(trim($data[$lang]));
        $flag = 'assets' . DS . 'flags' . DS . $country . '.webp';
        if (!file_exists($flag)) {
          // Fallback: try country code from languages.php
          if (file_exists($languagesFile)) {
            $data = include $languagesFile;
            if (is_array($data) && isset($data[$lang][2])) {
              $country = strtolower(trim($data[$lang][2]));
              $flag = 'assets' . DS . 'flags' . DS . $country . '.webp';
              if (file_exists($flag)) {
                return $country;
              } else {
                // Fallback to UN flag if not found
                return $lang;
              }
            } else {
              return $lang;
            }
          } else {
            return $lang;
          }
        } else {
          return $country;
        }
      } else {
        return $lang;
      }
    } else {
      return $lang;
    }
  }

  /**
   * Returns all supported languages and their supported countries.
   * Uses data from language.php and countries.php.
   */
  public static function getSupportedLanguages()
  {
    // Adjust these paths as needed for your project structure
    $languagesFile = __DIR__ . '/../data/language.php';
    $countriesFile = __DIR__ . '/../data/countries.php';

    // language.php should return array like ['en' => ['name' => 'English', 'native' => 'English', 'countries' => ['US', 'GB', ...]], ...]
    // countries.php should return array like ['US' => 'United States', 'GB' => 'United Kingdom', ...]
    $languages = file_exists($languagesFile) ? include $languagesFile : [];
    $countries = file_exists($countriesFile) ? include $countriesFile : [];

    $supported = [];
    foreach ($languages as $langCode => $langData) {
      // Only include countries that exist in countries.php
      $validCountries = [];
      if (isset($langData['countries']) && is_array($langData['countries'])) {
        foreach ($langData['countries'] as $countryCode) {
          if (isset($countries[$countryCode])) {
            $validCountries[] = $countryCode;
          }
        }
      }
      if (!empty($validCountries)) {
        $supported[$langCode] = [
          'name' => $langData['name'] ?? $langCode,
          'native' => $langData['native'] ?? $langData['name'] ?? $langCode,
          'countries' => $validCountries,
        ];
      }
    }
    return $supported;
  }

    /**
     * Get the current locale string (e.g., 'en_US') from current language and country code.
     * @return string
     */
    public static function getCurrentLocale(): string
    {
        $lang = self::getCurrentLanguageCode();
        $country = self::getCurrentCountryCode($lang);
        return strtolower($lang) . '_' . strtoupper($country);
    }

    /**
     * Set the current language and country code from a locale string (e.g., 'en_US').
     * @param string $locale
     * @return void
     */
    public static function setCurrentLocale(string $locale): void
    {
        $parts = preg_split('/[_\-]/', $locale);
        if (count($parts) === 2) {
            self::$currentLanguageCode = strtolower($parts[0]);
            $_SESSION['country_code'] = strtoupper($parts[1]);
            $localeString = strtolower($parts[0]) . '_' . strtoupper($parts[1]) . '.UTF-8';
            setlocale(LC_ALL, $localeString);
        } elseif (count($parts) === 1) {
            self::$currentLanguageCode = strtolower($parts[0]);
            setlocale(LC_ALL, strtolower($parts[0]) . '.UTF-8');
        }
        // Optionally, reload translations or perform other updates here
    }
}
