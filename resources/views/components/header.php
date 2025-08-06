<header class="header-container">

  <?php require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'language-selector.php'; ?>

  <div class="main-header-content-container">

    <div class="header-left">
      <?php
      $imgsrc = 'assets' . DS . 'logos' . DS . 'logo_shifted.gif';
      $imgalt = (isset($t['app_title']) ? $t['app_title'] : 'Kidney Tales');
      $imgalt = $imgalt . ' ' . (isset($t['app_logo']) ? $t['app_logo'] : 'Application Logo')
      ?>
      <img src="<?= $imgsrc ?>" alt="<?= $imgalt ?>" title="<?= $imgalt ?>" class="logoimg">
      <h1><?= (isset($t['app_title']) ? $t['app_title'] : 'Kidney Tales'); ?></h1>
      <h2><?= APP_NAME ?></h2>
      <h3><?= (isset($t['app_subtitle']) ? $t['app_subtitle'] : 'A Multilingual Web Application'); ?></h3>
      <h4><span class="description"><?= (isset($t['website']) ? $t['website'] : 'WebSite') . ': '; ?></span>&nbsp;<a title="<?= (isset($t['app_url']) ? $t['app_url'] : 'https://www.ladvina.eu/'); ?>" href="<?= (isset($t['app_url']) ? $t['app_url'] : 'https://www.ladvina.eu/'); ?>"><?= (isset($t['app_url']) ? $t['app_url'] : 'https://www.ladvina.eu/'); ?></a></h4>
      <h4><span class="description"><?= (isset($t['email']) ? $t['email'] : 'E-Mail') . ': '; ?></span>&nbsp;&nbsp;&nbsp;<a title="<?= (isset($t['app_email']) ? $t['app_email'] : 'info@ladvina.eu'); ?>" href="mailto:<?= (isset($t['app_email']) ? $t['app_email'] : 'info@ladvina.eu'); ?>"><?= (isset($t['app_email']) ? $t['app_email'] : 'info@ladvina.eu'); ?></a></h4>
      <h5><?= (isset($t['app_version']) ? $t['app_version'] : '2005.08.01.01'); ?></h5>
      <h6><?= (isset($t['app_author']) ? $t['app_author'] : 'Lumpe Paskuden von Lumpenen aka Walter Kyo aka Walter Csoelle aka Ľubomír Polaščín'); ?></h6>
    </div>

    <div class="header-center">
      <?php
      require_once APP_ROOT . DS . 'resources' . DS . 'views' . DS . 'components' . DS . 'current-time.php';
      ?>
    </div>

    <div class="header-right">
      <?php

      use KidneyTales\Models\LanguageModel;

      // Show flag for current language/country
      $currentLanguageCode = LanguageModel::getCurrentLanguageCode();
      $currentCountryCode = LanguageModel::getCurrentCountryCode($currentLanguageCode);
      $flag = LanguageModel::getFlagPath($currentLanguageCode);
      $flagAlt = LanguageModel::getLanguageNativeName($currentLanguageCode);
      $flagTitle = $flagAlt . ' (' . strtoupper($currentCountryCode) . ')';
      ?>
      <div>
        <img src="<?= $flag ?>" alt="<?= $flagAlt ?>" title="<?= $flagTitle ?>" class="flag">
      </div>
      <div class="userinfo">
        <div><span class="description"><?= (isset($t['user_information']) ? $t['user_information'] : 'User information') ?> </span></div>
        <div><span class="description"><?= (isset($t['user']) ? $t['user'] : 'User') . ': ' ?> </span>Placeholder</div>
        <div><span class="description"><?= (isset($t['name']) ? $t['name'] : 'Name') . ': ' ?></span>Placeholder</div>
        <div><span class="description"><?= (isset($t['user_email']) ? $t['user_email'] : 'User`s e-mail') . ': ' ?> </span>Placeholder</div>
        <div><span class="description"><?= (isset($t['role']) ? $t['role'] : 'role') . ': ' ?></span>Placeholder</div>
      </div>
    </div>

  </div>

</header>