<?php

declare(strict_types=1);

/**
 * Administrator dashboard. Every route requires an admin or super_admin role,
 * enforced server-side by middleware — never by hiding a menu item.
 */

use App\Core\Router;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

// Permission-scoped groups (see SiteContentSeeder::permissions for the catalogue).
$can = static fn (string $permission): array => [AuthMiddleware::class, PermissionMiddleware::class . ':' . $permission];
$canCsrf = static fn (string $permission): array => [AuthMiddleware::class, PermissionMiddleware::class . ':' . $permission, CsrfMiddleware::class];

$admin = [AuthMiddleware::class, AdminMiddleware::class];
$adminCsrf = [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class];
$superCsrf = [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class];

return static function (Router $router) use ($admin, $adminCsrf, $superCsrf, $can, $canCsrf): void {
    $router->get('/admin', [DashboardController::class, 'index'], $can('dashboard.view'), 'admin.dashboard');

    // --- website content (CMS) ----------------------------------------------
    // Every /admin/cms/{resource} route is additionally checked against the
    // resource's own permission inside ResourceController.
    $router->get('/admin/cms/not-found', [ResourceController::class, 'notFound'], $can('content.manage'), 'admin.cms.404');
    $router->post('/admin/cms/not-found', [ResourceController::class, 'notFound'], $canCsrf('content.manage'), 'admin.cms.404.clear');
    $router->get('/admin/cms/{resource}', [ResourceController::class, 'index'], $can('dashboard.view'), 'admin.cms');
    $router->get('/admin/cms/{resource}/create', [ResourceController::class, 'create'], $can('dashboard.view'), 'admin.cms.create');
    $router->post('/admin/cms/{resource}', [ResourceController::class, 'store'], $canCsrf('dashboard.view'), 'admin.cms.store');
    $router->post('/admin/cms/{resource}/reorder', [ResourceController::class, 'reorder'], $canCsrf('dashboard.view'), 'admin.cms.reorder');
    $router->get('/admin/cms/{resource}/{id}/edit', [ResourceController::class, 'edit'], $can('dashboard.view'), 'admin.cms.edit');
    $router->post('/admin/cms/{resource}/{id}', [ResourceController::class, 'update'], $canCsrf('dashboard.view'), 'admin.cms.update');
    $router->post('/admin/cms/{resource}/{id}/delete', [ResourceController::class, 'destroy'], $canCsrf('dashboard.view'), 'admin.cms.delete');
    $router->post('/admin/cms/{resource}/{id}/revisions/{rev}/restore', [ResourceController::class, 'restoreRevision'], $canCsrf('content.manage'), 'admin.cms.restore');

    $router->get('/admin/menus', [MenuController::class, 'index'], $can('content.manage'), 'admin.menus');
    $router->post('/admin/menus/{location}', [MenuController::class, 'save'], $canCsrf('content.manage'), 'admin.menus.save');
    $router->post('/admin/menus/{location}/reset', [MenuController::class, 'reset'], $canCsrf('content.manage'), 'admin.menus.reset');

    // --- inbox: website form submissions -------------------------------------
    $router->get('/admin/inbox/{box}', [InboxController::class, 'index'], $can('dashboard.view'), 'admin.inbox');
    $router->get('/admin/inbox/{box}/export', [InboxController::class, 'export'], $can('dashboard.view'), 'admin.inbox.export');
    $router->get('/admin/inbox/{box}/{id}', [InboxController::class, 'show'], $can('dashboard.view'), 'admin.inbox.show');
    $router->get('/admin/inbox/{box}/{id}/resume', [InboxController::class, 'resume'], $can('volunteers.manage'), 'admin.inbox.resume');
    $router->post('/admin/inbox/{box}/{id}', [InboxController::class, 'update'], $canCsrf('dashboard.view'), 'admin.inbox.update');
    $router->post('/admin/inbox/{box}/{id}/delete', [InboxController::class, 'destroy'], $canCsrf('dashboard.view'), 'admin.inbox.delete');

    // --- campaigns ----------------------------------------------------------
    $router->get('/admin/campaigns', [CampaignController::class, 'index'], $admin, 'admin.campaigns');
    $router->get('/admin/campaigns/create', [CampaignController::class, 'create'], $admin, 'admin.campaigns.create');
    $router->post('/admin/campaigns', [CampaignController::class, 'store'], $adminCsrf, 'admin.campaigns.store');
    $router->get('/admin/campaigns/{id}/edit', [CampaignController::class, 'edit'], $admin, 'admin.campaigns.edit');
    $router->post('/admin/campaigns/{id}', [CampaignController::class, 'update'], $adminCsrf, 'admin.campaigns.update');
    $router->post('/admin/campaigns/{id}/delete', [CampaignController::class, 'destroy'], $adminCsrf, 'admin.campaigns.delete');

    // --- backups ------------------------------------------------------------
    $router->get('/admin/backups', [BackupController::class, 'index'], $can('backups.manage'), 'admin.backups');
    $router->post('/admin/backups/run', [BackupController::class, 'run'], $canCsrf('backups.manage'), 'admin.backups.run');
    $router->get('/admin/backups/{id}/download', [BackupController::class, 'download'], $can('backups.manage'), 'admin.backups.download');
    $router->post('/admin/backups/{id}/delete', [BackupController::class, 'delete'], $canCsrf('backups.manage'), 'admin.backups.delete');

    // --- finance (reconciliation, adjustments, offline donations) ------------
    $router->get('/admin/finance', [FinanceController::class, 'index'], $can('finance.manage'), 'admin.finance');
    $router->get('/admin/finance/export', [FinanceController::class, 'export'], $can('finance.manage'), 'admin.finance.export');
    $router->post('/admin/finance/reconcile', [FinanceController::class, 'reconcile'], $canCsrf('finance.manage'), 'admin.finance.reconcile');
    $router->post('/admin/finance/sync-pending', [FinanceController::class, 'syncPending'], $canCsrf('finance.manage'), 'admin.finance.sync');
    $router->get('/admin/finance/reconciliations/{id}', [FinanceController::class, 'showRun'], $can('finance.manage'), 'admin.finance.run');
    $router->post('/admin/finance/items/{id}/resolve', [FinanceController::class, 'resolveItem'], $canCsrf('finance.manage'), 'admin.finance.resolve');
    $router->get('/admin/finance/offline', [FinanceController::class, 'offlineForm'], $can('finance.manage'), 'admin.finance.offline');
    $router->post('/admin/finance/offline', [FinanceController::class, 'offlineStore'], $canCsrf('finance.manage'), 'admin.finance.offline.store');
    $router->get('/admin/finance/donations/{id}/adjust', [FinanceController::class, 'adjustForm'], $can('finance.manage'), 'admin.finance.adjust');
    $router->post('/admin/finance/donations/{id}/adjust', [FinanceController::class, 'adjustStore'], $canCsrf('finance.manage'), 'admin.finance.adjust.store');
    $router->post('/admin/finance/donations/{id}/receipt', [FinanceController::class, 'sendReceipt'], $canCsrf('finance.manage'), 'admin.finance.receipt');

    // --- donations and payments ---------------------------------------------
    $router->get('/admin/donations', [DonationController::class, 'index'], $can('donations.view'), 'admin.donations');
    $router->get('/admin/donations/export', [DonationController::class, 'export'], $can('reports.view'), 'admin.donations.export');
    $router->get('/admin/donations/{id}', [DonationController::class, 'show'], $can('donations.view'), 'admin.donations.show');
    $router->post('/admin/donations/{id}/reconcile', [DonationController::class, 'reconcile'], $canCsrf('donations.manage'), 'admin.donations.reconcile');
    $router->post('/admin/donations/{id}/refund', [DonationController::class, 'refund'], $canCsrf('donations.manage'), 'admin.donations.refund');
    $router->post('/admin/transactions/{id}/reconcile', [DonationController::class, 'reconcileTransaction'], $canCsrf('donations.manage'), 'admin.transactions.reconcile');

    $router->get('/admin/payment-callbacks', [DonationController::class, 'callbacks'], $can('logs.view'), 'admin.callbacks');

    // --- users --------------------------------------------------------------
    $router->get('/admin/users', [UserController::class, 'index'], $admin, 'admin.users');
    $router->get('/admin/users/{id}', [UserController::class, 'show'], $admin, 'admin.users.show');
    $router->post('/admin/users/{id}/suspend', [UserController::class, 'suspend'], $adminCsrf, 'admin.users.suspend');
    $router->post('/admin/users/{id}/reactivate', [UserController::class, 'reactivate'], $adminCsrf, 'admin.users.reactivate');
    $router->post('/admin/users/{id}/verify-email', [UserController::class, 'forceVerify'], $adminCsrf, 'admin.users.verify');
    $router->post('/admin/users/{id}/force-password-reset', [UserController::class, 'forcePasswordReset'], $adminCsrf, 'admin.users.reset');
    $router->post('/admin/users/{id}/roles', [UserController::class, 'updateRoles'], $superCsrf, 'admin.users.roles');

    $router->get('/admin/donors', [UserController::class, 'donors'], $can('donations.view'), 'admin.donors');

    // --- content ------------------------------------------------------------
    $router->get('/admin/content', [ContentController::class, 'index'], $can('content.manage'), 'admin.content');
    $router->post('/admin/content/{block}', [ContentController::class, 'saveBlock'], $canCsrf('content.manage'), 'admin.content.save');
    $router->post('/admin/faqs', [ContentController::class, 'saveFaq'], $canCsrf('content.manage'), 'admin.faqs.save');
    $router->post('/admin/faqs/{id}/delete', [ContentController::class, 'deleteFaq'], $canCsrf('content.manage'), 'admin.faqs.delete');

    // --- email --------------------------------------------------------------
    $router->get('/admin/email-templates', [EmailController::class, 'templates'], $admin, 'admin.email.templates');
    $router->post('/admin/email-templates/apply-design', [EmailController::class, 'applyDesign'], $adminCsrf, 'admin.email.templates.design');
    $router->get('/admin/email-templates/{id}', [EmailController::class, 'editTemplate'], $admin, 'admin.email.templates.edit');
    $router->post('/admin/email-templates/{id}', [EmailController::class, 'saveTemplate'], $adminCsrf, 'admin.email.templates.update');
    $router->post('/admin/email-templates/{id}/reset', [EmailController::class, 'resetTemplate'], $adminCsrf, 'admin.email.templates.reset');
    $router->get('/admin/email-queue', [EmailController::class, 'queue'], $can('logs.view'), 'admin.email.queue');
    $router->post('/admin/email-queue/{id}/retry', [EmailController::class, 'retry'], $canCsrf('logs.view'), 'admin.email.queue.retry');

    // --- settings -----------------------------------------------------------
    $router->get('/admin/settings', [SettingsController::class, 'index'], $admin, 'admin.settings');
    $router->post('/admin/settings/gateway/{code}', [SettingsController::class, 'saveGateway'], $superCsrf, 'admin.settings.gateway');
    $router->post('/admin/settings/order-number', [SettingsController::class, 'saveOrderNumber'], $superCsrf, 'admin.settings.order_number');
    $router->post('/admin/settings/smtp', [SettingsController::class, 'saveSmtp'], $superCsrf, 'admin.settings.smtp');
    $router->post('/admin/settings/smtp/test-connection', [SettingsController::class, 'testSmtp'], $superCsrf, 'admin.settings.smtp.test');
    $router->post('/admin/settings/smtp/send-test', [SettingsController::class, 'sendTestEmail'], $superCsrf, 'admin.settings.smtp.send');
    $router->post('/admin/settings/site', [SettingsController::class, 'saveSite'], $adminCsrf, 'admin.settings.site');
    $router->post('/admin/settings/branding', [SettingsController::class, 'saveBranding'], $adminCsrf, 'admin.settings.branding');
    $router->post('/admin/settings/gateway/{code}/test', [SettingsController::class, 'testGateway'], $superCsrf, 'admin.settings.gateway.test');

    // --- audit --------------------------------------------------------------
    $router->get('/admin/audit', [AuditController::class, 'index'], $can('logs.view'), 'admin.audit');
};
