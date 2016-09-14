<?php

namespace Fuga\Component\Form\Widget;

abstract class AbstractWidget implements WidgetInterface {
	
	public function getValue($name, $entity) {
		return $entity ? $entity[$name] : '';
	}
	
	public function getSearchName($name) {
		return 'search_filter_'.$name;
	}
	
	public function getstatic($name, $entity = null){
		return strip_tags(trim($this->getValue($name, $entity)));
	}
	
	public function getInput($name, $entity = null) {
		return '<input type="text" name="'.$name.'" value="'.$this->getValue($name, $entity).'">';
	}
	
	public function getSearchInput($name, $entity = null) {
		return '<input type="text" name="search_filter_'.$name.'" value="'.$this->getValue($this->getSearchName($name), $entity).'">';
	}
}