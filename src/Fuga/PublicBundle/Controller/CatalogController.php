<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CatalogController extends PublicController
{
	public function __construct()
	{
		parent::__construct('catalog');
	}

	public function indexAction()
	{
		$cats = $this->get('container')->getItems('catalog_category', 'publish=1');
		foreach ($cats as &$cat) {
			$cat['items'] = $this->get('container')->getItems('catalog_product', 'publish=1 AND category_id='.$cat['id']);
		}
		unset($cat);
		$cart = $this->get('session')->get('cart');

		return $this->render('catalog/index.html.twig', compact('cats', 'cart'));
	}

	public function productAction($id, $gsize = 0)
	{
		$response = new JsonResponse();

		$product = $this->get('container')->getItem('catalog_product', intval($id));

		if (!$product) {
			if($this->isXmlHttpRequest()) {
				$response->setData(array(
					'title' => 'Ошибка',
					'content' => 'Товар отсутствует',
				));

				return $response;
			}

			return 'Товар отсутствует';
		}

		if ($product['is_preorder']) {
			$sizes = $this->get('container')->getItems('catalog_sku', 'publish=1 AND product_id='.$product['id']);
		} else {
			$sizes = $this->get('container')->getItems('catalog_sku', 'publish=1 AND (quantity>0 OR quantity2>0) AND product_id='.$product['id']);
			// OR quantity3>0
		}

		reset($sizes);
		$first_key = key($sizes);

		$sku = array(
			'id' => $sizes[$first_key],
			'name' => $product['name'],
			'price' => $product['price'],
			'category' => $product['category_id_value']['item']['title'],
		);

		if($this->isXmlHttpRequest()) {
			$response->setData(array(
				'title' => $product['name'],
				'content' => $this->render('catalog/product.html.twig', compact('product', 'sizes')),
				'sku' => $sku
			));

			return $response;
		}

		return $this->render('catalog/productfull.html.twig', compact('product', 'sizes', 'gsize'));
	}

	public function socialAction()
	{
		$items = $this->get('container')->getItems('catalog_social', 'publish=1');

		return $this->render('catalog/social.html.twig', compact('items'));
	}

	public function videoAction()
	{
		$items = $this->get('container')->getItems('catalog_video', 'publish=1');

		return $this->render('catalog/video.html.twig', compact('items'));
	}

	public function advAction()
	{
		$item = $this->get('container')->getItem('catalog_adv', 'publish=1');

		return $this->render('catalog/adv.html.twig', compact('item'));
	}

	public function catalogAction()
	{
		$name = $this->get('request')->request->get('name');
		$email = $this->get('request')->request->get('email');
		$phone = $this->get('request')->request->get('phone');

		$this->get('mailer')->send(
			'Заказ каталога на сайте '.$_SERVER['SERVER_NAME'],
			$this->render('mail/catalog.html.twig', compact('name', 'email', 'phone')),
			array(ADMIN_EMAIL)
		);

		$response = new JsonResponse();
		$response->setData(array(
			'text' => 'Мы отправим вам оптовый прайс-лист в ближайшее время.',
		));

		return $response;
	}

	public function callAction()
	{
		if ('POST' != $_SERVER['REQUEST_METHOD'] || !$this->isXmlHttpRequest()) {
			$this->get('log')->addError('ORDER CALL: direct access to form');
			return $this->redirect('/');
		}

		$lastname = $this->get('request')->request->get('lastname');
		$name = $this->get('request')->request->get('name');
		$phone = $this->get('request')->request->get('phone');
		$csrf = $this->get('request')->request->get('csrf_token');

		if ($this->get('session')->get('csrf_token') != $csrf) {
			$this->get('log')->addError('ORDER CALL: csrf error');
			return $this->redirect('/');
		}

		$this->get('log')->addError('ORDER CALL: referer'.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));

		if (empty($lastname)) {
			$this->get('mailer')->send(
				'Заказ звонка на сайте '.$_SERVER['SERVER_NAME'],
				$this->render('mail/call.html.twig', compact('name', 'phone')),
				array(ADMIN_EMAIL, 'rawork@yandex.ru')
			);
		} else {
			$this->get('log')->addError('ORDER CALL: bot');
		}

		$response = new JsonResponse();
		$response->setData(array(
			'text' => 'Мы перезвоним Вам в ближайшее время',
		));

		return $response;
	}

}