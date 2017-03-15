<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class TagController extends Controller
{
	public function indexAction()
	{
		$response = new JsonResponse();
		$response->setData($this->get('container')->getItems('blog_tag'));

		return $response;
	}

	public function createAction()
	{
		$name = $this->get('request')->request->get('name');

		$tag = $this->get('container')->getItem('blog_tag', 'name="'.$name.'""');

		if (!$tag) {
			$tagId = $this->get('container')->addItem(
				'blog_tag',
				array(
					'name' => $name,
				)
			);
		} else {
			$tagId = $tag['id'];
		}

		$response = new JsonResponse();
		$response->setData(array('id' => $tagId));

		return $response;
	}



} 