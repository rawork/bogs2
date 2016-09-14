<?php

namespace Fuga\CommonBundle\Model;

class ModelManager implements ModelManagerInterface {
	
	protected $entityTable;
	protected $connection;
	
	public function findBy($criteria = '', $sort = null, $limit = null) {
		$this->get('container')->getItems($this->entityTable, $criteria, $sort, $limit);
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