<?php

declare(strict_types=1);

namespace KidneyTales\Models;

use PDO;
use PDOException;

/**
 * SubscriptionPlan Model for Kidney Tales
 * 
 * Manages subscription plans for the multilingual blog system.
 * Supports different access levels and features for each plan.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Models
 * @version 2025.10.1
 */
class SubscriptionPlan
{
    private static ?PDO $db = null;
    
    public int $id;
    public string $name;
    public string $display_name;
    public string $description;
    public float $price_monthly;
    public float $price_yearly;
    public int $max_articles_per_month;
    public int $max_translations_per_month;
    public bool $premium_content_access;
    public bool $translation_tools_access;
    public bool $priority_support;
    public bool $analytics_access;
    public bool $api_access;
    public bool $is_active;
    public int $sort_order;
    public string $created_at;
    public string $updated_at;

    // Plan constants
    public const FREE = 'free';
    public const BASIC = 'basic';
    public const PREMIUM = 'premium';
    public const PROFESSIONAL = 'professional';
    public const ENTERPRISE = 'enterprise';

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
     * Get all active subscription plans
     * 
     * @return array Array of SubscriptionPlan objects
     */
    public static function getAllPlans(): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order, price_monthly";
            $stmt = $db->query($sql);
            $plansData = $stmt->fetchAll();

            $plans = [];
            foreach ($plansData as $planData) {
                $plans[] = self::createFromArray($planData);
            }

            return $plans;
        } catch (PDOException $e) {
            error_log("Get all subscription plans failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find subscription plan by ID
     * 
     * @param int $id Plan ID
     * @return SubscriptionPlan|null Plan object or null if not found
     */
    public static function findById(int $id): ?SubscriptionPlan
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $planData = $stmt->fetch();

            return $planData ? self::createFromArray($planData) : null;
        } catch (PDOException $e) {
            error_log("Find subscription plan by ID failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find subscription plan by name
     * 
     * @param string $name Plan name
     * @return SubscriptionPlan|null Plan object or null if not found
     */
    public static function findByName(string $name): ?SubscriptionPlan
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM subscription_plans WHERE name = ? AND is_active = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name]);
            $planData = $stmt->fetch();

            return $planData ? self::createFromArray($planData) : null;
        } catch (PDOException $e) {
            error_log("Find subscription plan by name failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create SubscriptionPlan object from array data
     * 
     * @param array $data Plan data from database
     * @return SubscriptionPlan Plan object
     */
    private static function createFromArray(array $data): SubscriptionPlan
    {
        $plan = new self();
        $plan->id = (int)$data['id'];
        $plan->name = $data['name'];
        $plan->display_name = $data['display_name'];
        $plan->description = $data['description'];
        $plan->price_monthly = (float)$data['price_monthly'];
        $plan->price_yearly = (float)$data['price_yearly'];
        $plan->max_articles_per_month = (int)$data['max_articles_per_month'];
        $plan->max_translations_per_month = (int)$data['max_translations_per_month'];
        $plan->premium_content_access = (bool)$data['premium_content_access'];
        $plan->translation_tools_access = (bool)$data['translation_tools_access'];
        $plan->priority_support = (bool)$data['priority_support'];
        $plan->analytics_access = (bool)$data['analytics_access'];
        $plan->api_access = (bool)$data['api_access'];
        $plan->is_active = (bool)$data['is_active'];
        $plan->sort_order = (int)$data['sort_order'];
        $plan->created_at = $data['created_at'];
        $plan->updated_at = $data['updated_at'];
        
        return $plan;
    }

    /**
     * Get formatted monthly price
     * 
     * @return string Formatted price
     */
    public function getFormattedMonthlyPrice(): string
    {
        return $this->price_monthly > 0 ? '$' . number_format($this->price_monthly, 2) : 'Free';
    }

    /**
     * Get formatted yearly price
     * 
     * @return string Formatted price
     */
    public function getFormattedYearlyPrice(): string
    {
        return $this->price_yearly > 0 ? '$' . number_format($this->price_yearly, 2) : 'Free';
    }

    /**
     * Get yearly savings amount
     * 
     * @return float Savings amount
     */
    public function getYearlySavings(): float
    {
        if ($this->price_monthly <= 0 || $this->price_yearly <= 0) {
            return 0;
        }
        return ($this->price_monthly * 12) - $this->price_yearly;
    }

    /**
     * Get yearly savings percentage
     * 
     * @return int Savings percentage
     */
    public function getYearlySavingsPercentage(): int
    {
        $savings = $this->getYearlySavings();
        if ($savings <= 0 || $this->price_monthly <= 0) {
            return 0;
        }
        return (int)round(($savings / ($this->price_monthly * 12)) * 100);
    }
}

/**
 * UserSubscription Model for Kidney Tales
 * 
 * Manages user subscriptions, billing cycles, and usage tracking.
 * 
 * @author Ľubomír Polaščín
 * @package KidneyTales\Models
 * @version 2025.10.1
 */
class UserSubscription
{
    private static ?PDO $db = null;
    
    public int $id;
    public int $user_id;
    public int $subscription_plan_id;
    public string $status;
    public string $billing_cycle;
    public string $start_date;
    public ?string $end_date;
    public ?string $next_billing_date;
    public float $amount_paid;
    public string $currency;
    public ?string $payment_method;
    public ?string $payment_provider_id;
    public int $articles_used_this_month;
    public int $translations_used_this_month;
    public string $usage_reset_date;
    public bool $auto_renew;
    public string $created_at;
    public string $updated_at;

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';

    // Billing cycle constants
    public const BILLING_MONTHLY = 'monthly';
    public const BILLING_YEARLY = 'yearly';
    public const BILLING_LIFETIME = 'lifetime';

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
     * Get user's active subscription
     * 
     * @param int $userId User ID
     * @return UserSubscription|null Active subscription or null if not found
     */
    public static function getUserActiveSubscription(int $userId): ?UserSubscription
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM user_subscriptions 
                    WHERE user_id = ? AND status = ? 
                    AND (end_date IS NULL OR end_date > NOW())
                    ORDER BY created_at DESC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, self::STATUS_ACTIVE]);
            $subscriptionData = $stmt->fetch();

            return $subscriptionData ? self::createFromArray($subscriptionData) : null;
        } catch (PDOException $e) {
            error_log("Get user active subscription failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new subscription
     * 
     * @param array $subscriptionData Subscription data array
     * @return int|false Subscription ID on success, false on failure
     */
    public static function create(array $subscriptionData): int|false
    {
        try {
            $db = self::getDb();
            
            // Validate required fields
            $required = ['user_id', 'subscription_plan_id', 'billing_cycle', 'amount_paid'];
            foreach ($required as $field) {
                if (!isset($subscriptionData[$field])) {
                    throw new \InvalidArgumentException("Missing required field: {$field}");
                }
            }

            $sql = "INSERT INTO user_subscriptions (
                user_id, subscription_plan_id, status, billing_cycle, start_date, 
                end_date, next_billing_date, amount_paid, currency, payment_method,
                payment_provider_id, usage_reset_date, auto_renew, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $endDate = null;
            $nextBillingDate = null;
            $usageResetDate = date('Y-m-01'); // Reset on first of month

            if ($subscriptionData['billing_cycle'] === self::BILLING_MONTHLY) {
                $nextBillingDate = date('Y-m-d', strtotime('+1 month'));
            } elseif ($subscriptionData['billing_cycle'] === self::BILLING_YEARLY) {
                $nextBillingDate = date('Y-m-d', strtotime('+1 year'));
            }

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $subscriptionData['user_id'],
                $subscriptionData['subscription_plan_id'],
                $subscriptionData['status'] ?? self::STATUS_ACTIVE,
                $subscriptionData['billing_cycle'],
                $subscriptionData['start_date'] ?? date('Y-m-d'),
                $endDate,
                $nextBillingDate,
                $subscriptionData['amount_paid'],
                $subscriptionData['currency'] ?? 'USD',
                $subscriptionData['payment_method'] ?? null,
                $subscriptionData['payment_provider_id'] ?? null,
                $usageResetDate,
                $subscriptionData['auto_renew'] ?? true
            ]);

            return $result ? (int)$db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Subscription creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update subscription status
     * 
     * @param int $subscriptionId Subscription ID
     * @param string $status New status
     * @return bool Success status
     */
    public static function updateStatus(int $subscriptionId, string $status): bool
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE user_subscriptions SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([$status, $subscriptionId]);
        } catch (PDOException $e) {
            error_log("Subscription status update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment article usage for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function incrementArticleUsage(int $userId): bool
    {
        try {
            $db = self::getDb();
            
            // Reset usage if it's a new month
            self::resetUsageIfNewMonth($userId);
            
            $sql = "UPDATE user_subscriptions 
                    SET articles_used_this_month = articles_used_this_month + 1, updated_at = NOW()
                    WHERE user_id = ? AND status = ?";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([$userId, self::STATUS_ACTIVE]);
        } catch (PDOException $e) {
            error_log("Article usage increment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment translation usage for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function incrementTranslationUsage(int $userId): bool
    {
        try {
            $db = self::getDb();
            
            // Reset usage if it's a new month
            self::resetUsageIfNewMonth($userId);
            
            $sql = "UPDATE user_subscriptions 
                    SET translations_used_this_month = translations_used_this_month + 1, updated_at = NOW()
                    WHERE user_id = ? AND status = ?";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([$userId, self::STATUS_ACTIVE]);
        } catch (PDOException $e) {
            error_log("Translation usage increment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user can create more articles this month
     * 
     * @param int $userId User ID
     * @return bool True if user can create articles, false otherwise
     */
    public static function canCreateArticle(int $userId): bool
    {
        try {
            $subscription = self::getUserActiveSubscription($userId);
            if (!$subscription) {
                return false;
            }

            $plan = SubscriptionPlan::findById($subscription->subscription_plan_id);
            if (!$plan) {
                return false;
            }

            // Unlimited articles for some plans
            if ($plan->max_articles_per_month === -1) {
                return true;
            }

            return $subscription->articles_used_this_month < $plan->max_articles_per_month;
        } catch (\Exception $e) {
            error_log("Article creation check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user can create more translations this month
     * 
     * @param int $userId User ID
     * @return bool True if user can create translations, false otherwise
     */
    public static function canCreateTranslation(int $userId): bool
    {
        try {
            $subscription = self::getUserActiveSubscription($userId);
            if (!$subscription) {
                return false;
            }

            $plan = SubscriptionPlan::findById($subscription->subscription_plan_id);
            if (!$plan) {
                return false;
            }

            // Unlimited translations for some plans
            if ($plan->max_translations_per_month === -1) {
                return true;
            }

            return $subscription->translations_used_this_month < $plan->max_translations_per_month;
        } catch (\Exception $e) {
            error_log("Translation creation check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset usage counters if it's a new month
     * 
     * @param int $userId User ID
     * @return void
     */
    private static function resetUsageIfNewMonth(int $userId): void
    {
        try {
            $db = self::getDb();
            $currentMonth = date('Y-m-01');
            
            $sql = "UPDATE user_subscriptions 
                    SET articles_used_this_month = 0, translations_used_this_month = 0, 
                        usage_reset_date = ?, updated_at = NOW()
                    WHERE user_id = ? AND status = ? AND usage_reset_date < ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$currentMonth, $userId, self::STATUS_ACTIVE, $currentMonth]);
        } catch (PDOException $e) {
            error_log("Usage reset failed: " . $e->getMessage());
        }
    }

    /**
     * Create UserSubscription object from array data
     * 
     * @param array $data Subscription data from database
     * @return UserSubscription Subscription object
     */
    private static function createFromArray(array $data): UserSubscription
    {
        $subscription = new self();
        $subscription->id = (int)$data['id'];
        $subscription->user_id = (int)$data['user_id'];
        $subscription->subscription_plan_id = (int)$data['subscription_plan_id'];
        $subscription->status = $data['status'];
        $subscription->billing_cycle = $data['billing_cycle'];
        $subscription->start_date = $data['start_date'];
        $subscription->end_date = $data['end_date'];
        $subscription->next_billing_date = $data['next_billing_date'];
        $subscription->amount_paid = (float)$data['amount_paid'];
        $subscription->currency = $data['currency'];
        $subscription->payment_method = $data['payment_method'];
        $subscription->payment_provider_id = $data['payment_provider_id'];
        $subscription->articles_used_this_month = (int)$data['articles_used_this_month'];
        $subscription->translations_used_this_month = (int)$data['translations_used_this_month'];
        $subscription->usage_reset_date = $data['usage_reset_date'];
        $subscription->auto_renew = (bool)$data['auto_renew'];
        $subscription->created_at = $data['created_at'];
        $subscription->updated_at = $data['updated_at'];
        
        return $subscription;
    }

    /**
     * Get subscription plan details
     * 
     * @return SubscriptionPlan|null Plan object or null if not found
     */
    public function getPlan(): ?SubscriptionPlan
    {
        return SubscriptionPlan::findById($this->subscription_plan_id);
    }

    /**
     * Check if subscription is active
     * 
     * @return bool True if active, false otherwise
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               ($this->end_date === null || strtotime($this->end_date) > time());
    }

    /**
     * Get days remaining in subscription
     * 
     * @return int|null Days remaining or null for unlimited
     */
    public function getDaysRemaining(): ?int
    {
        if ($this->end_date === null) {
            return null; // Unlimited/active subscription
        }
        
        $endTimestamp = strtotime($this->end_date);
        $currentTimestamp = time();
        
        if ($endTimestamp <= $currentTimestamp) {
            return 0;
        }
        
        return (int)ceil(($endTimestamp - $currentTimestamp) / 86400);
    }
}