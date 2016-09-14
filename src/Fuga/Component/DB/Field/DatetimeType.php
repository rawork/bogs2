<?php

namespace Fuga\Component\DB\Field;

class DatetimeType extends Type {
	protected $arr;
	protected $year, $month, $day, $time; 
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		// немного уменьшаем геморой...
		$this->arr = array(
			'year' => 4,
			'month' => 2,
			'day' => 2,
			'time' => 8
		);
	}

	public function value2YMD($value = '') {
		if (!empty($value)) {
			$this->year = substr($value, 0, $this->arr['year']);
			$this->month = substr($value, 5, $this->arr['month']);
			$this->day = substr($value, 8, $this->arr['day']);
			$this->time = substr($value, 11, $this->arr['time']);
		} else {
			$ts = time();
			$this->year = date('Y', $ts);
			$this->month = date('m', $ts);
			$this->day = date('d', $ts);
			$this->time = date('H:i:s', $ts);
		}
	}

	public function getSQLValue($name = '') {
		$value = $this->getValue($name);
		if (in_array($value, array(null, '00.00.0000 00:00:00', '0000-00-00 00:00:00'))) {
			return "0000-00-00 00:00:00";
		}	
		
		$date = \DateTime::createFromFormat('d.m.Y H:i:s', $this->getValue($name));

		return $date ? $date->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
	}

	public function getStatic() {
		$this->value2YMD($this->dbValue);
		return $this->day.'.'.$this->month.'.'.$this->year.' '.$this->time;
	}

	public function getInput($value = '', $name = '') {
		return $this->dateType_getInput(($name ? $name : $this->getName()), $this->dbValue);
	}

	public function getSearchInput() {
		if ($date = $this->getSearchValue('beg')) {
			$date_beg = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2).' '.substr($date,11,8);
		} else {
			$date_beg = '';
		}
		if ($date = $this->getSearchValue('end')) {
			$date_end = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2).' '.substr($date,11,8);
		} else {
			$date_end = '';
		}
		return '<div class="input-group"><span class="input-group-addon">c</span>'.$this->dateType_getInput(parent::getSearchName('beg'), $date_beg, false).
				'<span class="input-group-addon">по</span>'.$this->dateType_getInput(parent::getSearchName('end'), $date_end, false).
				'<span class="input-group-addon"><a href="javascript:void(0)" onClick="emptyDateSearch(\''.parent::getSearchName().'\')">Обнулить</a></span>
</div>';
	}

	public function getSearchSQL() {
		$ret = '';
		if ($date = $this->getSearchValue('beg')) {
			$ret .= ($ret ? ' AND ' : '').$this->getName().">=STR_TO_DATE('$date','%d.%m.%Y %H:%i:%s')";
		}
		if ($date = $this->getSearchValue('end')) {
			$ret .= ($ret ? ' AND ' : '').$this->getName()."<=STR_TO_DATE('$date','%d.%m.%Y %H:%i:%s')";
		}
		return $ret;	
	}

	public function getSearchURL($name = '') {
		$ret = '';
		if (parent::getSearchURL('beg')) {
			$ret .= parent::getSearchURL('beg');
		}
		if (parent::getSearchURL('end')) {
			$ret .= ($ret ? '&' : '').parent::getSearchURL('end');
		}
		return $ret;
	}

	public function dateType_getInput($name, $value = '', $insertValue = true) {
		if ($value || $insertValue) {
			$this->value2YMD($value);
			$date = $this->day.'.'.$this->month.'.'.$this->year.' '.$this->time;
		} else {
			$date = '';	
		}
		
		return '
<div class="input-group">
	<input class="form-control" type="text" readonly="true" value="'.$date.'" name="'.$name.'" id="'.$name.'">
	<span class="input-group-btn">
		<a class="btn btn-warning" href="javascript:void(0)" id="trigger_'.$name.'"><span class="glyphicon glyphicon-calendar icon-white"></span></a>
	</span>
</div>			
<script type="text/javascript">addCalendar(\''.$name.'\', \'%H:%M:00\')</script>';
	}
	
	public function getType() {
		return 'datetime';
	}
}
