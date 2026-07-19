<?php

/** @var \App\Core\Router $router */

$router->get('/__smoke-test', 'SmokeTestController@ping');
$router->get('/', 'Public\HomeController@index');
$router->get('/about', 'Public\AboutController@index');
$router->get('/contact', 'Public\ContactController@index');
$router->post('/contact', 'Public\ContactController@submit');
$router->get('/blog', 'Public\BlogController@index');
$router->get('/blog/{slug}', 'Public\BlogController@show');
