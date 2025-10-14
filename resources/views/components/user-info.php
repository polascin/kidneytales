<?php
// User Information Component for Kidney Tales Header
// File: resources/views/components/user-info.php

declare(strict_types=1);

use KidneyTales\Controllers\UserController;
use KidneyTales\Controllers\AuthenticationManager;
use KidneyTales\Models\Role;
use KidneyTales\Models\UserSubscription;

// Get current user information
$currentUser = UserController::getCurrentUser();
$isLoggedIn = UserController::isLoggedIn();

if ($isLoggedIn && $currentUser) {
    // Get user role
    $userRole = Role::getUserRole($currentUser->id);
    $roleName = $userRole ? $userRole->display_name : ($t['guest'] ?? 'Guest');
    
    // Get subscription info
    $subscription = UserSubscription::getUserActiveSubscription($currentUser->id);
    $subscriptionPlan = $subscription ? $subscription->getPlan() : null;
    
    // Get user avatar initials
    $userInitials = strtoupper(substr($currentUser->first_name, 0, 1) . substr($currentUser->last_name, 0, 1));
    
    // Check if user has unread notifications (placeholder for future feature)
    $hasNotifications = false; // This would be implemented with a notifications system
    
    // Get quick access permissions
    $canCreateContent = AuthenticationManager::canCreateContent();
    $canTranslate = AuthenticationManager::canTranslate();
    $canAccessAdmin = AuthenticationManager::canAccessAdmin();
}
?>

<div class="userinfo">
    <?php if ($isLoggedIn && $currentUser): ?>
        <!-- Logged in user information -->
        <div class="user-info-header">
            <div class="user-avatar-small">
                <?= htmlspecialchars($userInitials) ?>
                <?php if ($hasNotifications): ?>
                    <span class="notification-badge"></span>
                <?php endif; ?>
            </div>
            <div class="user-info-text">
                <div class="user-welcome">
                    <span class="description"><?= htmlspecialchars($t['welcome'] ?? 'Welcome') ?>:</span>
                    <strong><?= htmlspecialchars($currentUser->first_name) ?></strong>
                </div>
            </div>
        </div>
        
        <div class="user-info-details">
            <div>
                <span class="description"><?= htmlspecialchars($t['user'] ?? 'User') ?>:</span>
                <span class="user-value"><?= htmlspecialchars($currentUser->username) ?></span>
            </div>
            <div>
                <span class="description"><?= htmlspecialchars($t['name'] ?? 'Name') ?>:</span>
                <span class="user-value"><?= htmlspecialchars($currentUser->getFullName()) ?></span>
            </div>
            <div>
                <span class="description"><?= htmlspecialchars($t['user_email'] ?? 'Email') ?>:</span>
                <span class="user-value"><?= htmlspecialchars($currentUser->email) ?></span>
            </div>
            <div>
                <span class="description"><?= htmlspecialchars($t['role'] ?? 'Role') ?>:</span>
                <span class="user-value role-<?= htmlspecialchars(strtolower($userRole ? $userRole->name : 'guest')) ?>">
                    <?= htmlspecialchars($roleName) ?>
                </span>
            </div>
            
            <?php if ($subscription && $subscriptionPlan): ?>
            <div>
                <span class="description"><?= htmlspecialchars($t['subscription'] ?? 'Plan') ?>:</span>
                <span class="user-value subscription-<?= htmlspecialchars(strtolower($subscriptionPlan->name)) ?>">
                    <?= htmlspecialchars($subscriptionPlan->display_name) ?>
                </span>
            </div>
            <?php endif; ?>
            
            <div>
                <span class="description"><?= htmlspecialchars($t['last_login'] ?? 'Last Login') ?>:</span>
                <span class="user-value">
                    <?= $currentUser->last_login ? htmlspecialchars(date('M j, H:i', strtotime($currentUser->last_login))) : htmlspecialchars($t['never'] ?? 'Never') ?>
                </span>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="user-quick-actions">
            <div class="quick-actions-row">
                <a href="/dashboard" class="quick-action-btn" title="<?= htmlspecialchars($t['dashboard'] ?? 'Dashboard') ?>">
                    <span class="quick-action-icon">🏠</span>
                </a>
                <a href="/profile" class="quick-action-btn" title="<?= htmlspecialchars($t['profile'] ?? 'Profile') ?>">
                    <span class="quick-action-icon">👤</span>
                </a>
                <?php if ($canCreateContent): ?>
                <a href="/create-article" class="quick-action-btn" title="<?= htmlspecialchars($t['create_article'] ?? 'Create Article') ?>">
                    <span class="quick-action-icon">✏️</span>
                </a>
                <?php endif; ?>
                <?php if ($canTranslate): ?>
                <a href="/translations" class="quick-action-btn" title="<?= htmlspecialchars($t['translations'] ?? 'Translations') ?>">
                    <span class="quick-action-icon">🌐</span>
                </a>
                <?php endif; ?>
                <?php if ($canAccessAdmin): ?>
                <a href="/admin" class="quick-action-btn admin-action" title="<?= htmlspecialchars($t['admin'] ?? 'Admin') ?>">
                    <span class="quick-action-icon">⚙️</span>
                </a>
                <?php endif; ?>
                <a href="/logout" class="quick-action-btn logout-action" title="<?= htmlspecialchars($t['logout'] ?? 'Logout') ?>">
                    <span class="quick-action-icon">🚪</span>
                </a>
            </div>
        </div>
        
        <!-- Usage Stats (if subscription has limits) -->
        <?php if ($subscription && $subscriptionPlan && ($subscriptionPlan->max_articles_per_month > 0 || $subscriptionPlan->max_translations_per_month > 0)): ?>
        <div class="user-usage-stats">
            <?php if ($subscriptionPlan->max_articles_per_month > 0): ?>
            <div class="usage-stat">
                <span class="usage-label"><?= htmlspecialchars($t['articles'] ?? 'Articles') ?>:</span>
                <span class="usage-value">
                    <?= $subscription->articles_used_this_month ?>/<?= $subscriptionPlan->max_articles_per_month ?>
                </span>
                <div class="usage-bar">
                    <div class="usage-bar-fill" style="width: <?= min(100, ($subscription->articles_used_this_month / $subscriptionPlan->max_articles_per_month) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($subscriptionPlan->max_translations_per_month > 0): ?>
            <div class="usage-stat">
                <span class="usage-label"><?= htmlspecialchars($t['translations'] ?? 'Translations') ?>:</span>
                <span class="usage-value">
                    <?= $subscription->translations_used_this_month ?>/<?= $subscriptionPlan->max_translations_per_month ?>
                </span>
                <div class="usage-bar">
                    <div class="usage-bar-fill" style="width: <?= min(100, ($subscription->translations_used_this_month / $subscriptionPlan->max_translations_per_month) * 100) ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Guest user information -->
        <div class="guest-info">
            <div>
                <span class="description"><?= htmlspecialchars($t['user_information'] ?? 'User Information') ?></span>
            </div>
            <div>
                <span class="description"><?= htmlspecialchars($t['status'] ?? 'Status') ?>:</span>
                <span class="user-value guest-status"><?= htmlspecialchars($t['guest'] ?? 'Guest') ?></span>
            </div>
            <div class="guest-actions">
                <a href="/login" class="guest-action-btn login-btn">
                    <?= htmlspecialchars($t['login'] ?? 'Login') ?>
                </a>
                <a href="/register" class="guest-action-btn register-btn">
                    <?= htmlspecialchars($t['register'] ?? 'Register') ?>
                </a>
            </div>
            <div class="guest-info-text">
                <small><?= htmlspecialchars($t['login_for_features'] ?? 'Login to access all features') ?></small>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* User Information Styles */
.userinfo {
    text-align: right;
    font-size: 0.85rem;
    max-width: 280px;
}

.user-info-header {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    background: #007cba;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.7rem;
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    background: #dc3545;
    border-radius: 50%;
    border: 1px solid white;
}

.user-info-text .user-welcome {
    font-size: 0.9rem;
}

.user-info-details > div {
    margin-bottom: 0.2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-value {
    font-weight: 500;
    margin-left: 0.5rem;
}

.role-administrator {
    color: #dc3545;
    font-weight: bold;
}

.role-editor {
    color: #fd7e14;
    font-weight: bold;
}

.role-creator {
    color: #198754;
    font-weight: bold;
}

.role-translator {
    color: #6f42c1;
    font-weight: bold;
}

.role-reader {
    color: #6c757d;
}

.subscription-enterprise {
    color: #dc3545;
    font-weight: bold;
}

.subscription-professional {
    color: #fd7e14;
    font-weight: bold;
}

.subscription-premium {
    color: #198754;
    font-weight: bold;
}

.subscription-basic {
    color: #0d6efd;
}

.subscription-free {
    color: #6c757d;
}

.user-quick-actions {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #eee;
}

.quick-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.quick-action-btn {
    display: inline-block;
    width: 24px;
    height: 24px;
    text-align: center;
    line-height: 24px;
    border-radius: 4px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: #007cba;
    border-color: #007cba;
    transform: translateY(-1px);
}

.quick-action-btn.admin-action:hover {
    background: #dc3545;
    border-color: #dc3545;
}

.quick-action-btn.logout-action:hover {
    background: #6c757d;
    border-color: #6c757d;
}

.quick-action-icon {
    font-size: 0.7rem;
    display: block;
}

.user-usage-stats {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #eee;
}

.usage-stat {
    margin-bottom: 0.3rem;
}

.usage-stat:last-child {
    margin-bottom: 0;
}

.usage-label,
.usage-value {
    font-size: 0.75rem;
}

.usage-value {
    float: right;
    font-weight: 500;
}

.usage-bar {
    width: 100%;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    margin-top: 0.2rem;
    overflow: hidden;
}

.usage-bar-fill {
    height: 100%;
    background: #007cba;
    border-radius: 2px;
    transition: width 0.3s ease;
}

/* Guest styles */
.guest-info {
    text-align: right;
}

.guest-status {
    color: #6c757d;
    font-style: italic;
}

.guest-actions {
    margin: 0.5rem 0;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.guest-action-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid;
    transition: all 0.3s ease;
}

.login-btn {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.login-btn:hover {
    background: #005a87;
    border-color: #005a87;
}

.register-btn {
    background: #198754;
    color: white;
    border-color: #198754;
}

.register-btn:hover {
    background: #146c43;
    border-color: #146c43;
}

.guest-info-text {
    margin-top: 0.3rem;
    color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .userinfo {
        max-width: 200px;
        font-size: 0.75rem;
    }
    
    .user-info-details > div {
        flex-direction: column;
        align-items: flex-end;
    }
    
    .user-value {
        margin-left: 0;
        margin-top: 0.1rem;
    }
    
    .quick-actions-row {
        justify-content: center;
    }
}
</style>