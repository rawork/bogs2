<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class ExportController extends AdminController
{
	public function indexAction()
	{
		$state = 'content';
		$module = 'catalog';

		return new Response($this->render('admin/export/catalog/index.html.twig', compact('state', 'module')));
	}

}