# Kidney Tales - AI Coding Assistant Instructions

## Project Overview
Kidney Tales is a **multilingual web application** built in PHP 8.4+ for sharing stories within the kidney disorder community. It features a sophisticated language detection system supporting 200+ languages with secure session management and a modular MVC architecture.

## Architecture & Key Patterns

### MVC Structure
- **Controllers**: `src/Controllers/` - Handle business logic and user interactions
- **Models**: `src/Models/` - Manage data and core application logic
- **Views**: `src/Views/` → `resources/views/` - Presentation layer with component-based structure

### Autoloading & Bootstrap
```php
// PSR-4 namespace: KidneyTales\
require_once APP_ROOT . DS . 'bootstrap.php'; // Always include first
```

### Language System Architecture
The app features a **6-tier language detection** system:
1. URL parameters (`?lang=sk`)
2. POST with CSRF validation
3. Session storage
4. Cookie preferences  
5. Browser Accept-Language headers
6. GeoIP-based detection (with fallback)

**Critical files:**
- `languages/{lang}.php` - Translation arrays (200+ files)
- `resources/languages.php` - Language metadata [English, Native, Country]
- `resources/countries.php` - Country code mappings
- `LanguageModel::loadLanguageTranslations()` - Loads translations into `$t` array

### Security Patterns
```php
// CSRF protection - always validate POST requests
SessionManager::isValidCsrfToken()

// Session binding to prevent hijacking
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
```

## Development Conventions

### File Structure Patterns
```
public/           # Web root, entry point
src/Controllers/  # Business logic classes  
src/Models/       # Data management
src/Views/        # View controllers
resources/views/  # Actual template files
  components/     # Reusable view components
languages/        # Translation files ({lang}.php)
config/           # Application constants
```

### Naming Conventions
- **Classes**: PascalCase (`LanguageController`, `SessionManager`)
- **Methods**: camelCase (`detectCurrentLanguage()`, `setCurrentLanguage()`)
- **Constants**: UPPER_CASE (`DEFAULT_LANGUAGE`, `LANGUAGES_PATH`)
- **Variables**: snake_case (`$current_language_code`, `$t`)

### Translation System
```php
// Load current language
$currentLanguageCode = LanguageModel::getCurrentLanguageCode();
$t = LanguageModel::$t; // Translation array

// Usage in templates
echo $t['welcome_message'] ?? 'Welcome';
```

## Critical Integration Points

### Session Management Flow
1. `SessionManager::StartSession()` - Initialize secure session
2. CSRF token generation with 1-hour expiry
3. Session binding to user agent + IP
4. Automatic regeneration for new sessions

### Language Detection Flow
1. `LanguageController::detectCurrentLanguage()` - Multi-tier detection
2. `LanguageController::setCurrentLanguage()` - Persist choice
3. `LanguageModel::loadLanguageTranslations()` - Load translations
4. `LanguageModel::isSupported()` - Validate language codes

### Error Handling
- Uses **Whoops** library for development error pages
- Fallback mechanisms for missing translations/languages
- Security violations trigger session destruction

## Common Development Tasks

### Adding New Language Support
1. Create `/languages/{lang}.php` with translation array
2. Add entry to `/resources/languages.php`
3. Add country mapping to `/resources/countries.php`
4. Place flag image at `/public/assets/flags/{country}.webp`

### Security-First Patterns
```php
// Always validate CSRF for POST
if (!SessionManager::isValidCsrfToken()) {
    // Handle invalid token
}

// Sanitize language inputs
$sanitize = function ($code) {
    return preg_match('/^[a-z0-9_-]+$/i', $code) ? $code : '';
};
```

### View Component Structure
Views follow a nested include pattern:
```php
// resources/views/HomePageView.php includes:
require_once 'components/html.php';
require_once 'components/head.php';  
require_once 'components/body.php';
```

## Dependencies & Environment
- **PHP 8.4+** with strict typing (`declare(strict_types=1)`)
- **Composer** for PSR-4 autoloading
- **Whoops** for development error handling
- **No framework** - custom lightweight MVC

## File Path Patterns
- Use `APP_ROOT . DS . 'path'` for cross-platform compatibility
- `DS` constant = `DIRECTORY_SEPARATOR`
- All paths relative to project root defined in `bootstrap.php`

When working on this codebase, prioritize **multilingual compatibility**, **security validation**, and follow the established **MVC separation**. The language system is the core differentiator - ensure all new features support the translation workflow.