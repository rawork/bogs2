<?php

namespace Fuga\Component\Templating;

use Fuga\Component\Container;

class TwigTemplating implements TemplatingInterface {
	
	private $container;
	private $engine;


	public function __construct(\Twig_Environment $engine, Container $container)
	{
		$this->container = $container;
		$this->engine = $engine;
	}
	
	public function assign($params = array())
	{
		foreach ($params as $paramName => $paramValue) {
			$this->engine->addGlobal($paramName, $paramValue);
		}
	}
	
	public function render($template, $params = array(), $silent = false)
	{
		try {
			if (empty($template)) {
				throw new \Exception('Для обработки передан шаблон без названия.');
			}

			return $this->engine->render($template, $params);
		} catch (\Exception $e) {
			$this->container->get('log')->addError($e->getMessage());
			$this->container->get('log')->addError($e->getTraceAsString());
			if (!$silent) {
				if (PRJ_ENV == 'dev') {
					throw $e;
				} else {
					throw new \Exception('Ошибка рендеринга шаблона "'.$template.'".');
				}
			}
		}

		return false;
	}
	
}
