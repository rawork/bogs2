<?php

namespace Fuga\CommonBundle\Model;

class Config {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name' => 'module',
			'module' => 'config',
			'title' => 'Модули',
			'order_by' => 'sort,title',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '25%'
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Сист. имя',
				'type' => 'string',
				'width' => '20%',
				'search' => true
			),
			'path' => array (
				'name' => 'path',
				'title' => 'Путь',
				'type' => 'string',
				'width' => '50%',
				'search' => true
			),
			'sort' => array (
				'name' => 'sort',
				'title' => 'Сорт.',
				'type' => 'number',
				'group_update'  => true,
				'width' => '3%'
			)
		));

		$this->tables[] = array(
			'name' => 'variable',
			'module' => 'config',
			'title' => 'Общие настройки',
			'order_by' => 'name',
			'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '33%',
				'search' => true
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя (англ.)',
				'type' => 'string',
				'width' => '33%',
				'search'=> true
			),
			'value' => array (
				'name'  => 'value',
				'title' => 'Значение',
				'width' => '33%',
				'type' => 'string'
			)
		));

		$this->tables[] = array(
			'name' => 'version',
			'module' => 'config',
			'title' => 'Версии сайта',
			'order_by' => 'id',
			'is_publish' => true,
			'fieldset' => array (
				'title' => array (
					'name' => 'title',
					'title' => 'Название',
					'type' => 'string',
					'width' => '40%',
				),
				'folder' => array (
					'name' => 'folder',
					'title' => 'Папка',
					'type' => 'string',
					'width' => '40%',
				),
				'language' => array (
					'name'  => 'language',
					'title' => 'Язык',
					'width' => '15%',
					'type'  => 'enum',
					'select_values' => 'ru;en;it',
					'defvalue' => 'ru',
				)
			));

	}
}
