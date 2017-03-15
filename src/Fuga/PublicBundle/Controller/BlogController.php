<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;

class BlogController extends PublicController
{
	public function __construct()
	{
		parent::__construct('blog');
	}

	public function indexAction()
	{
		$page = $this->get('request')->query->getInt('page', 1);

		$paginator = $this->get('paginator');
		$paginator->paginate(
			$this->get('container')->getTable('blog_article'),
			'/blog?page=###',
			'publish=1',
			5,
			$page,
			10,
			'public'
		);

		$articles = $this->get('container')->getItems('blog_article', 'publish=1', null, $paginator->limit);

		return $this->render('blog/index.html.twig', compact('articles', 'paginator'));
	}

	public function readAction($id)
	{
		$article = $this->get('container')->getItem('blog_article', $id);

		if (!$article) {
			throw $this->createNotFoundException('Страница на найдена!');
		}

		$prev = $this->get('container')->getItem('blog_article', 'id<'.$id);
		$next = $this->get('container')->getItem('blog_article', 'id>'.$id, 'id');

		$this->get('container')->setVar('title', $article['name']);
		$this->get('container')->setVar('h1', $article['name']);

		$related = $this->getManager('Fuga:Public:Article')->getRelated($article);

		return $this->render('blog/read.html.twig', compact('article', 'next', 'prev', 'related'));
	}

} 