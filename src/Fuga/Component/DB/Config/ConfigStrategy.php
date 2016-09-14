<?php

namespace Fuga\Component\DB\Config;

interface ConfigStrategy {
	
	public function read($name);
	
}