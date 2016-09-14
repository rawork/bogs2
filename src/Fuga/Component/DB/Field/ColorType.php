<?php

namespace Fuga\Component\DB\Field;

class ColorType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getInput($value = '', $name = '') {
		return $this->colorType_getInput($name ?: $this->getName(), $value ?: $this->dbValue);
	}

	public function getSearchInput() {
		return $this->colorType_getInput(parent::getSearchName(), '');
	}

	public function getStatic() {
		$value = strip_tags(trim($this->dbValue));
		return '<div class="color-static" style="background-color:'.($value ?: '#ffffff').'"></div>';
	}

	public function colorType_getInput($name = '', $value = '') {
		return '<input class="form-control clPicker" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" size="7">';
	}

}
