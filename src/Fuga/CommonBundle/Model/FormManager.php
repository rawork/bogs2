<?php

namespace Fuga\CommonBundle\Model;

use Fuga\Component\Form\FormBuilder;

class FormManager extends ModelManager {
	
	private $params;
	
	public function __construct()
	{
		$params = $this->get('container')->getManager('Fuga:Common:Param')->findAll('form');
		$this->params = array();
		foreach ($params as $param) {
			$this->params[$param['name']] = $param['type'] == 'int' ? intval($param['value']) : $param['value'];
		}
	}
	
	public function getForm($name)
	{
		$form = $this->get('container')->getItem('form_form', "name='$name' AND publish=1");
		if (!$form) {
			return null;
		}

		$form['fields'] = $this->get('container')->getItems('form_field', 'form_id='.$form['id']);
		$builder = new FormBuilder($form, '');
		$builder->items = $form['fields'];
		$builder->message = $this->processForm($builder);

		if ($builder->message[0] == 'error'){
			$builder->fillGlobals();
		}

		return $builder->render();
	}

	private function processForm($form)
	{
		$message = null;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($form->defense && $this->get('session')->get('captcha.phrase') != md5($this->get('request')->request->get('securecode').CAPTCHA_KEY)){
				$message[0] = 'error';
				$message[1] = $this->params['securecode_error'];
			} else {
				$errors = $form->sendMail($this->params);
				if ($errors === true){
					$message[0] = 'success';
					$message[1] = $this->params['form_success'];
				} else {
					$message[0] = 'error';
					$message[1] = implode('<br>', $errors);
				}
			}
			$this->get('session')->remove('captcha.phrase');
		}
		
		return $message;
	}

}
