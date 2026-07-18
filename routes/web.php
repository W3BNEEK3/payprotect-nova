<?php

/** @var \App\Core\Router $router */

$router->get('/__smoke-test', 'SmokeTestController@ping');
