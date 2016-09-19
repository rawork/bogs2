<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BasketController extends PublicController
{
	public function __construct()
	{
		parent::__construct('basket');
	}

	public function indexAction()
	{
		$cart = $this->get('session')->get('cart');

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

		return $this->render('basket/index.html.twig', compact('cart', 'merchantInfo', 'createPaymentResponse'));
	}

	public function saveAction() {

	}

	public function orderAction($id)
	{
		return 'order';
	}

	public function statusAction() {
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

	public function successAction() {
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

} 