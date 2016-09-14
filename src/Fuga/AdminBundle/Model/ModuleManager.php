<?php

namespace Fuga\AdminBundle\Model;

use Fuga\CommonBundle\Model\ModelManager;

class ModuleManager extends ModelManager
{

	private $states = array(
		'content' => 'Структура и контент',
		'service' => 'Сервисы',
		'system'  => 'Настройки',
	);

	function getEntitiesByModule($moduleName)
	{
		$ret = array();
		$module = $this->get('container')->getModule($moduleName);
		$tables = $this->get('container')->getTables($moduleName);

		foreach ($tables as $table) {
			if (empty($table->params['is_hidden'])) {
				$ret[] = array (
					'ref' => $this->get('routing')->getGenerator()->generate(
							'admin_entity_index',
							array('state' => $module['ctype'], 'module' => $module['name'], 'entity' => $table->name)
					),
					'name' => $table->title
				);
			}
		}
		if ($this->get('security')->isSuperuser()) {
			if ($this->get('container')->getManager('Fuga:Common:Param')->findAll($module['name'])) {
				$ret[] = array (
					'ref' => $this->get('routing')->getGenerator()->generate(
						'admin_module_setting',
						array('state' => $module['ctype'], 'module' => $module['name'])
					),
					'name' => 'Настройки'
				);
			}
		}
		if ($module['name'] == 'config' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->get('routing')->getGenerator()->generate('admin_service'),
				'name' => 'Обслуживание'
			);
		}
		if ($module['name'] == 'subscribe' && $this->get('security')->isSuperuser()) {
			$ret[] = array (
				'ref' => $this->get('routing')->getGenerator()->generate('admin_subscribe_export'),
				'name' => 'Экспорт'
			);
		}

		return $ret;
	}

	// TODO  не используется
	public function getModule($moduleName)
	{
		if (isset($this->modules[$moduleName]) && $this->modules[$moduleName]->isAvailable()){
			return $this->modules[$moduleName];
		} else {
			throw new \Exception('Отсутствует запрашиваемый модуль: '.$moduleName);
		}
	}

	// TODO доработать проверку прав на модуль, не используется
	function isAvailable()
	{
		return $this->get('security')->isSuperuser() || 1 == $this->users[$this->get('session')->get('fuga_user')];
	}

	public function getByState($state, $currentModule = '')
	{
		$modules = array();
		$modules0 = $this->get('container')->getModulesByState($state);
		if ($modules0) {
			$basePath = PRJ_REF.'/bundles/admin/img/module/';
			foreach ($modules0 as $module) {
				$icon = $this->get('fs')->exists(PRJ_DIR.$basePath.$module['name'].'.gif')
						? $basePath.$module['name'].'.gif'
						: $basePath.'folder'.'.gif';
				$modules[] = array(
					'name' => $module['name'],
					'title' => $module['title'],
					'icon' => $icon,
					'current' => $module['name'] == $currentModule
				);
			}
		}

		return $modules;
	}

	public function getStates()
	{
		return $this->states;
	}

}
