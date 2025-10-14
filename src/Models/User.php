<?php

declare(strict_types=1);

namespace KidneyTales\Models;

use PDO;
use PDOException;

/**
 * User Model for Kidney Tales
 * 
 * Handles user authentication, profile management, and database operations
 * with secure password handling and multilingual support.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Models
 * @version 2025.10.1
 */
class User
{
    private static ?PDO $db = null;
    
    public int $id;
    public string $username;
    public string $email;
    public string $first_name;
    public string $last_name;
    public string $preferred_language;
    public int $role_id;
    public ?int $subscription_id;
    public bool $email_verified;
    public bool $is_active;
    public string $created_at;
    public string $updated_at;
    public ?string $last_login;
    public int $login_attempts;
    public ?string $locked_until;

    /**
     * Initialize database connection
     */
    private static function getDb(): PDO
    {
        if (self::$db === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$db = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$db;
    }

    /**
     * Create a new user account
     * 
     * @param array $userData User data array
     * @return int|false User ID on success, false on failure
     */
    public static function create(array $userData): int|false
    {
        try {
            $db = self::getDb();
            
            // Validate required fields
            $required = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new \InvalidArgumentException("Missing required field: {$field}");
                }
            }

            // Check if username or email already exists
            if (self::usernameExists($userData['username'])) {
                throw new \InvalidArgumentException("Username already exists");
            }
            
            if (self::emailExists($userData['email'])) {
                throw new \InvalidArgumentException("Email already exists");
            }

            // Hash password securely
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (
                username, email, password_hash, first_name, last_name, 
                preferred_language, role_id, email_verified, is_active, 
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['first_name'],
                $userData['last_name'],
                $userData['preferred_language'] ?? DEFAULT_LANGUAGE,
                $userData['role_id'] ?? 1, // Default to Reader role
                false, // Email not verified by default
                true   // Active by default
            ]);

            return $result ? (int)$db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user by username/email and password
     * 
     * @param string $identifier Username or email
     * @param string $password Plain text password
     * @return User|false User object on success, false on failure
     */
    public static function authenticate(string $identifier, string $password): User|false
    {
        try {
            $db = self::getDb();
            
            $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$identifier, $identifier]);
            $userData = $stmt->fetch();

            if (!$userData) {
                return false;
            }

            // Check if account is locked
            if ($userData['locked_until'] && strtotime($userData['locked_until']) > time()) {
                throw new \Exception("Account is temporarily locked");
            }

            // Verify password
            if (!password_verify($password, $userData['password_hash'])) {
                self::incrementLoginAttempts($userData['id']);
                return false;
            }

            // Reset login attempts on successful login
            self::resetLoginAttempts($userData['id']);
            self::updateLastLogin($userData['id']);

            return self::createFromArray($userData);
        } catch (PDOException $e) {
            error_log("Authentication failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return User|null User object or null if not found
     */
    public static function findById(int $id): ?User
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM users WHERE id = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $userData = $stmt->fetch();

            return $userData ? self::createFromArray($userData) : null;
        } catch (PDOException $e) {
            error_log("Find user by ID failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return User|null User object or null if not found
     */
    public static function findByUsername(string $username): ?User
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$username]);
            $userData = $stmt->fetch();

            return $userData ? self::createFromArray($userData) : null;
        } catch (PDOException $e) {
            error_log("Find user by username failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $updateData Data to update
     * @return bool Success status
     */
    public static function updateProfile(int $userId, array $updateData): bool
    {
        try {
            $db = self::getDb();
            
            $allowedFields = [
                'first_name', 'last_name', 'email', 'preferred_language'
            ];
            
            $updates = [];
            $values = [];
            
            foreach ($updateData as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "{$field} = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Profile update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool Success status
     */
    public static function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        try {
            $user = self::findById($userId);
            if (!$user) {
                return false;
            }

            $db = self::getDb();
            $sql = "SELECT password_hash FROM users WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $currentHash)) {
                return false;
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([$newHash, $userId]);
        } catch (PDOException $e) {
            error_log("Password change failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @return bool True if exists, false otherwise
     */
    public static function usernameExists(string $username): bool
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Username check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool True if exists, false otherwise
     */
    public static function emailExists(string $email): bool
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Email check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment login attempts and lock account if necessary
     * 
     * @param int $userId User ID
     * @return void
     */
    private static function incrementLoginAttempts(int $userId): void
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);

            // Check if account should be locked
            $sql = "SELECT login_attempts FROM users WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $attempts = $stmt->fetchColumn();

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
                $sql = "UPDATE users SET locked_until = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$lockUntil, $userId]);
            }
        } catch (PDOException $e) {
            error_log("Login attempts increment failed: " . $e->getMessage());
        }
    }

    /**
     * Reset login attempts
     * 
     * @param int $userId User ID
     * @return void
     */
    private static function resetLoginAttempts(int $userId): void
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Login attempts reset failed: " . $e->getMessage());
        }
    }

    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     * @return void
     */
    private static function updateLastLogin(int $userId): void
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Last login update failed: " . $e->getMessage());
        }
    }

    /**
     * Create User object from array data
     * 
     * @param array $data User data from database
     * @return User User object
     */
    private static function createFromArray(array $data): User
    {
        $user = new self();
        $user->id = (int)$data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->preferred_language = $data['preferred_language'];
        $user->role_id = (int)$data['role_id'];
        $user->subscription_id = $data['subscription_id'] ? (int)$data['subscription_id'] : null;
        $user->email_verified = (bool)$data['email_verified'];
        $user->is_active = (bool)$data['is_active'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        $user->last_login = $data['last_login'];
        $user->login_attempts = (int)$data['login_attempts'];
        $user->locked_until = $data['locked_until'];
        
        return $user;
    }

    /**
     * Get user's full name
     * 
     * @return string Full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $roleName Role name to check
     * @return bool True if user has role, false otherwise
     */
    public function hasRole(string $roleName): bool
    {
        return Role::userHasRole($this->id, $roleName);
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permissionName Permission name to check
     * @return bool True if user has permission, false otherwise
     */
    public function hasPermission(string $permissionName): bool
    {
        return Role::userHasPermission($this->id, $permissionName);
    }
}