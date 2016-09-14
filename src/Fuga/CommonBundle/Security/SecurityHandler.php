<?php

namespace Fuga\CommonBundle\Security;
	
use Fuga\Component\Container;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SecurityHandler
{
	private $user;
	private $hash;
	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}
	
	public function isAuthenticated() {
		return $this->check();
	}
	
	public function isSecuredArea()
	{
		if (preg_match('/^'.PRJ_REF.'\/admin\/(logout|forget|password)/', $_SERVER['REQUEST_URI'])) {
			return false;
		}
		
		return 'Y' == PROJECT_LOCKED || preg_match('/^'.PRJ_REF.'\/admin/', $_SERVER['REQUEST_URI']);
	}
	
	public function getCurrentUser()
	{
		if (!$this->user) {
			if ($this->isDeveloper()) {
				$this->user = array(
					'id' => 0,
					'name' => 'dev',
					'lastname' => '',
					'email'    => DEV_EMAIL,
					'group_id' => 1,
					'group_id_title' => 'Администратор', 
					'is_admin' => 1,
					'is_active' => 1,
				);
			} else {
				$login = $this->container->get('session')->get('fuga_user');
				$sql = "
					SELECT u.*, g.id as group_id, g.name as group_id_name, g.title as group_id_title FROM user_user u
					JOIN user_group g ON u.group_id=g.id
					WHERE u.login = :login OR u.email = :login LIMIT 1";
				$stmt = $this->container->get('connection')->prepare($sql);
				$stmt->bindValue('login', $login);
				$stmt->execute();
				$this->user = $stmt->fetch();
				$sql = 'SELECT module_id, group_id FROM user_group_module WHERE group_id= :id';
				$stmt = $this->container->get('connection')->prepare($sql);
				$stmt->bindValue('id', $this->user['group_id']);
				$stmt->execute();
				$modules = $stmt->fetchAll();
				$rules = array();
				foreach($modules as $module) {
					$rules[] = $module['module_id'];
				}
				$this->user['rules'] = $rules;
				unset($this->user['password']);
			}
		}
		
		return $this->user;
	}

	private function check()
	{
		$login = $this->container->get('session')->get('fuga_user');
		$token = $this->container->get('session')->get('fuga_key');

		if (!$login && !$token) {
			$login = $this->container->get('request')->cookies->get('fuga_user');
			$token = $this->container->get('request')->cookies->get('fuga_key');
		}
		if ($login && $token) {
			if ($token == $this->token(DEV_USER, DEV_PASS)) {
				$user = array('login' => DEV_USER);
			} else {
				$user = $this->container->getItem('user_user', "token='".$token."'");
			}

			if ($user) {
				$this->hash = $token;
				$this->container->get('session')->set('fuga_user', $login);
				$this->container->get('session')->set('fuga_key', $token);

				return true;
			}
		}

		return false;
	}

	public function logout()
	{
		$this->container->get('session')->invalidate();
		$response = new Response();
		$response->headers->clearCookie('fuga_key');

		return $response;
	}

	public function login($login, $password, $isRemember = false)
	{
		$passwordHash = hash('sha512', $password);
		if ($login == DEV_USER && $passwordHash == DEV_PASS) {
			$user = array('login' => $login, 'id' => 0);
		} else {
			$sql = "SELECT id, login FROM user_user WHERE login= :login AND password= :password AND is_active=1 LIMIT 1";
			$stmt = $this->container->get('connection')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->bindValue("password", $passwordHash);
			$stmt->execute();
			$user = $stmt->fetch();
		}
		if ($user){
			$token = $this->token($login, $passwordHash);
			$response = new RedirectResponse($this->container->get('request')->getRequestUri());
			$this->container->get('session')->set('fuga_user', $user['login']);
			$this->container->get('session')->set('fuga_key', $token);
			$this->container->updateItem(
				'user_user',
				array('token' => $token),
				array('id' => $user['id'])
			);

			if ($isRemember) {
				$this->container->updateItem('user_user',
					array('token' => $token),
					array('id' => $user['id'])
				);
				$response->headers->setCookie(new Cookie('fuga_user', $user['login'], time()+AUTH_TTL));
				$response->headers->setCookie(new Cookie('fuga_key', $token, time()+AUTH_TTL));
			}

			return $response;
		} else {
			return false;
		}
	}
	
	private function token($login, $password)
	{
		return hash('sha512', $password.$login.$this->container->get('request')->getClientIp());
	}
	
	public function getGroup($name) {
		return $this->container->getItem('user_group', "name='$name'");
	}
	
	public function isGroup($name) {
		$group = $this->getGroup($name);
		$user = $this->getCurrentUser();
		return !empty($user['group_id']) && !empty($group['id']) && $user['group_id'] == $group['id'];
	}

	public function isAdmin() {
		return $this->container->get('session')->get('fuga_user') == 'admin';
	}

	public function isDeveloper() {
		return $this->container->get('session')->get('fuga_user') == 'dev';
	}

	public function isSuperuser() {
		return $this->isAdmin() || $this->isDeveloper();
	}

	public function isLocal() {
		return $this->container->get('request')->getClientIp() == gethostbyname($this->container->get('request')->server->get('SERVER_NAME'));
	}

	public function isServer() {
		return isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']);
	}
	
}
