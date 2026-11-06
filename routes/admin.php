<?php

declare(strict_types=1);

/**
 * Unified staff dashboard. Each restricted page/action is guarded server-side
 * by its role and/or named permission; hiding a sidebar item is not security.
 */

use App\Core\Router;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\DonationReviewController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\GrantController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\StaffMiddleware;

// Permission-scoped groups (see SiteContentSeeder::permissions for the catalogue).
$can = static fn (string $permission): array => [AuthMiddleware::class, PermissionMiddleware::class . ':' . $permission];
$canCsrf = static fn (string $permission): array => [AuthMiddleware::class, PermissionMiddleware::class . ':' . $permission, CsrfMiddleware::class];
$grantAssignCsrf = static fn (string $permission): array => [AuthMiddleware::class, PermissionMiddleware::class . ':grants.view', PermissionMiddleware::class . ':' . $permission, CsrfMiddleware::class];

// Campaigns remain an admin-only function, and also require content permission.
$adminContent = [AuthMiddleware::class, AdminMiddleware::class, PermissionMiddleware::class . ':content.manage'];
$adminContentCsrf = [...$adminContent, CsrfMiddleware::class];
$superCsrf = [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class];

return static function (Router $router) use ($adminContent, $adminContentCsrf, $superCsrf, $can, $canCsrf, $grantAssignCsrf): void {
    $router->get('/admin', [DashboardController::class, 'index'], $can('dashboard.view'), 'admin.dashboard');

    // Minimal grant register; assignment access is independent of grant visibility.
    $router->addPattern('userId','[0-9]+');
    $router->get('/admin/grants', [GrantController::class, 'index'], $can('grants.view'), 'admin.grants');
    $router->get('/admin/grants/create', [GrantController::class, 'form'], $can('grants.create|grants.manage'), 'admin.grants.create');
    $router->post('/admin/grants', [GrantController::class, 'save'], $canCsrf('grants.create|grants.manage'), 'admin.grants.store');
    $router->get('/admin/grants/{id}', [GrantController::class, 'show'], $can('grants.view'), 'admin.grants.show');
    $router->get('/admin/grants/{id}/edit', [GrantController::class, 'form'], [AuthMiddleware::class, PermissionMiddleware::class . ':grants.view', PermissionMiddleware::class . ':grants.edit|grants.manage'], 'admin.grants.edit');
    $router->post('/admin/grants/{id}', [GrantController::class, 'save'], [AuthMiddleware::class, PermissionMiddleware::class . ':grants.view', PermissionMiddleware::class . ':grants.edit|grants.manage', CsrfMiddleware::class], 'admin.grants.update');
    $router->post('/admin/grants/{id}/users', [GrantController::class, 'assignUsers'], $grantAssignCsrf('grant_assignments.create|grant_assignments.manage'), 'admin.grants.users');
    $router->post('/admin/grants/{id}/users/{userId}', [GrantController::class, 'updateAssignment'], $grantAssignCsrf('grant_assignments.edit|grant_assignments.manage'), 'admin.grants.users.update');
    $router->post('/admin/grants/{id}/users/{userId}/change', [GrantController::class, 'changeAssignee'], $grantAssignCsrf('grant_assignments.manage'), 'admin.grants.users.change');
    $router->post('/admin/grants/{id}/users/{userId}/remove', [GrantController::class, 'removeAssignment'], $grantAssignCsrf('grant_assignments.delete|grant_assignments.manage'), 'admin.grants.users.remove');
    $router->post('/admin/users/{id}/grants', [GrantController::class, 'assignGrants'], $grantAssignCsrf('grant_assignments.create|grant_assignments.manage'), 'admin.users.grants');

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
    $router->get('/admin/campaigns', [CampaignController::class, 'index'], $adminContent, 'admin.campaigns');
    $router->get('/admin/campaigns/create', [CampaignController::class, 'create'], $adminContent, 'admin.campaigns.create');
    $router->post('/admin/campaigns', [CampaignController::class, 'store'], $adminContentCsrf, 'admin.campaigns.store');
    $router->get('/admin/campaigns/{id}/edit', [CampaignController::class, 'edit'], $adminContent, 'admin.campaigns.edit');
    $router->post('/admin/campaigns/{id}', [CampaignController::class, 'update'], $adminContentCsrf, 'admin.campaigns.update');
    $router->post('/admin/campaigns/{id}/delete', [CampaignController::class, 'destroy'], $adminContentCsrf, 'admin.campaigns.delete');

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
    $router->post('/admin/donations/{id}/review', [DonationReviewController::class, 'decide'], [AuthMiddleware::class, StaffMiddleware::class, CsrfMiddleware::class], 'admin.donations.review');
    $router->get('/admin/donations/{id}/proof', [DonationReviewController::class, 'proof'], $can('donations.view'), 'admin.donations.proof');
    $router->get('/admin/donations/{id}/documents/{kind}', [DonationReviewController::class, 'document'], $can('donations.view'), 'admin.donations.document');
    $router->post('/admin/donations/{id}/certificate', [DonationReviewController::class, 'certificate'], $superCsrf, 'admin.donations.certificate');
    $router->post('/admin/donations/{id}/correct', [DonationReviewController::class, 'correct'], $superCsrf, 'admin.donations.correct');
    $router->post('/admin/donations/{id}/reconcile', [DonationController::class, 'reconcile'], $canCsrf('donations.manage'), 'admin.donations.reconcile');
    $router->post('/admin/donations/{id}/refund', [DonationController::class, 'refund'], $canCsrf('donations.manage'), 'admin.donations.refund');
    $router->post('/admin/transactions/{id}/reconcile', [DonationController::class, 'reconcileTransaction'], $canCsrf('donations.manage'), 'admin.transactions.reconcile');

    $router->get('/admin/payment-callbacks', [DonationController::class, 'callbacks'], $can('logs.view'), 'admin.callbacks');

    // --- user and role administration -------------------------------------
    $router->get('/admin/users', [UserController::class, 'index'], $can('users.view|users.manage'), 'admin.users');
    $router->get('/admin/users/create', [UserController::class, 'createForm'], $can('users.create|users.manage'), 'admin.users.create');
    $router->post('/admin/users', [UserController::class, 'create'], $canCsrf('users.create|users.manage'), 'admin.users.store');
    $router->get('/admin/users/{id}', [UserController::class, 'show'], $can('users.view|users.manage'), 'admin.users.show');
    $router->post('/admin/users/{id}/edit', [UserController::class, 'edit'], $canCsrf('users.edit|users.manage'), 'admin.users.edit');
    $router->post('/admin/users/{id}/suspend', [UserController::class, 'suspend'], $canCsrf('users.edit|users.manage'), 'admin.users.suspend');
    $router->post('/admin/users/{id}/reactivate', [UserController::class, 'reactivate'], $canCsrf('users.edit|users.manage'), 'admin.users.reactivate');
    $router->post('/admin/users/{id}/verify-email', [UserController::class, 'forceVerify'], $canCsrf('users.edit|users.manage'), 'admin.users.verify');
    $router->post('/admin/users/{id}/force-password-reset', [UserController::class, 'forcePasswordReset'], $canCsrf('users.edit|users.manage'), 'admin.users.reset');
    $router->post('/admin/users/{id}/roles', [UserController::class, 'updateRoles'], $superCsrf, 'admin.users.roles');
    $router->get('/admin/roles', [RoleController::class, 'index'], $can('roles.view|roles.manage'), 'admin.roles');
    $router->get('/admin/roles/create', [RoleController::class, 'form'], $superCsrf, 'admin.roles.create');
    $router->post('/admin/roles', [RoleController::class, 'save'], $superCsrf, 'admin.roles.store');
    $router->get('/admin/roles/{id}/edit', [RoleController::class, 'form'], $can('roles.view|roles.manage'), 'admin.roles.edit');
    $router->post('/admin/roles/{id}', [RoleController::class, 'save'], $superCsrf, 'admin.roles.update');
    $router->post('/admin/roles/{id}/delete', [RoleController::class, 'delete'], $superCsrf, 'admin.roles.delete');
    $router->get('/admin/donors', [UserController::class, 'donors'], $can('donations.view|donors.view'), 'admin.donors');

    // --- content ------------------------------------------------------------
    $router->get('/admin/content', [ContentController::class, 'index'], $can('content.manage'), 'admin.content');
    $router->post('/admin/content/{block}', [ContentController::class, 'saveBlock'], $canCsrf('content.manage'), 'admin.content.save');
    $router->post('/admin/faqs', [ContentController::class, 'saveFaq'], $canCsrf('content.manage'), 'admin.faqs.save');
    $router->post('/admin/faqs/{id}/delete', [ContentController::class, 'deleteFaq'], $canCsrf('content.manage'), 'admin.faqs.delete');

    // --- email --------------------------------------------------------------
    $router->get('/admin/email-templates', [EmailController::class, 'templates'], $can('settings.manage'), 'admin.email.templates');
    $router->post('/admin/email-templates/apply-design', [EmailController::class, 'applyDesign'], $canCsrf('settings.manage'), 'admin.email.templates.design');
    $router->get('/admin/email-templates/{id}', [EmailController::class, 'editTemplate'], $can('settings.manage'), 'admin.email.templates.edit');
    $router->post('/admin/email-templates/{id}', [EmailController::class, 'saveTemplate'], $canCsrf('settings.manage'), 'admin.email.templates.update');
    $router->post('/admin/email-templates/{id}/reset', [EmailController::class, 'resetTemplate'], $canCsrf('settings.manage'), 'admin.email.templates.reset');
    $router->get('/admin/email-queue', [EmailController::class, 'queue'], $can('logs.view'), 'admin.email.queue');
    $router->post('/admin/email-queue/{id}/retry', [EmailController::class, 'retry'], $canCsrf('logs.view'), 'admin.email.queue.retry');

    // --- settings -----------------------------------------------------------
    $router->get('/admin/settings', [SettingsController::class, 'index'], $can('settings.manage'), 'admin.settings');
    $router->post('/admin/settings/gateway/{code}', [SettingsController::class, 'saveGateway'], $superCsrf, 'admin.settings.gateway');
    $router->post('/admin/settings/order-number', [SettingsController::class, 'saveOrderNumber'], $superCsrf, 'admin.settings.order_number');
    $router->post('/admin/settings/smtp', [SettingsController::class, 'saveSmtp'], $superCsrf, 'admin.settings.smtp');
    $router->post('/admin/settings/smtp/test-connection', [SettingsController::class, 'testSmtp'], $superCsrf, 'admin.settings.smtp.test');
    $router->post('/admin/settings/smtp/send-test', [SettingsController::class, 'sendTestEmail'], $superCsrf, 'admin.settings.smtp.send');
    $router->post('/admin/settings/site', [SettingsController::class, 'saveSite'], $canCsrf('settings.manage'), 'admin.settings.site');
    $router->post('/admin/settings/branding', [SettingsController::class, 'saveBranding'], $canCsrf('settings.manage'), 'admin.settings.branding');
    $router->post('/admin/settings/gateway/{code}/test', [SettingsController::class, 'testGateway'], $superCsrf, 'admin.settings.gateway.test');

    // --- reports: read-only; reports.view enforced server-side on pages AND PDFs --
    $router->get('/admin/reports', [ReportController::class, 'overview'], $can('reports.view'), 'admin.reports');
    $router->get('/admin/reports/{slug}', [ReportController::class, 'show'], $can('reports.view'), 'admin.reports.show');
    $router->get('/admin/reports/{slug}/pdf', [ReportController::class, 'pdf'], $can('reports.view'), 'admin.reports.pdf');
    $router->get('/admin/reports/{slug}/csv', [method_exists(ReportController::class, 'csv') ? ReportController::class : \App\Http\Controllers\Admin\ReportExportController::class, 'csv'], $can('reports.view'), 'admin.reports.csv');
    $router->get('/admin/reports/{slug}/xlsx', [method_exists(ReportController::class, 'xlsx') ? ReportController::class : \App\Http\Controllers\Admin\ReportExportController::class, 'xlsx'], $can('reports.view'), 'admin.reports.xlsx');

    // --- audit --------------------------------------------------------------
    $router->get('/admin/audit', [AuditController::class, 'index'], $can('logs.view'), 'admin.audit');
};
