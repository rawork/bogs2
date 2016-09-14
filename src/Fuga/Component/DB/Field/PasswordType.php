<?php

namespace Fuga\Component\DB\Field;

class PasswordType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getInput($value = '', $name = '') {
		$value = !$value ? $this->dbValue : $value;
		$name = !$name ? $this->getName() : $name;
		return '<input class="form-control" type="password" name="'.$name.'">';
	}

	public function getSQLValue($name = '') {
		$text = $this->getValue($name);
		
		return empty($text) ? ($this->dbValue ? $this->dbValue : $text) : hash('sha512', $text);
	}
}
