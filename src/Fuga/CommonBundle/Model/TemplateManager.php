<?php

namespace Fuga\CommonBundle\Model;

class TemplateManager extends ModelManager {
	
	protected $entityTable = 'template_template';

	public function getByNode($name) {
		$sql = "
			SELECT t.template 
			FROM template_template t 
			JOIN template_rule r ON t.id = r.template_id 
			WHERE r.locale= :locale AND (
				(r.type='0' AND r.cond='')
				OR (r.type = 'T' AND ((r.datefrom < NOW() AND r.datetill > NOW()) OR (r.datefrom = 0 AND r.datetill = 0))) 
				OR (r.type = 'U' AND LOCATE(r.cond, :url ) > 0)	
				OR (r.type = 'F' AND r.cond = :name )
			) ORDER BY sort DESC LIMIT 1";
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue("locale", $this->get('session')->get('locale'));
		$stmt->bindValue("url", $_SERVER['REQUEST_URI']);
		$stmt->bindValue("name", $name);
		$stmt->execute();
		$template = $stmt->fetch();
		if ($template){
			return $template['template'];
		} else {
			throw new \Exception('Отсутствует шаблон для запрашиваемой страницы');
		}
	}
}