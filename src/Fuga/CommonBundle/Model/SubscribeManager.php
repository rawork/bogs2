<?php

namespace Fuga\CommonBundle\Model;

class SubscribeManager extends ModelManager {
	
	protected $entityTable = 'subscribe_lists';
	protected $subscriberTable = 'subscribe_subscriber';
	protected $rubricTable = 'subscribe_subscriber_rubric';

	public function processMessage() {
		$letter = $this->get('container')->getItem($this->entityTable, 'TO_DAYS(date) <= TO_DAYS(NOW())');
		if ($letter) {
			$emails = array();
			$subscribers = $this->get('container')->getItems($this->subscriberTable, "is_active=1");
			foreach ($subscribers as $subscriber) {
				$emails[] = $subscriber['email'];
			}
			if ($letter['file']) {
				$this->get('mailer')->attach($letter['file']);
			}
			$this->get('mailer')->send(
				$letter['subject'],
				$letter['message'],
				$emails	
			);
			
			$this->get('container')->deleteItem($this->entityTable, 'id='.$letter['id']);
		}
	}
	
	public function getSubscriber($email) {
		$sql = "SELECT * FROM ".$this->subscriberTable." WHERE email = :email LIMIT 1";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue("email", $email);
		$stmt->execute();
		return $stmt->fetch();
	}
	
	public function subscribe($email, $rubrics) {
		$email = trim($email);
		$subscriber = $this->getSubscriber($email);
		$key = md5($this->get('util')->genKey(20));
		$values = array(
			'email' => $email,
			'date' => date('Y-m-d H:i:s'),
			'is_active' => 0,
			'hashkey' => $key
		);
		try {
			if (empty($rubrics)){
				$message = array(
					'error' => 'Вы не выбрали<br> рубрики рассылки',
				);
			} elseif ($subscriber) {
				$this->get('connection')->delete($this->rubricTable, array('subscriber_id' => $subscriber['id']));
				foreach ($rubrics as $rubric) {
					$this->get('connection')->insert(
						$this->rubricTable,
						array(
							'subscriber_id' => $subscriber['id'],
							'rubric_id'     => $rubric,
						)
					);
				}
				$message = array(
					'success' => 'Адрес '.htmlspecialchars($email).' занесен в список рассылки',
				);
			} elseif ($subscriberId = $this->get('container')->addItem($this->subscriberTable, $values)) {
				foreach ($rubrics as $rubric) {
					$this->get('connection')->insert(
						$this->rubricTable,
						array(
							'subscriber_id' => $subscriberId,
							'rubric_id'     => $rubric,
						)
					);
				}
				$letterText = "Уважаемый пользователь!\n\n
	Вы подписались на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n
	Для подтверждения, пожалуйста, проследуйте по ссылке:\n
	http://".$_SERVER['SERVER_NAME']."/subscribe/activate?key=".$key;
				$this->get('mailer')->send(
					'Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					$email
				);
				$letterText = "На e-mail ".$email." оформлена подписка на рассылку на сайте http://".$_SERVER['SERVER_NAME']."\n";
				$this->get('mailer')->send(
					'Оповещение о подписке на рассылку на сайте '.$_SERVER['SERVER_NAME'],
					nl2br($letterText),
					array(ADMIN_EMAIL)
				);
				$message = array(
					'success' => 'Адрес '.htmlspecialchars($email).' занесен в список рассылки',
				);
			}
		} catch(\Exception $e) {
			$this->get('log')->addError($e->getMessage());
			$message = array(
				'error' => 'Ошибка при добавлении.<br> Обратитесь к администратору',
			);
		}

		return $message;
	}
	
	public function unsubscribe($email) {
		$email = trim($email);
		$subscriber = $this->getSubscriber($email);
		if (!$subscriber) {
			$message = array(
				'message' => 'Адреса '.htmlspecialchars($email).' нет в списке рассылки',
				'success' => false
			);	
		} elseif ($this->get('connection')->delete($this->subscriberTable, array('email' => $email))) {
			$message = array(
				'message' => 'Адрес '.htmlspecialchars($email).' удален из списка рассылки',
				'success' => true
			);	
		} else {
			$message = array(
				'message' => 'Ошибка базы данных при удалении',
				'success' => false
			);	
		}
		return $message;
	}
	
	public function activate($key) {
		$key = addslashes(trim($key));
		$subscriber = $this->get('container')->getItem($this->subscriberTable, "hashkey='".$key."'");
		if ($subscriber) {
			$this->get('container')->updateItem($this->subscriberTable, 
					array('is_active' => 1, 'hashkey' => ''), 
					array('id' => $subscriber['id'])
			);
			$message = 'Адрес '.htmlspecialchars($subscriber['email']).' активирован';
		} else {
			$message = 'Ошибка активации адреса подписки';
		}
		return $message;
	}
}
