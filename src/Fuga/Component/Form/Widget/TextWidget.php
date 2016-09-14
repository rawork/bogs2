<?php

namespace Fuga\Component\Form\Widget;

class TextWidget implements AbstractWidget {
	
	public function getstatic($name, $entity = null){
		return strip_tags(trim($this->getValue($name, $entity)));
	}
	
	public function getInput($name, $entity = null) {
		return '<textarea name="'.$name.'">'.$this->getValue($name, $entity).'</textarea>';
	}
	
}