<?php

namespace Fuga\CommonBundle\Controller;

use Fuga\Component\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller {
	
	public function get($name) 
	{
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}
	
	public function getManager($path)
	{
		return $this->get('container')->getManager($path);
	}

	public function getRequest()
	{
		return $this->get('request');
	}

	public function generateUrl($name, $options = array(), $locale = PRJ_LOCALE)
	{
		if (isset($options['node']) && '/' == $options['node']) {
			unset($options['node']);
		}
		return ($locale != PRJ_LOCALE ? '/'.$locale : '').$this->get('routing')->getGenerator()->generate($name, $options);
	}

	public function redirect($url, $status = 302)
	{
		$response = new RedirectResponse($url, $status);

		return $response;
	}

	public function reload()
	{
		return $this->redirect($this->getRequest()->getRequestUri());
	}
	
	public function render($template, $params = array(), $silent = false) 
	{
		return $this->get('templating')->render($template, $params, $silent);
	}
	
	public function call($path, $params = array()) 
	{
		return $this->get('container')->callAction($path, $params);
	}
	
	public function t($name)
	{
		return $this->get('translator')->t($name);
	}
	
	public function flash($name)
	{
		$message = null;
		if ($this->get('session')->get($name)) {
			$message = array(
				'type' => $name,
				'text' => $this->get('session')->get($name)
			);
			$this->get('session')->remove($name);
		}

		return $message;
	}
	
	public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }

	public function isXmlHttpRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'];
	}
	
}