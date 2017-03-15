<?php

namespace Fuga\PublicBundle\Model;

use Fuga\CommonBundle\Model\ModelManager;

class ArticleManager extends ModelManager
{

	function getRelated($article, $limit = 3)
	{

		if (!is_array($article['tags_value'])) {
			return false;
		}

		$func = function($item) {
			return $item['tag_id'];
		};

		$tags = array_map($func, $article['tags_value']);

		$sql = 'SELECT article_id FROM blog_article_tag WHERE tag_id IN(?) AND article_id <> ? ORDER BY article_id DESC LIMIT ?';

		$links = $this->get('connection')
			->executeQuery($sql, array($tags, $article['id'], $limit), array(
				\Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
				\PDO::PARAM_INT,
				\PDO::PARAM_INT
			))->fetchAll();

		$ids = array();
		foreach ( $links as $link) {
			$ids[] = $link['article_id'];
		}

		$criteria = 'publish=1 AND id IN('.implode(',', $ids).')';

		return $this->get('container')->getItems('blog_article', $criteria, 'id DESC', 3);
	}

	function getByTag($tagId) {
		$sql = 'SELECT article_id FROM blog_article_tag WHERE tag_id = ? ORDER BY article_id DESC';

		$links = $this->get('connection')
			->executeQuery($sql, array($tagId), array(\PDO::PARAM_INT))->fetchAll();

		$ids = array();
		foreach ( $links as $link) {
			$ids[] = $link['article_id'];
		}

		$criteria = 'publish=1 AND id IN('.implode(',', $ids).')';

		return $this->get('container')->getItems('blog_article', $criteria);
	}

}
