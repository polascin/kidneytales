<?php

declare(strict_types=1);

// File: /bootstrap.php

/**
 * Bootstrap for Kidney Tales - multilingual web application entry point
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2005.08.1.0
 * 
 */

require_once APP_ROOT . DS . 'config' . DS . 'setconstants.php';

require_once APP_ROOT . DS . 'vendor' . DS . 'autoload.php'; // Composer autoload
