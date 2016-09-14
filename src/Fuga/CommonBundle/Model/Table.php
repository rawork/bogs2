<?php
	
namespace Fuga\CommonBundle\Model;

class Table {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
			'name'			=> 'table',
			'module'		=> 'table',
			'title'			=> 'Таблицы',
			'order_by'		=> 'module_id,sort,name',
			'is_sort'		=> true,
			'is_publish'	=> true,
			'fieldset'		=> array (
				'title' => array (
					'name'	=> 'title',
					'title' => 'Заголовок',
					'type'	=> 'string',
					'width' => '20%',
					'search'=> true
				),
				'name' => array (
					'name'	=> 'name',
					'title' => 'Сист. имя',
					'type'	=> 'string',
					'width' => '20%',
					'help'	=> 'Англ. без пробелов',
					'search' => true
				),
				'module_id' => array (
					'name'	=> 'module_id',
					'title' => 'Компонент',
					'type'	=> 'select',
					'help'	=> 'Модуль таблицы',
					'l_table' => 'config_module',
					'l_field' => 'title',
					'width' => '25%'//,
					//'group_update' => true
				),
				'order_by' => array (
					'name' => 'order_by',
					'title' => 'Сортировка',
					'type' => 'string'
				),
				'is_view'	=> array (
					'name'	=> 'is_view',
					'title' => 'Дерево',
					'type'	=> 'checkbox'
				),
				'is_lang'	=> array (
					'name'	=> 'is_lang',
					'title' => 'Мультияз.',
					'type'	=> 'checkbox',
					'width' => '1%',
					'group_update' => true
				),
				'is_sort'	=> array (
					'name'	=> 'is_sort',
					'title'	=> 'Поле сорт.',
					'type'	=> 'checkbox',
					'width' => '1%',
					'group_update' => true
				),
				'is_publish' => array (
					'name'	=> 'is_publish',
					'title'	=> 'Поле акт.',
					'type'	=> 'checkbox',
					'width' => '1%',
					'group_update' => true
				),
				'is_search' => array (
					'name'	=> 'is_search',
					'title' => 'Поиск',
					'type'	=> 'checkbox',
					'width' => '1%',
					'group_update' => true
				),
				'show_credate'	=> array (
					'name'	=> 'show_credate',
					'title' => 'Пок. дату создания',
					'type'	=> 'checkbox'
				),
				'multifile' => array (
					'name'	=> 'multifile',
					'title' => 'Доп. файлы',
					'type'	=> 'checkbox'
				)
			)
		);

	$this->tables[] = array(
			'name'		=> 'field',
			'module' => 'table',
			'title'		=> 'Поля',
			'order_by'	=> 'table_id,sort',
			'is_sort'	=> true,
			'is_publish' => true,
			'fieldset'	=> array (
			'title'		=> array (
				'name'  => 'title',
				'title' => 'Заголовок',
				'type'  => 'string',
				'width' => '21%',
				'search'=> true
			),
			'name' => array (
				'name'		=> 'name',
				'title'		=> 'Сист. имя',
				'search'	=> true,
				'type'		=> 'string',
				'help'		=> 'Англ. название поля',
				'width'		=> '21%',
				'search'	=> true
			),
			'table_id' => array (
				'name'		=> 'table_id',
				'title'		=> 'Таблица',
				'type'		=> 'select',
				'l_table'	=> 'table_table',
				'l_field'	=> 'title',
				'width'		=> '21%',
				'search'	=> true
			),
			'type' => array (
				'name'		=> 'type',
				'title'		=> 'Тип поля',
				'type'		=> 'enum',
				'select_values' => 'HTML|html;Выбор|select;Выбор из дерева|select_tree;Выбор (мульти)|select_list;Выбор цвета|color;Галерея|gallery;Дата|date;Дата и время|datetime;Деньги|currency;Изображение|image;Пароль|password;Перечисление|enum;Строка|string;Текст|text;Файл|file;Флажок|checkbox;Целое число|number;Шаблон|template',
				'defvalue'	=> 'string',
				'width'		=> '21%'
			),
			'select_values' => array (
				'name'  => 'select_values',
				'title' => 'Значения',
				'type'  => 'string',
				'help'  => 'Знак-раздельтель &laquo;;&raquo;'
			),
			'params' => array (
				'name'  => 'params',
				'title' => 'Параметры',
				'type'  => 'string'
			),
			'width' => array (
				'name'  => 'width',
				'title' => 'Ширина',
				'type'  => 'string',
				'width' => '10%',
				'defvalue' => '95%',
				'group_update' => true
			),
			'group_update' => array (
				'name'  => 'group_update',
				'title' => 'G',
				'type'  => 'checkbox',
				'width' => '1%',
				'group_update' => true,
				'help'  => 'Групповое обновление'
			),
			'readonly' => array (
				'name'  => 'readonly',
				'title' => 'R',
				'type'  => 'checkbox',
				'width' => '1%',
				'group_update' => true,
				'help' => 'Только чтение'
			),
			'search' => array (
				'name'  => 'search',
				'title' => 'S',
				'type'  => 'checkbox',
				'width' => '1%',
				'group_update' => true,
				'help' => 'Поиск'
			),
			'not_empty' => array (
				'name' => 'not_empty',
				'title' => 'Обяз.',
				'type' => 'checkbox',
				'group_update'  => true,
				'width' => '1%'
			),
			'defvalue' => array (
				'name'  => 'defvalue',
				'title' => 'Значение по умолчанию',
				'search' => true,
				'type'  => 'string'
			)
		));
	}
}	