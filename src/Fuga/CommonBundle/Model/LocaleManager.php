<?php

namespace Fuga\CommonBundle\Model;

use Symfony\Component\HttpFoundation\RedirectResponse;

class LocaleManager extends ModelManager
{
	public function getLocales()
	{
		return array('ru');
	}

	public function getCurrentLocale()
	{
		return $this->get('session')->get('locale');
	}

	public function setLocale($site)
	{
		if (!$this->get('session')->get('locale')) {
			$this->get('session')->set('locale', PRJ_LOCALE);
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->get('request')->request->get('locale')) {
			$this->get('session')->set('locale', $this->get('request')->request->get('locale'));
			$response = new RedirectResponse($_SERVER['REQUEST_URI'], 302);
			$response->send();
			exit;
		} elseif (substr($site['url'], 0, 6) != '/admin')  {
			$this->get('session')->set('locale', $site['language']);
		}
	}
} 