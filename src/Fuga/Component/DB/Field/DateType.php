<?php

namespace Fuga\Component\DB\Field;

// Даты можно разбирать как объект, php 5
class DateType extends Type {
	protected $arr;
	protected $year, $month, $day; 
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
		// немного уменьшаем геморой...
		$this->arr = array(
			'year' => 4,
			'month' => 2,
			'day' => 2
		);
	}

	public function value2YMD($value) {
		if (!empty($value)) {
			$this->year = substr($value, 0, $this->arr['year']);
			$this->month = substr($value, 5, $this->arr['month']);
			$this->day = substr($value, 8, $this->arr['day']);
		} else {
			$ts = time();
			$this->year = date('Y', $ts);
			$this->month = date('m', $ts);
			$this->day = date('d', $ts);
		}
	}

	/*** implementation */

	public function getSQLValue($name = '') {
		if (trim($this->getValue($name))) {
			$date = \DateTime::createFromFormat('d.m.Y', $this->getValue($name));
			return $date->format('Y-m-d');
		} else {
			return "0000-00-00";
		}
	}

	public function getStatic() {
		$this->value2YMD($this->dbValue);
		return $this->day.".".$this->month.".".$this->year;
	}

	public function getInput($value = '', $name = '') {
		return $this->dateType_getInput(($name ?: $this->getName()), $value?: $this->dbValue);
	}

	public function getSearchInput() {
		if ($date = $this->getSearchValue('beg')) {
			$date_beg = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2);
		} else {
			$date_beg = '';//date('Y-m-d');
		}
		if ($date = $this->getSearchValue('end')) {
			$date_end = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2);
		} else {
			$date_end = '';//date('Y-m-d');
		}
		return '<div class="form-inline">c '.$this->dateType_getInput(parent::getSearchName('beg'), $date_beg, false).' по '.$this->dateType_getInput(parent::getSearchName('end'), $date_end, false).' <a href="javascript:void(0)" onClick="emptyDateSearch(\''.parent::getSearchName().'\')">Обнулить</a></div>';
	}

	public function getSearchSQL() {
		$ret = '';
		if ($date = $this->getSearchValue('beg')) {
			$ret .= ($ret ? ' AND ' : '').$this->getName().">=STR_TO_DATE('$date','%d.%m.%Y')";
		}
		if ($date = $this->getSearchValue('end')) {
			$ret .= ($ret ? ' AND ' : '').$this->getName()."<=STR_TO_DATE('$date','%d.%m.%Y')";
		}
		return $ret;
	}

	public function getSearchURL($name = '') {
		$ret = '';
		if (parent::getSearchURL('beg')) {
			$ret = parent::getSearchURL('beg');
		}
		if (parent::getSearchURL('end')) {
			$ret .= ($ret ? '&' : '').parent::getSearchURL('end');
		}
		return $ret;
	}

	public function dateType_getInput($name, $value = '', $insertValue = true) {
		if ($value || $insertValue) {
			$this->value2YMD($value);
			$date = $this->day.'.'.$this->month.'.'.$this->year;
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
<script type="text/javascript">addCalendar(\''.$name.'\')</script>';
	}
	
	public function getType() {
		return 'date';
	}
}
