<?php

namespace Fuga\CommonBundle\Model;

class CategoryManager extends ModelManager {
	
	public function getPathNodes($id = 0){
		$nodes = array();
		if ($this->get('routing')->getParam('action') == 'product') {
			$node = $this->get('container')->getItem('catalog_product', $id);
			if ($node) {
				$nodes = $this->get('container')->getTable('catalog_category')->getPrev($node['category_id']);
				$nodes[] = $node; 
			}
		} else {
			$nodes = $this->get('container')->getTable('catalog_category')->getPrev($id);
		}
		foreach ($nodes as &$node) {
			$node['title'] = $node['name'];
			$node['ref'] = $this->get('routing')->generateUrl($this->get('routing')->getParam('node'), 'index', array($node['id']));
		}
		
		return $nodes;
	}
	
}