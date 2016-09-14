<?php

namespace Fuga\Component;

class Translator {
	
	private $locale;
	private $messages= array(
		'apply_for_service' => array(
			'ru' => 'Отправить запрос',
			'en' => 'Аpply for service',
		),
		'services_solutions' => array(
			'ru' => 'Услуги и решения',
			'en' => 'Services & Solutions',
		),
		'projects_references' => array(
			'ru' => 'Проекты и отзывы',
			'en' => 'Projects & References',
		),
	);
			
	public function __construct($locale) {
		$this->locale = $locale;
	}
	
	public function import(array $messages) {
		foreach ($messages as $name => $value) {
			$this->$messages[$name] = array($this->locale => $value);	
		} 
	}
	
	public function t($name) {
		return isset($this->messages[$name][$this->locale]) ? $this->messages[$name][$this->locale] : $name;
	}
	
}