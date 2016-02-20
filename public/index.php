<?php

require realpath(__DIR__ . '/../resources/init/app.php');

$apiServer = new \Salexandru\Api\Server();
$apiServer->bootstrap()->run();
