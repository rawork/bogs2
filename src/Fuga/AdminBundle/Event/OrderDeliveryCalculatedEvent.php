<?php

namespace Fuga\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OrderDeliveryCalculatedEvent extends Event
{
	const NAME = 'order.delivery_calculated';

	protected $order;
	protected $container;

	public function __construct($container, $order)
	{
		$this->container = $container;
		$this->order = $order;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getContainer()
	{
		return $this->container;
	}
}