<?php

namespace Fuga\Component\Templating;

interface TemplatingInterface {

	public function assign($param);
	public function render($template, $params = array(), $silent = false);

}