<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\EnvLoader;

EnvLoader::load(__DIR__ . '/../.env');

$app = new App();

return $app;
