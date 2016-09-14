<?php

namespace Fuga\CommonBundle\Model;

class User {
	
	public $tables;

	public function __construct() {

		$this->tables = array();

		$this->tables[] = array(
		'name' => 'user',
		'module' => 'user',
		'title' => 'Список пользователей',
		'order_by' => 'login',
		'fieldset' => array (
			'login' => array (
				'name' => 'login',
				'title' => 'Логин',
				'type' => 'string',
				'width' => '25%',
				'search' => true,
			),
			'password' => array (
				'name' => 'password',
				'title' => 'Пароль',
				'type' => 'password',
			),
			'token' => array (
				'name' => 'token',
				'title' => 'Токен',
				'type' => 'string',
				'readonly' => true,
			),
			'hashkey' => array (
				'name' => 'hashkey',
				'title' => 'Ключ',
				'type' => 'string',
				'readonly' => true,
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Имя',
				'type' => 'string',
				'width' => '15%',
				'search' => true,
			),
			'lastname' => array (
				'name' => 'lastname',
				'title' => 'Фамилия',
				'type' => 'string',
				'width' => '15%',
				'search' => true,
			),
			'email' => array (
				'name' => 'email',
				'title' => 'Эл. почта',
				'type' => 'string',
				'width' => '20%',
				'search' => true,
			),
			'group_id' => array (
				'name' => 'group_id',
				'title' => 'Группа',
				'type' => 'select',
				'l_table' => 'user_group',
				'l_field' => 'title',
				'width' => '25%',
				'search' => true,
			),
			'is_admin' => array (
				'name' => 'is_admin',
				'title' => 'Админ',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true
			),
			'is_active' => array (
				'name' => 'is_active',
				'title' => 'Активен',
				'type' => 'checkbox',
				'width' => '1%',
				'group_update' => true,
				'search' => true,
			)	
		));

		$this->tables[] = array(
		'name' => 'group',
		'module' => 'user',
		'title' => 'Группы пользователей',
		'order_by' => 'title',
		'fieldset' => array (
			'title' => array (
				'name' => 'title',
				'title' => 'Название',
				'type' => 'string',
				'width' => '20%',
			),
			'name' => array (
				'name' => 'name',
				'title' => 'Системное имя',
				'type' => 'string',
				'width' => '15%',
				'help' => 'англ. буквы без пробелов',
				'search' => true,
			),
			'rules' => array (
				'name' => 'rules',
				'title' => 'Доступ к модулям',
				'type' => 'select_list',
				'l_table' => 'config_module',
				'l_field' => 'title',
				'view_type' => 'simple', // dialog
				'link_table' => 'user_group_module',
				'link_inversed' => 'group_id',
				'link_mapped' => 'module_id',
				'width' => '60%',
				'search' => true,
			)
		));
		
	}
}