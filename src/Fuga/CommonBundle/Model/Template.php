<?php
	
namespace Fuga\CommonBundle\Model;

class Template {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
		$this->tables[] = array(
		'name' => 'template',
		'module' => 'template',
		'title' => 'Шаблоны',
		'order_by' => 'name',
		'is_lang' => true,
		'fieldset' => array (
			'name' => array (
				'name' => 'name',
				'title' => 'Название макета',
				'type' => 'string',
				'width' => '95%'
			),
			'template' => array (
				'name' => 'template',
				'title' => 'Шаблон HTML',
				'type' => 'template'
			)
		));
		$this->tables[] = array(
		'name' => 'version',
		'module' => 'template',
		'title' => 'Версионирование',
		'order_by' => 'id DESC',
		'is_hidden' => true,
		'fieldset' => array (
			'table_name' => array (
				'name' => 'table_name',
				'title' => 'Таблица',
				'type' => 'string',
				'width' => '20%',
				'search'=> true
			),
			'field_name' => array (
				'name' => 'field_name',
				'title' => 'Поле',
				'type' => 'string',
				'width' => '25%',
				'search'=> true
			),
			'entity_id' => array (
				'name' => 'entity_id',
				'title' => 'Запись',
				'type' => 'number',
				'width' => '25%',
				'search' => true
			),
			'file' => array (
				'name'  => 'file',
				'title' => 'Файл-версия',
				'type' => 'file',
				'width' => '25%'
			)
		));
		$this->tables[] = array(
		'name' => 'rule',
		'module' => 'template',
		'title' => 'Правила шаблонов',
		'order_by' => 'sort',
		'is_lang' => true,
		'is_sort' => true,
		'fieldset' => array (
			'template_id' => array (
				'name' => 'template_id',
				'title' => 'Шаблон',
				'type' => 'select',
				'l_table' => 'template_template',
				'l_field' => 'name',
				'l_lang' => true,
				'width' => '31%'
			),
			'type' => array (
				'name' => 'type',
				'title' => 'Тип условия',
				'type' => 'enum',
				'select_values' => 'Раздел|F;Параметр URL|U;Период времени|T',
				'width' => '20%'
			),
			'cond' => array (
				'name' => 'cond',
				'title' => 'Условие',
				'type' => 'string',
				'width' => '20%'
			),
			'datefrom' => array (
				'name' => 'datefrom',
				'title' => 'Начало показа',
				'type' => 'datetime',
				'width' => '12%'
			),
			'datetill' => array (
				'name' => 'datetill',
				'title' => 'Конец показа',
				'type' => 'datetime',
				'width' => '12%'
			)
		));
	}
}	