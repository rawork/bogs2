<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\JsonResponse;

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
				'index' => '',
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
			$sku = $this->get('container')->getItem('catalog_sku', 'modarola_id=' . $id);

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
		$delivery_type = $this->get('session')->get('cart.delivery.type', 'self');
		$payment_type = $this->get('session')->get('cart.payment.type', 'card');
		$delivery_type_title = $this->delivery[$delivery_type];
		$delivery_cost = $this->get('session')->get('cart.delivery.cost');
		$delivery_country = $this->get('session')->get('cart.delivery.country');
		$delivery_region = $this->get('session')->get('cart.delivery.region');
		$delivery_city = $this->get('session')->get('cart.delivery.city');
		$payment_type_title = $this->payment[$payment_type];

		$ending = $this->get('util')->ending($num, array('', 'а', 'ов'));

		return $this->render('basket/new.html.twig', compact('cart', 'num', 'ending', 'total', 'delivery_type', 'delivery_type_title', 'delivery_cost', 'delivery_country', 'delivery_region', 'delivery_city', 'payment_type', 'payment_type_title'));
	}

	public function orderAction($id)
	{
		$api = new \Fuga\Kaznachey\Api(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST["action"] == "create_payment") {

			var_dump($_POST);
			//Запрос на создание платежа.
			$request = Array(
				"SelectedPaySystemId" => $_POST["SelectedPaySystemId"], //Выбранная платёжная система (1 - идентификатор тестовой платежной системы,
				//необходимо передавать идентификатор системы, которую выберет пользователь)
				"Products" => array( // Список продуктов
					array(
						"ImageUrl" => "http://someImage.com//some.jpg", // Ссылка на изображение товара
						"ProductItemsNum" => "1", // Колличество
						"ProductName" => "Модель танка Т34-85 ", // Наименование товара
						"ProductPrice" => "500", //Стоимость товара
						"ProductId" => "123", // Идентификатор товара из системы мерчанта. Необходим для аналити продаж
					),
					array(
						"ImageUrl" => "http://someImage.com/some.jpg", // Ссылка на изображение товара
						"ProductItemsNum" => "2", // Колличество
						"ProductName" => "Модель танка Т34-76 ", // Наименование товара
						"ProductPrice" => "400", //Стоимость товара
						"ProductId" => "124", // Идентификатор товара из системы мерчанта. Необходим для аналити продаж
					)
				),
				"Currency" => "RUB", // Валюта (UAH, USD, RUB, EUR)
				"Language" => "RU", // Язык страницы оплаты (RU, EN)

				"PaymentDetails" => array( //Детали платежа
					//Обязательные поля
					"EMail" => "rawork@yandex.ru", //Емайл клиента
					"PhoneNumber" => "9159210472", //Номер телефона клиента

					"MerchantInternalPaymentId" => "1234", // Номер платежа в системе мерчанта
					"MerchantInternalUserId" => "21", //Номер пользователя в системе мерчанта

					"StatusUrl" => "http://test2.galichstrana.ru/basket/status", // url для ответа платежного сервера с состоянием платежа.
					"ReturnUrl" => "http://test2.galichstrana.ru/basket/success", //url возврата ползователя после платежа.

					//По возможности нужно заполнить эти поля.
					"CustomMerchantInfo" => "", // Любая информация
					"BuyerCountry" => "Россия", //Страна
					"BuyerFirstname" => "Роман", //Имя,
					"BuyerPatronymic" => "Алякритский", // отчество
					"BuyerLastname" => "Яковлевич", //Фамилия
					"BuyerStreet" => "Крестьянская, дом 24", // Адрес
					"BuyerZone" => "Костромская область", //   Область
					"BuyerZip" => "157203", //  Индекс
					"BuyerCity" => "Галич", //   Город,

					//аналогичная информация о доставке. Если информация совпадает можно скопировать.
					"DeliveryFirstname" => "Роман",
					"DeliveryPatronymic" => "Яковлевич",
					"DeliveryLastname" => "Алякритский",
					"DeliveryZip" => "157203",
					"DeliveryCountry" => "Россия",
					"DeliveryStreet" => "Крестьянская, дом 24",
					"DeliveryCity" => "Галич",
					"DeliveryZone" => "Костромская область",
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

		return $this->render('basket/order.html.twig', compact('merchantInfo', 'createPaymentResponse'));
	}

	public function statusAction()
	{
		$api = new \Fuga\Kaznachey\Api(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

		try {
			$statusRequest = $api->GetStatusResponse();
			echo "ok";
		} catch (\Exception $e) {
			print "Error!";
			print $e->getMessage();
		}

		$fp = fopen(PRJ_DIR.'/counter.txt', 'a');

		fputs($fp, $statusRequest, strlen($statusRequest));
		fclose($fp);
	}

	public function successAction()
	{
		$api = new \Fuga\Kaznachey\Api(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

		try {
			$statusRequest = $api->GetStatusResponse();
			echo "ok";
		} catch (\Exception $e) {
			print "Error!";
			print $e->getMessage();
		}

		$fp = fopen(PRJ_DIR.'/counter.txt', 'a');

		fputs($fp, $statusRequest, strlen($statusRequest));
		fclose($fp);
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