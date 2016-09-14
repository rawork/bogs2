<?php

namespace Fuga\Component\DB\Field;

class CurrencyType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQLValue($name = '') {
		return floatval(preg_replace('/\s+/', '', preg_replace('/\,/', '.', $this->getValue($name))));
	}

	public function getInput($value = '', $name = '') {
		$name = $name ? $name : $this->getName();
		$value = $value ? $value : $this->dbValue;
		return '<input type="text" class="form-control text-right" name="'.$name.'" value="'.str_replace('"', '&quot;', $value).'">';
	}
	
	public function getType() {
		return 'money';
	}
}
