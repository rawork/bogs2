<?php

namespace Fuga\Component\Storage;

class TemplateStorageDecorator implements StorageInterface {
	
	private $storageEngine;
	private $options;
	private $connection;
	
	public function __construct($storageEngine, $connection, $options = array()) {
		$this->storageEngine = $storageEngine;
		$this->connection = $connection;
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
	
	public function save($filename, $sourcePath) {
		$createdFileName = $this->storageEngine->save($filename, $sourcePath);
		return $createdFileName;
	}
	
	public function copy($filename, $sorcePath) {
		return $this->storageEngine->copy($filename, $sorcePath);
	}
	
	public function remove($filename) {
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
		return $this->storageEngine->size($filename, $precision);
	}
	
}