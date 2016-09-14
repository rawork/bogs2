<?php

namespace Fuga\AdminBundle\Admin;

class Admin {
	
	public $name;
	public $params = array();

	function __construct($name) {
		$this->name = $name;
		$params = $this->get('container')->getManager('Fuga:Common:Param')->findAll($name);
		foreach ($params as $param) {
			$this->params[$param['name']] = ($param['type'] == 'int' ? intval($param['value']) : $param['value']);
		}
	}

	public function get($name) {
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
}
