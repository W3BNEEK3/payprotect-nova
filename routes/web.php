<?php

/** @var \App\Core\Router $router */

use App\Middlewares\{AuthMiddleware, GuestMiddleware, CsrfMiddleware};

// ---- Public marketing site ----
$router->get('/__smoke-test', 'SmokeTestController@ping');
$router->get('/', 'Public\HomeController@index');
$router->get('/about', 'Public\AboutController@index');
$router->get('/contact', 'Public\ContactController@index');
$router->post('/contact', 'Public\ContactController@submit', [CsrfMiddleware::class]);
$router->get('/blog', 'Public\BlogController@index');
$router->get('/blog/{slug}', 'Public\BlogController@show');

// ---- Authentication ----
$router->get('/login', 'Auth\LoginController@index', [GuestMiddleware::class]);
$router->post('/login', 'Auth\LoginController@submit', [GuestMiddleware::class, CsrfMiddleware::class]);
$router->post('/logout', 'Auth\LoginController@logout', [AuthMiddleware::class]);

$router->get('/register', 'Auth\RegisterController@index', [GuestMiddleware::class]);
$router->post('/register', 'Auth\RegisterController@submit', [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/forgot-password', 'Auth\PasswordResetController@showRequestForm', [GuestMiddleware::class]);
$router->post('/forgot-password', 'Auth\PasswordResetController@submitRequest', [GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/reset-password/{token}', 'Auth\PasswordResetController@showResetForm', [GuestMiddleware::class]);
$router->post('/reset-password/{token}', 'Auth\PasswordResetController@submitReset', [GuestMiddleware::class, CsrfMiddleware::class]);

// ---- Control Center (Admin) ----
$router->get('/control-center/auth', 'Auth\AdminLoginController@index', [\App\Middlewares\AdminGuestMiddleware::class]);
$router->post('/control-center/auth', 'Auth\AdminLoginController@submit', [\App\Middlewares\AdminGuestMiddleware::class, CsrfMiddleware::class]);
$router->post('/control-center/logout', 'Auth\AdminLoginController@logout', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/dashboard', 'Admin\AdminDashboardController@index', [\App\Middlewares\AdminMiddleware::class]);

// ---- Authenticated Shell ----
$router->get('/dashboard', 'DashboardController@index', [AuthMiddleware::class]);

// ---- User Virtual Card ----
$router->get('/virtual-card', 'User\VirtualCardController@index', [AuthMiddleware::class]);
$router->get('/virtual-card/create', 'User\VirtualCardController@create', [AuthMiddleware::class]);
$router->post('/virtual-card/request', 'User\VirtualCardController@request', [AuthMiddleware::class, CsrfMiddleware::class]);

// ---- Admin Users ----
$router->get('/admin/users', 'Admin\UsersController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/users/search', 'Admin\UsersController@search', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/users/{id}', 'Admin\UsersController@show', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/users/{id}', 'Admin\UsersController@update', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/balance', 'Admin\UsersController@updateBalance', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/impersonate', 'Admin\UserImpersonationController@impersonate', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);

// ---- Admin Virtual Card ----
$router->get('/admin/virtual-cards', 'Admin\VirtualCardReviewController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/virtual-cards/{id}/approve', 'Admin\VirtualCardReviewController@approve', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/virtual-cards/{id}/reject', 'Admin\VirtualCardReviewController@reject', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);

// ---- Phase 10: User Compliance Flow ----
$router->get('/compliance', 'User\ComplianceController@index', [AuthMiddleware::class]);
$router->post('/compliance/verify', 'User\ComplianceController@verify', [AuthMiddleware::class, CsrfMiddleware::class]);

// ---- Admin Transactions ----
$router->get('/admin/transactions', 'Admin\TransactionController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/transactions', 'Admin\TransactionController@store', [\App\Middlewares\AdminMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->get('/admin/transactions/{id}/edit', 'Admin\TransactionController@edit', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/transactions/{id}/edit', 'Admin\TransactionController@update', [\App\Middlewares\AdminMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);

// ---- Phase 10: Admin Compliance Management ----
$router->get('/admin/compliance', 'Admin\ComplianceController@flags', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/compliance/{id}/assign-code', 'Admin\ComplianceController@showAssignCode', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/compliance/{id}/assign-code', 'Admin\ComplianceController@assignCode', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/compliance/manual-flag', 'Admin\ComplianceController@manualFlag', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/compliance/requirements', 'Admin\ComplianceController@requirements', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/compliance/requirements', 'Admin\ComplianceController@storeRequirement', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/compliance/requirements/kyc/toggle', 'Admin\ComplianceController@toggleKyc', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/compliance/requirements/{id}/toggle', 'Admin\ComplianceController@toggleRequirement', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);

// ---- Phase 11: User Withdrawal Flow ----
$router->get('/withdraw', 'User\WithdrawalController@index', [AuthMiddleware::class]);
$router->post('/withdraw/submit', 'User\WithdrawalController@submit', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/withdraw/success/{id}', 'User\WithdrawalController@success', [AuthMiddleware::class]);
$router->get('/withdraw/{method}', 'User\WithdrawalController@methodForm', [AuthMiddleware::class]);
$router->post('/withdraw/{method}/review', 'User\WithdrawalController@review', [AuthMiddleware::class, CsrfMiddleware::class]);

// ---- Phase 11: Admin Withdrawal Management ----
$router->get('/admin/withdrawals', 'Admin\WithdrawalController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/withdrawals/{id}', 'Admin\WithdrawalController@show', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/withdrawals/{id}/approve', 'Admin\WithdrawalController@approve', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/withdrawals/{id}/reject', 'Admin\WithdrawalController@reject', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);

// ---- Phase 12: Notifications ----
$router->get('/notifications', 'User\NotificationController@index', [AuthMiddleware::class]);

// ---- Phase 13: Support ----
$router->get('/profile', 'User\ProfileController@index', [AuthMiddleware::class]);
$router->post('/profile/password', 'User\ProfileController@updatePassword', [AuthMiddleware::class]);

$router->get('/settings', 'User\SettingsController@index', [AuthMiddleware::class]);
$router->post('/settings', 'User\SettingsController@update', [AuthMiddleware::class]);

$router->get('/transactions', 'User\TransactionController@index', [AuthMiddleware::class]);

$router->get('/transfer', 'User\TransferController@index', [AuthMiddleware::class]);
$router->get('/transfer/internal', 'User\TransferController@internal', [AuthMiddleware::class]);
$router->post('/transfer/internal', 'User\TransferController@internal', [AuthMiddleware::class]);
$router->get('/transfer/bank', 'User\TransferController@bank', [AuthMiddleware::class]);
$router->post('/transfer/bank', 'User\TransferController@bank', [AuthMiddleware::class]);
$router->get('/transfer/international', 'User\TransferController@international', [AuthMiddleware::class]);
$router->post('/transfer/international', 'User\TransferController@international', [AuthMiddleware::class]);

$router->get('/support', 'User\SupportController@index', [AuthMiddleware::class]);
$router->post('/support/ticket', 'User\SupportController@submitTicket', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/support/chat/widget', 'User\SupportController@widget', [AuthMiddleware::class]);

// ---- Phase 13: Admin Chat ----
$router->get('/admin/chat', 'Admin\ChatController@queue', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/chat/queue', 'Admin\ChatController@queue', [\App\Middlewares\AdminMiddleware::class]);
$router->get('/admin/chat/conversation/{id}', 'Admin\ChatController@showConversation', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/chat/conversation/{id}/close', 'Admin\ChatController@closeConversation', [\App\Middlewares\AdminMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->get('/admin/chat/bot-rules', 'Admin\ChatController@botRules', [\App\Middlewares\AdminMiddleware::class]);

// ---- Phase 14: Mail Settings ----
$router->get('/admin/mail-settings', 'Admin\MailSettingsController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/mail-settings', 'Admin\MailSettingsController@update', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/api/admin/mail-settings/test', 'Admin\MailSettingsController@testMail', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);

// ---- Phase 15: Site Settings ----
$router->get('/admin/site-settings', 'Admin\SiteSettingsController@index', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/admin/site-settings/update', 'Admin\SiteSettingsController@update', [\App\Middlewares\AdminMiddleware::class, CsrfMiddleware::class]);
