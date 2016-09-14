<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class FeedbackController extends PublicController
{
	public function __construct()
	{
		parent::__construct('feedback');
	}

	public function indexAction()
	{
		$items = $this->get('container')->getItems('feedback_feedback', 'publish=1');

		return $this->render('feedback/index.html.twig', compact('items'));
	}

} 