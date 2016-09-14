<?php

namespace Fuga\Component\Mailer;

class Mailer 
{
	
	private $engine;
	
	function __construct() 
	{
		$this->engine = new MailEngine();
	}
	
	function attach($fileName) {
		$this->engine->Attach(PRJ_DIR.$fileName);
	}
	
	function send($subject, $message, $emails) 
	{
		if (!is_array($emails)) {
			if (preg_match_all('/[a-z0-9]+([-_\.]?[a-z0-9])*@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}/i', $emails, $finded)) {
				$subscribers = array_unique($finded[0]);
			}
		} else {
			$subscribers = $emails;
		}
		$this->engine->From(ADMIN_EMAIL);
		$this->engine->Subject($subject);
		$this->engine->Html($message, 'UTF-8');
		$this->engine->To($subscribers);
		$this->engine->Send();
		@unlink(PRJ_DIR.$letter['file']);
	}

}
