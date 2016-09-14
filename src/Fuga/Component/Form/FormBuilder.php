<?php
	
namespace Fuga\Component\Form;

class FormBuilder {
	public $items;
	public $action;
	public $defense;
	public $message;
	private $again_postfix;
	private $form;
	private $email;
	private $secureNames = array(
		'ru' => 'Введите текст с изображения',
		'en' => 'Enter the text from the image',
		'it' => 'Inserisci il testo dell`immagine',
	);

	public function __construct($form, $action = '.') {
		$this->form = $form;
		$this->action = $action;
		$this->again_postfix = '_again';
		$this->form['needed'] = false;
		$this->form['submit_text'] = empty($form['submit_text']) ? 'Отправить' : $form['submit_text'];
		$this->defense = !empty($this->form['is_defense']);
		$this->email = empty($form['email']) ? ADMIN_EMAIL : $form['email'];
	}

	public function fillGlobals() {
		foreach ($this->items as $k => $v) {
			if (empty($this->items[$k]['value'])) {
				$this->items[$k]['value'] = $this->get('request')->request->get($v['name']);
			}
		}
	}

	public function fillValues(&$a) {
		for ($i = 0; $i < sizeof($this->items); $i++) {
			$name = $this->items[$i]['name'];
			if (!stristr($name, $this->again_postfix)) {
				if (!empty($a[$name])) {
					$this->items[$i]['value'] = $a[$name];
					if (stristr($name, 'password')) {
						$this->items[$i + 1]['value'] = $this->items[$i]['value'];
					}
				}
			}
		}
	}

	private function parseItem($item) {
		switch ($item['type']) {
			case 'select':
				if (!empty($item['select_values'])) {
					$item['select_values'] = explode(';', $item['select_values']);
					foreach ($item['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$item['select_values'][$k] = array();
							$item['select_values'][$k]['name'] = $v;
							$item['select_values'][$k]['value'] = $v;
						}
						if (!empty($item['value']) && $item['select_values'][$k]['value'] == $item['value']) {
							$item['select_values'][$k]['sel'] = ' selected';
						}
					}
				}
				if (!empty($item['select_table'])) {
					if (empty($item['select_name'])) {
						$item['select_name'] = 'name';
					}
					if (empty($item['select_value'])) {
						$item['select_value'] = 'id';
					}
					if (empty($item['select_order'])) {
						$item['select_order'] = $item['select_name'];
					}
					if (!empty($item['select_filter'])) {
						$item['select_filter'] = str_replace("`", "'",$item['select_filter']);
					}

					$sql = 'SELECT * FROM '.$item['select_table'].(!empty($item['select_filter']) ? ' WHERE '.$item['select_filter'] : '').' ORDER BY '.$item['select_order'];
					$stmt = $this->get('connection')->prepare($sql);
					$stmt->execute();
					$items = $stmt->fetchAll();
					$item['select_values'] = array();
					foreach ($items as $item) {
						$citem = array('name' => $item[$item['select_name']], 'value' => $item[$item['select_value']]);
						if (!empty($item['value']) && $item['value'] == $item[$item['select_value']]) {
							$citem['sel'] = ' selected';
						}
						$item['select_values'][] = $citem;
					}
				}
				break;
			case 'enum':
				if (!empty($item['select_values'])) {
					$item['select_values'] = explode(',', $item['select_values']);
					foreach ($item['select_values'] as $k => $v) {
						if (!is_array($v)) {
							$item['select_values'][$k] = array();
							$item['select_values'][$k]['name'] = $v;
							$item['select_values'][$k]['value'] = $v;
						}
						if (!empty($item['value']) && $item['select_values'][$k]['value'] == $item['value']) {
							$item['select_values'][$k]['sel'] = ' selected';
						}
					}
				}
			break;	
			case 'string':
			// do something
		}
		return $item;
	}

	public function render() {
		$ret = '';
		if (count($this->items)) {
			foreach ($this->items as &$item) {
				$item = $this->parseItem($item);
				if (!empty($item['not_empty'])) {
					$this->form['not_empty'] = true;
				}	
			}
			unset($item);
			$params = array (
				'action' => $this->action,
				'dbform' => $this->form,
				'items' => $this->items,
				'frmMessage' => $this->message,
				'again_postfix' => $this->again_postfix,
				'session_name' => session_name(),
				'session_id' => session_id(),
				'secure_name' => $this->secureNames[$this->get('session')->get('locale')],
				'now' => time(),
			);
			try {
				$locale = $this->get('session')->get('locale');
				$ret = $this->get('templating')->render('form/'.$this->form['name'].'.'.$locale.'.html.twig', $params);
			} catch (\Exception $e) {
				$ret = $this->get('templating')->render('form/basic.html.twig', $params);
			}
		} else {
			$ret = 'Форма '.$this->name.' не содержит полей';
		}
		return $ret;
	}

	function getFieldValue($sName) {
		return isset($_POST[$sName]) ? addslashes($_POST[$sName]) : null;
	}

	public function getIncorrectFieldTitle() {
		foreach ($this->items as $i) {
			if (!empty($i['not_empty']) && !$this->getFieldValue($i['name'])) {
				return $i['title'];
			}
		}
		return null;
	}

	public function isCorrect() {
		foreach ($this->items as $k => $i) {
			if ($i['type'] == 'password' && $this->get('request')->request->get($i['name']) != $this->get('request')->request->get($i['name'].$this->again_postfix)) {
				return false;
			}
		}
		return $this->getIncorrectFieldTitle() === null;
	}

	public function sendMail($params) {
		$errors = array();
		$fields = array();
		foreach ($this->items as $field){
			$value = strip_tags($this->get('request')->request->get($field['name']));
			if ($field['not_empty'] && empty($value)) {
				$ftitle = $field['title'];
				$error = $params['field_error'];
				$errors[] = $this->get('templating')->render('form/error.html.twig', compact('ftitle', 'error'));
			}
			if ($field['type'] == 'checkbox') {
				$value = (empty($value) ? 'нет' : 'да').'<br>';
			} elseif ($field['type'] == 'file' && is_array($_FILES) && isset($_FILES[$field['name']]) && $_FILES[$field['name']]['name'] != '') {
				$upfile = $_FILES[$field['name']];
				if ($upfile['name'] != '' && $upfile['size'] < MAX_FILE_SIZE ){
					$this->get('mailer')->Attach( $upfile['tmp_name'], $upfile['type'], 'inline', $upfile['name']);	
				}
				$value = $upfile['name'].' см. вложение<br>';
			} else {	
				$value = htmlspecialchars($value);
			}
			$fields[] = array('value' => $value, 'title' => $field['title']);
		}
		if ($errors) {
			return $errors;
		} else {
			if ($this->defense) {
				$fields[] = array('value' => $this->get('request')->request->get('securecode'), 'title' => 'Код безопасности');
			}	
			$this->get('mailer')->send(
				$this->form['title'].' на сайте '.$_SERVER['SERVER_NAME'],
				$this->get('templating')->render('form/mail.html.twig', compact('fields')),
				$this->email
			);
			
			return true;	
		}
	}

	public function get($name) {
		global $container;
		if ($name == 'container') {
			return $container;
		} else {
			return $container->get($name);
		}
	}

}
