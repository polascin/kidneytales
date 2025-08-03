<!-- The LANGUAGE SELECTOR -->
<div class="language-selector-container">
  <form method="post" action="" onsubmit="location.reload(); return false;">
    <select id="language-selector" name="lang" onchange="this.form.submit()">
      <?php

      use KidneyTales\Models\LanguageModel;
      use KidneyTales\Controllers\LanguageController;

      $currentLanguageCode = LanguageController::detectCurrentLanguage();
      $supportedLanguages = LanguageModel::getSupportedLanguageCodes();
      foreach ($supportedLanguages as $langCode):
        $languageCode = htmlspecialchars(trim(strtolower($langCode)));
        $languageName = htmlspecialchars(trim(LanguageModel::getLanguageEnglishName($langCode)));
        $languageNative = htmlspecialchars(trim(LanguageModel::getLanguageNativeName($langCode)));
        $flag = LanguageModel::getFlagPath($langCode);
      ?>
        <option value=" <?= $langCode ?>" <?= $currentLanguageCode === $langCode ? 'selected' : '' ?>><img class="language-selector-img" src="<?= $flag ?>" alt="<?= $languageNative . ' (' . $languageName . ')' ?>"><?= $languageNative ?> (<?= $languageName ?>) [<?= $languageCode ?>]</option>
      <?php
      endforeach;
      echo PHP_EOL;
      ?>
    </select>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
  </form>
</div>
<!-- The END of the Language Selector -->