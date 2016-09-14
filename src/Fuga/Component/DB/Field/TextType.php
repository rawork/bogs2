<?php

namespace Fuga\Component\DB\Field;

class TextType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getStatic() {
		return $this->get('util')->cut_text(parent::getStatic());
	}

	public function getSearchInput() {
		return '<input class="form-control" type="text" id="'.$this->getSearchName().'" name="'.$this->getSearchName().'" value="'.htmlspecialchars($this->getSearchValue()).'">';
	}

	public function getInput($value = '', $name = '') {
		$value = $value ? $value : $this->dbValue;
		$name = $name ? $name : $this->getName();
		
		return '<textarea name="'.$name.'" class="form-control " rows="4">'.htmlspecialchars($value).'</textarea>';
	}
	
	public function getType() {
		return 'text';
	}

}
