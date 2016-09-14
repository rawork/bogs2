<?php

namespace Fuga\Component\DB\Field;

class CheckboxType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}
	
	public function getValue($name = '') {
		$name = $name ? $name : $this->getName();
		$value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
		return $value;
	}

	public function getSQLValue($name = '') {
		$value = intval($this->getValue($name));
		return $value  == 1 ? $value : 0;
	}

	public function getStatic() {
		return $this->dbValue == 1 ? 'Да' : 'Нет';
	}

	public function getInput($value = '', $name = '') {
		return '<input type="checkbox" value="1" name="'.($name ?: $this->getName()).'" '.(empty($this->dbValue) ? '' : 'checked').'>';
	}

	public function getSearchInput() {
		$name = parent::getSearchName();
		$value = parent::getSearchValue();
		$yes = $no = $no_matter = "";
		switch ($value) {
			case "on":
				$yes = 'checked';
				break;
			case "off":
				$no = 'checked';
				break;
			default: 
				$no_matter = 'checked';
		}
		return '
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_yes" value="on" '.$yes.'>
  да
</label>
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_no" value="off" '.$no.'>
  нет
</label>
<label class="radio-inline">
  <input type="radio" name="'.$name.'" id="'.$name.'_nomatter" value="" '.$no_matter.'>
  все равно
</label>';
	}

	public function getSearchSQL() {
		$value = parent::getSearchValue();
		if ($value == 'off') {
			return $this->getName()."=0";
		} elseif ($value == 'on') {
			return $this->getName()."=1";
		} else {
			return '';
		}
	}
	
	public function getType() {
		return 'boolean';
	}

	public function getGroupSQLValue() {
		return $this->getSQLValue($this->getName().$this->dbId);
	}
}
