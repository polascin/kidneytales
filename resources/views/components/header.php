<header class="header.container">
  <h1><?= isset($t['welcome_message']) ? htmlspecialchars($t['welcome_message']) : 'Welcome to Kidney Tales'; ?></h1>
  <p><?= isset($t['app_description']) ? htmlspecialchars($t['app_description']) : 'A multilingual web application for kidney health stories and resources.'; ?></p>
</header>