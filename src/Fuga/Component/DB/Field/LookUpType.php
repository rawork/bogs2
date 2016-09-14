<?php

namespace Fuga\Component\DB\Field;

class LookUpType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		$this->dbValue = intval($this->dbValue);
	}

	public function getSearchSQL() {
		if ($value = $this->getSearchValue()) {
			return $this->getName().'='.$value;
		}
		
		return '';
	}

	public function getValue($name = '') {
		$name = $name ?: $this->getName();
		$value = $this->get('request')->request->getInt($name, $this->dbValue);
		
		return $value;
	}
	
	public function getType() {
		return 'integer';
	}

}
