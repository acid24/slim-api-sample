<?php

require realpath(__DIR__ . '/../resources/appinit.php');

$apiServer = new \Salexandru\Api\Server();
$apiServer->bootstrap()->run();
