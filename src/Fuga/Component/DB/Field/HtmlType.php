<?php

namespace Fuga\Component\DB\Field;

class HtmlType extends Type {
	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}

	public function getStatic() {
		return $this->get('util')->cut_text(parent::getStatic());
	}

	public function getSearchInput() {
		$name = $this->getSearchName();
		$value = $this->getSearchValue();		
		
		return '<input type="text" id="'.$name.'" name="'.$name.'" value="'.htmlspecialchars($value).'">';
	}

	public function getInput($value = '', $name = '') {
		$value = $value ?: $this->dbValue;
		$name = $name ?: $this->getName();
		$content = '<label><input type="checkbox" checked="checked" onClick="controlEditor(this, \''.$name.'\')"> Редактор</label>';
		$content .= '<textarea class="form-control tinymce" id="'.$name.'" name="'.$name.'">'.htmlspecialchars($value).'</textarea>';
		
		return $content;
	}

	public function getSQLValue($name = '') {
		$text = $this->getValue($name);
		if (preg_match_all('/href="'.PCRE_RES_REF.'\/[\S]*\.(jpg|gif|png)+"/', $text, $matches)){
			foreach ($matches[0] as $m){
				$tmp = substr($m, 6, strlen($m)-7);
				$width = 50;
				$height = 50;
				if (file_exists(PRJ_DIR.$tmp)){
					$size = getImagesize(PRJ_DIR.$tmp);
					$width = $size[0];
					$height = $size[1];					
				}
				$text = str_replace($m, 'href="#" onClick="newWin(\''.$tmp.'\','.$width.','.$height.'); return false;"',$text);
			}
		}
		if (preg_match_all('/href="'.PCRE_RES_REF.'\/[\S]*\.(html)"/', $text, $matches)){
			preg_replace('/onclick=".+; return false;"/', '', $text);
			foreach ($matches[0] as $m){
				$tmp = substr($m, 6, strlen($m)-7);
				$width = 640;
				$height = 480;
				$text = str_replace($m, 'href="#" onClick="newWinHtml(\''.$tmp.'\','.$width.','.$height.'); return false;"',$text);
			}
		}
		return str_replace('&amp;', '&', $text);
	}
	
	public function getType() {
		return 'text';
	}
}
