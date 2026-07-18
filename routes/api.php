<?php

/** @var \App\Core\Router $router */
$router->get('/api/__smoke-test', 'SmokeTestController@ping');
