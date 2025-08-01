<?php

declare(strict_types=1);

use KidneyTales\Models\LanguageModel;

// File: /views/homePageView.php

/**
 * Homepage View for Kidney Tales - multilingual web application
 *  
 * @author Ľubomír Polaščín
 * @package KidneyTales
 * @version 2005.08.01.01
 * 
 */

$currentLanguageCode = LanguageModel::detectCurrentLanguage();

?>

<!DOCTYPE html>
<html lang="<?= $currentLanguageCode ?>">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($t['app_title']) ? htmlspecialchars($t['app_title']) : APP_NAME; ?></title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.webp" type="image/webp">
    <link rel="icon" href="/favicon.avif" type="image/avif">
    <!-- Favicons for various devices and browsers -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#007bff">
    <meta name="msapplication-TileColor" content="#007bff">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <!-- Meta Tags for Content -->
    <meta name="application-name" content="<?= isset($t['app_name']) ? htmlspecialchars($t['app_name']) : APP_NAME; ?>">
    <meta name="description" content="<?= isset($t['app_description']) ? htmlspecialchars($t['app_description']) : 'A multilingual web application for kidney health stories and resources.'; ?>">
    <meta name="keywords" content="<?= isset($t['meta_keywords']) ? htmlspecialchars($t['meta_keywords']) : 'kidney, health, stories, tales, resources, multilingual, kidney tales, kidney disease, kidney health, kidney stories, renal tales, renal stories, dialysis, kidney transplant, renal transplant, nephrology'; ?>">
    <meta name="author" content="<?= isset($t['meta_author']) ? htmlspecialchars($t['meta_author']) : 'Lubomir Polascin'; ?>">
    <meta name="copyright" content="&copy;&nbsp;<?= date('Y') ?>&nbsp;<?= isset($t['meta_copyright']) ? htmlspecialchars($t['meta_copyright']) : 'Lubomir Polascin @ Kidney Tales Contributor Teams Members'; ?>">
    <meta name="creator" content="<?= isset($t['meta_creator']) ? htmlspecialchars($t['meta_creator']) : 'Lubomir Polascin'; ?>">
    <meta name="publisher" content="<?= isset($t['meta_publisher']) ? htmlspecialchars($t['meta_publisher']) : 'Lubomir Polascin @ Kidney Tales Contributors Teams Members'; ?>">
    <meta name="robots" content="index, follow">
    <meta name="generator" content="Kidney Tales Content Management System (CMS) and Framework and Code Development by Lubomir Polascin">
    <meta name="theme-color" content="#007bff">
    <meta name="rating" content="General">
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <!-- Language and alternate -->
    <meta http-equiv="content-language" content="<?= htmlspecialchars($currentLanguageCode) ?>">
    <link rel="alternate" href="/" hreflang="x-default">
    <link rel="alternate" href="/en/" hreflang="en">
    <link rel="alternate" href="/sk/" hreflang="sk">
    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="<?= isset($t['app_name']) ? htmlspecialchars($t['app_name']) : 'Kidney Tales'; ?>">
    <meta property="og:description" content="<?= isset($t['app_description']) ? htmlspecialchars($t['app_description']) : 'A multilingual web application for kidney health stories and resources.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <meta property="og:image" content="/images/og-image.png">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= isset($t['app_name']) ? htmlspecialchars($t['app_name']) : 'Kidney Tales'; ?>">
    <meta name="twitter:description" content="<?= isset($t['app_description']) ? htmlspecialchars($t['app_description']) : 'A multilingual web application for kidney health stories and resources.'; ?>">
    <meta name="twitter:image" content="/images/og-image.png">
    <!-- Optimized Google Fonts: Roboto, Fira Mono, Segoe UI (fallback) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Fira+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Note: Segoe UI is a system font and does not need to be loaded from Google Fonts -->
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/basic.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/language.css?v=<?= time() ?>">
    <!-- Additional styles can be added here -->
    <style>
    /* Custom styles can be added here */
    </style>
  </head>

  <body>
    <div class="language-selector-container">
      <form method="post" action="" onsubmit="location.reload(); return false;">
        <select id="language-selector" name="lang" onchange="this.form.submit()">
          <?php
          $currentLanguageCode = LanguageModel::detectCurrentLanguage();
          $supportedLanguages = LanguageModel::getSupportedLanguageCodes();
          foreach ($supportedLanguages as $langCode):
            $languageCode = htmlspecialchars(trim(strtolower($langCode)));
            $languageName = htmlspecialchars(trim(LanguageModel::getLanguageEnglishName($langCode)));
            $languageNative = htmlspecialchars(trim(LanguageModel::getLanguageNativeName($langCode)));
            $flag = LanguageModel::getFlagPath($langCode);
            ?>
          <option value="<?=$langCode?>" <?=$currentLanguageCode === $langCode ? 'selected' : '' ?>><img class="language-selector-img" src="<?=$flag?>" alt="<?=$languageNative.' ('.$languageName.')'?>"><?=$languageNative?> (<?=$languageName?>) [<?=$languageCode?>]</option>
          <?php
          endforeach;
          echo PHP_EOL;
          ?>
        </select>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      </form>
    </div>

    <h1><?= isset($t['welcome_message']) ? htmlspecialchars($t['welcome_message']) : 'Welcome to Kidney Tales'; ?></h1>
    <p><?= isset($t['app_description']) ? htmlspecialchars($t['app_description']) : 'A multilingual web application for kidney health stories and resources.'; ?></p>


  </body>

</html>