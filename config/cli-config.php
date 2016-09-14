<?php

require_once __DIR__ . '/../app/init.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
// replace with mechanism to retrieve EntityManager in your app
$entityManager = $container->get('em');

return ConsoleRunner::createHelperSet($entityManager);