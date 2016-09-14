<?php

namespace Fuga\CommonBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
	
	public function loginAction()
	{
		$message = null;
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$login = $this->get('request')->request->get('_user');
			$password = $this->get('request')->request->get('_password');
			$is_remember = $this->get('request')->request->get('_remember_me');
			
			if (!$login || !$password){
				$this->get('session')->set('danger', 'Неверный Логин или Пароль');
			} elseif ($this->get('security')->isServer()) {
				$res = $this->get('security')->login($login, $password, $is_remember);
				if ($res) {
					return $res;
				} else {
					$this->get('session')->set('danger', 'Неверный Логин или Пароль');
				}
			}
			
			return $this->reload();
		}

		$message = $this->flash('danger');

		return new Response($this->render('admin/form/login.html.twig', compact('message')));
	}
	
	public function forgetAction()
	{
		$message = null;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$login  = $this->get('request')->request->get('_user');
			$sql = "SELECT id, login, email FROM user_user WHERE login= :login OR email = :login ";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue("login", $login);
			$stmt->execute();
			$user = $stmt->fetch();
			if ($user) {
				$key = $this->get('util')->genKey(32);
				$this->get('connection')->update(
						'user_user', 
						array('hashkey' => $key), 
						array('id' => $user['id'])
				);
				$text = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
				$text .= '------------------------------------------'."\n";
				$text .= 'Вы запросили ваши регистрационные данные.'."\n\n";
				$text .= 'Ваша регистрационная информация:'."\n";
				$text .= 'ID пользователя: '.$user['id']."\n";
				$text .= 'Логин: '.$user['login']."\n\n";
				$text .= 'Для смены пароля перейдите по следующей ссылке:'."\n";
				$text .= 'http://'.$_SERVER['SERVER_NAME'].PRJ_REF.'/admin/password/'.$key."\n\n";
				$text .= 'Сообщение сгенерировано автоматически.'."\n";
				$this->get('mailer')->send(
					'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
					nl2br($text),
					$user['email']
				);
				$this->get('session')->set('success', 'Новые параметры авторизации отправлены Вам на Электронную почту!');
				return $this->reload();
			} else {
				$this->get('session')->set('danger', 'Пользователь не найден');
				return $this->reload();
			}
		}
		$message = $this->flash('danger') ?: $this->flash('success');

		return new Response($this->render('admin/form/forget.html.twig', compact('message')));
	}
	
	public function logoutAction()
	{
		$this->get('security')->logout();
		if (empty($_SERVER['HTTP_REFERER']) || preg_match('/^'.PRJ_REF.'\/admin\/logout/', $_SERVER['HTTP_REFERER'])) {
			$uri = $this->generateUrl('admin_index');
		} else {
			$uri = $_SERVER['HTTP_REFERER'];
		}

		return $this->redirect($uri);
	}
	
	public function passwordAction($key)
	{
		$user = $this->get('container')->getItem('user_user', "hashkey='".$key."'");
		if ($user && !empty($user['email'])) {
			$password = $this->get('util')->genKey();
			$this->get('container')->updateItem(
					'user_user',
					array('hashkey' => '', 'password' => hash('sha512', $password)),
					array('id' => $user['id'])
			);
			$text = 'Информационное сообщение сайта '.$_SERVER['SERVER_NAME']."\n";
			$text .= '------------------------------------------'."\n";
			$text .= 'Вы запросили ваши регистрационные данные.'."\n";
			$text .= 'Ваша регистрационная информация:'."\n";
			$text .= 'ID пользователя: '.$user['id']."\n";
			$text .= 'Логин: '.$user['login']."\n";
			$text .= 'Пароль: '.$password."\n\n";
			$text .= 'Сообщение сгенерировано автоматически.'."\n";
			$this->get('mailer')->send(
				'Новые регистрационные данные. Сайт '.$_SERVER['SERVER_NAME'],
				nl2br($text),
				$user['email']
			);
		}

		return $this->redirect($this->generateUrl('admin_index'));
	}
	
}
