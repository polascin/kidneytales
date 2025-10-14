<?php

declare(strict_types=1);

namespace KidneyTales\Controllers;

use KidneyTales\Models\User;
use KidneyTales\Models\Role;
use KidneyTales\Models\SubscriptionPlan;
use KidneyTales\Models\UserSubscription;
use KidneyTales\Models\LanguageModel;

/**
 * User Controller for Kidney Tales
 * 
 * Handles user registration, authentication, profile management,
 * and role-based access control with multilingual support.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Controllers
 * @version 2025.10.1
 */
class UserController
{
    /**
     * Handle user registration
     * 
     * @return void
     */
    public static function register(): void
    {
        SessionManager::StartSession();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SessionManager::isValidCsrfToken()) {
                self::setFlashMessage('error', 'Invalid security token. Please try again.');
                self::redirect('/register');
                return;
            }

            $errors = self::validateRegistrationData($_POST);
            
            if (empty($errors)) {
                $userData = [
                    'username' => trim($_POST['username']),
                    'email' => trim($_POST['email']),
                    'password' => $_POST['password'],
                    'first_name' => trim($_POST['first_name']),
                    'last_name' => trim($_POST['last_name']),
                    'preferred_language' => $_POST['preferred_language'] ?? DEFAULT_LANGUAGE,
                    'role_id' => 1 // Default to Reader role
                ];

                $userId = User::create($userData);
                
                if ($userId) {
                    // Create free subscription by default
                    $freePlan = SubscriptionPlan::findByName(SubscriptionPlan::FREE);
                    if ($freePlan) {
                        UserSubscription::create([
                            'user_id' => $userId,
                            'subscription_plan_id' => $freePlan->id,
                            'billing_cycle' => UserSubscription::BILLING_MONTHLY,
                            'amount_paid' => 0.00,
                            'status' => UserSubscription::STATUS_ACTIVE
                        ]);
                    }

                    self::setFlashMessage('success', 'Registration successful! Please log in.');
                    self::redirect('/login');
                } else {
                    self::setFlashMessage('error', 'Registration failed. Please try again.');
                }
            } else {
                foreach ($errors as $error) {
                    self::setFlashMessage('error', $error);
                }
            }
        }

        self::renderView('register', [
            'languages' => self::getAvailableLanguages(),
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Handle user login
     * 
     * @return void
     */
    public static function login(): void
    {
        SessionManager::StartSession();
        
        // Redirect if already logged in
        if (self::isLoggedIn()) {
            self::redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SessionManager::isValidCsrfToken()) {
                self::setFlashMessage('error', 'Invalid security token. Please try again.');
                self::redirect('/login');
                return;
            }

            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                self::setFlashMessage('error', 'Please provide both username/email and password.');
            } else {
                try {
                    $user = User::authenticate($identifier, $password);
                    
                    if ($user) {
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['username'] = $user->username;
                        $_SESSION['role_id'] = $user->role_id;
                        $_SESSION['login_time'] = time();
                        
                        self::setFlashMessage('success', 'Welcome back, ' . $user->getFullName() . '!');
                        
                        // Redirect to intended page or dashboard
                        $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard';
                        unset($_SESSION['redirect_after_login']);
                        self::redirect($redirectTo);
                    } else {
                        self::setFlashMessage('error', 'Invalid credentials. Please try again.');
                    }
                } catch (\Exception $e) {
                    self::setFlashMessage('error', $e->getMessage());
                }
            }
        }

        self::renderView('login', [
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Handle user logout
     * 
     * @return void
     */
    public static function logout(): void
    {
        SessionManager::StartSession();
        
        // Clear user session data
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role_id']);
        unset($_SESSION['login_time']);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        self::setFlashMessage('success', 'You have been logged out successfully.');
        self::redirect('/');
    }

    /**
     * Display user dashboard
     * 
     * @return void
     */
    public static function dashboard(): void
    {
        SessionManager::StartSession();
        
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = '/dashboard';
            self::redirect('/login');
            return;
        }

        $user = User::findById($_SESSION['user_id']);
        if (!$user) {
            self::logout();
            return;
        }

        $subscription = UserSubscription::getUserActiveSubscription($user->id);
        $role = Role::getUserRole($user->id);
        $permissions = $role ? $role->getPermissions() : [];

        self::renderView('dashboard', [
            'user' => $user,
            'subscription' => $subscription,
            'subscription_plan' => $subscription ? $subscription->getPlan() : null,
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * Display and handle user profile
     * 
     * @return void
     */
    public static function profile(): void
    {
        SessionManager::StartSession();
        
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = '/profile';
            self::redirect('/login');
            return;
        }

        $user = User::findById($_SESSION['user_id']);
        if (!$user) {
            self::logout();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SessionManager::isValidCsrfToken()) {
                self::setFlashMessage('error', 'Invalid security token. Please try again.');
                self::redirect('/profile');
                return;
            }

            $errors = self::validateProfileData($_POST);
            
            if (empty($errors)) {
                $updateData = [
                    'first_name' => trim($_POST['first_name']),
                    'last_name' => trim($_POST['last_name']),
                    'email' => trim($_POST['email']),
                    'preferred_language' => $_POST['preferred_language']
                ];

                if (User::updateProfile($user->id, $updateData)) {
                    self::setFlashMessage('success', 'Profile updated successfully.');
                    
                    // Update language session if changed
                    if ($updateData['preferred_language'] !== $user->preferred_language) {
                        $_SESSION['language'] = $updateData['preferred_language'];
                        LanguageController::setCurrentLanguage($updateData['preferred_language']);
                    }
                } else {
                    self::setFlashMessage('error', 'Profile update failed. Please try again.');
                }
                
                self::redirect('/profile');
            } else {
                foreach ($errors as $error) {
                    self::setFlashMessage('error', $error);
                }
            }
        }

        $user = User::findById($_SESSION['user_id']); // Reload user data
        
        self::renderView('profile', [
            'user' => $user,
            'languages' => self::getAvailableLanguages(),
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Handle password change
     * 
     * @return void
     */
    public static function changePassword(): void
    {
        SessionManager::StartSession();
        
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = '/change-password';
            self::redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SessionManager::isValidCsrfToken()) {
                self::setFlashMessage('error', 'Invalid security token. Please try again.');
                self::redirect('/change-password');
                return;
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];
            
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }

            if (empty($errors)) {
                if (User::changePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
                    self::setFlashMessage('success', 'Password changed successfully.');
                    self::redirect('/profile');
                } else {
                    self::setFlashMessage('error', 'Current password is incorrect.');
                }
            } else {
                foreach ($errors as $error) {
                    self::setFlashMessage('error', $error);
                }
            }
        }

        self::renderView('change-password', [
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Display subscription management page
     * 
     * @return void
     */
    public static function subscription(): void
    {
        SessionManager::StartSession();
        
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = '/subscription';
            self::redirect('/login');
            return;
        }

        $user = User::findById($_SESSION['user_id']);
        $currentSubscription = UserSubscription::getUserActiveSubscription($user->id);
        $availablePlans = SubscriptionPlan::getAllPlans();

        self::renderView('subscription', [
            'user' => $user,
            'current_subscription' => $currentSubscription,
            'current_plan' => $currentSubscription ? $currentSubscription->getPlan() : null,
            'available_plans' => $availablePlans
        ]);
    }

    /**
     * Admin dashboard for user management
     * 
     * @return void
     */
    public static function adminDashboard(): void
    {
        SessionManager::StartSession();
        
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = '/admin';
            self::redirect('/login');
            return;
        }

        $user = User::findById($_SESSION['user_id']);
        if (!$user->hasPermission('admin_dashboard')) {
            self::setFlashMessage('error', 'Access denied. Administrator privileges required.');
            self::redirect('/dashboard');
            return;
        }

        // Get user statistics and data for admin dashboard
        $stats = self::getAdminStats();
        $recentUsers = self::getRecentUsers();
        $roles = Role::getAllRoles();
        $subscriptionPlans = SubscriptionPlan::getAllPlans();

        self::renderView('admin/dashboard', [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'roles' => $roles,
            'subscription_plans' => $subscriptionPlans
        ]);
    }

    /**
     * Validate registration data
     * 
     * @param array $data POST data
     * @return array Array of error messages
     */
    private static function validateRegistrationData(array $data): array
    {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors[] = 'Username is required.';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        } elseif (User::usernameExists($data['username'])) {
            $errors[] = 'Username already exists.';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif (User::emailExists($data['email'])) {
            $errors[] = 'Email already exists.';
        }

        // Password validation
        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        }

        if ($data['password'] !== ($data['confirm_password'] ?? '')) {
            $errors[] = 'Password and confirmation do not match.';
        }

        // Name validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required.';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required.';
        }

        return $errors;
    }

    /**
     * Validate profile data
     * 
     * @param array $data POST data
     * @return array Array of error messages
     */
    private static function validateProfileData(array $data): array
    {
        $errors = [];

        // Email validation
        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } else {
            // Check if email exists for another user
            $currentUser = User::findById($_SESSION['user_id']);
            if ($data['email'] !== $currentUser->email && User::emailExists($data['email'])) {
                $errors[] = 'Email already exists.';
            }
        }

        // Name validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required.';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required.';
        }

        return $errors;
    }

    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) && 
               (time() - $_SESSION['login_time']) < SESSION_TIMEOUT;
    }

    /**
     * Get currently logged in user
     * 
     * @return User|null User object or null if not logged in
     */
    public static function getCurrentUser(): ?User
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return User::findById($_SESSION['user_id']);
    }

    /**
     * Check if current user has permission
     * 
     * @param string $permission Permission name
     * @return bool True if has permission, false otherwise
     */
    public static function hasPermission(string $permission): bool
    {
        $user = self::getCurrentUser();
        return $user ? $user->hasPermission($permission) : false;
    }

    /**
     * Check if current user has role
     * 
     * @param string $role Role name
     * @return bool True if has role, false otherwise
     */
    public static function hasRole(string $role): bool
    {
        $user = self::getCurrentUser();
        return $user ? $user->hasRole($role) : false;
    }

    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     * @return void
     */
    private static function setFlashMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get and clear flash messages
     * 
     * @return array Array of flash messages
     */
    public static function getFlashMessages(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    private static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Render view
     * 
     * @param string $view View name
     * @param array $data Data to pass to view
     * @return void
     */
    private static function renderView(string $view, array $data = []): void
    {
        // Load language translations
        $currentLanguageCode = LanguageModel::getCurrentLanguageCode();
        $t = LanguageModel::loadLanguageTranslations($currentLanguageCode);
        
        // Add common data
        $data['t'] = $t;
        $data['current_language'] = $currentLanguageCode;
        $data['flash_messages'] = self::getFlashMessages();
        $data['current_user'] = self::getCurrentUser();
        
        // Extract data for view
        extract($data);
        
        // Include view file
        $viewFile = APP_ROOT . DS . 'resources' . DS . 'views' . DS . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }

    /**
     * Get available languages for forms
     * 
     * @return array Array of languages
     */
    private static function getAvailableLanguages(): array
    {
        // Load from resources/languages.php
        $languagesFile = APP_ROOT . DS . 'resources' . DS . 'languages.php';
        if (file_exists($languagesFile)) {
            return include $languagesFile;
        }
        return [];
    }

    /**
     * Get admin statistics
     * 
     * @return array Statistics data
     */
    private static function getAdminStats(): array
    {
        // This would typically be implemented with more sophisticated queries
        return [
            'total_users' => 0, // Implement actual counting
            'active_subscriptions' => 0,
            'total_articles' => 0,
            'total_translations' => 0
        ];
    }

    /**
     * Get recent users for admin dashboard
     * 
     * @return array Recent users data
     */
    private static function getRecentUsers(): array
    {
        // This would typically be implemented with database queries
        return [];
    }
}