<?php

define('ROOT_DIR', realpath(__DIR__ . '/../'));
define('VENDOR_DIR', ROOT_DIR . '/vendor');
define('SRC_DIR', ROOT_DIR . '/src');
define('CONFIG_DIR', ROOT_DIR . '/config');

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require VENDOR_DIR . '/autoload.php';

$apiServer = new \Salexandru\Api\Server();
$apiServer->bootstrap()->run();
