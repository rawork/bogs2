<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class NewsController extends PublicController
{
	public function __construct()
	{
		parent::__construct('news');
	}

	public function indexAction()
	{
		$items = $this->get('container')->getItems('news_news', 'publish=1', 'id DESC', $this->getParam('per_feed'));

		return $this->render('news/feed.html.twig', compact('items'));
	}
	
	public function feedAction()
	{
		$items = $this->get('container')->getItems('news_news', 'publish=1', 'id DESC', $this->getParam('per_feed'));
		
		return $this->render('news/feed.html.twig', compact('items'));
	}
	
}