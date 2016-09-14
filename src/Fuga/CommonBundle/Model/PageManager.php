<?php

namespace Fuga\CommonBundle\Model;

class PageManager extends ModelManager {
	
	public function getNodes($uri = 0, $recursive = false, $where = "publish=1") {
		$nodes = $this->get('container')->getItemsRaw(
			'SELECT t1.*, t3.name as module_id_name, t3.path as module_id_path FROM page_page as t1 '.
			'LEFT JOIN page_page as t2 ON t1.parent_id=t2.id '.
			'LEFT JOIN config_module as t3 ON t1.module_id=t3.id '.
			"WHERE t1.publish=1 AND t1.locale='".$this->get('session')->get('locale')."' AND ".(is_numeric($uri) ? ($uri == 0 ? ' t1.parent_id=0 ' : 't2.id='.$uri.' ') : "t2.name='".$uri."' ").
			'ORDER BY t1.sort,t1.name '
		);
		foreach ($nodes as &$node) {
			if ($recursive) {
				$node['children'] = $this->getNodes($node['name'], $recursive, $where);
			}
			$node['class'] = empty($node['children']) ? 'collapsed' : 'leaf';
			$node['ref'] = $this->getUrl($node);
		}
		
		return $nodes;	
	}
	
	public function getUrl($node) {
		return trim($node['url']) ?: ($this->get('session')->get('locale') != 'ru' ? '/'.$this->get('session')->get('locale') : '').$this->get('routing')->getGenerator()->generate('public_page', array('node' => $node['name']));
	}
	
	public function getPathNodes($id = 0) {
		$titles = array('ru' => 'Главная', 'en' => 'Home');
		$nodes = $this->get('container')->getTable('page_page')->getPrev($id);
		if ($nodes[0]['name'] != '/') {
			array_unshift($nodes, array(
				'name' => '/', 
				'url' => '', 
				'title' => $titles[$this->get('session')->get('locale')]
			));
		}
		foreach ($nodes as &$node) {
			if ('/' == $node['name']) {
				$node['title'] = $titles[$this->get('session')->get('locale')];
			}	
			$node['ref'] = $this->getUrl($node);
		}
		
		return $nodes;
	}
	
	public function getNodeByName($name) {
		return $this->get('container')->getItem('page_page', "name='".$name."'");
	}
	
	public function getNode($id) {
		return $this->get('container')->getItem('page_page', $id);
	}
	
}
