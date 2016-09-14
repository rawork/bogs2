<?php

namespace Fuga\Component;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Registry {

    protected static $store = array();
 
    protected function __construct() {}
    protected function __clone() {}
	protected function __wakeup() {}
 
	public static function init($filename) {
		try {
			self::parseArray(Yaml::parse($filename));
		} catch (ParseException $e) {
			throw new \Exception("Ошибки в настройке файла конфигурации", $e->getMessage());
		}
    }
	
	private static function parseArray($array, $basename = '') {
		foreach ($array as $name => $params) {
			$name = ($basename ? $basename.'.' : '').$name;
			if (is_array($params)) {
				self::parseArray($params, $name);
			} else {
				self::set($name, $params);
			}
		}
	}
    
    public static function exists($name) 
    {
    	return isset(self::$store[$name]);
    }
 
    public static function get($name) 
    {
        return self::exists($name) ? self::$store[$name] : null;
    }
 
    public static function set($name, $obj) 
    {
        return self::$store[$name] = $obj;
    }
}
