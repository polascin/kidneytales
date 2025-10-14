<?php

declare(strict_types=1);

namespace KidneyTales\Models;

use PDO;
use PDOException;

/**
 * Role Model for Kidney Tales
 * 
 * Manages user roles and permissions for the multilingual blog system.
 * Supports reader, translator, creator, editor, and administrator roles.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Models
 * @version 2025.10.1
 */
class Role
{
    private static ?PDO $db = null;
    
    public int $id;
    public string $name;
    public string $display_name;
    public string $description;
    public bool $is_active;
    public string $created_at;
    public string $updated_at;

    // Role constants
    public const READER = 'reader';
    public const TRANSLATOR = 'translator';
    public const CREATOR = 'creator';
    public const EDITOR = 'editor';
    public const ADMINISTRATOR = 'administrator';

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
     * Get all active roles
     * 
     * @return array Array of Role objects
     */
    public static function getAllRoles(): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM roles WHERE is_active = 1 ORDER BY id";
            $stmt = $db->query($sql);
            $rolesData = $stmt->fetchAll();

            $roles = [];
            foreach ($rolesData as $roleData) {
                $roles[] = self::createFromArray($roleData);
            }

            return $roles;
        } catch (PDOException $e) {
            error_log("Get all roles failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find role by ID
     * 
     * @param int $id Role ID
     * @return Role|null Role object or null if not found
     */
    public static function findById(int $id): ?Role
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM roles WHERE id = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $roleData = $stmt->fetch();

            return $roleData ? self::createFromArray($roleData) : null;
        } catch (PDOException $e) {
            error_log("Find role by ID failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find role by name
     * 
     * @param string $name Role name
     * @return Role|null Role object or null if not found
     */
    public static function findByName(string $name): ?Role
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM roles WHERE name = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name]);
            $roleData = $stmt->fetch();

            return $roleData ? self::createFromArray($roleData) : null;
        } catch (PDOException $e) {
            error_log("Find role by name failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has specific role
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @return bool True if user has role, false otherwise
     */
    public static function userHasRole(int $userId, string $roleName): bool
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) FROM users u 
                    JOIN roles r ON u.role_id = r.id 
                    WHERE u.id = ? AND r.name = ? AND u.is_active = 1 AND r.is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $roleName]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("User role check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has specific permission
     * 
     * @param int $userId User ID
     * @param string $permissionName Permission name
     * @return bool True if user has permission, false otherwise
     */
    public static function userHasPermission(int $userId, string $permissionName): bool
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) FROM users u
                    JOIN roles r ON u.role_id = r.id
                    JOIN role_permissions rp ON r.id = rp.role_id
                    JOIN permissions p ON rp.permission_id = p.id
                    WHERE u.id = ? AND p.name = ? 
                    AND u.is_active = 1 AND r.is_active = 1 AND p.is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $permissionName]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("User permission check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's role
     * 
     * @param int $userId User ID
     * @return Role|null User's role or null if not found
     */
    public static function getUserRole(int $userId): ?Role
    {
        try {
            $db = self::getDb();
            $sql = "SELECT r.* FROM roles r
                    JOIN users u ON r.id = u.role_id
                    WHERE u.id = ? AND u.is_active = 1 AND r.is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $roleData = $stmt->fetch();

            return $roleData ? self::createFromArray($roleData) : null;
        } catch (PDOException $e) {
            error_log("Get user role failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get permissions for a role
     * 
     * @param int $roleId Role ID
     * @return array Array of Permission objects
     */
    public static function getRolePermissions(int $roleId): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT p.* FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = ? AND p.is_active = 1
                    ORDER BY p.name";
            $stmt = $db->prepare($sql);
            $stmt->execute([$roleId]);
            $permissionsData = $stmt->fetchAll();

            $permissions = [];
            foreach ($permissionsData as $permissionData) {
                $permissions[] = Permission::createFromArray($permissionData);
            }

            return $permissions;
        } catch (PDOException $e) {
            error_log("Get role permissions failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param int $roleId Role ID
     * @return bool Success status
     */
    public static function assignRoleToUser(int $userId, int $roleId): bool
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE users SET role_id = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([$roleId, $userId]);
        } catch (PDOException $e) {
            error_log("Role assignment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create Role object from array data
     * 
     * @param array $data Role data from database
     * @return Role Role object
     */
    private static function createFromArray(array $data): Role
    {
        $role = new self();
        $role->id = (int)$data['id'];
        $role->name = $data['name'];
        $role->display_name = $data['display_name'];
        $role->description = $data['description'];
        $role->is_active = (bool)$data['is_active'];
        $role->created_at = $data['created_at'];
        $role->updated_at = $data['updated_at'];
        
        return $role;
    }

    /**
     * Get permissions for this role
     * 
     * @return array Array of Permission objects
     */
    public function getPermissions(): array
    {
        return self::getRolePermissions($this->id);
    }
}

/**
 * Permission Model for Kidney Tales
 * 
 * Manages individual permissions that can be assigned to roles.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Models
 * @version 2025.10.1
 */
class Permission
{
    private static ?PDO $db = null;
    
    public int $id;
    public string $name;
    public string $display_name;
    public string $description;
    public string $category;
    public bool $is_active;
    public string $created_at;
    public string $updated_at;

    // Permission categories
    public const CATEGORY_CONTENT = 'content';
    public const CATEGORY_TRANSLATION = 'translation';
    public const CATEGORY_USER = 'user';
    public const CATEGORY_ADMIN = 'admin';
    public const CATEGORY_SUBSCRIPTION = 'subscription';

    // Core permissions
    public const READ_CONTENT = 'read_content';
    public const CREATE_CONTENT = 'create_content';
    public const EDIT_CONTENT = 'edit_content';
    public const DELETE_CONTENT = 'delete_content';
    public const PUBLISH_CONTENT = 'publish_content';
    
    public const TRANSLATE_CONTENT = 'translate_content';
    public const REVIEW_TRANSLATIONS = 'review_translations';
    public const APPROVE_TRANSLATIONS = 'approve_translations';
    
    public const MANAGE_USERS = 'manage_users';
    public const ASSIGN_ROLES = 'assign_roles';
    public const VIEW_USER_PROFILES = 'view_user_profiles';
    
    public const ADMIN_DASHBOARD = 'admin_dashboard';
    public const SYSTEM_SETTINGS = 'system_settings';
    public const VIEW_ANALYTICS = 'view_analytics';
    
    public const MANAGE_SUBSCRIPTIONS = 'manage_subscriptions';
    public const VIEW_SUBSCRIPTION_REPORTS = 'view_subscription_reports';

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
     * Get all active permissions
     * 
     * @return array Array of Permission objects
     */
    public static function getAllPermissions(): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM permissions WHERE is_active = 1 ORDER BY category, name";
            $stmt = $db->query($sql);
            $permissionsData = $stmt->fetchAll();

            $permissions = [];
            foreach ($permissionsData as $permissionData) {
                $permissions[] = self::createFromArray($permissionData);
            }

            return $permissions;
        } catch (PDOException $e) {
            error_log("Get all permissions failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get permissions by category
     * 
     * @param string $category Permission category
     * @return array Array of Permission objects
     */
    public static function getPermissionsByCategory(string $category): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM permissions WHERE category = ? AND is_active = 1 ORDER BY name";
            $stmt = $db->prepare($sql);
            $stmt->execute([$category]);
            $permissionsData = $stmt->fetchAll();

            $permissions = [];
            foreach ($permissionsData as $permissionData) {
                $permissions[] = self::createFromArray($permissionData);
            }

            return $permissions;
        } catch (PDOException $e) {
            error_log("Get permissions by category failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find permission by name
     * 
     * @param string $name Permission name
     * @return Permission|null Permission object or null if not found
     */
    public static function findByName(string $name): ?Permission
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM permissions WHERE name = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name]);
            $permissionData = $stmt->fetch();

            return $permissionData ? self::createFromArray($permissionData) : null;
        } catch (PDOException $e) {
            error_log("Find permission by name failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Permission object from array data
     * 
     * @param array $data Permission data from database
     * @return Permission Permission object
     */
    public static function createFromArray(array $data): Permission
    {
        $permission = new self();
        $permission->id = (int)$data['id'];
        $permission->name = $data['name'];
        $permission->display_name = $data['display_name'];
        $permission->description = $data['description'];
        $permission->category = $data['category'];
        $permission->is_active = (bool)$data['is_active'];
        $permission->created_at = $data['created_at'];
        $permission->updated_at = $data['updated_at'];
        
        return $permission;
    }
}