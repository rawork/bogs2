<?php

namespace Fuga\Component\Mailer;

use Monolog\Logger;

class Mailer
{
	private $engine;
	private $logger;
	
	function __construct(MailEngine $engine, Logger $logger)
	{
		$this->engine = $engine;
        $this->logger = $logger;
	}
	
	function attach($fileName) {
		$this->engine->Attach(PRJ_DIR.$fileName);
	}
	
	function send($subject, $message, $emails) 
	{
		if (!is_array($emails)) {
			if (preg_match_all('/(.+@.+)/i', $emails, $finded)) {
				$subscribers = array_unique($finded[0]);
			} else {
			    $this->logger->addError('Email not found in string: '.$emails);
            }
		} else {
			$subscribers = $emails;
		}
		$this->engine->From(ADMIN_EMAIL);
		$this->engine->Subject($subject);
		$this->engine->Html($message, 'UTF-8');
		$this->engine->To($subscribers);
		$this->engine->Send();
	}

}
