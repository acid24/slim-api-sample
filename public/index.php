<?php

require realpath(__DIR__ . '/../bootstrap/appinit.php');

$apiServer = new \Salexandru\Api\Server();
$apiServer->bootstrap()->run();
