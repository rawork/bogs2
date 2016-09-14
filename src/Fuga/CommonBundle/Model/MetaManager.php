<?php

namespace Fuga\CommonBundle\Model;

class MetaManager extends ModelManager {
	
	protected $entityTable = 'page_seo';
	
	public function getMeta($uri = false) {
		return $this->getItem('meta', $uri);
	}
	
	public function getTitle($uri = false) {
		return $this->getItem('title', $uri);
	}
	
	public function getItem($field, $uri = false) {
		if (!$uri) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		$items = $this->get('container')->getItems($this->entityTable);
		foreach ($items as $item) {
			$words = split(",", $item['words']);
			foreach ($words as $w) {
				$w = trim($w);
				if (!empty($w) && $uri == $w) {
					return $item[$field];
				}
			}
			$keywords = split(',', $item['keywords']);
			foreach ($keywords as $w) {
				$w = trim($w);
				if (!empty($w) && stristr($uri, $w)) {
					return $item[$field];
				}
			}
		}
	}
}