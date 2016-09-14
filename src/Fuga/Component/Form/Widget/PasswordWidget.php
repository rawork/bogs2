<?php

namespace Fuga\Component\Form\Widget;

class PasswordWidget extends AbstractWidget {
	
	public function getStatic($name, $entity) {
		return '';
	}
	
	public function getInput($name, $entity = null) {
		return '<input type="password" name="'.$name.'" value="">';
	}
	
	public function getSearchInput($name, $entity = null) {
		return '';
	}
}

