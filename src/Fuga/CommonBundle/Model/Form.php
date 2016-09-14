<?php

namespace Fuga\CommonBundle\Model;

class Form {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name' => 'form',
			'module' => 'form',
			'title' => 'Веб формы',
			'order_by' => 'name',
			'is_publish' => true,
			'is_lang' => true,
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '22%',
				'search' => true
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '22%',
			),
			'email' => array (
				'name' => 'email',
				'title' => 'E-mail',
				'type' => 'text',
				'width' => '22%',
			),
			'submit_text' => array (
				'name' => 'submit_text',
				'title' => 'Submit кнопка',
				'type' => 'string',
				'width' => '22%'
			),
			'is_defense' => array (
				'name' => 'is_defense',
				'title' => 'CAPTCHA',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			)
		));

		$this->tables[] = array(
			'name' => 'field',
			'module' => 'form',
			'title' => 'Поля формы',
			'order_by' => 'form_id,sort', 
			'is_sort' => true, 
			'is_lang' => true,
			//'is_hidden' => true,
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '25%'
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '25%',
				'search' => true
			),
			'form_id' => array (
				'name' => 'form_id',
				'title' => 'Форма',
				'type' => 'select',
				'l_table' => 'form_form',
				'l_field' => 'title',
				'l_lang' => true,
				'width' => '25%',
				'search' => true
			),
			'type' => array (
				'name' => 'type',
				'title' => 'Тип',
				'type' => 'enum',
				'select_values' => 'Строка|string;Текст|text;Список|select;Флаг|checkbox;Файл|file;Пароль|password',
				'width' => '15%'
			),
			'select_table' => array (
				'name' => 'select_table',
				'title' => 'Таблица значений',
				'type' => 'string',
				'help' => 'Таблица значений'
			),
			'select_name' => array (
				'name' => 'select_name',
				'title' => 'Поле залоговка',
				'type' => 'string',
			),
			'select_value' => array (
				'name' => 'select_value',
				'title' => 'Поле значения',
				'type' => 'string',
			),
			'select_filter' => array (
				'name' => 'select_filter',
				'title' => 'Запрос',
				'type' => 'string',
			),
			'select_values' => array (
				'name' => 'select_values',
				'title' => 'Значения',
				'type' => 'string'
			),
			'not_empty' => array (
				'name' => 'not_empty',
				'title' => 'Обяз.',
				'type' => 'checkbox',
				'group_update'  => true,
				'width' => '1%'
			),
			'is_check' => array (
				'name' => 'is_check',
				'title' => 'Пров.',
				'type' => 'checkbox',
				'group_update'  => true,
				'width' => '1%'
			)  
		));
	}	
}
