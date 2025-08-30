<?php

declare(strict_types=1);

use KidneyTales\Models\LanguageModel;
use KidneyTales\Controllers\SessionManager;
use KidneyTales\Controllers\LanguageController;

/**
 * Kidney Tales - multilingual web application entry point
 *
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2025.08.1.1
 */

// @file /public/index.php

define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

require_once APP_ROOT . DS . 'bootstrap.php';

// Initialize secure session management for the application
SessionManager::StartSession();

// --- Language Loading and Error Handling ---
try {
  $currentLanguage = LanguageController::detectCurrentLanguage();
  LanguageController::setCurrentLanguage($currentLanguage);
} catch (Throwable $e) {
  error_log('[index.php] Language loading error: ' . $e->getMessage());
  // Fallback to English and empty translations on error
  LanguageModel::$currentLanguageCode = 'en';
  LanguageModel::$t = [];
}

// --- End Language Loading ---


// Render the main homepage view
include_once APP_ROOT . DS . 'src' . DS . 'Views' . DS . 'HomePageView.php';
