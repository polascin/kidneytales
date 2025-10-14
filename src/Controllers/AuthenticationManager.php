<?php

declare(strict_types=1);

namespace KidneyTales\Controllers;

use KidneyTales\Models\User;
use KidneyTales\Models\Role;
use KidneyTales\Models\Permission;

/**
 * Authentication Manager for Kidney Tales
 * 
 * Provides middleware functions for authentication and authorization,
 * session management, and access control with role-based permissions.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Controllers
 * @version 2025.10.1
 */
class AuthenticationManager
{
    /**
     * Require user to be logged in
     * Redirects to login page if not authenticated
     * 
     * @param string $redirectAfterLogin Optional redirect URL after login
     * @return void
     */
    public static function requireLogin(string $redirectAfterLogin = ''): void
    {
        SessionManager::StartSession();
        
        if (!UserController::isLoggedIn()) {
            if (!empty($redirectAfterLogin)) {
                $_SESSION['redirect_after_login'] = $redirectAfterLogin;
            } else {
                $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/dashboard';
            }
            
            header('Location: /login');
            exit;
        }
        
        // Extend session if user is active
        $_SESSION['login_time'] = time();
    }

    /**
     * Require user to have specific permission
     * 
     * @param string $permission Required permission
     * @param string $redirectUrl Optional redirect URL on failure
     * @return void
     */
    public static function requirePermission(string $permission, string $redirectUrl = '/dashboard'): void
    {
        self::requireLogin();
        
        if (!UserController::hasPermission($permission)) {
            $_SESSION['flash_messages'][] = [
                'type' => 'error',
                'message' => 'Access denied. You do not have permission to access this resource.'
            ];
            
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    /**
     * Require user to have specific role
     * 
     * @param string $role Required role
     * @param string $redirectUrl Optional redirect URL on failure
     * @return void
     */
    public static function requireRole(string $role, string $redirectUrl = '/dashboard'): void
    {
        self::requireLogin();
        
        if (!UserController::hasRole($role)) {
            $_SESSION['flash_messages'][] = [
                'type' => 'error',
                'message' => 'Access denied. You do not have the required role to access this resource.'
            ];
            
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    /**
     * Require user to have any of the specified roles
     * 
     * @param array $roles Array of allowed roles
     * @param string $redirectUrl Optional redirect URL on failure
     * @return void
     */
    public static function requireAnyRole(array $roles, string $redirectUrl = '/dashboard'): void
    {
        self::requireLogin();
        
        $hasRole = false;
        foreach ($roles as $role) {
            if (UserController::hasRole($role)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            $_SESSION['flash_messages'][] = [
                'type' => 'error',
                'message' => 'Access denied. You do not have any of the required roles to access this resource.'
            ];
            
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    /**
     * Require user to have any of the specified permissions
     * 
     * @param array $permissions Array of required permissions
     * @param string $redirectUrl Optional redirect URL on failure
     * @return void
     */
    public static function requireAnyPermission(array $permissions, string $redirectUrl = '/dashboard'): void
    {
        self::requireLogin();
        
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (UserController::hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }
        
        if (!$hasPermission) {
            $_SESSION['flash_messages'][] = [
                'type' => 'error',
                'message' => 'Access denied. You do not have any of the required permissions to access this resource.'
            ];
            
            header("Location: {$redirectUrl}");
            exit;
        }
    }

    /**
     * Check if user can access content creation
     * Includes subscription limits and role permissions
     * 
     * @return bool True if can create content, false otherwise
     */
    public static function canCreateContent(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        if (!$user) {
            return false;
        }

        // Check role permission
        if (!$user->hasPermission(Permission::CREATE_CONTENT)) {
            return false;
        }

        // Check subscription limits
        return \KidneyTales\Models\UserSubscription::canCreateArticle($user->id);
    }

    /**
     * Check if user can access translation features
     * 
     * @return bool True if can translate, false otherwise
     */
    public static function canTranslate(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        if (!$user) {
            return false;
        }

        // Check role permission
        if (!$user->hasPermission(Permission::TRANSLATE_CONTENT)) {
            return false;
        }

        // Check subscription limits
        return \KidneyTales\Models\UserSubscription::canCreateTranslation($user->id);
    }

    /**
     * Check if user can edit specific content
     * 
     * @param int $contentUserId User ID of content creator
     * @return bool True if can edit, false otherwise
     */
    public static function canEditContent(int $contentUserId): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        if (!$user) {
            return false;
        }

        // Users can edit their own content if they have create permission
        if ($user->id === $contentUserId && $user->hasPermission(Permission::CREATE_CONTENT)) {
            return true;
        }

        // Editors and admins can edit all content
        return $user->hasPermission(Permission::EDIT_CONTENT);
    }

    /**
     * Check if user can delete specific content
     * 
     * @param int $contentUserId User ID of content creator
     * @return bool True if can delete, false otherwise
     */
    public static function canDeleteContent(int $contentUserId): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        if (!$user) {
            return false;
        }

        // Users can delete their own content if they have create permission
        if ($user->id === $contentUserId && $user->hasPermission(Permission::CREATE_CONTENT)) {
            return true;
        }

        // Only admins and those with explicit delete permission can delete others' content
        return $user->hasPermission(Permission::DELETE_CONTENT);
    }

    /**
     * Check if user can publish content
     * 
     * @return bool True if can publish, false otherwise
     */
    public static function canPublishContent(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        return $user && $user->hasPermission(Permission::PUBLISH_CONTENT);
    }

    /**
     * Check if user can manage other users
     * 
     * @return bool True if can manage users, false otherwise
     */
    public static function canManageUsers(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        return $user && $user->hasPermission(Permission::MANAGE_USERS);
    }

    /**
     * Check if user can assign roles
     * 
     * @return bool True if can assign roles, false otherwise
     */
    public static function canAssignRoles(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        return $user && $user->hasPermission(Permission::ASSIGN_ROLES);
    }

    /**
     * Check if user can access admin dashboard
     * 
     * @return bool True if can access admin, false otherwise
     */
    public static function canAccessAdmin(): bool
    {
        if (!UserController::isLoggedIn()) {
            return false;
        }

        $user = UserController::getCurrentUser();
        return $user && $user->hasPermission(Permission::ADMIN_DASHBOARD);
    }

    /**
     * Get user's role-based menu items
     * 
     * @return array Array of menu items based on user's permissions
     */
    public static function getUserMenu(): array
    {
        $menu = [
            ['title' => 'Home', 'url' => '/', 'permission' => null]
        ];

        if (!UserController::isLoggedIn()) {
            $menu[] = ['title' => 'Login', 'url' => '/login', 'permission' => null];
            $menu[] = ['title' => 'Register', 'url' => '/register', 'permission' => null];
            return $menu;
        }

        // Authenticated user menu
        $menu[] = ['title' => 'Dashboard', 'url' => '/dashboard', 'permission' => null];
        $menu[] = ['title' => 'Profile', 'url' => '/profile', 'permission' => null];

        if (self::canCreateContent()) {
            $menu[] = ['title' => 'Create Article', 'url' => '/create-article', 'permission' => Permission::CREATE_CONTENT];
        }

        if (self::canTranslate()) {
            $menu[] = ['title' => 'Translations', 'url' => '/translations', 'permission' => Permission::TRANSLATE_CONTENT];
        }

        if (UserController::hasPermission(Permission::VIEW_ANALYTICS)) {
            $menu[] = ['title' => 'Analytics', 'url' => '/analytics', 'permission' => Permission::VIEW_ANALYTICS];
        }

        if (self::canManageUsers()) {
            $menu[] = ['title' => 'User Management', 'url' => '/admin/users', 'permission' => Permission::MANAGE_USERS];
        }

        if (self::canAccessAdmin()) {
            $menu[] = ['title' => 'Admin', 'url' => '/admin', 'permission' => Permission::ADMIN_DASHBOARD];
        }

        $menu[] = ['title' => 'Subscription', 'url' => '/subscription', 'permission' => null];
        $menu[] = ['title' => 'Logout', 'url' => '/logout', 'permission' => null];

        return $menu;
    }

    /**
     * Get user's dashboard widgets based on role and permissions
     * 
     * @return array Array of dashboard widgets
     */
    public static function getDashboardWidgets(): array
    {
        if (!UserController::isLoggedIn()) {
            return [];
        }

        $widgets = [];
        $user = UserController::getCurrentUser();

        // Basic user widget
        $widgets[] = [
            'type' => 'profile',
            'title' => 'Profile Summary',
            'permission' => null
        ];

        // Subscription widget
        $widgets[] = [
            'type' => 'subscription',
            'title' => 'Subscription Status',
            'permission' => null
        ];

        // Content creation widget
        if (self::canCreateContent()) {
            $widgets[] = [
                'type' => 'create_content',
                'title' => 'Quick Actions',
                'permission' => Permission::CREATE_CONTENT
            ];
        }

        // Translation widget
        if (self::canTranslate()) {
            $widgets[] = [
                'type' => 'translations',
                'title' => 'Translation Dashboard',
                'permission' => Permission::TRANSLATE_CONTENT
            ];
        }

        // Analytics widget
        if (UserController::hasPermission(Permission::VIEW_ANALYTICS)) {
            $widgets[] = [
                'type' => 'analytics',
                'title' => 'Content Analytics',
                'permission' => Permission::VIEW_ANALYTICS
            ];
        }

        // Admin widget
        if (self::canAccessAdmin()) {
            $widgets[] = [
                'type' => 'admin',
                'title' => 'Admin Tools',
                'permission' => Permission::ADMIN_DASHBOARD
            ];
        }

        return $widgets;
    }

    /**
     * Validate API access for user
     * 
     * @param string $apiKey API key to validate
     * @return User|null User object if valid, null otherwise
     */
    public static function validateApiAccess(string $apiKey): ?User
    {
        // This would typically involve API key validation from database
        // For now, we'll focus on session-based authentication
        return null;
    }

    /**
     * Log security event
     * 
     * @param string $event Event type
     * @param string $description Event description
     * @param int|null $userId User ID if available
     * @return void
     */
    public static function logSecurityEvent(string $event, string $description, ?int $userId = null): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'description' => $description,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        // Log to file or database
        error_log("SECURITY EVENT: " . json_encode($logEntry));
    }

    /**
     * Check for suspicious activity
     * 
     * @return bool True if activity seems suspicious, false otherwise
     */
    public static function checkSuspiciousActivity(): bool
    {
        // Implement rate limiting, unusual access patterns, etc.
        // This is a placeholder for more sophisticated security monitoring
        return false;
    }

    /**
     * Generate secure token for password reset, email verification, etc.
     * 
     * @param int $length Token length
     * @return string Generated token
     */
    public static function generateSecureToken(int $length = 32): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (\Exception $e) {
            // Fallback for systems without random_bytes
            return bin2hex(uniqid((string)mt_rand(), true));
        }
    }

    /**
     * Rate limit checker
     * 
     * @param string $action Action being performed
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if within limits, false if rate limited
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        SessionManager::StartSession();
        
        $key = "rate_limit_{$action}";
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => $now];
            return true;
        }
        
        $rateData = $_SESSION[$key];
        
        // Reset if time window has passed
        if ($now - $rateData['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => $now];
            return true;
        }
        
        // Check if within limits
        if ($rateData['attempts'] >= $maxAttempts) {
            return false;
        }
        
        return true;
    }

    /**
     * Increment rate limit counter
     * 
     * @param string $action Action being performed
     * @return void
     */
    public static function incrementRateLimit(string $action): void
    {
        SessionManager::StartSession();
        
        $key = "rate_limit_{$action}";
        
        if (isset($_SESSION[$key])) {
            $_SESSION[$key]['attempts']++;
        }
    }
}