<?php

namespace Fuga\AdminBundle\Listener;

use Fuga\AdminBundle\Event\OrderDeliveryCalculatedEvent;

class OrderListener
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
		'card' => 'Оплата банковской картой',
        'bank' => 'Оплата квитанции в банке',
	);

	private $statuses = array(
		'new' => 'Получен, в обработке',
		'calc' => 'Требуется расчет доставки',
		'wait' => 'Ожидает оплаты',
		'paid' => 'Оплачен',
		'delivered' => 'Передан в службу доставки',
		'completed' => 'Выполнен',
	);

	public function onDeliveryCalcAction(OrderDeliveryCalculatedEvent $event)
	{
		$order = $event->getOrder();

		if ($order['order_status'] != 'wait') {
			return;
		}

		$link = $event->getContainer()
			->get('routing')
			->getGenerator()
			->generate('order', array('id' => md5($order['email'] . $order['id'])));

		$delivery_type_title = $this->delivery[$order['delivery_type']];
		$payment_type_title = $this->payment[$order['payment_type']];

		$event->getContainer()->get('mailer')->send(
			'Заказ №' . $order['id'] . ' ожидает оплаты в интернет магазине BOGS-SHOP.RU',
			$event->getContainer()->get('templating')->render(
				'mail/delivery.buyer.html.twig',
				array(
					'order' => $order,
					'order_id' => $order['id'],
					'details' => json_decode($order['detail_json'], true),
					'delivery_type_title' => $delivery_type_title,
					'payment_type_title' => $payment_type_title,
					'delivery_info_string' => $order['address'],
					'status_title' => $this->statuses[$order['order_status']],
					'link' => 'http://' . $_SERVER['SERVER_NAME'] . $link,
				)
			),
			$order['email']
		);

	}
}