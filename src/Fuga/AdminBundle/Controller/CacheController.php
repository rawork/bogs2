<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class CacheController extends AdminController
{
	public function clearAction()
	{
		$this->get('templating')->clearCompiled();

		$response = new JsonResponse();
		$response->setData(array('content' => 'Кэш очищен'));
		$response->prepare($this->get('request'));

		return $response;
	}
} 