<?php

namespace Fuga\CommonBundle\Controller;

class PublicController extends Controller {
	
	public $params = array();
	
	function __construct($name) {
		$params = $this->getManager('Fuga:Common:Param')->findAll($name);
		foreach ($params as $param) {
			$this->params[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
		}
	}
	
	public function mapAction() {
		return null;
	}


	public function getParam($name) {
		return $this->params[$name];
	}

}
