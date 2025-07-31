<?php
declare(strict_types=1);
/**
 * LanguageModel for Kidney Tales - multilingual web application
 *
 * @version 2005.08.1.0
 */
class LanguageModel
{
    /**
     * @var array<string, string> Holds translations
     */
    public static $t = [];

    /** @var string|null */
    private $text;
    /** @var string */
    private $languageFilesPath;
    /** @var string */
    private $languageFileExtension = '.php';
    /** @var string|null */
    private $currentLanguageCode;
    /** @var string|null */
    private $currentLanguageName;
    /** @var string|null */
    private $currentLanguageNativeName;
    /** @var string|null */
    private $currentLanguageFile;
    /** @var string */
    private $defaultLanguageCode = 'en';
    /** @var array|null */
    private static $supportedLanguagesCache = null;
    /** @var array|null */
    private static $languageMeta = null;
    /** @var array|null */
    private static $customLanguageMeta = null;

    public function setCurrentLanguage(string $lang, int $cookieExpire = 2592000): bool
    {
        // Stub: always return true for now
        return true;
    }

    public function getSupportedLanguages(): array
    {
        // Stub: return English only for now
        return ['en'];
    }

    private function parseAcceptLanguage(string $header): array
    {
        // Stub: return English only for now
        return ['en'];
    }

    public function loadLanguageMetadata(?array $customMeta = null): void
    {
        // Stub: do nothing for now
    }

    public function loadLanguageTranslation(array $translations = [])
    {
        $this->currentLanguageCode = $this->detectCurrentLanguage();
        $defaultFile = $this->languageFilesPath . $this->defaultLanguageCode . $this->languageFileExtension;
        $defaultTranslations = [];
        if (file_exists($defaultFile)) {
            $defaultTranslations = include $defaultFile;
        }
        $langFile = $this->languageFilesPath . $this->currentLanguageCode . $this->languageFileExtension;
        $langTranslations = [];
        if (file_exists($langFile)) {
            $langTranslations = include $langFile;
        }
        self::$t = array_merge(
            is_array($defaultTranslations) ? $defaultTranslations : [],
            is_array($langTranslations) ? $langTranslations : [],
            $translations
        );
        $this->setCurrentLanguage($this->currentLanguageCode);
    }

    public function detectCurrentLanguage(): string
    {
        $supported = array_flip($this->getSupportedLanguages());
        $localeFallback = function($code) use ($supported) {
            if (isset($supported[$code])) return $code;
            if (strpos($code, '-') !== false) {
                $primary = strtolower(substr($code, 0, 2));
                if (isset($supported[$primary])) return $primary;
            }
            return null;
        };
        if (isset($_GET['lang'])) {
            $lang = $localeFallback($_GET['lang']);
            if ($lang) return $lang;
            error_log("[LanguageModel] Unsupported GET lang: {$_GET['lang']}");
        }
        if (
            isset($_POST['lang'])
            && isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            && (time() - $_SESSION['csrf_token_time'] <= 3600)
        ) {
            $lang = $localeFallback($_POST['lang']);
            if ($lang) return $lang;
            error_log("[LanguageModel] Unsupported POST lang: {$_POST['lang']}");
        }
        if (isset($_SESSION['lang'])) {
            $lang = $localeFallback($_SESSION['lang']);
            if ($lang) return $lang;
            error_log("[LanguageModel] Unsupported SESSION lang: {$_SESSION['lang']}");
        }
        if (isset($_COOKIE['lang'])) {
            $lang = $localeFallback($_COOKIE['lang']);
            if ($lang) return $lang;
            error_log("[LanguageModel] Unsupported COOKIE lang: {$_COOKIE['lang']}");
        }
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLangs = $this->parseAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptLangs as $langCode) {
                $lang = $localeFallback($langCode);
                if ($lang) return $lang;
            }
        }
        return $this->defaultLanguageCode;
    }
}

// --- UNIT TEST STUB ---
// To run: php models/LanguageModel.php
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    /**
     * Unit test stub for LanguageModel (to be implemented in test suite)
     */
    $lm = new LanguageModel(__DIR__ . '/../resources/lang/');
    $lm->loadLanguageMetadata();
    $langs = $lm->getSupportedLanguages();
    if (!in_array('en', $langs, true)) {
        echo "[TEST FAILED] English language not found in supported languages\n" . PHP_EOL;
    } else {
        echo "[TEST PASSED] English language found\n" . PHP_EOL;
    }
}
