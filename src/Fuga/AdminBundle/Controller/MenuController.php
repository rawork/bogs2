<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AdminController {

	public function stateAction($state, $module = '')
	{
		if (!$this->get('session')->get('fuga_user')) {
			if ($this->get('request')->isXmlHttpRequest()) {
				return json_encode(array('error' => true));
			}
		}

		$modules = array();
		$modules0 = $this->get('container')->getModulesByState($state);

		if ($module) {
			$entities = $this->getManager('Fuga:Admin:Module')->getEntitiesByModule($module);
		}

		foreach ($modules0 as $mod) {
			$modules[] = array(
				'name' => $mod['name'],
				'title' => $mod['title'],
				'submenu' => $mod['name'] == $module ? $this->render('admin/menu/module.html.twig', compact('entities')) : '',
			);
		}

		$text = $this->get('templating')->render('admin/menu/state.html.twig', compact('state', 'modules', 'module'));

		if ($this->get('request')->isXmlHttpRequest()) {
			$response = new JsonResponse();
			$response->setData(array('content' => $text));

			return $response;
		} else {
			return $text;
		}
	}

	public function moduleAction($module) {
		if (!$this->get('session')->get('fuga_user')) {
			if ($this->get('request')->isXmlHttpRequest()) {
				return json_encode(array('error' => true));
			}
		}

		$entities = $this->getManager('Fuga:Admin:Module')->getEntitiesByModule($module);

		$text = $this->render('admin/menu/module.html.twig', compact('entities'));
		if ($this->get('request')->isXmlHttpRequest()) {
			$response = new JsonResponse();
			$response->setData(array('content' => $text));

			return $response;
		} else {
			return $text;
		}
	}

	public function entityAction($links)
	{
		return $this->render('admin/menu/entity.html.twig', compact('links'));
	}
} 