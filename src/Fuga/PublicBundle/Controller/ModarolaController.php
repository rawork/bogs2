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
			$data = json_decode($json, true);

			$this->get('log')->addError(serialize($data));

			foreach ($data as $id => $quantity) {

				try {
					$sku = $this->get('container')->getItem('catalog_sku', 'modarola_id='.$id);

					$this->get('container')->updateItem(
						'catalog_sku',
						array('quantity' => $quantity, 'updated' => date('Y-m-d H:i:s')),
						array('modarola_id'=> $id)
					);

					if ($sku) {
						$sku2 = $this->get('container')->getItem('catalog_sku', 'modarola_id='.$id);

						$this->get('container')->addItem(
							'catalog_stock_history',
							array(
								'articul' => $sku['product_id_value']['item']['articul'],
								'modarola_id' => $sku['modarola_id'],
								'quantity_old' => $sku['quantity'],
								'quantity_new' => $sku2['quantity'],
								'date' => date('Y-m-d H:i:s'),
								'created' => date('Y-m-d H:i:s'),
							)
						);
					}
				} catch (\Exception $e) {
					$this->get('log')->addError('Modarole sync error:'. $e->getMessage());
				}


			}

			$response->setData(array(
				'message' => 'ok',
			));
		} else {
            $this->get('log')->addError('Modarola stock no auth');
			$response->setData(array(
				'message' => 'no',
			));
		}

		return $response;
	}

	public function productsAction()
	{
		$this->get('log')->addError('Modarola products');

		$url = "http://modarola.ru/api/products";
		$response = \Httpful\Request::get($url)
			->expectsJson()
			->addHeader('X-Auth-Token', MODAROLA_AUTH_TOKEN)
			->send();

		$articuls = array();
		$products = $this->get('container')->getItems('catalog_product');
		foreach ($products as $product) {
			$articuls[$product['articul']] = $product['id'];
		}
		$sizes = $this->get('container')->getItems('catalog_sku');

		$this->get('log')->addError(serialize($response->body));

		foreach ($response->body as $variant) {
			$size = $this->get('container')->getItem('catalog_sku', 'modarola_id='.$variant->variant_id);
			if (!array_key_exists($variant->product_sku, $articuls)) {
				continue;
			}
			if ($size) {
				unset($sizes[$size['id']]);
			} else {
				$this->get('container')->addItem(
					'catalog_sku',
					array(
						'modarola_id' => $variant->variant_id,
						'product_id' => $articuls[$variant->product_sku],
						'size' => $variant->variant_size,
						'publish' => $variant->product_enabled == 'Y' ? 1 : 0,
					)
				);
			}
		}

//		if (count($sizes) > 0) {
//			$ids = array_keys($sizes);
//			foreach ($ids as $id) {
//				$this->get('container')->updateItem(
//					'catalog_sku',
//					array('publish' => 0),
//					array('id' => $id)
//				);
//			}
//
//		}

		$response = new JsonResponse();
		$response->setData(array(
			'message' => 'ok',
		));

		return $response;
	}

} 