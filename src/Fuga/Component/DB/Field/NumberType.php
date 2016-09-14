<?php

namespace Fuga\Component\DB\Field;    

class NumberType extends LookUpType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}
	
	public function getGroupInput() {
		return $this->getInput('', $this->getName().$this->dbId);
	}
	
	public function getSQLValue($name='') {
		return intval(preg_replace('/\s+/', '', preg_replace('/\,/', '.', $this->getValue($name))));
	}
	
	public function getStatic() {
		return $this->dbValue.' &nbsp;';
	}
}
