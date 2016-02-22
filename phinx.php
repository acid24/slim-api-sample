<?php

use Slim\Container;
use Salexandru\Bootstrap\ConfigInitializer;

require realpath(__DIR__ . '/bootstrap/appinit.php');

$container = new Container();
$configInitializer = new ConfigInitializer($container);

// In a real application $container should be used to extract db related settings.
// Here we use a simple sqlite database so that's not necessary

return [
    'paths' => [
        'migrations' => RESOURCES_DIR . '/db/migrations',
        'seeds' => RESOURCES_DIR . '/db/seeds'
    ],
    'environments' => [
        'production' => [
            'adapter' => 'sqlite',
            'name' => RESOURCES_DIR . '/db/sample.db'
        ]
    ],
];
