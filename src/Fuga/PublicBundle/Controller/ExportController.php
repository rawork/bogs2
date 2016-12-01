<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\PublicController;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends PublicController
{
	private $shop = 'Богс шоп';
	private $company = 'ООО Колорс лайф';
	private $url = 'http://bogs-shop.ru/';

	public function __construct()
	{
		parent::__construct('export');
	}

	public function yandexAction()
	{

	}

	public function mailAction()
	{
		$categories = $this->get('container')->getItems('catalog_category', 'publish=1');
		$products = $this->get('container')->getItems('catalog_product', 'publish=1');
		$date = date('Y-m-d H:i');

		$content = '<?xml version="1.0" encoding="UTF-8"?>
<torg_price date="'.$date.'"> 
  <shop>
	<shopname>'.$this->shop.'</shopname>
	<company>'.$this->company.'</company>
	<url>'.$this->url.'</url>
	<currencies>
		<currency id="RUR" rate="1"/>
	</currencies>
	<categories>
';
		foreach ($categories as $category) {
			$content .= '		<category id="'.$category['id'].'" parentId="0">'.$category['title'].'</category>
';
		}
		$content .= '	</categories>
	<offers>
';
		foreach ($products as $product) {
			$content .= '		<offer id="'.$product['id'].'" available="true" cbid="'.$product['cbid'].'">
			<url>http://'.$_SERVER['SERVER_NAME'].'#/'.$product['id'].'</url>
			<price>'.$product['price'].'</price>
			<currencyId>RUR</currencyId>
			<categoryId>'.$product['category_id'].'</categoryId>
			<picture>http://'.$_SERVER['SERVER_NAME'].$product['photo_value']['extra']['main']['path'].'</picture>
			<vendor>BOGS</vendor>
			<model>'.str_replace('Bogs ', '', $product['name']).'</model>
			<name>'.$product['name'].'</name>
			<description>'.htmlspecialchars(strip_tags($product['description'])).'</description>
			<delivery>true</delivery>
			<pickup>true</pickup>
			<local_delivery_cost>350</local_delivery_cost>
		</offer>
';
		}

		$content .= '	</offers>
  </shop>
</torg_price>';

		$response = new Response();
		$response->setContent($content);
		$response->headers->set('Content-Type', 'xml');

		return $response;
	}

	public function googleAction()
	{
		$products = $this->get('container')->getItems('catalog_product', 'publish=1');
		foreach ($products as &$product) {
			$product['sku'] = $this->get('container')->getItems('catalog_sku', 'publish=1 AND (quantity > 0 OR quantity2 > 0) AND product_id='.$product['id']);
		}
		unset($product);
		$date = date('Y-m-d');
		$time = date('H:i:s');

		$content = '<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">
	<title>'.$this->shop.' - Online Store</title>
	<link rel="self" href="'.$this->url.'"/>
	<updated>'.$date.'T'.$time.'Z</updated> 
		';

		foreach ($products as $product) {
			foreach ($product['sku'] as $sku) {
				$content .= '	<entry>
		<!-- The following attributes are always required -->
		<g:id>'.$product['articul'].'-'.$sku['id'].'</g:id>
		<g:title>'.$product['name'].' - Размер '.$sku['size'].' US</g:title>
		<g:description>'.htmlspecialchars(strip_tags($product['description'])).'</g:description>
		<g:link>http://'.$_SERVER['SERVER_NAME'].'/#/'.$product['id'].'/'.$sku['id'].'</g:link>
		<g:image_link>http://'.$_SERVER['SERVER_NAME'].$product['photo_value']['extra']['main']['path'].'</g:image_link>
		<g:condition>new</g:condition>
		<g:availability>'.($product['is_preorder'] == 1 ? 'preorder' : 'in stock').'</g:availability>	
		<g:price>'.$product['price'].' RUR</g:price>
		<g:google_product_category>187</g:google_product_category>
		<g:brand>BOGS</g:brand>
		<g:gender>'.$product['gender'].'</g:gender>
		<g:age_group>'.$product['age_group'].'</g:age_group>
		<g:size>'.$sku['size'].'</g:size>
				
		<g:item_group_id>'.$product['articul'].'</g:item_group_id>
	</entry>
				
';
			}
		}

		$content .= '</feed>';

		$response = new Response();
		$response->setContent($content);
		$response->headers->set('Content-Type', 'xml');

		return $response;
	}

} 