<!-- The LANGUAGE COUNTRY FLAGS SELECTOR -->
<div class="flags-selector-container">
  <?php

  use KidneyTales\Models\LanguageModel;

  $supportedLanguages = LanguageModel::getSupportedLanguageCodes();
  foreach ($supportedLanguages as $langCode):
    $languageCode = htmlspecialchars(trim(strtolower($langCode)));
    $languageName = htmlspecialchars(trim(LanguageModel::getLanguageEnglishName($langCode)));
    $languageNative = htmlspecialchars(trim(LanguageModel::getLanguageNativeName($langCode)));
    $flag = htmlspecialchars_decode(trim(LanguageModel::getFlagPath($langCode)));
  ?>
    <form method="post" style="display:inline;" id="flag-form-<?= $languageCode ?>">
      <input type="hidden" name="lang" value="<?= $languageCode ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <button class="flags-selector" type="button" onclick="document.getElementById('flag-form-<?= $languageCode ?>').submit();">
        <div><?= $languageCode ?></div>
        <img src="<?= $flag ?>" alt="<?= $languageNative . ' - ' . $languageName ?>" class="flags-selector" title="<?= $languageNative ?>">
      </button>
    </form>
  <?php
  endforeach;
  echo PHP_EOL;
  ?>
</div>
<!-- The END Language Country Flags Selector -->