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

	public function productAction($id)
	{
		$response = new JsonResponse();
		$product = $this->get('container')->getItem('catalog_product', $id);

		if (!$product) {
			$response->setData(array(
				'title' => 'Ошибка',
				'content' => 'Товар отсутствует',
			));

			return $response;
		}

		$response->setData(array(
			'title' => $product['name'],
			'content' => $this->render('catalog/product.html.twig', compact('product')),
		));

		return $response;
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
		$items = $this->get('container')->getItems('catalog_adv', 'publish=1');

		return $this->render('catalog/adv.html.twig', compact('items'));
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
			'text' => 'Мы отправим Вам PDF-каталог в ближайшее время',
		));

		return $response;
	}

	public function callAction()
	{
		$name = $this->get('request')->request->get('name');
		$phone = $this->get('request')->request->get('phone');

		$this->get('mailer')->send(
			'Заказ звонка на сайте '.$_SERVER['SERVER_NAME'],
			$this->render('mail/call.html.twig', compact('name', 'phone')),
			array(ADMIN_EMAIL)
	);

		$response = new JsonResponse();
		$response->setData(array(
			'text' => 'Мы перезвоним Вам в ближайшее время',
		));

		return $response;
	}

	public function orderAction()
	{
		$products = $this->get('request')->request->get('products');
		$name = $this->get('request')->request->get('name');
		$email = $this->get('request')->request->get('email');
		$phone = $this->get('request')->request->get('phone');
		$address = $this->get('request')->request->get('address');

		$detail = '';
		foreach ($products as $item) {
			$product = $this->get('container')->getItem('catalog_product', $item['id']);
			if ($product) {
				$detail .= $product['name'].'('.$product['articul'].') - Размер '.$item['size']."\n";
			}
		}

		$this->get('container')->addItem(
			'catalog_order_product',
			array(
				'detail' => $detail,
				'name' => $name,
				'email' => $email,
				'phone' => $phone,
				'address' => $address,
			)
		);

		$detail = nl2br($detail);

		$this->get('mailer')->send(
			'Заказ товара на сайте '.$_SERVER['SERVER_NAME'],
			$this->render('mail/order.html.twig', compact('detail', 'name', 'email', 'phone', 'address')),
			array(ADMIN_EMAIL)
		);

		$this->get('session')->set('cart', array());

		$response = new JsonResponse();
		$response->setData(array(
			'text' => 'Ваш заказ принят. Скоро Мы свяжемся с Вами',
		));

		return $response;
	}

	public function cartAction()
	{
		if ($this->get('session')->has('cart')){
			$num = count($this->get('session')->get('cart'));
		} else {
			$num = 0;
		}


		$ending = $this->getNumEnding($num);
		$response = new JsonResponse();
		$response->setData(array(
			'text' => $this->render('catalog/cart.html.twig', compact('num', 'ending')),
		));

		return $response;
	}

	public function addAction()
	{
		$id = $this->get('request')->request->get('id');
		$amount = $this->get('request')->request->get('amount');

		if (!$this->get('session')->has('cart')) {
			$this->get('session')->set('cart', array());
		}

		$cart = $this->get('session')->get('cart');

		if (!isset($cart[$id])) {
			$cart[$id] = array(
				'product' => $this->get('container')->getItem('catalog_product', $id),
				'amount' => $amount,
				'size' => '',
			);
		} else {
			$cart[$id]['amount'] += $amount;
		}
		if ($cart[$id]['amount'] <= 0) {
			unset($cart[$id]);
		}

		$this->get('session')->set('cart', $cart);

		$num = count($cart);
		$ending = $this->getNumEnding($num);

		$response = new JsonResponse();
		$response->setData(array(
			'text' => $this->render('catalog/cart.html.twig', compact('num', 'ending')),
		));

		return $response;
	}

	public function formAction()
	{
		$cart = $this->get('session')->get('cart');
		$response = new JsonResponse();
		$response->setData(array(
			'text' => $this->render('catalog/order.html.twig', compact('cart')),
		));

		return $response;
	}

	private function getNumEnding($number, $endingArray = array('пару', 'пары', 'пар'))
	{
		$number = $number % 100;
		if ($number>=11 && $number<=19) {
			$ending=$endingArray[2];
		}
		else {
			$i = $number % 10;
			switch ($i)
			{
				case (1): $ending = $endingArray[0]; break;
				case (2):
				case (3):
				case (4): $ending = $endingArray[1]; break;
				default: $ending=$endingArray[2];
			}
		}
		return $ending;
	}

} 