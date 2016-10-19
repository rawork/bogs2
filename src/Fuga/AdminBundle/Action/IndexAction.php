<?php
	
namespace Fuga\AdminBundle\Action;

use Fuga\AdminBundle\Controller\AdminController;

class IndexAction extends AdminController
{
	public $table;
	public $baseRef;
	public $searchRef;
	public $fullRef;
	public $action;
	protected $search_url;
	protected $search_sql;
	protected $tableParams;
	private $links = array();

	private $showGroupSubmit	= false;
	private $paginator;
	private $elementsIds		= array();
	protected $rowPerPage		= 25;

	private $state;
	private $module;
	private $entity;

	public function __construct($state, $module, $entity)
	{
		$this->state = $state;
		$this->module = $module;
		$this->entity = $entity;
		$this->paginator = $this->get('paginator');
		$this->table = $this->get('container')->getTable($module.'_'.$entity);
		$this->baseRef = $this->generateUrl(
			'admin_entity_index',
			array(
				'state'  => $state,
				'module' => $module,
				'entity' => $entity,
			)
		);
		$this->searchRef = $this->baseRef;
		$this->fullRef = $this->searchRef.($this->get('request')->query->get('page') ? '?page='.$this->get('request')->query->get('page') : '');
		$this->rowPerPage = $this->get('session')->get($this->table->dbName().'_rpp', $this->rowPerPage);
	}

	/* Кнопки управления записью */
	private function _getUpdateDelete($id)
	{
		$ref = explode('?', $this->fullRef);
		$buttons = '<td>
<div class="btn-group pull-right">
  <a class="btn btn-default btn-sm dropdown-toggle admin-dropdown-toggle" id="drop'.$id.'" data-toggle="dropdown" href="#">
    <span class="glyphicon glyphicon-align-justify"></span>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu admin-dropdown-menu">
    <li><a href="'.$ref[0].'/'.$id.'/edit"><i class="glyphicon glyphicon-pencil"></i> Изменить</a></li>
    <li><a href="javascript:startDelete('.$id.')"><i class="glyphicon glyphicon-trash"></i> Удалить</a></li>
    <li><a href="javascript:showCopyDialog('.$id.')"><i class="glyphicon glyphicon-random"></i> Копировать</a></li>
  </ul>
</div>
</td>
';
		return $buttons;
	}
	
	private function showCredate() 
	{
		return !empty($this->table->params['show_credate']);
	}

	private function getTableContent()
	{
		$tableHtml = '';
		$this->paginator->paginate(
			$this->table,
			$this->searchRef.'?page=###', 
			$this->search_sql, 
			$this->rowPerPage, 
			$this->get('request')->query->getInt('page', 1),
			10
		);
		$this->table->select(
			array (
				'where' => $this->search_sql,
				'limit' => $this->paginator->limit
			)
		);
		$entities = $this->table->getNextArrays(false);
		foreach ($entities as $entity) {
			$this->elementsIds[] = $entity['id'];
			$tableHtml .= '<tr>';
			$tableHtml .= '
<td><input type="checkbox" class="list-checker" value="'.$entity['id'].'"></td>
<td>'.$entity['id'].'</td>';
			reset($this->table->fields);
			foreach ($this->table->fields as $field) {
				if (!empty($field['width'])) {
					$ft = $this->table->getFieldType($field, $entity);
					$tableHtml .= '<td>';
					$sFieldHtml = '';
					if (!empty($field['group_update']) && empty($field['readonly'])) {
						$sFieldHtml .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$sFieldHtml .= $ft->getGroupStatic();
					}
					$tableHtml .= ($sFieldHtml ? $sFieldHtml : '&nbsp;').'</td>'."\n";
				}
			}
			if ( $this->table->params['show_credate'] ) {
				$tableHtml .= '<td>'.$entity['created'].'</td>'."\n";
			}
			$tableHtml .= $this->_getUpdateDelete($entity['id']).'</tr>'."\n";
		}

		$message = null;
		if ($adminMessage = $this->get('session')->getFlashBag()->get('admin.message')) {
			$message = array_shift($adminMessage);
		}

		$params = array(
			'state' => $this->state,
			'module' => $this->module,
			'entity' => $this->entity,
			'tableData' => $tableHtml,
			'paginator' => $this->paginator,
			'showCredate' => $this->showCredate(),
			'fields' => $this->table->fields,
			'rpps' => array(10,25,50,100,200),
			'rowPerPage' => $this->rowPerPage,
			'ids' => join(',', $this->elementsIds),
			'isView' => !empty($this->table->params['is_view']),
			'tableName' => $this->table->dbName(),
			'showGroupSubmit' => $this->showGroupSubmit,
			'links' => $this->links,
			'filters' => $this->getFilterForm(),
			'message' => $message,
			'title' => $this->table->title,
		);
		
		return $this->render('admin/action/index.html.twig', $params);
	}

	private function getTree($parentId, $prefixWidth = 0, $styleClass = '')
	{
		$tableHtml = '';
		$where = 'parent_id='.$parentId.' '.($this->search_sql ? ' AND '.$this->search_sql : '');
		$this->table->select(
			array(
				'where'		=> '1=1',
				'order_by'	=> 'left_key'
			)
		);
		$nodes = $this->table->getNextArrays();
		$styleClass .= 't'.$parentId;
		foreach ($nodes as $node) {
			$this->elementsIds[] = $node['id'];
			$tableHtml .= '<tr rel="'.$node['parent_id'].'" class="'.$styleClass.'">';
			$tableHtml .= '<td width="1%"><input type="checkbox" class="list-checker" value="'.$node['id'].'"></td><td width="1%">'.$node['id'].'</td>';
			$num = 0;

			$prefixWidth = 20 * ((int)$node['level']-1);
			foreach ($this->table->fields as $field) {
				if (!empty($field['width'])) {
					$tableHtml .= '<td width="'.$field['width'].'">';
					if ($num == 0) {
						$tableHtml .= '<span><div style="display:inline-block;width:'.$prefixWidth.'px"></div> &rarr; </span>';
					}
					$ft = $this->table->getFieldType($field, $node);
					if (!empty($field['group_update']) && empty($field['readonly'])) {
						$tableHtml .= $ft->getGroupInput();
						$this->showGroupSubmit = true;
					} else {
						$tableHtml .= $ft->getStatic();
					}
					if ($num == 0) {
						if ($this->table->dbName() == 'page_page' && isset($node['module_id_value']['item'])) {
							$module = $this->get('container')->getModule($node['module_id_value']['item']['name']);
							if ( $module ) {
								$tableHtml .= ' (тип &mdash; '.$module['title'].')';
							}
						}
					}
					$tableHtml .= '</td>';
				}
				$num++;
			}
			$tableHtml .= $this->_getUpdateDelete($node['id']).'</tr>';
		}
		
		return $tableHtml;
	}

	private function getTreeContent()
	{
		$message = null;
		if ($adminMessage = $this->get('session')->getFlashBag()->get('admin.message')) {
			$message = array_shift($adminMessage);
		}

		$params = array(
			'state' => $this->state,
			'module' => $this->module,
			'entity' => $this->entity,
			'tableData' => $this->getTree(0, 0, ''),
			'paginator' => $this->paginator,
			'showCredate' => $this->showCredate(),
			'fields' => $this->table->fields,
			'ids' => join(',', $this->elementsIds),
			'isView' => !empty($this->table->params['is_view']),
			'showGroupSubmit' => $this->showGroupSubmit,
			'links' => $this->links,
			'filters' => $this->getFilterForm(),
			'message' => $message,
			'title' => $this->table->title,
		);
		return $this->render('admin/action/index.html.twig', $params);
	}

	private function getFilterForm()
	{
		foreach ($this->table->fields as &$field) {
			if (!empty($field['search'])) {
				$ft = $this->table->getFieldType($field);
				$field['searchinput'] = $ft->getSearchInput();
			}
		}
		unset($field);
		$params = array(
			'baseRef' => $this->baseRef,
			'fields' => $this->table->fields
		);
		return $this->get('templating')->render('admin/common/filter.html.twig', $params);
	}

	private function initSearchCriteria()
	{
		if (is_object($this->table)) {
			if ($filterType = $this->get('request')->request->get('filter_type')) {
				switch ($filterType) {
					case 'cancel':
						$this->get('session')->remove('cms_table_'.$this->table->dbName());
						break;
					default:
						$this->search_url = $this->table->getSearchURL($this->get('request'));
						parse_str($this->search_url, $this->tableParams);
						$this->get('session')->set('cms_table_'.$this->table->dbName(), serialize($this->tableParams));
				}

				return true;
			}

			$this->tableParams = unserialize($this->get('session')->get('cms_table_'.$this->table->dbName()));

			if (is_array($this->tableParams)) {
				foreach ($this->tableParams as $key => $value) {
					$this->get('request')->request->set($key, $value);
				}
			}

			$this->search_sql = $this->table->getSearchSQL();
		}

		return false;
	}

	public function run()
	{
		if ($this->initSearchCriteria()) {
			return $this->reload();
		}

		$this->links[] = array(
			'ref' => $this->generateUrl(
				'admin_entity_add',
				array('state' => $this->state, 'module' => $this->module, 'entity' => $this->entity)),
			'name' => 'Добавить запись',
		);
		if ($this->get('security')->isDeveloper()) {
			$links[] =	array(
				'ref' => $this->fullRef.'/table',
				'name' => 'Настройка таблицы'
			);
		}
		$this->links[] = array(
			'ref' => $this->generateUrl(
					'admin_entity_table_create',
					array('state' => $this->state, 'module' => $this->module, 'entity' => $this->entity)),
			'name' => 'Создать таблицу',
		);
		$this->links[] = array(
			'ref' => $this->generateUrl(
					'admin_entity_table_alter',
					array('state' => $this->state, 'module' => $this->module, 'entity' => $this->entity)),
			'name' => 'Обновить таблицу',
		);

		if (!empty($this->table->params['is_view'])) {
			return $this->getTreeContent();
		} else {
			return $this->getTableContent();
		}
	}
}
