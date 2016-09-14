<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SubscribeController extends Controller
{
	public function indexAction()
	{
		$rubrics = $this->get('container')->getItems('subscribe_rubric');

		return $this->render('subscribe/form.html.twig', compact('rubrics'));
	}

	public function subscribeAction()
	{
		$email = $this->get('request')->request->get('email');
		$rubrics = $this->get('request')->request->get('rubrics');

		if (!$this->get('util')->isEmail($email)) {
			$data = array(
				'error' => 'АДРЕС ЭЛЕКТРОННОЙ ПОЧТЫ<br> УКАЗАН НЕПРАВИЛЬНО!',
			);
		} else {
			$data = $this->getManager('Fuga:Common:Subscribe')->subscribe($email, $rubrics);
		}

		$response = new JsonResponse();
		$response->setData($data);

		return $response;
	}

	public function activateAction($key)
	{
		$response = new Response();
		$response->setContent($this->getManager('Fuga:Common:Subscribe')->activate($key));

		return $response;
	}

	public function sendAction()
	{
		$this->get('session')->getFlashBag()->add(
			'admin.message',
			$this->get('scheduler')->everyMinute() ? 'Ошибка отправки писем' : 'Письма разосланы'
		);

		return $this->redirect($this->generateUrl(
			'admin_entity_index',
			array('state' => 'service', 'module' => 'subscribe', 'entity' => 'lists')
		));
	}

}