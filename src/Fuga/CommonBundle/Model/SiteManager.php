<?php

namespace Fuga\CommonBundle\Model;

class SiteManager extends ModelManager
{
	public function detectSite($url)
	{
		$sites = $this->get('container')->getItems('config_version', '1=1', 'id DESC');
		foreach($sites as $site) {
			if (strpos($url, $site['folder']) === 0) {
				$site['url'] = '/'.substr($url, strlen($site['folder']));
				return $site;
			}
		}

		return array(
			'title' => 'default',
			'folder' => '/',
			'url' => $url,
			'language' => PRJ_LOCALE
		);
	}
} 