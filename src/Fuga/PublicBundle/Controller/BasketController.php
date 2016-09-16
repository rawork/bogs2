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
		return 'index';
	}

	public function paymentAction($id)
	{
		var_dump($id);
		return 'payment';
	}

	public function orderAction($id)
	{
		return 'order';
	}

} 