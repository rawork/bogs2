<?php

define('LIB_VERSION', '6.0.5');
define('LIB_DATE', '2014.04.05');

mb_http_input('UTF-8'); 
mb_http_output('UTF-8'); 
mb_internal_encoding("UTF-8");

$loader = require __DIR__.'/../vendor/autoload.php';

use Fuga\Component\Container;
use Fuga\Component\Registry;

function exception_handler($exception) 
{	
	$statusCode = $exception instanceof \Fuga\Component\Exception\NotFoundHttpException 
			? $exception->getStatusCode() 
			: 500;
	if (isset($_SERVER['REQUEST_URI'])) {
		$controller = new Fuga\CommonBundle\Controller\ExceptionController();
		echo $controller->indexAction($statusCode, $exception->getMessage());
	} else {
		echo $exception->getMessage();
	}
}

set_exception_handler('exception_handler');

if (file_exists(__DIR__.'/config/config.php')) {
	require_once __DIR__.'/config/config.php';
}

// инициализация переменных
if (isset($_SERVER['REQUEST_URI'])) {
	$container = new Container($loader);
	$params = array();
	$sql = 'SELECT name, value FROM config_variable';
	$stmt = $container->get('connection')->prepare($sql);
	$stmt->execute();
	$vars = $stmt->fetchAll();
	foreach ($vars as $var) {
		$params[strtolower($var['name'])] = $var['value'];
		define($var['name'], $var['value']);
	}

	// TODO убрать инициализацию всех таблиц
	$container->initialize();
}