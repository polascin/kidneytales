<?php
// Dashboard View for Kidney Tales
// File: resources/views/dashboard.php

declare(strict_types=1);

use KidneyTales\Controllers\AuthenticationManager;

$pageTitle = $t['dashboard'] ?? 'Dashboard';
$pageDescription = $t['dashboard_description'] ?? 'Your Kidney Tales dashboard';

// Get user role information
$userRole = $role ?? null;
$userPermissions = $permissions ?? [];
$widgets = AuthenticationManager::getDashboardWidgets();
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars(APP_NAME) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/basic.css">
    <link rel="stylesheet" href="/assets/css/colors.css">
    <link rel="stylesheet" href="/assets/css/font-families.css">
    <link rel="stylesheet" href="/assets/css/language.css">
    
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        
        .dashboard-header h1 {
            color: #333;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            background: #007cba;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .user-details h3 {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .user-details p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .widget {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }
        
        .widget h3 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1.2rem;
        }
        
        .widget-content {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007cba;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            background: #007cba;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .flash-messages {
            margin-bottom: 1rem;
        }
        
        .flash-message {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .flash-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .flash-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .subscription-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .subscription-active {
            background: #d4edda;
            color: #155724;
        }
        
        .subscription-expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: #007cba;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .navigation {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1rem 2rem;
            z-index: 1000;
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-menu {
            display: flex;
            gap: 1rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-menu a {
            color: #333;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-menu a:hover {
            background: #f8f9fa;
        }
        
        body {
            padding-top: 80px; /* Account for fixed navigation */
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navigation">
        <div class="nav-content">
            <div>
                <strong><?= htmlspecialchars(APP_NAME) ?></strong>
            </div>
            <ul class="nav-menu">
                <?php 
                $menuItems = AuthenticationManager::getUserMenu();
                foreach ($menuItems as $item): 
                    if ($item['permission'] === null || AuthenticationManager::canAccessAdmin()): 
                ?>
                    <li><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                <?php 
                    endif;
                endforeach; 
                ?>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><?= htmlspecialchars($t['welcome_dashboard'] ?? 'Welcome to Your Dashboard') ?></h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?= htmlspecialchars(strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1))) ?>
                </div>
                <div class="user-details">
                    <h3><?= htmlspecialchars($user->getFullName()) ?></h3>
                    <p><?= htmlspecialchars($userRole ? $userRole->display_name : 'User') ?></p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flash_messages)): ?>
            <div class="flash-messages">
                <?php foreach ($flash_messages as $message): ?>
                    <div class="flash-message <?= htmlspecialchars($message['type']) ?>">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Widgets -->
        <div class="widgets-grid">
            <!-- Profile Widget -->
            <div class="widget">
                <h3><?= htmlspecialchars($t['profile_summary'] ?? 'Profile Summary') ?></h3>
                <div class="widget-content">
                    <p><strong><?= htmlspecialchars($t['username'] ?? 'Username') ?>:</strong> <?= htmlspecialchars($user->username) ?></p>
                    <p><strong><?= htmlspecialchars($t['email'] ?? 'Email') ?>:</strong> <?= htmlspecialchars($user->email) ?></p>
                    <p><strong><?= htmlspecialchars($t['role'] ?? 'Role') ?>:</strong> <?= htmlspecialchars($userRole ? $userRole->display_name : 'User') ?></p>
                    <p><strong><?= htmlspecialchars($t['member_since'] ?? 'Member Since') ?>:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($user->created_at))) ?></p>
                    
                    <div class="action-buttons" style="margin-top: 1rem;">
                        <a href="/profile" class="btn btn-secondary"><?= htmlspecialchars($t['edit_profile'] ?? 'Edit Profile') ?></a>
                    </div>
                </div>
            </div>

            <!-- Subscription Widget -->
            <?php if ($subscription && $subscription_plan): ?>
            <div class="widget">
                <h3><?= htmlspecialchars($t['subscription_status'] ?? 'Subscription Status') ?></h3>
                <div class="widget-content">
                    <div class="subscription-status subscription-<?= $subscription->isActive() ? 'active' : 'expired' ?>">
                        <?= htmlspecialchars($subscription->status) ?>
                    </div>
                    
                    <p style="margin-top: 1rem;"><strong><?= htmlspecialchars($t['plan'] ?? 'Plan') ?>:</strong> <?= htmlspecialchars($subscription_plan->display_name) ?></p>
                    <p><strong><?= htmlspecialchars($t['billing_cycle'] ?? 'Billing') ?>:</strong> <?= htmlspecialchars($subscription->billing_cycle) ?></p>
                    
                    <?php if ($subscription_plan->max_articles_per_month > 0): ?>
                    <div style="margin-top: 1rem;">
                        <p><strong><?= htmlspecialchars($t['articles_this_month'] ?? 'Articles This Month') ?>:</strong> 
                           <?= $subscription->articles_used_this_month ?> / <?= $subscription_plan->max_articles_per_month ?>
                        </p>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?= min(100, ($subscription->articles_used_this_month / $subscription_plan->max_articles_per_month) * 100) ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($subscription_plan->max_translations_per_month > 0): ?>
                    <div style="margin-top: 1rem;">
                        <p><strong><?= htmlspecialchars($t['translations_this_month'] ?? 'Translations This Month') ?>:</strong> 
                           <?= $subscription->translations_used_this_month ?> / <?= $subscription_plan->max_translations_per_month ?>
                        </p>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?= min(100, ($subscription->translations_used_this_month / $subscription_plan->max_translations_per_month) * 100) ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="action-buttons" style="margin-top: 1rem;">
                        <a href="/subscription" class="btn btn-secondary"><?= htmlspecialchars($t['manage_subscription'] ?? 'Manage Subscription') ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions Widget -->
            <?php if (AuthenticationManager::canCreateContent() || AuthenticationManager::canTranslate()): ?>
            <div class="widget">
                <h3><?= htmlspecialchars($t['quick_actions'] ?? 'Quick Actions') ?></h3>
                <div class="widget-content">
                    <div class="action-buttons">
                        <?php if (AuthenticationManager::canCreateContent()): ?>
                            <a href="/create-article" class="btn"><?= htmlspecialchars($t['create_article'] ?? 'Create Article') ?></a>
                        <?php endif; ?>
                        
                        <?php if (AuthenticationManager::canTranslate()): ?>
                            <a href="/translations" class="btn"><?= htmlspecialchars($t['manage_translations'] ?? 'Manage Translations') ?></a>
                        <?php endif; ?>
                        
                        <a href="/articles" class="btn btn-secondary"><?= htmlspecialchars($t['browse_articles'] ?? 'Browse Articles') ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Admin Tools Widget -->
            <?php if (AuthenticationManager::canAccessAdmin()): ?>
            <div class="widget">
                <h3><?= htmlspecialchars($t['admin_tools'] ?? 'Admin Tools') ?></h3>
                <div class="widget-content">
                    <div class="action-buttons">
                        <a href="/admin" class="btn"><?= htmlspecialchars($t['admin_dashboard'] ?? 'Admin Dashboard') ?></a>
                        
                        <?php if (AuthenticationManager::canManageUsers()): ?>
                            <a href="/admin/users" class="btn btn-secondary"><?= htmlspecialchars($t['manage_users'] ?? 'Manage Users') ?></a>
                        <?php endif; ?>
                        
                        <a href="/admin/analytics" class="btn btn-secondary"><?= htmlspecialchars($t['view_analytics'] ?? 'View Analytics') ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Activity Widget -->
            <div class="widget">
                <h3><?= htmlspecialchars($t['recent_activity'] ?? 'Recent Activity') ?></h3>
                <div class="widget-content">
                    <p><?= htmlspecialchars($t['last_login'] ?? 'Last Login') ?>: 
                       <?= $user->last_login ? htmlspecialchars(date('F j, Y g:i A', strtotime($user->last_login))) : htmlspecialchars($t['never'] ?? 'Never') ?>
                    </p>
                    <p><?= htmlspecialchars($t['account_created'] ?? 'Account Created') ?>: 
                       <?= htmlspecialchars(date('F j, Y', strtotime($user->created_at))) ?>
                    </p>
                    
                    <!-- Placeholder for recent articles/translations -->
                    <div style="margin-top: 1rem;">
                        <p style="color: #666; font-style: italic;">
                            <?= htmlspecialchars($t['recent_content_placeholder'] ?? 'Recent content activity will appear here.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>