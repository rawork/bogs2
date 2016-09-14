<?php

namespace Fuga\Component\Storage;

interface StorageInterface 
{
	
	public function save($filename, $sourcePath);
	public function copy($filename, $sourcePath);
	public function remove($filename);
	public function exists($filename);
	public function realPath($filename);
	public function path($filename);
	public function size($filename, $precision = 2);
}

