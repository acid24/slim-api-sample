<?php

require realpath(__DIR__ . '/bootstrap/constants.php');

$container = new \Slim\Container([]);
(new \Salexandru\Api\Server\Bootstrap\ContainerServicesProvider())->register($container);

/** @var \Doctrine\ORM\EntityManager $em */
$em = $container->get('orm.doctrine.entityManager');

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);
