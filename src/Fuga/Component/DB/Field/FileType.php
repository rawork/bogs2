<?php

namespace Fuga\Component\DB\Field;
    
class FileType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getSQLValue($inputName = '') {
		$inputName = $inputName ? $inputName : $this->getName();
		$fileName = $this->dbValue;
		if ($this->get('request')->request->get($inputName.'_delete')) {
			$this->get('filestorage')->remove($fileName);
			$fileName = '';
		}
		if (!empty($_FILES[$inputName]) && !empty($_FILES[$inputName]['name'])) {
			$this->get('filestorage')->remove($fileName);
			$fileName = $this->get('filestorage')->save($_FILES[$inputName]['name'], $_FILES[$inputName]['tmp_name']);
		}
		return $fileName;
	}
	
	public function getNativeValue() {
		return $this->get('filestorage')->path(parent::getNativeValue());
	}

	public function getStatic() {
		$content = '';
		if ($this->getNativeValue())
			$content = '<a href="'.$this->getNativeValue().'">'.$this->getNativeValue().'</a>&nbsp;('.$this->get('filestorage')->size($this->get('filestorage')->realPath(parent::getNativeValue())).')';
		return $content;
	}
	
	public function getInput($value = '', $name = '') {
		$name = $name ? $name : $this->getName();
		if ($s = $this->getStatic()) {
			$r = rand(0, getrandmax());
			$s = $s.'&nbsp;<label for="'.$r.'"><input name="'.$name.'_delete" type="checkbox" id="'.$r.'"> удалить</label>';
		}
		return '<input type="hidden" name="'.$name.'_oldValue" value="'.$this->dbValue.'">'.$s.'<input type="file" class="form-control" name="'.$name.'">';
	}

	public function free() {
		$this->get('filestorage')->remove($this->dbValue);
	}
}
