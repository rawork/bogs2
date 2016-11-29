<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BasketController extends PublicController
{
	private $delivery = array(
		'self' 			=> 'Самовывоз',
		'courier' 		=> 'Доставка по Москве курьером',
		'mkad_post' 	=> 'За МКАД &laquo;Почта России&raquo;',
		'mkad_carrier' 	=> 'За МКАД курьером',
		'russia_post' 	=> 'По России &laquo;Почта России&raquo',
		'russia_carrier'=> 'По России курьером',
		'sng_post' 		=> 'Страны СНГ &laquo;Почта Росии&raquo;',
	);
	private $payment = array(
		'cash' => 'Оплата наличными при получении',
		'card' => 'Оплата банковской картой'
	);

	private $statuses = array(
		'new' => 'Получен, в обработке',
		'calc' => 'Требуется расчет доставки',
		'wait' => 'Ожидает оплаты',
		'paid' => 'Оплачен',
		'delivered' => 'Передан в службу доставки',
		'completed' => 'Выполнен',
	);

	private $paymentResults = array(
		'success'    => 'Платеж выполнен успешно',
		'failed'     => 'Ошибка платежа',
		'inprogress' =>	'Платеж в обработке, или аккаунт продавца не активен',
		'deferred'   => 'Платеж ожидает подтверждения продавца (Отложенный платеж)',
	);

	public function __construct()
	{
		parent::__construct('basket');
		if (!$this->get('session')->has('cart')) {
			$this->get('session')->set('cart', array());
			$this->get('session')->set('num', 0);
			$this->get('session')->set('total', 0);
			$this->get('session')->set('cart.delivery.type', '');
			$this->get('session')->set('cart.delivery.country', '');
			$this->get('session')->set('cart.delivery.region', '');
			$this->get('session')->set('cart.delivery.city', '');
			$this->get('session')->set('cart.delivery.cost', '');
			$this->get('session')->set('cart.payment.type', '');
			$this->get('session')->set('cart.buyer', array(
				'name' => '',
				'lastname' => '',
				'email' => '',
				'phone' => ''
			));
			$this->get('session')->set('cart.delivery.address', array(
				'postindex' => '',
				'country' => '',
				'region' => '',
				'city' => '',
				'street' => '',
				'house' => '',
				'building' => '',
				'apartment' => '',
			));
		}
	}

	public function indexAction()
	{
		$cart = $this->get('session')->get('cart');
		$total = $this->get('session')->get('total');
		$payment_type = $this->get('session')->get('cart.payment.type');
		$delivery_type = $this->get('session')->get('cart.delivery.type');

//		var_dump(array_shift($cart)['sku']['product_id_value']['item']);
//		var_dump($_SESSION);

		$this->get('container')->addScript('/bundles/public/js/cart.js');

		return $this->render('basket/index.html.twig', compact('cart', 'total', 'payment_type' , 'delivery_type'));
	}

	public function miniAction()
	{
		$num = $this->get('session')->get('num');
		$total = $this->get('session')->get('total');
		$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

		return $this->render('basket/mini.html.twig', compact('ending', 'num', 'total'));
	}

	public function editAction()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$id = $this->get('request')->request->get('id');
			$amount = $this->get('request')->request->get('amount');

			$cart = $this->get('session')->get('cart');
			$sku = $this->get('container')->getItem('catalog_sku', 'id=' . $id);

			if (isset($cart[$id])) {
				$cart[$id]['amount'] += $amount;
			} else {
				$cart[$id] = array(
					'sku' => $sku,
					'product' => $this->get('container')->getItem('catalog_product', $sku['product_id']),
					'amount' => $amount,
				);
			}
			if ($cart[$id]['amount'] <= 0) {
				unset($cart[$id]);
			}

			$num = 0;
			$total = 0;
			foreach ($cart as $item) {
				$num += $item['amount'];
				$total += $item['amount'] * $item['sku']['product_id_value']['item']['price'];
			}

//		$this->get('log')->addError($id);
//		$this->get('log')->addError(json_encode($sku));
//		$this->get('log')->addError(json_encode($cart));

			$this->get('session')->set('cart', $cart);
			$this->get('session')->set('num', $num);
			$this->get('session')->set('total', $total);

			$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

			$response = new JsonResponse();
			$response->setData(array(
				'minicart' => $this->render('basket/mini.html.twig', compact('num', 'total', 'ending')),
			));

			return $response;
		}

		return $this->redirect('/');
	}

	public function amountAction()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$id = $this->get('request')->request->get('id');
			$amount = $this->get('request')->request->get('amount');

			$cart = $this->get('session')->get('cart');

			if (isset($cart[$id])) {
				$cart[$id]['amount'] = $amount;
			}

			$num = 0;
			$total = 0;
			foreach ($cart as $item) {
				$num += $item['amount'];
				$total += $item['amount'] * $item['sku']['product_id_value']['item']['price'];
			}

			$this->get('session')->set('cart', $cart);
			$this->get('session')->set('num', $num);
			$this->get('session')->set('total', $total);

			$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

			$response = new JsonResponse();
			$response->setData(array(
				'minicart' => $this->render('basket/mini.html.twig', compact('num', 'total', 'ending')),
			));

			return $response;
		}

		return $this->redirect('/');
	}

	public function removeAction()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$id = $this->get('request')->request->get('id');
			$cart = $this->get('session')->get('cart');

			if (isset($cart[$id])) {
				unset($cart[$id]);
			}

			$num = 0;
			$total = 0;
			foreach ($cart as $item) {
				$num += $item['amount'];
				$total += $item['amount'] * $item['sku']['product_id_value']['item']['price'];
			}

			$this->get('session')->set('cart', $cart);
			$this->get('session')->set('num', $num);
			$this->get('session')->set('total', $total);

			$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

			$response = new JsonResponse();
			$response->setData(array(
				'status' => true,
				'minicart' => $this->render('basket/mini.html.twig', compact('num', 'total', 'ending')),
			));

			return $response;
		}

		return $this->redirect('/');
	}

	public function infoAction()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$response = new JsonResponse();
			try {
				$delivery_type = $this->get('request')->request->get('delivery_type');
				$delivery_country = $this->get('request')->request->get('delivery_country');
				$delivery_region = $this->get('request')->request->get('delivery_region');
				$delivery_city = $this->get('request')->request->get('delivery_city');
				$delivery_cost = $this->get('request')->request->get('delivery_cost');
				$payment_type = $this->get('request')->request->get('payment_type');

				$this->get('session')->set('cart.delivery.type', $delivery_type);
				$this->get('session')->set('cart.delivery.country', $delivery_country);
				$this->get('session')->set('cart.delivery.region', $delivery_region);
				$this->get('session')->set('cart.delivery.city', $delivery_city);
				$this->get('session')->set('cart.delivery.cost', $delivery_cost);
				$this->get('session')->set('cart.payment.type', $payment_type);

				if ($delivery_type == 'courier') {
					$this->get('session')->set('cart.delivery.region', 'Москва');
					$this->get('session')->set('cart.delivery.city', 'Москва');
				}

				$response->setData(array('status' => 'ok'));
			} catch (\Exception $e) {
				$this->get('log')->addError($e->getMessage());
				$response->setData(array('status' => 'error'));
			}

			return $response;
		}

		return $this->redirect('/');
	}

	public function buyerAction()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$response = new JsonResponse();
			try {
				$name = $this->get('request')->request->get('delivery_type');
				$lastname = $this->get('request')->request->get('delivery_country');
				$email = $this->get('request')->request->get('delivery_region');
				$phone = $this->get('request')->request->get('delivery_city');

				$addressIndex = $this->get('request')->request->get('index');
				$addressCountry = $this->get('request')->request->get('country');
				$addressRegion = $this->get('request')->request->get('region');
				$addressCity = $this->get('request')->request->get('city');
				$addressHouse = $this->get('request')->request->get('house');
				$addressBuilding = $this->get('request')->request->get('building');
				$addressApartment = $this->get('request')->request->get('apartment');

				$this->get('session')->set('cart.buyer', array(
					'name' => $name,
					'lastname' => $lastname,
					'email' => $email,
					'phone' => $phone,
				));
				$address = array();
				if ($addressIndex) {
					$address['index'] = $addressIndex;
				}
				if ($addressCountry) {
					$address['country'] = $addressCountry;
				}
				if ($addressRegion) {
					$address['region'] = $addressRegion;
				}
				if ($addressCity) {
					$address['city'] = $addressCity;
				}
				if ($addressHouse) {
					$address['house'] = $addressHouse;
				}
				if ($addressBuilding) {
					$address['building'] = $addressBuilding;
				}
				if ($addressApartment) {
					$address['apartment'] = $addressApartment;
				}

				$this->get('session')->set('cart.delivery.address', $address);

				$response->setData(array('status' => 'ok'));
			} catch (\Exception $e) {
				$this->get('log')->addError($e->getMessage());
				$response->setData(array('status' => 'error'));
			}

			return $response;
		}

		return $this->redirect('/');
	}

	public function newAction()
	{
		$cart = $this->get('session')->get('cart');
		$num = $this->get('session')->get('num');
		$total = $this->get('session')->get('total');
		$buyer = $this->get('session')->get('cart.buyer');
		$delivery_info = $this->get('session')->get('cart.delivery.address');

		$delivery_type = $this->get('session')->get('cart.delivery.type', 'self');
		$payment_type = $this->get('session')->get('cart.payment.type', 'card');
		$delivery_type_title = $this->delivery[$delivery_type];
		$delivery_cost = $this->get('session')->get('cart.delivery.cost');
		$delivery_country = $this->get('session')->get('cart.delivery.country');
		$delivery_region = $this->get('session')->get('cart.delivery.region');
		$delivery_city = $this->get('session')->get('cart.delivery.city');
		$payment_type_title = $this->payment[$payment_type];

		$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

		$countries = $this->get('container')->getItems('basket_country', 'publish=1');

		$this->get('container')->setVar('title', 'Оформление заказа');
		$this->get('container')->setVar('h1', 'Оформление заказа');

		$this->get('container')->addScript('/bundles/public/js/order.js');

		return $this->render('basket/new.html.twig', compact('cart', 'num', 'ending', 'total', 'delivery_type', 'delivery_type_title', 'delivery_cost', 'delivery_country', 'delivery_region', 'delivery_city', 'payment_type', 'payment_type_title', 'buyer', 'delivery_info', 'countries'));
	}

	public function saveAction(){
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->isXmlHttpRequest()) {
			$response = new JsonResponse();

			$cart = $this->get('session')->get('cart');
			$num = $this->get('session')->get('num');
//			$total = $this->get('session')->get('total');
			$delivery_type = $this->get('session')->get('cart.delivery.type', 'self');
			$payment_type = $this->get('session')->get('cart.payment.type', 'card');
			$delivery_type_title = $this->delivery[$delivery_type];
			$delivery_cost = $this->get('session')->get('cart.delivery.cost');
			$payment_type_title = $this->payment[$payment_type];

			$buyer = array(
				'name' => $this->get('request')->request->get('name', ''),
				'lastname' => $this->get('request')->request->get('lastname', ''),
				'email' => $this->get('request')->request->get('email', ''),
				'phone' => $this->get('request')->request->get('phone', ''),
			);

			$delivery_info = array();
			if ($this->get('request')->request->get('country', '')) {
				$delivery_info['country'] = $this->get('request')->request->get('country', '');
			}
			if ($this->get('request')->request->get('postindex', '')) {
				$delivery_info['postindex'] = $this->get('request')->request->get('postindex', '');
			}
			if ($this->get('request')->request->get('region', '')) {
				$delivery_info['region'] = $this->get('request')->request->get('region', '');
			}
			if ($this->get('request')->request->get('city', '')) {
				$delivery_info['city'] = $this->get('request')->request->get('city', '');
			}
			if ($this->get('request')->request->get('street', '')) {
				$delivery_info['street'] = $this->get('request')->request->get('street', '');
			}
			if ($this->get('request')->request->get('house', '')) {
				$delivery_info['house'] = $this->get('request')->request->get('house', '');
			}
			if ($this->get('request')->request->get('building', '')) {
				$delivery_info['building'] = $this->get('request')->request->get('building', '');
			}
			if ($this->get('request')->request->get('apartment', '')) {
				$delivery_info['apartment'] = $this->get('request')->request->get('apartment', '');
			}

			$delivery_info_string = join(', ', $delivery_info);

			switch ($payment_type) {
				case 'card':
					$order_status = 'wait';
					break;
				default:
					$order_status = 'new';
			}

			if (in_array($delivery_type, array('russia_post', 'russia_carrier', 'sng_post')) ) {
				$order_status = 'calc';
			}


			$user = $this->get('container')->getItem('basket_user', 'email="'.$buyer['email'].'"');
			if ($user) {
				$user_id = $user['id'];
				if (count($delivery_info) > 0) {
					$this->get('container')->updateItem(
						'basket_user',
						$delivery_info,
						array('id' => $user_id)
					);
				}
			} else {
				try {
					$user_id = $this->get('container')->addItem(
						'basket_user',
						array_merge($buyer, $delivery_info, array('publish' => 1))
					);
				} catch(\Exception $e){
					$this->get('log')->addError($e->getMessage());
					$response->setData(array(
						'status' => 'error',
						'error' => 'Ошибка добавления пользователя',
					));

					return $response;
				}
			}

			$realProducts = array();
			$realTotal = 0;
			$realNum = 0;
			$preorderProducts = array();
			$preorderTotal = 0;
			$preorderNum = 0;

			$orderData = array(
				'user_id' => $user_id,
				'delivery_type' => $delivery_type,
				'delivery_cost' => $delivery_cost,
				'delivery_details' => count($delivery_info) > 0 ? json_encode($delivery_info) : '',
				'address' => $delivery_info_string,
				'payment_type' => $payment_type,
				'order_status'=> $order_status,
			);

			$orderData = array_merge($orderData, $buyer);

			foreach ($cart as $product){
				$sku = array(
					'photo' => $product['product']['photo_value']['extra']['thumb']['path'],
					'name'  => $product['product']['name'].'(Артикул-'.$product['product']['articul'].')',
					'size'  => $product['sku']['size'].' US',
					'price' => $product['product']['price'],
					'amount'=> $product['amount'],
					'local' => $product['sku']['quantity2'],
					'sku'   => $product['sku']['id'],
				);
				if ($product['product']['is_preorder'] == 1) {
					$preorderProducts[] = $sku;
					$preorderTotal += $sku['price']*$sku['amount'];
					$preorderNum += $sku['amount'];
				} else {
					$realProducts[] = $sku;
					$realTotal += $sku['price']*$sku['amount'];
					$realNum += $sku['amount'];
				}
			}

			$link = '';

			if ($preorderTotal > 0) {
				$preorderData = $orderData;
				$preorderData['cost'] = $preorderTotal;
				$preorderData['num'] = $preorderNum;
				$preorderData['is_preorder'] = 1;
				$preorderData['detail_json'] = json_encode($preorderProducts);

				$strProducts = '';
				$strProducts .= join("\t", array('Название', 'Размер', 'Цена, руб.', 'Количество, шт.'))."\n";
				foreach ($preorderProducts as $product) {
					array_shift($product);
					$strProducts .= join("\t", $product)."\n";
				}

				$preorderData['detail'] = $strProducts;

				$this->get('connection')->beginTransaction();

				try {
					$order_id = $this->get('container')->addItem('basket_order', array_merge($preorderData, array('publish' => 1)));

					foreach($preorderProducts as $product) {

						$quantity2 = $product['amount'] <= $product['local'] ? $product['local']-$product['amount'] : 0;

						$this->get('container')->updateItem(
							'catalog_sku',
							array('quantity2' => $quantity2),
							array('id' => $product['sku'])
						);
					}

					$link = $this->generateUrl('order', array('id' => md5($buyer['email'].$order_id)));

					$this->get('mailer')->send(
						'Предзаказ №'.$order_id.' оформлен в интернет магазине BOGS-SHOP.RU',
						$this->render(
							'mail/preorder.buyer.html.twig',
							array(
								'order' => $preorderData,
								'details' => json_decode($orderData['detail_json'], true),
								'order_id' => $order_id,
								'delivery_type_title' => $delivery_type_title,
								'payment_type_title' => $payment_type_title,
								'delivery_info_string' => $delivery_info_string,
								'status_title' => $this->statuses[$preorderData['order_status']],
								'link' => 'http://'.$_SERVER['SERVER_NAME'].$link,
							)
						),
						$buyer['email']
					);

					$this->get('mailer')->send(
						'Предзаказ №'.$order_id.' оформлен в BOGS-SHOP.RU',
						$this->render(
							'mail/preorder.admin.html.twig',
							array(
								'order' => $preorderData,
								'details' => json_decode($preorderData['detail_json'], true),
								'order_id' => $order_id,
								'delivery_type_title' => $delivery_type_title,
								'payment_type_title' => $payment_type_title,
								'delivery_info_string' => $delivery_info_string,
								'status_title' => $this->statuses[$preorderData['order_status']],
								'link' => 'http://'.$_SERVER['SERVER_NAME'].$link,
							)
						),
						ADMIN_EMAIL
					);

					$this->get('connection')->commit();

				} catch (\Exception $e) {

					$this->get('connection')->rollBack();

					$this->get('log')->addError($e->getMessage());
					$response->setData(array(
						'status' => 'error',
						'error' => 'Ошибка добавления предзаказа',
					));

					return $response;
				}
			}

			if ($realTotal > 0) {

				$orderData['cost'] = $realTotal;
				$orderData['num'] = $realNum;
				$orderData['is_preorder'] = 0;
				$orderData['detail_json'] = json_encode($realProducts);

				$strProducts = '';
				$strProducts .= join("\t", array('Название', 'Размер', 'Цена, руб.', 'Количество, шт.')) . "\n";
				foreach ($realProducts as $product) {
					array_shift($product);
					$strProducts .= join("\t", $product) . "\n";
				}

				$orderData['detail'] = $strProducts;

				$this->get('connection')->beginTransaction();

				try {
					$order_id = $this->get('container')->addItem('basket_order', array_merge($orderData, array('publish' => 1)));

					foreach($realProducts as $product) {

						$quantity2 = $product['amount'] <= $product['local'] ? $product['local']-$product['amount'] : 0;

						$this->get('container')->updateItem(
							'catalog_sku',
							array('quantity2' => $quantity2),
							array('id' => $product['sku'])
						);
					}

					$link = $this->generateUrl('order', array('id' => md5($buyer['email'] . $order_id)));

					$this->get('mailer')->send(
						'Заказ №' . $order_id . ' оформлен в интернет магазине BOGS-SHOP.RU',
						$this->render(
							'mail/order.buyer.html.twig',
							array(
								'order' => $orderData,
								'details' => json_decode($orderData['detail_json'], true),
								'order_id' => $order_id,
								'delivery_type_title' => $delivery_type_title,
								'payment_type_title' => $payment_type_title,
								'delivery_info_string' => $delivery_info_string,
								'status_title' => $this->statuses[$orderData['order_status']],
								'link' => 'http://' . $_SERVER['SERVER_NAME'] . $link,
							)
						),
						$buyer['email']
					);

					$this->get('mailer')->send(
						'Заказ №' . $order_id . ' оформлен в BOGS-SHOP.RU',
						$this->render(
							'mail/order.admin.html.twig',
							array(
								'order' => $orderData,
								'details' => json_decode($orderData['detail_json'], true),
								'order_id' => $order_id,
								'delivery_type_title' => $delivery_type_title,
								'payment_type_title' => $payment_type_title,
								'delivery_info_string' => $delivery_info_string,
								'status_title' => $this->statuses[$orderData['order_status']],
								'link' => 'http://' . $_SERVER['SERVER_NAME'] . $link,
							)
						),
						ADMIN_EMAIL
					);

					$this->get('connection')->commit();

				} catch (\Exception $e) {

					$this->get('connection')->rollBack();

					$this->get('container')->addError($e->getMessage());
					$response->setData(array(
						'status' => 'error',
						'error' => 'Ошибка добавления заказа',
					));

					return $response;
				}
			}

			$this->get('session')->set('cart', array());
			$this->get('session')->set('num', 0);
			$this->get('session')->set('total', 0);
			$this->get('session')->set('cart.buyer', $buyer);
			$this->get('session')->set('cart.delivery.address', $delivery_info);

			$response->setData(array(
				'status' => 'ok',
				'link' => 'http://'.$_SERVER['SERVER_NAME'].$link,
			));

			return $response;
		}

		return $this->redirect('/');
	}

	public function orderAction($id)
	{
		$order = $this->get('container')->getItem('basket_order', 'MD5(CONCAT(email, id)) = "'.$id.'"');
		if (!$order) {
			return $this->redirect('/');
		}
		$order['detail_json'] = json_decode($order['detail_json'], true);

		$api = new \Fuga\Kaznachey\Api(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST["action"] == "create_payment") {

			//Запрос на создание платежа.
			$request = Array(
				"SelectedPaySystemId" => $_POST["SelectedPaySystemId"], //Выбранная платёжная система (1 - идентификатор тестовой платежной системы,
				//необходимо передавать идентификатор системы, которую выберет пользователь)
				"Products" => array( // Список продуктов
					array(
						"ProductItemsNum" => "1", // Количество
						"ProductName" => "Оплата заказа № ".$order['id'], // Наименование товара
						"ProductPrice" => number_format($order['cost']+$order['delivery_cost'], 2, '.', ''), //Стоимость товара
						"ProductId" => $order['id'], // Идентификатор товара из системы мерчанта. Необходим для аналити продаж
					)
				),
				"Currency" => "RUB", // Валюта (UAH, USD, RUB, EUR)
				"Language" => "RU", // Язык страницы оплаты (RU, EN)

				"PaymentDetails" => array( //Детали платежа
					//Обязательные поля
					"EMail" => $order['email'], //Емайл клиента
					'PhoneNumber' => preg_replace('/(\+|\s|\(|\))+/','',$order['phone']),

					"MerchantInternalPaymentId" => $order['id'], // Номер платежа в системе мерчанта
					"MerchantInternalUserId" => $order['user_id'], //Номер пользователя в системе мерчанта

					"StatusUrl" => "http://".$_SERVER['SERVER_NAME']."/basket/status", // url для ответа платежного сервера с состоянием платежа.
					"ReturnUrl" => "http://".$_SERVER['SERVER_NAME']."/basket/payment", //url возврата ползователя после платежа.

				),
			);

			//Результатом запроса будет код закодированный в base64 для продолжения оплаты, который необходимо вставить в html-код текущей старницы
			////ExternalForm - код закодированный в base64, который необходимо вставить в html-код текущей старницы
			////ErrorCode - Код ошибки в системе (0 - успешный запрос)
			////DebugMessage - Описание ошибки
			$createPaymentResponse = $api->CreatePayment($request);

		} else {
			$merchantInfo = $api->GetMerchantInfo();
		}

		$delivery_type_title = $this->delivery[$order['delivery_type']];
		$payment_type_title = $this->payment[$order['payment_type']];
		$order_status_title = $this->statuses[$order['order_status']];

		$this->get('container')->setVar('title', 'Заказ № '.$order['id']);
		$this->get('container')->setVar('h1', 'Заказ № '.$order['id']);
		$this->get('container')->addScript('/bundles/public/js/order-status.js');

		return $this->render('basket/order.html.twig', compact('order', 'delivery_type_title', 'payment_type_title', 'order_status_title', 'merchantInfo', 'createPaymentResponse'));
	}

	public function statusAction()
	{
		$api = new \Fuga\Kaznachey\Api(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

		$response = new Response();

		try {
			$statusRequest = $api->GetStatusResponse();

			$this->get('log')->addError(serialize($statusRequest));

			$order = $this->get('container')->getItem(
				'basket_order',
				'order_status="wait" AND id='.$statusRequest['MerchantInternalPaymentId']
			);
			if (!$order) {
				throw new \Exception('Order not found');
			}

			if ($statusRequest['ErrorCode'] != 0) {
				$this->get('container')->updateItem(
					'basket_order',
					array(
						'payment_details' => json_encode($statusRequest),
					),
					array('id' => $order['id'])
				);
				throw new \Exception('Payment error code '.$statusRequest['ErrorCode']);
			}

			$this->get('container')->updateItem(
				'basket_order',
				array(
					'payment_status' => 'paid',
					'payment_details' => json_encode($statusRequest),
					'order_status' => 'paid',
				),
				array('id' => $order['id'])
			);

			$order = $this->get('container')->getItem(
				'basket_order',
				$statusRequest['MerchantInternalPaymentId']
			);

			$delivery_info = array();
			if ($order['delivery_detail']) {
				$delivery_info = json_decode($order['delivery_detail'], true);
			}

			$this->get('mailer')->send(
				'Заказ №' . $order['id'] . ' оплата получена. Интернет магазин BOGS-SHOP.RU',
				$this->render(
					'mail/payment.buyer.html.twig',
					array(
						'order' => $order,
						'details' => json_decode($order['detail_json'], true),
						'status' => $statusRequest,
						'delivery_type_title' => $this->delivery[$order['delivery_type']],
						'payment_type_title' => $this->payment[$order['payment_type']],
						'delivery_info_string' => $order['address'],
						'status_title' => $this->statuses[$order['order_status']],
					)
				),
				$order['email']
			);

			$this->get('mailer')->send(
				'Заказ №' . $order['id'] . ' оплата получена. BOGS-SHOP.RU',
				$this->render(
					'mail/payment.admin.html.twig',
					array(
						'order' => $order,
						'status' => $statusRequest,
						'status_title' => $this->statuses[$order['order_status']],
					)
				),
				ADMIN_EMAIL
			);

			$response->setContent("ok");
		} catch (\Exception $e) {
			$response->setContent("Error!".$e->getMessage());
			$this->get('log')->addError($e->getMessage());
		}

		return $response;
	}

	public function paymentAction()
	{
		$id = $this->get('request')->query->getInt('OrderId', 0);
		$result = $this->get('request')->query->get('Result', 'Failed');

		$order = $this->get('container')->getItem('basket_order', $id);
		if (!$order) {
			return $this->redirect('/');
		}

		$order['detail_json'] = json_decode($order['detail_json'], true);
		$delivery_type_title = $this->delivery[$order['delivery_type']];
		$payment_type_title = $this->payment[$order['payment_type']];
		$order_status_title = $this->statuses[$order['order_status']];
		$result_title = $this->paymentResults[$result];

		$this->get('container')->setVar('title', 'Оплата заказа № '.$order['id']);
		$this->get('container')->setVar('h1', 'Оплата заказа № '.$order['id']);

		return $this->render('basket/payment.html.twig', compact('order', 'delivery_type_title', 'payment_type_title', 'result', 'result_title'));
	}

	public function regionsAction()
	{
		$regions0 = $this->get('container')->getItems('basket_region', 'publish=1');
		$regions = array();
		foreach ($regions0 as $region) {
			$regions[] = array(
				'subject' => $region['name'],
				'price' => $region['cost_post'],
				'price_carrier' => $region['cost_carrier'],
			);
		}
		$response = new JsonResponse();
		$response->setData($regions);

		return $response;
	}

	public function citiesAction()
	{
		$items0 = $this->get('container')->getItems('basket_city', 'publish=1');
		$items = array();
		foreach ($items0 as $item) {
			$items[] = array(
				'subject' => $item['name'],
				'price' => $item['cost_post'],
				'price_carrier' => $item['cost_carrier'],
			);
		}
		$response = new JsonResponse();
		$response->setData($items);

		return $response;
	}

	public function countriesAction()
	{
		$items0 = $this->get('container')->getItems('basket_country', 'publish=1');
		$items = array();
		foreach ($items0 as $item) {
			$items[] = array(
				'subject' => $item['name'],
				'price' => $item['cost_post'],
				'price_carrier' => $item['cost_carrier'],
			);
		}
		$response = new JsonResponse();
		$response->setData($items);

		return $response;
	}

} 