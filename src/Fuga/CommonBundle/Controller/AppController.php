<?php

namespace Fuga\CommonBundle\Controller;

use Fuga\AdminBundle\AdminInterface;
use Fuga\Component\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AppController extends Controller
{
	public function handle(Request $request)
	{
		$session = new Session();
		$session->start();
		$this->get('container')->register('session', $session);
		$this->get('container')->register('request', $request);

		if ($this->get('request')->query->has('product') && $this->get('request')->query->has('size')) {
			$this->get('templating')->assign([
				'gproduct' => $this->get('request')->query->getInt('product'),
				'gsize' => $this->get('request')->query->getInt('size')
			]);
		}

		$site = $this->getManager('Fuga:Common:Site')->detectSite($_SERVER['REQUEST_URI']);
		$this->getManager('Fuga:Common:Locale')->setLocale($site);

		$this->get('container')->setVar('mainurl', $site['url']);

		if ($this->get('security')->isSecuredArea() && !$this->get('security')->isAuthenticated()) {
			$controller = new SecurityController();

			return $controller->loginAction();
		}

		try {
			$urlParts = explode('?', $site['url']);
			$parameters = $this->get('routing')->match(array_shift($urlParts));

			return $this->get('container')->callAction($parameters['_controller'], $parameters);
		} catch(ResourceNotFoundException $e) {
			throw new NotFoundHttpException('Несуществующая страница');
		}
	}
}
