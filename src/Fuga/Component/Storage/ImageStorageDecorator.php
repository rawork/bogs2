<?php

namespace Fuga\Component\Storage;

use Fuga\Component\PHPThumb\GD;

class ImageStorageDecorator implements StorageInterface {
	
	private $storageEngine;
	private $options;
	
	public function __construct($storageEngine, $options = array()) {
		$this->storageEngine = $storageEngine;
		$this->setOptions($options);
	}
	
	public function setOptions($options) {
		foreach ($options as $name => $value) {
			$this->options[$name] = $value;
		}
	}
	
	public function hasOption($name) {
		return isset($this->options[$name]);
	}
	
	public function getOption($name) {
		return $this->options[$name];
	}
	
	public function save($filename, $sorcePath) {
		$createdFileName = $this->storageEngine->save($filename, $sorcePath);
		$this->afterSave($createdFileName);
		return $createdFileName;
	}
	
	public function copy($filename, $sorcePath) {
		return $this->storageEngine->copy($filename, $sorcePath);
	}
	
	public function remove($filename) {
		if ($this->hasOption('sizes') && $filename) {
			$pathInfo = pathinfo($filename);
			$sizes = explode(',', $this->getOption('sizes'));
			foreach ($sizes as $size) {
				$sizeParams = explode('|', $size);
				if (count($sizeParams) == 2) {
					$this->storageEngine->remove($pathInfo['dirname']
							.DIRECTORY_SEPARATOR.$pathInfo['filename']
							.'_'.$sizeParams[0]
							.(isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '')
					);
				}
			}

		}
		return $this->storageEngine->remove($filename);
	}
	
	public function exists($filename) {
		return $this->storageEngine->exists($filename);
	}
	
	public function realPath($filename){
		return $this->storageEngine->realPath($filename);
	}
	
	public function path($filename){
		return $this->storageEngine->path($filename);
	}
	
	public function size($filename, $precision = 2){
		return $this->storageEngine->size($this->realPath($filename), $precision);
	}
	
	public function additionalFiles($filename, $options = array()) {
		$this->setOptions($options);
		$files = array();
		if ($this->hasOption('sizes') && $filename) {
			$pathParts = pathinfo($filename);
			$sizes = explode(',', $this->getOption('sizes'));
			foreach ($sizes as $sizeData) {
				$sizeParams = explode('|', $sizeData);
				if (count($sizeParams) == 2) {
					$path = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0];
					$path .= isset($pathParts['extension']) ? '.'.$pathParts['extension'] : '';
					$files[$sizeParams[0]] = array(
						'name' => $sizeParams[0], 
						'path' => $this->path($path),
						'size' => $this->size($path)
					);
				}
			}
		}	
		return $files;
	}
	
	public function afterSave($filename, $options = array()) {
		$this->setOptions($options);
		if ($imageData = GetImageSize($this->realPath($filename))) {
			$old_img_width = $imageData[0];
			$old_img_height = $imageData[1];
			$resize = false;
			if ($this->hasOption('sizes')) {
				$sizes = explode(',', $this->getOption('sizes'));
				foreach ($sizes as $sizeData) { 	
					$img_width = $imageData[0];
					$img_height = $imageData[1];
					$sizeParams = explode('|', $sizeData);
					if (count($sizeParams) == 2) {
						$asizes2 = explode('x', $sizeParams[1]);
						$max_width = $asizes2[0];
						$max_height = $asizes2[1];
						$method = isset($asizes2[2]) ? ($asizes2[2] == 'resize' ? $asizes2[2] : $asizes2[2].'Resize') : 'resize';
						try
						{
							$pathParts = pathinfo($filename);
							$thumb = new GD($this->realPath($filename));
							$thumb->$method($max_width, $max_height);
							$thumb->save($this->realPath($pathParts['dirname'].'/'.$pathParts['filename'].'_'.$sizeParams[0].'.'.$pathParts['extension']));		
						}
						catch (Exception $e)
						{
							// handle error here however you'd like
						}
					}
				}
			}
		}
	}
	
}