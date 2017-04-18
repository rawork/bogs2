<?php

namespace Fuga\Component\DB\Field;

class SelectListType extends Type
{
	public function __construct(&$params, $entity = null)
	{
		parent::__construct($params, $entity);
	}

	public function getSQLValue($name = '')
	{
		$name = $name ? $name : $this->getName();
		$value = explode(',', $this->get('request')->request->get($name));

		$this->get('connection')->delete($this->getParam('link_table'), array($this->getParam('link_inversed') => $this->dbId));
		if (is_array($value)) {
			foreach ($value as $id) {
				$this->get('connection')->insert(
					$this->getParam('link_table'),
					array(
						$this->getParam('link_inversed') => $this->dbId,
						$this->getParam('link_mapped') => $id,
					)
				);
			}
		}

		return '';
	}

	public function getStatic($value = null)
	{
		$content = '';
		$fields = explode(',', $this->getParam('l_field'));
		$items = $this->getNativeValue();
		if ($items) {
			foreach ($items as $k => $item) {
				$content .= (!empty($content) && $k) ? ', ' : '';
				foreach ($fields as $fieldName) {
					if (array_key_exists($fieldName, $item)) {
						$content .= ' '.$item[$fieldName];
					}
				}
				$content .= ' ('.$item['id'].')';
			}

			return $content;
		} else {
			return 'Не выбрано';
		}
	}

	public function getSearchInput() {
		$value = intval($this->dbValue);
		$name = $this->getName();
		$table = $this->getParam('table');
		$id = $this->dbId ?: '0';
		$input_id = strtr($name, '[]', '__');
		$empty = $value ? ' <a href="javascript:void(0)" onClick="emptySelect(\''.$input_id.'\')"><i class="icon-remove"></i></a>' : '';
		$content = '
<div id="'.$input_id.'_title">'.$this->getStatic($value).$empty.'</div>
<button class="btn btn-success" href="javascript:void(0)" type="button" onClick="showSelectDialog(\''.$input_id.'\',\''.$table.'\',\''.$name.'\', \''.$id.'\', \''.$this->getStatic($value).'\');">Выбрать</button>
<input type="hidden" name="'.$name.'" value="'.$value.'" id="'.$input_id.'">
<input type="hidden" name="'.$name.'_type" value="'.$this->getParam('link_type').'" id="'.$input_id.'_type">
';

		return $content;
	}
	
	public function getSearchSQL() {
		$value = $this->getSearchValue();
		if ($value) {
			$sql = 'SELECT * FROM '.$this->getParam('link_table').' WHERE '.$this->getParam('link_mapped').' = '.$value;
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue('id', $this->dbId);
			$stmt->execute();
			$links = $stmt->fetchAll();
			$ids  = array();
			foreach ($links as $link) {
				$ids[] = $link[$this->getParam('link_inversed')];
			}
			return $ids ? ' id IN('.implode(',', $ids).') ' : '';
		}

		return $value;
	}

	public function getInput($value = '', $name = '')
	{
		$name = $name ? $name : $this->getName();

		if (!$this->dbId) {
			return 'Добавление элементов будет доступно после сохранения записи!';
		}

		if ('dialog' == $this->getParam('view_type')) {
			$sql = 'SELECT t1.'.$this->getParam('link_mapped').', t0.*
			FROM '.$this->getParam('l_table').' as t0
			JOIN '.$this->getParam('link_table').' as t1 ON t1.'.$this->getParam('link_mapped').' = t0.id
			WHERE t1.'.$this->getParam('link_inversed').' = :id
			ORDER BY t0.'.$this->getParam('l_sort');
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue('id', $this->dbId);
			$stmt->execute();
			$items = $stmt->fetchAll();

			return $this->get('templating')->render(
				'form/field/selectlist.dialog.html.twig',
				array(
					'items' => $items,
					'name' => $name,
					'l_field' => $this->getParam('l_field'),
					'link_mapped' => $this->getParam('link_mapped')
				)
			);
		} else {
			$sql = 'SELECT t1.'.$this->getParam('link_inversed').',t1.'.$this->getParam('link_mapped').', t0.*
			FROM '.$this->getParam('l_table').' as t0
			JOIN '.$this->getParam('link_table').' as t1 ON (t1.'.$this->getParam('link_mapped').' = t0.id '.($this->dbId ? 'AND t1.'.$this->getParam('link_inversed').'='.$this->dbId : '').') 
			ORDER BY t0.'.$this->getParam('l_sort');

			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();
			$items = $stmt->fetchAll();

			$tags = array();
			foreach ($items as $item) {
				$tags[] = array(
					'tag_id' => $item['id'],
					'tag_name' => $item['name'],
				);
			}

			// todo сделать урлы универсальными
			return '<input class="multiselect" id="'.$name.'" name="'.$name.'" type="text" value="" data-list-url="/api/tag"  data-create-url="/api/tag/create" data-selectize-value=\''.json_encode($tags).'\'/>';
		}

	}

	public function getNativeValue() {
		$sql = 'SELECT t1.'.$this->getParam('link_mapped').', t0.*
		FROM '.$this->getParam('l_table').' as t0
		JOIN '.$this->getParam('link_table').' as t1 ON t1.'.$this->getParam('link_mapped').' = t0.id
		WHERE t1.'.$this->getParam('link_inversed').' = :id
		ORDER BY t0.'.$this->getParam('l_sort');

		$stmt = $this->get('connection')->prepare($sql);
		$stmt->bindValue('id', $this->dbId);
		$stmt->execute();
		$items = $stmt->fetchAll();

		return $items;
	}
}
