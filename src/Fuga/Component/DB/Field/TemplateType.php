<?php
    
namespace Fuga\Component\DB\Field;

class TemplateType extends Type {

	public function __construct(&$params, $entity = null) {
		parent::__construct($params, $entity);
	}
	
	protected function getPath($filename = '') {
		return PRJ_REF.SMARTY_PATH.$filename;
	}
	
	protected function getRealPath($filename = '') {
		return PRJ_DIR.SMARTY_PATH.$filename;
	}
	
	public function getBackupPath($filename) {
		return PRJ_REF.SMARTY_BACKUP_PATH.$filename;
	}
	
	public function getBackupRealPath($filename) {
		return PRJ_DIR.SMARTY_BACKUP_PATH.$filename;
	}
	
	public function backupTemplate($filename, $backup) {
		@copy($this->getRealPath($filename), $this->getBackupRealPath($backup));
		@chmod($this->getBackupRealPath($backup), 0666);
		@unlink($this->getRealPath($filename));
		$sql = "SELECT count(id) as quantity, min(id) as id FROM template_version WHERE table_name= :table AND field_name= :field AND entity_id= :id";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue('table', $this->getParam('table'));
		$stmt->bindValue('field', $this->getName());
		$stmt->bindValue('id', $this->dbId);
		$stmt->execute();
		$template = $stmt->fetch();
		if ($template['quantity'] >= TEMPLATE_VERSION_QUANTITY) {
			$this->get('connection')->delete('template_version', array('id' => $template['id']));
		}	
		$this->get('connection')->insert("template_version", array(
			'table_name' => $this->getParam('table'),
			'field_name' => $this->getName(),
			'entity_id' => $this->dbId,
			'file' => $backup,
			'created' => date('Y-d-m H:i:s')
		));
	}

	public function getSQLValue($name = '') {
		$name = $name ?: $this->getName();
		$filename = $this->get('request')->request->get($name.'_oldvalue');
		$date = date('Ymd_His');
		if ($filename && $this->get('request')->request->get($name.'_delete')) {
			$this->backupTemplate($filename, $filename.$date.'.bak');
			$filename = '';
		} elseif ($filename && $this->get('request')->request->get($name.'_version', true, 0)) {
			$sql = "SELECT * FROM template_version WHERE id= :id ";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue('id', $this->get('request')->request->get($name.'_version', true, 0));
			$stmt->execute();
			$template = $stmt->fetch();
			if ($template) {
				$this->backupTemplate($filename, $filename.$date.'.bak');
				@copy($this->getBackupRealPath($template['file']), $this->getRealPath($filename));
			}
		} elseif ($filename) {
			$fhandle = fopen($this->getRealPath($filename.'_new'), 'w');
			fwrite($fhandle, $_POST[$name.'_template']);
			fclose($fhandle);
			if (md5_file($this->getRealPath($filename.'_new')) != md5_file($this->getRealPath($filename))) {
				$this->backupTemplate($filename, $filename.$date.'.bak');
				@copy($this->getRealPath($filename.'_new'), $this->getRealPath($filename));
			}
			@unlink($this->getRealPath($filename.'_new'));
		}
		if (isset($_FILES[$name]) && $_FILES[$name]['name'] != '') {
			if ($filename) {
				$this->backupTemplate($filename, $filename.$date.'.bak');
			}
			$filename = $this->get('util')->getNextFileName($_FILES[$name]['name'], $this->getPath());
			move_uploaded_file($_FILES[$name]['tmp_name'], $this->getRealPath($filename));
			chmod($this->getRealPath($filename), 0666);
		} elseif ($this->get('request')->request->get($name.'_cre')) {
			$filename = trim($this->get('request')->request->get($name));
			if ($filename != '') {
				$filename = $this->get('util')->getNextFileName($filename, $this->getPath());
				$fhandle = fopen($this->getRealPath($filename), 'w');
				fwrite($fhandle, $_POST[$name."_template"]);
				fclose($fhandle);
				chmod($this->getRealPath($filename), 0666);
			}
		} 
		return $filename;
	}

	public function getStatic() {
		$content = '';
		if ($this->dbValue) {
			$content = '<a href="'.$this->getPath($this->dbValue).'">'.$this->dbValue.'</a> '.$this->get('filestorage')->size($this->getRealPath($this->dbValue));
		}
		return $content;
	}

	public function getInput($value = '', $name = '') {
		$text  = '';
		$content = '';
		$value = $value ?: $this->dbValue;
		$name  = $name  ?: $this->getName();
		if ($content = $this->getStatic()) {
			$content = '<div id="'.$name.'_delete">Текущая версия: '.$content.'<label for="'.$name.'_delete"><input name="'.$name.'_delete" type="checkbox" id="'.$name.'_delete"> удалить</label></div>';
			
			$sql = "SELECT * FROM template_version WHERE table_name= :table AND field_name= :field AND entity_id= :id ORDER BY id DESC";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue('table', $this->getParam('table'));
			$stmt->bindValue('field', $this->getName());
			$stmt->bindValue('id', $this->dbId);
			$stmt->execute();
			$versions = $stmt->fetchAll();
			if (count($versions)) {
				$content .= '<select class="form-control" onChange="changeTemplateState(\''.$name.'\')" id="'.$name.'_version" name="'.$name.'_version"><option value="0">Просмотр архивных версий</option>'."\n";
				foreach ($versions as $version) {
					$content .= '<option value="'.$version['id'].'">'.$version['created'].'</option>';
				}
				$content .= '</select> <div class="view-button hidden" id="'.$name.'_view"><input type="button" class="btn btn-success" onClick="showTemplateVersion(\''.$name.'\')" value="Просмотр"></div>';
			}
		}
		if (empty($value)){
			$text = '
<input type="hidden" name="'.$name.'_oldvalue" value="'.$this->dbValue.'">
<label for="'.$name.'_cre"><input name="'.$name.'_cre" type="checkbox" id="'.$name.'_cre" onClick="changeTemplateWidget(\''.$name.'\')"> Создать</label>
<div class="hidden" id="'.$name.'_template">
<input class="form-control" type="text" name="'.$name.'" placeholder="Название файла">
<textarea class="form-control" wrap="off" name="'.$name.'_template" placeholder="Шаблон" rows="15"></textarea>
</div>
<div id="'.$name.'_file"><input type="file" name="'.$name.'"></div>';
		} else {
			$text = @file_get_contents($this->getRealPath($this->dbValue));	
			$text = '
<input type="hidden" name="'.$name.'_oldvalue" value="'.$this->dbValue.'">'.$content.'
<div id="'.$name.'_file">Новый: <input type="file" name="'.$name.'"></div>
<textarea class="form-control" wrap="off" id="'.$name.'_template" name="'.$name.'_template" rows="15">'.htmlspecialchars($text).'</textarea>';
		}
		return $text;
	}
	
	public function getNativeValue() {
		return $this->getPath($this->dbValue);
	}
	
	public function free() {
		$sql = "SELECT id, file FROM template_version
				WHERE table_name= :table
				AND field_name= :field AND entity_id= :id ";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue('table', $this->getParam('table'));
		$stmt->bindValue('field', $this->getName());
		$stmt->bindValue('id', $this->dbId);
		$stmt->execute();
		$versions = $stmt->fetchAll();
		$ids = array();

		foreach ($versions as $version) {
			$ids[] = $version['id'];
			@unlink($this->getBackupRealPath($version['file']));
		}

		if ($ids) {
			$this->get('connection')->exec('DELETE FROM template_version WHERE id IN('.implode(',', $ids).')');
		}

		@unlink($this->getRealPath($this->dbValue));
	}

}
