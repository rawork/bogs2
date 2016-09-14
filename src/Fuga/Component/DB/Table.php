<?php

namespace Fuga\Component\DB;
	
use Fuga\Component\Container;

class Table
{
	public $name;
	public $id;
	public $title;
	public $fields;
	public $params;
	public  $moduleName;
	public  $tableName;

	private $dbname;
	private $stmt;
	private $filedtypes = array();
	private $container;

	public function __construct($table, Container $container)
	{
		$this->container = $container;
		$this->name = $table['name'];
		$this->title = $table['title'];
		$this->tableName		= $table['name'];
		$this->moduleName		= $table['module'];
		$this->dbname			= $this->moduleName.'_'.$this->tableName;

		$this->id = isset($table['id']) ? $table['id'] : 0;
		$this->fields = array();

		$table['is_lang']		= !empty($table['is_lang']);
		$table['is_sort']		= !empty($table['is_sort']);
		$table['is_publish']	= !empty($table['is_publish']);
		$table['is_system']		= !empty($table['is_system']);
		$table['is_search']		= !empty($table['is_search']);
		$table['multifile']		= !empty($table['multifile']);
		$table['show_credate']	= !empty($table['show_credate']);
		$table['order_by']		= !empty($table['order_by']) ? $table['order_by'] : '';
		$table['rpp']			= !empty($table['rpp']) ? $table['rpp'] : 25;

		$this->params = $table;
		$this->setTableFields();
	}
	
	public function dbName()
	{
		return $this->dbname;
	}
	
	private function readConfig()
	{
		if (!empty($this->params['fieldset']) && is_array($this->params['fieldset'])) {
			$this->fields = $this->params['fieldset'];
		} else {
			throw new \Exception('Table config file format error: '.$this->tableName);
		}
	}

	private function readDBConfig()
	{
		$sql = "SELECT * FROM table_field WHERE publish=1 AND table_id= :id ORDER by sort";
		$stmt = $this->container->get('connection')->prepare($sql);
		$stmt->bindValue('id', $this->id);
		$stmt->execute();
		$fields = $stmt->fetchAll();
		if ($fields) {
			foreach ($fields as $field) {
				$field['group_update'] = $field['group_update'] == 1;
				$field['readonly'] = $field['readonly'] == 1;
				$field['search'] = $field['search'] == 1;
				$field['table_name'] = $this->dbName();
				if (!empty($field['params'])) {
					$params = explode(';', trim($field['params']));
					foreach ($params as $param) {
						if (!empty($param) && stristr($param, ':')) {
							$values = explode(':', $param);
							$field[$values[0]] = str_replace("`", "'", $values[1]);
						}
					}
				}
				$this->fields[$field['name']] = $field;
			}
		} else {
			throw new \Exception('В таблице '.$this->dbName().' не настроены поля');
		}
	}
	
	public function getFieldType($field, $entity = null)
	{
		if (empty($this->filedtypes[$field['type']])) {
			switch ($field['type']) {
				case 'select_tree':
					$fieldName = 'SelectTree';
					break;
				case 'select_list':
					$fieldName = 'SelectList';
					break;
				default:	
					$fieldName = ucfirst($field['type']);
					break;
			}
			$className = '\\Fuga\\Component\\DB\\Field\\'.$fieldName.'Type';
			$this->filedtypes[$field['type']] = new $className($field);
		}
		$this->filedtypes[$field['type']]->setParams($field);
		$this->filedtypes[$field['type']]->setEntity($entity);
		
		return $this->filedtypes[$field['type']];
	}

	public function getFieldList()
	{
		$ret = array('id');
		foreach ($this->fields as $field) {
			if (in_array($field['type'], array('gallery'))) {
				continue;
			}
			$ret[] = $field['name'];
		}
		
		return $ret;
	}

	public function insertGlobals()
	{
		$extraIds = array();
		$values = array();
		foreach ($this->fields as $field) {
			if (in_array($field['type'], array('gallery', 'select_list'))) {
				continue;
			}	
			$fieldType = $this->getFieldType($field);
			switch ($field['name']) {
				case 'created':
					$values[$fieldType->getName()] = date('Y-m-d H:i:s');
					break;
				case 'locale':
					$values[$fieldType->getName()] = $this->container->get('session')->get('locale');
					break;
				default:
					$values[$fieldType->getName()] = $fieldType->getSQLValue();
			}
			if (in_array($field['type'], array('select', 'select_tree'))
				&& isset($field['link_type'])
				&& $field['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->container->get('request')->request->get($field['name'].'_extra'));
				$extraIds[] = $fieldType->getSQLValue();
				$linkTable = $field['link_table'];
				$linkInversed = $field['link_inversed'];
				$linkMapped = $field['link_mapped'];
			}
		}
		if ($lastId = $this->insert($values)) {
			foreach ($extraIds as $extraId) {
				$this->container->get('connection')->insert(
					$linkTable,
					array($linkInversed => $lastId, $linkMapped => $extraId)
				);
			}
			
			return $lastId;
		} else {
			return false;
		}
	}
	
	public function updateGlobals()
	{
		$entityId = $this->container->get('request')->request->get('id', true);
		$entity = $this->getItem($entityId);
		$values = array();
		foreach ($this->fields as $field) {
			$fieldType = $this->getFieldType($field, $entity);
			switch ($field['name']) {
				case 'updated':
					$values[$fieldType->getName()] = date('Y-m-d H:i:s');
					break;
				case 'gallery':
					$fieldType->getSQLValue();
					break;
				default:
					if (empty($field['readonly'])) {
						$values[$fieldType->getName()] = $fieldType->getSQLValue();
					}
			}

			if (in_array($field['type'], array('select', 'select_tree'))
				&& isset($field['link_type']) && $field['link_type'] == 'many'
				) {
				$extraIds = explode(',', $this->container->get('request')->request->get($field['name'].'_extra'));
				$extraIds[] = $fieldType->getSQLValue();
				$linkTable = $field['link_table'];
				$linkInversed = $field['link_inversed'];
				$linkMapped = $field['link_mapped'];
				$this->container->get('connection')->delete($linkTable, array($linkInversed => $entityId));
				foreach ($extraIds as $extraId) {
					$this->container->get('connection')->insert($linkTable, 
							array($linkInversed => $entityId, $linkMapped => $extraId)
					);
				}
			}
		}

		return $this->update($values, array('id' => $entityId));
	}

	function group_update()
	{
		$this->select(array('where' => 'id IN('.$this->container->get('request')->request->get('ids').')'));
		$entities = $this->getNextArrays();
		foreach ($entities as $entity) {
			$values = array();
			$entityId = $entity['id'];
			foreach ($this->fields as $field) {
				if ($field['type'] == 'gallery') {
					$fieldType = $this->getFieldType($field, $entity);
					$fieldType->getSQLValue();
				} else {
					$fieldType = $this->getFieldType($field, $entity);
					if ('checkbox' == $field['type'] && isset($field['group_update']) && true == $field['group_update']) {
						$values[$fieldType->getName()] = $fieldType->getGroupSQLValue();
					}
					if ($this->container->get('request')->request->get($fieldType->getName().$entity['id'])
						|| isset($_FILES[$fieldType->getName().$entity['id']])) {
						$values[$fieldType->getName()] = $fieldType->getGroupSQLValue(); 
					}	
				}
				
				if (($field['type'] == 'select' || $field['type'] == 'select_tree')
					&& isset($field['link_type']) && $field['link_type'] == 'many'
					) {
					$extraIds = explode(',', $this->container->get('request')->request->get($field['name'].$entityId.'_extra'));
					$linkTable = $field['link_table'];
					$linkInversed = $field['link_inversed'];
					$linkMapped = $field['link_mapped'];
					$this->container->get('connection')->delete($linkTable, array($linkInversed => $entityId));
					foreach ($extraIds as $extraId) {
						$this->container->get('connection')->insert($linkTable, array(
							$linkInversed => $entityId,
							$linkMapped => $extraId
						));
					}
				}
			}
			if ($values) {
				$this->update($values, array('id' => $entity['id']));
			}	
		}
		return true;
	}
	
	public function getSchema()
	{
		$schema = new \Doctrine\DBAL\Schema\Schema();
		$table = $schema->createTable($this->dbName());
		$column = $table->addColumn('id', 'integer', array('unsigned' => true));
		$column->setAutoincrement(true);
		foreach ($this->fields as $field) {
			$table->addColumn($field['name'], $this->getFieldType($field)->getType());
		}
		$table->setPrimaryKey(array('id'));
		return $schema;
	}

	public function create()
	{
		try {
			$queries = $this->getSchema()->toSql($this->container->get('connection')->getDatabasePlatform());
			foreach ($queries as $sql) {
				$this->container->get('connection')->query($sql);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}	
	}
	
	public function alter()
	{
		try {
			$sm = $this->container->get('connection')->getSchemaManager();
			$fromSchema = $sm->createSchema();
			$toSchema = clone $fromSchema;
			$table = $toSchema->getTable($this->dbName());
			foreach ($this->fields as $field) {
				if (in_array($field['type'], array('gallery'))) {
					continue;
				}
				try {
					$column = $table->getColumn($field['name']);
					if ($column->getType()->getName() != $this->getFieldType($field)->getType()) {
						$this->container->get('log')->addError($field['type']);
						$table->changeColumn(
							$field['name'], 
							array('Type' => \Doctrine\DBAL\Types\Type::getType($this->getFieldType($field)->getType())
						));
					}
				} catch (\Exception $e) {
					$table->addColumn($field['name'], $this->getFieldType($field)->getType());
				}
			}
			$columns = $table->getColumns();
			foreach ($columns as $column) {
				if ('id' == $column->getName()) {
					continue;
				}	
				if (!isset($this->fields[$column->getName()]))
					$table->dropColumn($column->getName());
			}

			// TODO Написать создание уникальных индексов по описанию
			// TODO Написать создание индексов по описанию

			if ($this->params['is_search']) {
				// TODO Заново написать создание индексов для поиска
			}
			
			$queries = $fromSchema->getMigrateToSql($toSchema, $this->container->get('connection')->getDatabasePlatform());
			foreach ($queries as $sql) {
				$this->container->get('log')->addError($sql);
				$this->container->get('connection')->query($sql);
			}
			
			return true;
		} catch (\Exception $e) {
			$this->container->get('log')->addError($e->getMessage());
			$this->container->get('log')->addError($e->getTraceAsString());
			
			return false;
		}	
		
	}
	
	private function drop()
	{
		return $this->container->get('connection')->query('DROP TABLE '.$this->dbName());
	}
	
	private function truncate()
	{
		return $this->container->get('connection')->query('TRUNCATE TABLE '.$this->dbName());
	}
	
	function getSearchSQL()
	{
		$filters = array();
		if (!empty($_REQUEST['search_filter_id'])) {
			$filters[] = 'id='.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $field) {
			$fieldType = $this->getFieldType($field);
			if ($filter = $fieldType->getSearchSQL()) {
				$filters[] = $filter;
			}
		}
		return implode(' AND ', $filters);
	}

	public function getSearchURL()
	{
		$filters = array();
		if (!empty($_REQUEST['search_filter_id'])) {
			$filters[] = 'search_filter_id='.intval($_REQUEST['search_filter_id']);
		}
		foreach ($this->fields as $field) {
			$fieldType = $this->getFieldType($field);
			if ($filter = $fieldType->getSearchURL()) {
				$filters[] = $filter;
			}
		}
		return implode('&', $filters);
	}
	
	public function insert($values)
	{
		if (!array_key_exists('created', $values)) {
			$values['created'] = date('Y-m-d H:i:s');
		}
		
		if (!array_key_exists('updated', $values)) {
			$values['updated'] = '0000-00-00 00:00:00';
		}
		
		if ($this->container->get('connection')->insert($this->dbName(), $values)) {
			$lastId = $this->container->get('connection')->lastInsertId();
			$this->updateNested();
			
			return $lastId;
		}
		
		return false;
	}
	
	function insertArray($entity)
	{
		$values = array();
		$entity['created'] = date('Y-m-d H:i:s');
		$entity['updated'] = '0000-00-00 00:00:00';
		foreach ($this->fields as $field) {
			foreach ($entity as $fieldName => $fieldValue) {
				if (empty($fieldValue)) {
					continue;
				}
				$fieldType = $this->getFieldType($field);
				if ($fieldType->getName() == $fieldName) {
					if ($field['type'] == 'template') {
						$fileInfo = pathinfo($fieldValue);
						$dest = $this->container->get('util')->getNextFileName($fileInfo['basename'], $fileInfo['dirname']);
						@copy(PRJ_DIR.$fieldValue, PRJ_DIR.$fileInfo['dirname'].'/'.$dest);
						$values[$fieldType->getName()] = $fileInfo['dirname'].'/'.$dest;
					} elseif ($field['type'] == 'file') {
						$fileInfo = pathinfo($fieldValue);
						$values[$fieldType->getName()] = $this->container->get('filestorage')->save($fileInfo['basename'], PRJ_DIR.UPLOAD_REF.$fieldValue);
					} elseif ($field['type'] == 'image') {
						$fileInfo = pathinfo($fieldValue);
						$this->container->get('imagestorage')->setOptions(array('sizes' => $fieldType->getParam('sizes')));
						$values[$fieldType->getName()] = $this->container->get('imagestorage')->save($fileInfo['basename'], PRJ_DIR.UPLOAD_REF.$fieldValue);
					} else {
						$values[$fieldType->getName()] = $fieldValue;
					}
					break;
				}
			}
		}
		$lastId = $this->insert($values);
		if ($this->params['multifile']) {
			$sql = "SELECT * FROM system_files WHERE entity_id= :id AND table_name= :table";
			$stmt = $this->container->get('connection')->prepare($sql);
			$stmt->bindValue('id', $entity['id']);
			$stmt->bindValue('table', $this->dbName());
			$stmt->execute();
			$files = $stmt->fetchAll();
			foreach ($files as $file) {
				$filepath = $file['file'];
				$dest = $this->container->get('util')->getNextFileName($filepath);
				@copy(PRJ_DIR.$filepath,PRJ_DIR.$dest);
				unset($file['id']);
				$file['file'] 		= $dest;
				$file['created'] 	= date("Y-m-d H:i:s");
				$file['entity_id'] = $lastId;
				$this->container->get('connection')->insert('system_files', $file);
			}
		}
		return true;
	}

	public function update($values, $criteria)
	{
		$ret = $this->container->get('connection')->update($this->dbName(), $values, $criteria);
		$this->updateNested();
		return $ret;
	}
	
	private function updateNested($parentId = 0, $level = 1, $left_key = 0)
	{
		if (empty($this->params['is_view'])) {
			return;
		}
		$table = $this->dbname;
		$sql = "SELECT id FROM $table WHERE parent_id= :id ".($this->params['is_lang'] ? 'AND locale= :locale' : '')." ORDER BY sort";
		$stmt = $this->container->get('connection')->prepare($sql);
		$stmt->bindValue('id', $parentId);
		$stmt->bindValue('locale', $this->container->get('session')->get('locale'));
		$stmt->execute();
		$items = $stmt->fetchAll();
		if ($items) {
			foreach ($items as $item) {
				$left_key++;
				$right_key = $this->updateNested($item['id'], $level+1, $left_key);
				$this->container->get('connection')->update($table,
					array(
						'left_key' => $left_key, 
						'right_key' => $right_key, 
						'level' => $level,
					),	
					array('id' => $item['id'])
				);
				$left_key = $right_key;
			}
		} else {
			$right_key = $left_key;
		}
		return ++$right_key;
	}
	
	public function delete($criteria)
	{
		return $this->container->get('connection')->query('DELETE FROM '.$this->dbName().' WHERE '.$criteria);
	}
	
	public function select($options = array())
	{
		try {
			if ($this->params['is_lang']) {
				$locale = $this->container->get('session')->get('locale');
				$options['where'] = empty($options['where']) ? 
						"locale='".$locale."'" 
						: 
						$options['where']." AND locale='".$locale."'";
			}
			if (empty($options['select'])) {
				$options['select'] = implode(',', $this->getFieldList());
			}
			if (empty($options['from'])) {
				$options['from'] = $this->dbName();
			}
			if (empty($options['where'])) {
				$options['where'] = '1=1';
			}
			if (empty($options['order_by'])) {
				$options['order_by'] = $this->params['order_by'] ?: 'id';
			}
			if (empty($options['limit'])) {
				$options['limit'] = '10000';
			}
			$sql = 'SELECT '.$options['select'].
				' FROM '.$options['from'].
				' WHERE '.$options['where'].
				' ORDER BY '.$options['order_by'].
				' LIMIT '.$options['limit'];
			$this->stmt = $this->container->get('connection')->prepare($sql);
			$this->stmt->execute();
			
			return true;	
		} catch (\Exception $e) {
			
			return false;
		}	
	}
	
	public function getNextArray($detailed = true)
	{
		$entity = $this->stmt->fetch();
		if ($detailed && $entity) {
			foreach ($this->fields as $field) {
				$entity[$this->getFieldType($field, $entity)->getName().'_value'] = $this->getFieldType($field, $entity)->getNativeValue();
			}
		}
		return $entity;
	}
	
	public function getNextArrays($detailed = true)
	{
		$items = array();
		while ($item = $this->getNextArray($detailed)) {
			if (isset($item['id'])) {
				$items[$item['id']] = $item;
			} else {
				$items[] = $item;
			}
		}	
		
		return $items;
	}
	
	public function getItem($criteria, $sort = '', $select = '', $detailed = true)
	{
		$criteria = is_numeric($criteria) ? 'id='.$criteria : $criteria;
		$this->select(array('where' => $criteria, 'select' => $select, 'order_by' => $sort));
		return $this->getNextArray($detailed);    
	}
	
	public function getPrev($id, $parent = 'parent_id')
	{
		$ret = array();
		$node = $this->getItem($id, '', '', false);
		if ($node) {
			$ret = $this->getPrev($node[$parent], $parent);
			$ret[] = $node;
		}
		
		return $ret;
	}

	function count($criteria = '')
	{
		try {
			$this->select(array(
				'select' => 'COUNT(id) as quantity', 
				'where' => $criteria
			));
			$quantity = $this->stmt->fetchColumn();
		} catch (\Exception $e) {
			$quantity = 0;
		}
		
		return $quantity ? (int)$quantity : 0;
	}

	private function setTableFields()
	{
		try {
			if ($this->params['is_system']) {
				$this->readConfig();
			} else {
				$this->readDBConfig();
			}
		} catch (\Exception $e) {
			echo $this->container->get('util')->error($e->getMessage());
		}

		if ($this->params['is_sort']) {
			$this->fields['sort'] = array(
				'name' => 'sort',
				'title' => 'Сорт.',
				'type' => 'number',
				'width' => '10%',
				'defvalue' => '500',
				'group_update' => true
			);
		}
		if ($this->params['is_publish']) {
			$this->fields['publish'] = array (
				'name' => 'publish',
				'title' => 'Акт.',
				'type' => 'checkbox',
				'search' => true,
				'group_update'  => true,
				'width' => '1%'
			);
		}
		if ($this->params['is_lang']) {
			$this->fields['locale'] = array (
				'name'  => 'locale',
				'title' => 'Локаль',
				'type'  => 'string',
				'readonly' => true
			);
		}
		$this->fields['created'] = array (
			'name'  => 'created',
			'title' => 'Дата создания',
			'type'  => 'datetime',
			'readonly' => true
		);
		$this->fields['updated'] = array (
			'name'  => 'updated',
			'title' => 'Дата изменения',
			'type'  => 'datetime',
			'readonly' => true
		);
		foreach ($this->fields as &$field) {
			$field['table'] = $this->dbName();
		}
	}
	
	public static function fillValue(&$value, $key)
	{
		$value = "'".$value."'";
	}
	
}
