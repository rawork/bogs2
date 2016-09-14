<?php

namespace Fuga\Component;
	
class Util
{
	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}
	
	public function cut_text($text, $len = 120) {
		if (strlen($text) > $len) {
			$text = substr(html_entity_decode($text, null, 'cp1251'), 0, $len);
			$text = substr($text, 0, strrpos($text, ' '));
			$text .= '...';
		}
		return $text;
	}

	public function prepend_zeroes($str, $length) {
		return str_pad($str, $length, '0', STR_PAD_LEFT);
	}

	public function getSize($bytes, $precision = 2) {
		$units = array('б', 'Кб', 'Мб', 'Гб', 'Тб');
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . '&nbsp;' . $units[$pow];
	} 

	/** 
	 * Заливка одноименных файлов 
	 */
	public function getNextFileName($filename, $path, $counter = null) {
		if (!$counter) {
			$filename = strtolower($this->translit($filename));
			if (!$filename) {
				throw new Exception('Пустое имя сохраняемого файла');
			}
		}	
		$pathParts = pathinfo($path.$filename);
		$pathParts['filename'] .= $counter ? '_'.$counter : '';
		$filename_path = $pathParts['dirname'].'/'.$pathParts['filename'].(isset($pathParts['extension']) ? '.'.$pathParts['extension'] : '');
		$filename_ready = $pathParts['filename'].(isset($pathParts['extension']) ? '.'.$pathParts['extension'] : '');

		return file_exists(PRJ_DIR.$filename_path) ? $this->getNextFileName($filename, $path, ++$counter) : $filename_ready;
	}

	public function translit($str) {
		$cyrilic = array(
			"а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", 
			"й", "к", "л", "м", "н", "о", "п", "р", "с", "т", 
			"у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", 
			"э", "ю", "я", "_", " ", ",", 
			"А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", 
			"Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", 
			"У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", 'Ь', 
			"Э", "Ю", "Я");
		$latin = array(
			"a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", 
			"y", "k", "l", "m", "n", "o", "p", "r", "s", "t", 
			"u", "f", "h", "ts", "ch", "sh", "shch", "-", "i", "-", 
			"e", "yu", "ya", "-", "-", "", 
			"A", "B", "V", "G", "D", "E", "E", "ZH", "Z", "I", 
			"Y", "K", "L", "M", "N", "O", "P", "R", "S", "T", 
			"U", "F", "H", "TS", "CH", "SH", "SHCH", "-", "I", "-",
			"E", "YU", "YA");
		return str_replace($cyrilic, $latin, $str);
	}

	public function format_date($str, $format) {
		setlocale(LC_ALL, 'ru_RU.utf-8');
		$date = new \DateTime($str);
		$dstr = $date->format($format);
		$locale = $this->container->get('session')->get('locale', PRJ_LOCALE);
		if ($locale != 'en') {
			$smonth = array(
				'ru' => array('Jan' => 'Январь', 'Feb' => 'Февраль', 'Mar' => 'Март', 'Apr' => 'Апрель', 'May' => 'Май', 'Jun' => 'Июнь', 'Jul' => 'Июль', 'Aug' => 'Август', 'Sep' => 'Сентябрь', 'Oct' => 'Октябрь', 'Nov'  => 'Ноябрь', 'Dec' => 'Декабрь'),
				'ua' => array(),
			);
			$month = array(
				'ru' => array('January' => 'Января', 'February' => 'Февраля', 'March' => 'Марта', 'April' => 'Апреля', 'May' => 'Мая', 'June' => 'Июня', 'July' => 'Июля', 'August' => 'Августа', 'September' => 'Сентября', 'October' => 'Октября', 'November'  => 'Ноября', 'December' => 'Декабря'),
				'ua' => array(),
			);
			$sweekday = array(
				'ru' => array('Mon' => 'пон', 'Tue' => 'вт', 'Wed' => 'ср', 'Thu' => 'чт', 'Fri' => 'пт', 'Sat' => 'сб', 'Sun' => 'вс'),
				'ua' => array(),
			);
			$weekday = array(
				'ru' => array('Monday' => 'понедельник', 'Tuesday' => 'вторник', 'Wednesday' => 'среда', 'Thursday' => 'четверг', 'Friday' => 'пятница', 'Saturday' => 'суббота', 'Sunday' => 'воскресенье'),
				'ua' => array(),
			);
			if (stripos($format, 'M') !== false) {
				$dstr = strtr($dstr, $smonth[$locale]);
			} else {
				$dstr = strtr($dstr, $month[$locale]);
			}
			$dstr = strtr($dstr, array_merge($sweekday[$locale],$weekday[$locale]));
		} 
		return $dstr;
	}

	public function isEmail($email) {
		return preg_match('/^[a-z0-9]+([-_\.]?[a-z0-9])*@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}$/i', $email);
	}

	public function get_http_content($host, $url = '/') {
		$retry = 0;
		while (!($f = @fsockopen($host, 80)) && $retry++ < 10) {
			sleep(1);
		}
		if ($f) {
			$request = 'GET '.$url." HTTP/1.1\r\n".
				'Host: '.$host."\r\n".
				'Referer: http://'.$host."/\r\n". 
				"Accept: text/html\r\n". 
				"Accept-Language: ru\r\n". 
				"User-Agent: Mozilla/5.0 (compatible; MSIE 5.5; Windows NT 5.0)\r\n\r\n";
				if (fputs($f, $request)) {
				$ret = '';
				while (!feof($f)) {
					$ret .= fgets($f, 1024);
				}
				fclose($f);
				return $ret;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Сумма прописью - доработал - Alexander Selifonov
	 * last updated: 15.01.2007
	 * echo SumProp(2004.30, 'руб.', 'коп.');
	 * SumProp(nnnn,'USD'|'RUR'|'EUR')-полный вывод со спряжением "долларов"-"центов"
	 * 
	 */
	public function sumProp($srcsumm,$val_rub='', $val_kop=''){
		$cifir= Array('од','дв','три','четыр','пят','шест','сем','восем','девят');
		$sotN = Array('сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот');
		$milion= Array('триллион','миллиард','миллион','тысяч');
		$anDan = Array('','','','сорок','','','','','девяносто');
		$scet=4;
		$cifR='';
		$cfR='';
		$oboR= Array();
		//==========================
		$splt = explode('.', $srcsumm);
		if(sizeof($splt)<2) $splt = explode(',', $srcsumm);
		$xx = $splt[0];
		$xx1 = (empty($splt[1])? '00': $splt[1]);
		$xx1 = str_pad($xx1, 2, '0', STR_PAD_RIGHT); // 2345.1 -> 10 копеек
		//  $xx1 = round(($srcsumm-floor($srcsumm))*100);
		if ($xx>999999999999999) { $cfR=$srcsumm; return $cfR; }
		while($xx/1000>0){
			$yy=floor($xx/1000);
			$delen= round(($xx/1000-$yy)*1000);

			$sot= floor($delen/100)*100;
			$des=(floor($delen-$sot)>9? floor(($delen-$sot)/10)*10:0);
			$ed= floor($delen-$sot)-floor(($delen-$sot)/10)*10;

			$forDes=($des/10==2?'а':'');
			$forEd= ($ed==1 ? 'ин': ($ed==2?'е':'') );
			if ( floor($yy/1000)>=1000 ) { // делаю "единицы" для тысяч, миллионов...
				$ffD=($ed>4?'ь': ($ed==1 || $scet<3? ($ed<2?'ин': ($scet==3?'на': ($scet<4? ($ed==2?'а':( $ed==4?'е':'')) :'на') ) ) : ($ed==2 || $ed==4?'е':'') ) );
			} else { // единицы для "единиц
				$ffD=($ed>4?'ь': ($ed==1 || $scet<3? ($scet<3 && $ed<2?'ин': ($scet==3?'на': ($scet<4? ($ed==2?'а':( $ed==4?'е':'')) :'ин') ) ) : ( $ed==4?'е':($ed==2?'а':'')) ) );
			}
			if($ed==2) $ffD = ($scet==3)?'е':'а'; // два рубля-миллиона-миллиарда, но две тысячи

			$forTys=($des/10==1? ($scet<3?'ов':'') : ($scet<3? ($ed==1?'': ($ed>1 && $ed<5?'а':'ов') ) : ($ed==1? 'а': ($ed>1 && $ed<5?'и':'') )) );
			$nnn = floor($sot/100)-1;
			$oprSot=(!empty($sotN[$nnn]) ? $sotN[$nnn]:'');
			$nnn = floor($des/10);
			$oprDes=(!empty($cifir[$nnn-1])? ($nnn==1?'': ($nnn==4 || $nnn==9? $anDan[$nnn-1]:($nnn==2 || $nnn==3?$cifir[$nnn-1].$forDes.'дцать':$cifir[$nnn-1].'ьдесят') ) ) :'');

			$oprEd=(!empty($cifir[$ed-1])? $cifir[$ed-1].(floor($des/10)==1?$forEd.'надцать' : $ffD ) : ($des==10?'десять':'') );
			$oprTys=(!empty($milion[$scet]) && $delen>0) ? $milion[$scet].$forTys : '';

			$cifR= (strlen($oprSot) ? ' '.$oprSot:'').
				(strlen($oprDes)>1 ? ' '.$oprDes:'').
				(strlen($oprEd)>1  ? ' '.$oprEd:'').
				(strlen($oprTys)>1 ? ' '.$oprTys:'');
			$oboR[]=$cifR;
			$xx=floor($xx/1000);
			$scet--;
			if (floor($xx)<1 ) break;
		}
		$oboR = array_reverse($oboR);
		for ($i=0; $i<sizeof($oboR); $i++){
			$probel = strlen($cfR)>0 ? ' ':'';
			$cfR .= (($oboR[$i]!='' && $cfR!='') ? $probel:'') . $oboR[$i];
		}
		if (strlen($cfR)<3) $cfR='ноль';

		$intsrc = $splt[0];
		$kopeiki = $xx1;
		$kop2 =str_pad($xx1, 2, '0', STR_PAD_RIGHT);

		$sum2 = str_pad($intsrc, 2, '0', STR_PAD_LEFT);
		$sum2 = substr($sum2, strlen($sum2)-2); // 676571-> '71'
		$sum21 = substr($sum2, strlen($sum2)-2,1); // 676571-> '7'
		$sum22 = substr($sum2, strlen($sum2)-1,1); // 676571-> '1'
		$kop1  = substr($kop2,0,1);
		$kop2  = substr($kop2,1,1);
		$ar234 = array('2','3','4'); // доллар-А, рубл-Я...
		// делаю спряжения у слова рубл-ей|я|ь / доллар-ов... / евро
		if($val_rub=='RUR') {
			$val1 = 'рубл';
			$val2 = 'копейка';
			if($sum22=='1' && $sum21!='1') $val1 .= 'ь'; // 01,21...91 рубль
			elseif(in_array($sum22, $ar234) && ($sum21!='1')) $val1 .= 'я';
			else $val1 .= 'ей';
			if(in_array($kop2, $ar234) && ($kop1!='1')) $val2 = 'копейки';
			elseif($kop2=='1' && $kop1!='1') $val2 = 'копейка'; // 01,21...91 копейка
			else $val2 = 'копеек';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} elseif($val_rub=='USD') {
			$val1 = 'доллар';
			$val2 = 'цент';
			if($sum22=='1' && $sum21!='1') $val1 .= ''; // 01,21...91 доллар
			elseif(in_array($sum22, $ar234) && ($sum21!='1')) $val1 .= 'a';
			else $val1 .= 'ов';
			if($kop2=='1' && $kop1!='1') $val2 .= ''; // 01,21...91 цент
			elseif(in_array($kop2, $ar234) && ($kop1!='1')) $val2 .= 'a';
			else $val2 .= 'ов';
			$val1 .= ' США';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} elseif($val_rub=='EUR') {
			$val1 = 'евро';
			$val2 = 'цент';
			if($kop2=='1' && $kop1!='1') $val2 .= ''; // 01,21...91 цент
			elseif(in_array($kop2, $ar234) && ($kop1!='1')) $val2 .= 'a';
			else $val2 .= 'ов';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} else {
			$cfR .= ' '.$val_rub;
			if($val_kop!='') $cfR .= ' '.$kopeiki.' '.$val_kop;
		}
		return $cfR;
	}

	/* Генерация Хеш-ключа */
	public function genKey($n = 8) {
		$pwd = ''; 
		$k = 0; 
		$pass = array(); 
		$w = rand(30,80); 
		for ($r = 0; $r < $w; $r++) { 
			$res = rand(65,90); 
			$pass[$k] = chr($res); 
			$k++; 
			$res = rand(97,122); 
			$pass[$k] = chr($res); 
			$k++; 
			$res = rand(48,57); 
			$pass[$k] = chr($res); 
			$k++; 
		} 
		for ($i = 0; $i < $n; $i++) { 
			$s = rand(1,$k-1); 
			$pwd .= $pass[$s]; 
		} 
		return $pwd;
	}

	public function guid(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		} else {
			mt_srand((double)microtime()*10000);
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
			return $uuid;
		}
	}

	public function fetch_user_salt($length = 3){
		$salt = '';
		for ($i = 0; $i < $length; $i++)
			$salt .= chr(rand(32, 126));
		return $salt;
	}

	public function error($message) {
		echo <<<EOD
<div style="padding:15px;color:#990000;font-size:14px;"><b>Ошибка</b>:&nbsp;$message</div>
EOD;
	}

}