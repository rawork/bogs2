<?php

namespace Fuga\AdminBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends Controller
{
	public function render($template, $params = array())
	{
		$params['user'] = $this->get('security')->getCurrentUser();
		$params['states'] = $this->getManager('Fuga:Admin:Module')->getStates();
		$params['locales'] = $this->getManager('Fuga:Common:Locale')->getLocales();
		$params['currentLocale'] = $this->get('session')->get('locale');
		$params['fugaVersion'] = LIB_VERSION;
		$params['mainurl'] = $this->get('container')->getVar('mainurl');

		return parent::render($template, $params);
	}

	public function rppAction()
	{
		$table = $this->get('request')->request->get('table');
		$rpp = $this->get('request')->request->get('rpp', 25);
		$this->get('session')->set($table.'_rpp', $rpp);
		$response = new JsonResponse();
		$response->setData(array('status' => $this->get('session')->get($table.'_rpp')));

		return $response;
	}
	
}
