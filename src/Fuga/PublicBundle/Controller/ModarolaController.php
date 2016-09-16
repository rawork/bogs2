<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModarolaController extends PublicController
{
	public function __construct()
	{
		parent::__construct('modarola');
	}

	public function indexAction()
	{
		return 'modarola integration';
	}

	public function stockAction()
	{
		$response = new JsonResponse();

		if ($this->get('request')->headers->has('X-Auth-Token')
			&& MODAROLA_AUTH_TOKEN == $this->get('request')->headers->get('X-Auth-Token')) {

			$json = file_get_contents('php://input');
			$this->get('log')->addError($json);
			$data = json_decode($json, TRUE);

			foreach ($data as $id => $quantity) {
				$this->get('container')->updateItem(
					'catalog_sizes',
					array('quantity' => $quantity),
					array('modarola_id'=> $id)
				);
			}

			$response->setData(array(
				'message' => 'ok',
			));
		} else {
			$response->setData(array(
				'message' => 'no',
			));
		}


		return $response;
	}

	public function productsAction()
	{
		$response = new JsonResponse();

		if ($this->get('request')->headers->has('X-Auth-Token')
			&& MODAROLA_AUTH_TOKEN == $this->get('request')->headers->get('X-Auth-Token')) {

			$json = file_get_contents('php://input');
			$this->get('log')->addError($json);
			$data = json_decode($json, TRUE);

			// TODO update product sizes variants


			$response->setData(array(
				'message' => 'ok',
			));

		} else {
			$response->setData(array(
				'message' => 'no',
			));
		}


		return $response;
	}

} 