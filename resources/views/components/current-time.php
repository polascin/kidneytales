<div style="margin-left: 0.6rem; margin-right: 0.6rem; margin-bottom: 0.6rem;">
  <div style="border-bottom: solid thin gray; padding: 0.3rem;">
    <a href="https://en.wikipedia.org/wiki/Swatch_Internet_Time" target="_blank" style="color: gray; text-decoration: none; font-weight: bold; font-size: larger;">
      <span>@</span><span id="beatsTime"></span>
    </a>
  </div>
  <div>
    <br>
    <a href="https://time.is/" target="_blank" style="text-decoration: none; color: gray;">
      <span><?= ((isset($t['day'])) ? $t['day'] : 'Day') . ': ' ?>&nbsp;</span><span id="dayOfYear" style="font-weight: bold;"></span>
      <span>&nbsp;&nbsp;<?= ((isset($t['year'])) ? $t['year'] : 'Year') . ': ' ?>&nbsp;</span><span id="currentYear" style="font-weight: bolder;"></span>
      <span>&nbsp;&nbsp;<?= ((isset($t['week'])) ? $t['week'] : 'Week') . ': ' ?>&nbsp;</span><span id="weekNumber" style="font-weight: bold;"></span>
      <br>
      <span>&nbsp;<?= ((isset($t['today_is'])) ? $t['today_is'] : 'Today is') . ' ' ?>&nbsp;</span><span id="dayOfWeek" style="font-weight: bold;"></span>
      <span id="dayOfMonth" style="font-weight: bold;"></span>.
      <span id="monthName" style="font-weight: bold;"></span>
      <span id="dateYear" style="font-weight: bold;"></span>
      <span id="currentTime" style="font-weight: bold;"></span>
      <br>
      <span>(</span><span id="timeZone" style="font-style: italic; font-variant: small-caps; font-size: small;"></span><span>)</span>
    </a>
  </div>
</div>
<script>
  // Get current locale, language, and country from PHP LanguageModel class for use in JS
  <?php
    use KidneyTales\Models\LanguageModel;
    $currentLocale = method_exists('KidneyTales\Models\LanguageModel', 'getCurrentLocale')
      ? LanguageModel::getCurrentLocale()
      : ((isset($t['locale'])) ? $t['locale'] : (defined('APP_LOCALE') ? APP_LOCALE : 'en-US'));
    // Convert underscore to dash for JS locale
    $jsLocale = str_replace('_', '-', $currentLocale);
    $currentLanguage = method_exists('KidneyTales\Models\LanguageModel', 'getCurrentLanguageCode')
      ? LanguageModel::getCurrentLanguageCode()
      : ((isset($t['lang'])) ? $t['lang'] : (defined('APP_LANG') ? APP_LANG : 'en'));
    $currentCountry = method_exists('KidneyTales\Models\LanguageModel', 'getCurrentCountryCode')
      ? LanguageModel::getCurrentCountryCode($currentLanguage)
      : ((isset($t['country'])) ? $t['country'] : (defined('APP_COUNTRY') ? APP_COUNTRY : 'US'));
  ?>
  const currentLocale = "<?= $jsLocale ?>";
  const currentLanguage = "<?= $currentLanguage ?>";
  const currentCountry = "<?= $currentCountry ?>";

  function updateDateTimeDetails() {
    const now = new Date();

    // Defensive: fallback to 'en-US' if locale is invalid
    let locale = currentLocale || 'en-US';

    let dayOfWeek, monthName, currentTime, timeZone;
    try {
      dayOfWeek = now.toLocaleString(locale, { weekday: 'long' });
      monthName = now.toLocaleString(locale, { month: 'long' });
      currentTime = now.toLocaleTimeString(locale, { timeZoneName: 'short' });
      timeZone = new Intl.DateTimeFormat(locale, { timeZoneName: 'long' }).format(now);
    } catch (e) {
      // fallback to en-US
      dayOfWeek = now.toLocaleString('en-US', { weekday: 'long' });
      monthName = now.toLocaleString('en-US', { month: 'long' });
      currentTime = now.toLocaleTimeString('en-US', { timeZoneName: 'short' });
      timeZone = new Intl.DateTimeFormat('en-US', { timeZoneName: 'long' }).format(now);
    }

    setText('dayOfYear', getDayOfYear(now));
    setText('weekNumber', getWeekNumber(now));
    setText('dayOfWeek', dayOfWeek);
    setText('dayOfMonth', now.getDate());
    setText('monthName', monthName);
    setText('currentYear', now.getFullYear());
    setText('dateYear', now.getFullYear());
    setText('currentTime', currentTime);
    setText('timeZone', timeZone);
    setText('beatsTime', calculateBeatsTime(now).toFixed(2));
  }

  function setText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function getDayOfYear(date) {
    const start = new Date(date.getFullYear(), 0, 0);
    const diff = (date - start) + ((start.getTimezoneOffset() - date.getTimezoneOffset()) * 60 * 1000);
    const oneDay = 1000 * 60 * 60 * 24;
    return Math.floor(diff / oneDay);
  }

  function getWeekNumber(date) {
    const firstDay = new Date(date.getFullYear(), 0, 1);
    const days = Math.floor((date - firstDay) / (24 * 60 * 60 * 1000)) + ((firstDay.getDay() + 1) % 7);
    return Math.ceil(days / 7);
  }

  function calculateBeatsTime(date) {
    // Swatch Internet Time is based on Biel Mean Time (UTC+1)
    const bmt = new Date(date.getTime() + (date.getTimezoneOffset() + 60) * 60000);
    const seconds = bmt.getUTCHours() * 3600 + bmt.getUTCMinutes() * 60 + bmt.getUTCSeconds() + bmt.getUTCMilliseconds() / 1000;
    return (seconds / 86.4) % 1000;
  }

  setInterval(updateDateTimeDetails, 1000);
  updateDateTimeDetails();
</script>