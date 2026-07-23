<?php

/** @var \App\Core\Router $router */
$router->get('/api/__smoke-test', 'SmokeTestController@ping');

// Phase 12: Notifications API
$router->get('/api/notifications', 'Api\NotificationApiController@index', [\App\Middlewares\AuthMiddleware::class]);
$router->post('/api/notifications/{id}/mark-read', 'Api\NotificationApiController@markRead', [\App\Middlewares\AuthMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->post('/api/notifications/mark-all-read', 'Api\NotificationApiController@markAllRead', [\App\Middlewares\AuthMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);

// Phase 13: Chat API
$router->get('/api/chat/status', 'Api\ChatApiController@status', [\App\Middlewares\AuthMiddleware::class]);
$router->get('/api/chat/{id}/messages', 'Api\ChatApiController@messages', [\App\Middlewares\AuthMiddleware::class]);
$router->post('/api/chat/messages', 'Api\ChatApiController@sendMessage', [\App\Middlewares\AuthMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->post('/api/chat/auth', 'Api\ChatApiController@pusherAuth', [\App\Middlewares\AuthMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->post('/api/chat/admin/auth', 'Api\ChatApiController@pusherAuthAdmin', [\App\Middlewares\AdminMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
$router->get('/api/chat/admin/{id}/messages', 'Api\ChatApiController@adminMessages', [\App\Middlewares\AdminMiddleware::class]);
$router->post('/api/chat/admin/messages/{id}', 'Api\ChatApiController@sendMessageAdmin', [\App\Middlewares\AdminMiddleware::class, \App\Middlewares\CsrfMiddleware::class]);
