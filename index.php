<?php

use Fuga\CommonBundle\Controller\AppController;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/app/init.php');


$request = Request::createFromGlobals();

$kernel = new AppController();
$response = $kernel->handle($request);
$response->send();