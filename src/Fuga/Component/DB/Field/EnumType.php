<?php

namespace Fuga\Component\DB\Field;

class EnumType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function enum_getInput($value, $name) {
		$value = $value ?: $this->dbValue;
		$sel = '';
		if ($this->getParam('name') == 'type' && $this->getParam('table') == 'table_field') {
			$sel = ' onChange="setType(this)"';
		}
		$ret = '<select class="form-control" '.$sel.' name="'.$name.'">';
		if ($this->getParam('select_values')) {
			$items = explode(';', $this->getParam('select_values'));
			foreach ($items as $item) {
				$aitem = explode('|', $item);
				if (count($aitem) == 2) {
					$ret .= '<option '.($value == $aitem[1] ? 'selected ' : '').'value="'.$aitem[1].'">'.$aitem[0].'</option>';
				} else {
					$ret .= '<option '.($value == $item ? 'selected ' : '').'value="'.$item.'">'.$item.'</option>';
				}
			}
		}
		$ret .= '</select>';
		return $ret;
	}

	public function getStatic() {
		if ($this->getParam('select_values')) {
			$svalues = explode(';', $this->getParam('select_values'));
			foreach ($svalues as $a) {
				$aa = explode('|', $a);
				if (count($aa)>1 && $aa[1] == $this->dbValue) {
					return $aa[0];
				}
			}	
		}
		return $this->dbValue;
	}

	public function getInput($value = '', $name = '') {
		return $this->enum_getInput(($value ?: $this->dbValue), ($name ?: $this->getName()));
	}

	public function getSearchInput() {
		return $this->enum_getInput(parent::getSearchValue(), parent::getSearchName());
	}
}
