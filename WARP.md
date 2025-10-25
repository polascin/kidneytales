# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Quick start and commonly used commands
- Install dependencies
  - `composer install`
  - Note: vendor/autoload.php is already loaded by bootstrap.php.
- Run local development server
  - `php -S localhost:8000 -t public/`
- Database setup
  - Web: open /database/setup.php (e.g., https://kidneytales.local/database/setup.php or http://localhost:8000/database/setup.php)
  - CLI: `php database/setup.php`
  - Manual: import database/schema.sql then database/seed_data.sql
- Verify installation
  - Open /database/test.php in your browser
- Windows local server switching (Laragon scripts)
  - `.\server-manager.ps1 nginx`
  - `.\server-manager.ps1 apache`
  - `.\server-manager.ps1 stop`
  - `.\server-manager.ps1 status`
- Testing note
  - There is no automated test suite in this repo; manual testing is documented. Useful routes to check: /login and /register (CSRF-protected and translated).

## Architecture and structure (big picture)
- Runtime and autoload
  - PHP 8.4+ with strict types; PSR-4 autoload for KidneyTales\\ via composer; bootstrap.php loads vendor/autoload.php.
- Entry point and routing
  - public/index.php defines APP_ROOT and DS, includes bootstrap.php, starts a secure session (SessionManager::StartSession), performs multi-tier language detection (LanguageController), then routes (simple switch) to pages such as /login, /register, /dashboard. Default includes src/Views/HomePageView.php which bridges into resources/views templates.
- Bootstrapping and configuration
  - bootstrap.php includes config/setconstants.php and composer autoload.
  - config/setconstants.php defines application constants (DEFAULT_LANGUAGE, DB_* credentials, PASSWORD_MIN_LENGTH, SESSION_TIMEOUT, etc.).
- Language system (core differentiator)
  - LanguageController::detectCurrentLanguage() uses six tiers: URL, validated POST+CSRF, session, cookie, Accept-Language, Geo IP fallback, with sanitisation.
  - LanguageController::setCurrentLanguage() persists to session and secure SameSite cookie, then loads translations.
  - LanguageModel loads languages/{code}.php into LanguageModel::$t and references resources/languages.php and resources/countries.php for names/flags.
- Session and security
  - SessionManager centralises secure session initialisation, binds session to user agent and IP, manages CSRF tokens with rotation and expiry.
- Domain models and authentication
  - PDO-based models (User, Role, Permission, Subscription/SubscriptionPlan, UserSubscription). AuthenticationManager provides guards (requireLogin/requirePermission/requireRole) and higher-level checks (e.g., canCreateContent, canTranslate).
- Views and templates
  - Templates live under resources/views (e.g., login.php, register.php, dashboard.php, components/*). src/Views/HomePageView.php glues controller/view to templates. Templates include bootstrap.php, start session, pull translations, and include component partials.
- Web server integration
  - Apache rewrite via public/.htaccess for clean URLs. Document root is public/. Local SSL and server management are documented for Laragon; use server-manager.ps1 to switch between Nginx/Apache.
- Dependencies
  - Composer-managed; notable dev/runtime packages include filp/whoops (development error pages) and psr/log.

## Conventions and doc highlights
- .github/copilot-instructions.md: MVC layout (src/Controllers, src/Models, src/Views → resources/views), PSR-4 KidneyTales\\, language detection tiers, session binding and CSRF checks, Whoops in development, and using APP_ROOT and DS for paths.
- AUTHENTICATION_TESTING.md: Quick-run server command (php -S ... -t public/), test routes (/login, /register); forms use CSRF and the translation system.
- docs/LARAGON_SSL_SETUP.md: Local domain kidneytales.local; document root is public/; server-manager.ps1 commands for switching and status.
- README_USER_MANAGEMENT.md and docs/USER_MANAGEMENT_SYSTEM.md: Installation steps (configure config/setconstants.php, composer install, run database/setup.php or apply schema/seed), change default admin credentials, and verify via /database/test.php.