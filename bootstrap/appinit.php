<?php

// Root directory of the project
define('ROOT_DIR', realpath(__DIR__ . '/../'));

// Vendor directory of the project
define('VENDOR_DIR', ROOT_DIR . '/vendor');

// Sources directory of the project
define('SRC_DIR', ROOT_DIR . '/src');

// Resources directory of the project
define('RESOURCES_DIR', ROOT_DIR . '/resources');

// Configuration directory of the project
define('CONFIG_DIR', ROOT_DIR . '/config');

// Unit tests directory of the project
define('UNIT_TESTS_DIR', ROOT_DIR . '/tests/unit');

require VENDOR_DIR . '/autoload.php';
