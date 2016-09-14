<?php

namespace Fuga\Component\Templating;

class SmartyTemplating implements TemplatingInterface {
	
	private $engine;

	public function __construct(\Smarty $engine)
	{
		$this->engine = $engine;
	}
	
	public function assign($params)
	{
		foreach ($params as $paramName => $paramValue) {
			$this->engine->assign($paramName, $paramValue);
		}
	}
	
	public function render($template, $params = array(), $silent = false)
	{
		try {
			if (empty($template)) {
				throw new \Exception('Для обработки передан шаблон без названия');
			}
			$this->assign($params);

			return $this->engine->fetch($template);
		} catch (\Exception $e) {
			if (!$silent) {
				throw new \Exception('Несуществующий шаблон "'.$template.'"');
			}
		}

		return false;
	}
	
	public function clearCompiled()
	{
		$this->engine->clear_compiled_tpl();
	}
	
	public function clearCache($template = '')
	{
		if ($template) {
			$this->engine->clear_cache($template);
		} else {
			$this->engine->clear_all_cache();
		}
		
	}
	
}
