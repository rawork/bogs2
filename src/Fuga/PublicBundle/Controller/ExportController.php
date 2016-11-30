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
			$content .= '		<offer id="'.$product['id'].'" available="true" cbid="4.50">
			<url>http://'.$_SERVER['SERVER_NAME'].'#/'.$product['id'].'</url>
			<price>'.$product['price'].'</price>
			<currencyId>RUR</currencyId>
			<categoryId>'.$product['category_id'].'</categoryId>
			<picture>http://'.$_SERVER['SERVER_NAME'].$product['photo_value']['extra']['main']['path'].'</picture>
			<vendor>BOGS</vendor>
			<model>'.str_replace('Bogs ', '', $product['name']).'</model>
			<name>'.$product['name'].'</name>
			<description>'.htmlspecialchars($product['description']).'</description>
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

	}

} 