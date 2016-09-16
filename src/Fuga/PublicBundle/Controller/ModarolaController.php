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
		$this->get('log')->addError('Modarola stock');

		if ($this->get('request')->headers->has('X-Auth-Token')
			&& MODAROLA_AUTH_TOKEN == $this->get('request')->headers->get('X-Auth-Token')) {

			$json = file_get_contents('php://input');
			$this->get('log')->addError($json);
			$data = json_decode($json, TRUE);

			foreach ($data as $id => $quantity) {
				$this->get('container')->updateItem(
					'catalog_sku',
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
		$this->get('log')->addError('Modarola products');

		if ($this->get('request')->headers->has('X-Auth-Token')
			&& MODAROLA_AUTH_TOKEN == $this->get('request')->headers->get('X-Auth-Token')) {

			$json = file_get_contents('php://input');
			$this->get('log')->addError($json);
			$data = json_decode($json, TRUE);

			$articuls = array();
			$products = $this->get('container')->getItem('catalog_products');
			foreach ($products as $product) {
				$articuls[$product['id']] = $product['articul'];
			}
			$sizes = $this->get('container')->getItems('catalog_sku');

			foreach ($data as $variant) {
				$size = $this->get('container')->getItem('catalog_sku', 'modarola_id='.$variant[0]);
				if (!array_key_exists($variant[2], $articuls)) {
					continue;
				}
				if ($size) {
					$this->get('container')->updateItem(
						'catalog_sku',
						array('qunanity' => $variant[8]),
						array('id' => $size['id'])
					);
					unset($sizes[$size['id']]);
				} else {
					$this->get('container')->addItem(
						'catalog_sku',
						array(
							'modarola_id' => $variant[0],
							'product_id' => $articuls[$variant[2]],
							'name' => $variant[7],
							'quntity' => $variant[8],
						)
					);
				}
				if (count($sizes) > 0) {
					$ids = array_keys($sizes);
					foreach ($ids as $id) {
						$this->get('container')->updateItem(
							'catalog_sku',
							array('publish' => 0),
							array('id' => $id)
						);
					}

				}

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

} 