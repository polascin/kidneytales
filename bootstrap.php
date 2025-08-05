<?php

declare(strict_types=1);

/**
 * Bootstrap for Kidney Tales - multilingual web application
 *
 * Loads configuration constants and Composer autoloader.
 *
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2025.08.1.1
 */

// Define application root and directory separator if not already defined
if (!defined('APP_ROOT')) {
  define('APP_ROOT', __DIR__);
}
if (!defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
}

// Load configuration constants
require_once APP_ROOT . DS . 'config' . DS . 'setconstants.php';

// Load Composer autoloader (PSR-4 autoloading)
require_once APP_ROOT . DS . 'vendor' . DS . 'autoload.php';
