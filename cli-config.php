<?php

require realpath(__DIR__ . '/bootstrap/constants.php');

$container = new \Slim\Container([]);
(new \Salexandru\Api\Server\Bootstrap\ContainerServicesProvider())->register($container);

$connection = $container->get('dbConnection');

return \Doctrine\DBAL\Tools\Console\ConsoleRunner::createHelperSet($connection);
