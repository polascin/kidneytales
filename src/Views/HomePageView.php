<?php

declare(strict_types=1);

namespace KidneyTales\Views;

use KidneyTales\Models\LanguageModel;
use KidneyTales\Controllers\LanguageController;

// File: /views/homePageView.php

/**
 * Homepage View for Kidney Tales - multilingual web application
 *  
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2005.08.01.01
 * 
 */

$currentLanguageCode = LanguageModel::getCurrentLanguageCode();
$t = LanguageModel::$t;

require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'HomePageView.php';

