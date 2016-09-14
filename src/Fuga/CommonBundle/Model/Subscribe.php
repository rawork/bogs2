<?php

namespace Fuga\CommonBundle\Model;

class Subscribe {
	
	public $tables;

	public function __construct() {

		$this->tables = array();
//		$this->tables[] = array(
//			'name' => 'lists',
//			'module' => 'subscribe',
//			'title' => 'Очередь рассылки',
//			'order_by' => 'date',
//			'fieldset' => array (
//			'rubrics' => array (
//				'name' => 'rubrics',
//				'title' => 'Списки рассылки',
//				'type' => 'select_list',
//				'l_table' => 'subscribe_rubric',
//				'l_field' => 'name',
//				'width' => '30%'
//			),
//			'subject' => array (
//				'name' => 'subject',
//				'title' => 'Тема',
//				'type' => 'string',
//				'width' => '35%',
//				'search'=> true
//			),
//			'message' => array (
//				'name' => 'message',
//				'title' => 'Текст',
//				'type' => 'html'
//			),
//			'file' => array (
//				'name' => 'file',
//				'title' => 'Файл',
//				'type' => 'file',
//				'path' => '/mailfiles',
//				'width' => '20%'
//
//			),
//			'date' => array (
//				'name' => 'date',
//				'title' => 'Дата',
//				'type' => 'datetime',
//				'width' => '15%',
//				'search'=> true
//			)
//		));

		$this->tables[] = array(
			'name' => 'subscriber',
			'module' => 'subscribe',
			'title' => 'Подписчики',
			'order_by' => 'created',
			'fieldset' => array (
			'email' => array (
				'name'  => 'email',
				'title' => 'Адрес',
				'type'  => 'string',
				'width' => '24%',
				'search'=> true
			),
			'rubrics' => array (
				'name' => 'rubrics',
				'title' => 'Списки рассылки',
				'type' => 'select_list',
				'l_table' => 'subscribe_rubric',
				'l_field' => 'name',
				'view_type' => 'simple', // dialog
				'link_table' => 'subscribe_subscriber_rubric',
				'link_inversed' => 'subscriber_id',
				'link_mapped' => 'rubric_id',
				'width' => '47%'
			),
			'hashkey' => array (
				'name'  => 'hashkey',
				'title' => 'Активационный ключ',
				'type'  => 'string',
				'readonly' => true
			),
			'date' => array (
				'name'  => 'date',
				'title' => 'Дата регистрации',
				'type'  => 'datetime',
				'width' => '24%',
				'search'=> true
			),
			'is_active' => array (
				'name' => 'is_active',
				'title' => 'Акт.',
				'type' => 'checkbox',
				'search' => true,
				'group_update'  => true,
				'width' => '1%'
			)
		));

		$this->tables[] = array(
			'name' => 'rubric',
			'module' => 'subscribe',
			'title' => 'Списки рассылки',
			'order_by' => 'name',
			'fieldset' => array (
			'name' => array (
				'name'  => 'name',
				'title' => 'Имя',
				'type'  => 'string',
				'width' => '95%',
				'search'=> true
			)
		));
	}
}
