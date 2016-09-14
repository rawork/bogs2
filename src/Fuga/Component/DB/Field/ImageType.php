<?php

namespace Fuga\Component\DB\Field;

class ImageType extends FileType {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}
	
	public function getStatic() {
		$value = $this->getNativeValue();
		$fileName = $value['value'];
		$additionalFiles = '';
		if (isset($value['extra'])) {
			foreach ($value['extra'] as $file) {
				if ($file['name'] == 'default') {
					$fileName = $file['path'];
				} else {
					$additionalFiles .= '<div><a target="_blank" href="'.$file['path'].'">'.ucfirst($file['name']).' image</a> ('.$file['size'].')</div>';
				}
			}
		}
		return $fileName ? '<a target="_blank" href="'.parent::getNativeValue().'"><img width="50" src="'.$fileName.'"></a><div>('.$this->get('imagestorage')->size($this->dbValue).')</div>'.$additionalFiles : '';
	}
	
	public function getGroupStatic() {
		$value = $this->getNativeValue();
		$fileName = isset($value['extra']['default']) ? $value['extra']['default']['path'] : $value['value'];
		return $fileName ? '<a target="_blank" href="'.$fileName.'"><img width="50" src="'.$fileName.'"></a>' : '';
	}

	public function getSQLValue($inputName = '') {
		$this->get('imagestorage')->setOptions(array('sizes' => $this->getParam('sizes')));
		$inputName = $inputName ? $inputName : $this->getName();
		$fileName = $this->dbValue;
		if ($fileName && $this->get('request')->request->get($inputName.'_delete')) {
			$this->get('imagestorage')->remove($fileName);
			$fileName = '';
		}
		if (!empty($_FILES[$inputName]) && !empty($_FILES[$inputName]['name'])) {
			$this->get('imagestorage')->remove($fileName);
			$fileName = $this->get('imagestorage')->save($_FILES[$inputName]['name'], $_FILES[$inputName]['tmp_name']);
		}
		return $fileName;
	}
	
	public function getNativeValue() {
		$value = array('value' => parent::getNativeValue());
		if ($value['value']) {
			if ($files = $this->get('imagestorage')->additionalFiles($this->dbValue, array('sizes' => $this->getParam('sizes')))) {
				$value['extra'] = $files;
			}
		}
		
		return $value;			
	}
	
	public function free() {
		$this->get('imagestorage')->remove($this->dbValue);
	}

}
