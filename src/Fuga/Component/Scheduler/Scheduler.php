<?php

namespace Fuga\Component\Scheduler;

use Fuga\Component\Container;

class Scheduler
{
	
	private $tasks = array();
	private $container;
	
	public function __construct(Container $container) {
		$this->container = $container;
		$this->tasks = array(
			'maillist' => array(
				'manager' => 'Fuga\\CommonBundle\\Model\\SubscribeManager',
				'action' => 'processMessage',
				'frequency' => 'minute',
				'params' => array()
			)
		);
	}
	
	public function registerTask($name, $className, $methodName, $frequency = 'hour', $params = array()) {
		$this->tasks[$name] = array(
			'manager' => $className,
			'action' => $methodName,
			'frequency' => $frequency,
			'params' => $params
		);
	}
	
	public function unregisterTask($name) {
		unset($this->tasks[$name]);
	}
	
	public function processTasks($frequency) {
		set_time_limit(0);
		foreach ($this->tasks as $name => $params) {
			if ($params['frequency'] == $frequency) {
				try {
					$this->processTask($name);
				} catch (\Exception $e) {
					$this->container->get('log')->addError($e->getMessage());
				}
			}
		}
	}
	
	public function processTask($name, $params = array()) {
		if (!isset($this->tasks[$name])) {
			throw new \Exception('Задача "'.$name.'" не зарегистрирована в планировщике');
		}
		$manager = $this->tasks[$name]['manager'];
		$action = $this->tasks[$name]['action'];
		$params = $this->tasks[$name]['params'];
		$obj = new $manager();
		$reflectionObj = new \ReflectionClass($manager);
		$reflectionObj->getMethod($action)->invokeArgs($obj, $params);
	}

	public function everyMinute() {
		$this->processTasks('minute');
	}
	
	public function everyHour() {
		$this->processTasks('hour');
	}
	
	public function everyDay() {
		$this->processTasks('day');
	}
	
	public function everyWeek() {
		$this->processTasks('week');
	}
	
	public function everyMonth() {
		$this->processTasks('month');
	}
	
}

