<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Gregwar\Captcha\CaptchaBuilder;

class CommonController extends Controller {

	public function dinamicAction($node, $action, $params = array())
	{
		$node = $this->getManager('Fuga:Common:Page')->getNodeByName($node);

		if ($node['module_id']) {
			try {
				return $this->get('container')->callAction(
					$node['module_id_value']['item']['path'].':'.$action,
					$params
				);
			} catch (NotFoundHttpException $e) {
				$this->get('log')->addError($e->getMessage());
				$this->get('log')->addError($e->getTraceAsString());
				throw $this->createNotFoundException($e->getMessage());
			} catch (\Exception $e) {
				$this->get('log')->addError($e->getMessage());
				$this->get('log')->addError($e->getTraceAsString());
				throw new \Exception($e->getMessage());
			}
		}

		return '';
	}

	public function indexAction($node = null, $action = null, $options = array())
	{
		$node = $node ?: '/';
		$action = $action ?: 'index';

		if ($this->get('request')->isXmlHttpRequest()) {
			$response = new JsonResponse();
			$response->setData($this->dinamicAction($node, $action, $options));

			return $response;
		}

		$staticContent = null;

		$nodeItem = $this->getManager('Fuga:Common:Page')->getNodeByName($node);
		if (!$nodeItem) {
			throw $this->createNotFoundException('Несуществующая страница');
		}

		if ($action == 'index') {
			$staticContent = $this->render('page/static.html.twig', array('node' => $nodeItem));
		}

		$links = $this->getManager('Fuga:Common:Page')->getNodes('/', true);

		foreach ($links as &$link) {
			$link['children'] = $this->getManager('Fuga:Common:Page')->getNodes($link['name'], true);
		}
		unset($link);

		$params = array(
			'h1'        => $nodeItem['title'],
			'title'     => $this->getManager('Fuga:Common:Meta')->getTitle() ?: strip_tags($nodeItem['title']),
			'meta'      => $this->getManager('Fuga:Common:Meta')->getMeta(),
			'links'     => $links,
			'action'    => $action,
			'options'   => $options,
			'curnode'   => $nodeItem,
			'curuser'   => $this->get('security')->getCurrentUser(),
			'locale'    => $this->get('session')->get('locale'),
			'asset'		=> 'dev' == PRJ_ENV ? date('YmdHis') : 'prodaction'
		);
		$this->get('templating')->assign($params);


		$res = $this->dinamicAction($node, $action, $options);
		if (is_object($res) && $res instanceof Response) {
			return $res;
		} elseif (is_array($res)) {
			$response = new JsonResponse();
			$response->setData($res);
			return $response;
		}

		$this->get('templating')->assign(array('maincontent' => $staticContent.$res));

		$response = new Response();
		$response->setContent($this->render(
			$this->getManager('Fuga:Common:Template')->getByNode($nodeItem['name']),
			$this->get('container')->getVars()
		));

		return $response;
	}
	
	public function blockAction($name)
	{
		$item = $this->get('container')->getItem('page_block',"name='{$name}' AND publish=1");
		
		return $item ? $item['content'] : '';
	}
	
	public function breadcrumbAction($nodes)
	{
		return $this->render('common/breadcrumb.html.twig', compact('nodes'));
	}
	
	private function getMapList($uri = 0)
	{
		$nodes = array();
		$items = $this->getManager('Fuga:Common:Page')->getNodes($uri);
		$block = strval($uri) == '0' ? '' :  '_sub';
		if (count($items)) {
			foreach ($items as $node) {
				$node['sub'] = '';
				if ($node['module_id']) {
					$controller = $this->get('container')->createController($node['module_id_path']);
					$node['sub'] = $controller->mapAction();
				}
				$node['sub'] .= $this->getMapList($node['id']);
				$nodes[] = $node;
			}
		}
		return $this->render('common/map.html.twig', compact('nodes', 'block'));
	}

	public function mapAction()
	{
		return $this->getMapList();
	}
	
	public function formAction($name)
	{
		return $this->getManager('Fuga:Common:Form')->getForm($name);
	}

	public function captchaAction()
	{
		$captcha = new CaptchaBuilder();
		$captcha->setBackgroundColor(255, 255, 255);
		$captcha->build(200, 50);

		$this->get('session')->set('captcha.phrase', md5($captcha->getPhrase().CAPTCHA_KEY));
		$response = new Response();
		$response->setContent($captcha->output());
		$response->headers->set('Content-Type', 'image/jpeg');

		return $response;
	}

	// TODO fileupload -> filestorage
	public function fileuploadAction()
	{
		$error = array();
		$msg = array();
		$fileElementName = 'fileToUpload';
		$date = new \Datetime();
		$upload_ref = UPLOAD_REF.$date->format('/Y/m/d/');
		@mkdir(PRJ_DIR.$upload_ref, 0755, true);
		$upload_path = PRJ_DIR.$upload_ref;
		$files_count = isset($_FILES[$fileElementName]["name"]) ? count($_FILES[$fileElementName]["name"]) : 0;
		if (!isset($_FILES[$fileElementName]["name"])) {
			echo 'Не выбраны файлы';
			exit;
		}
		foreach ($_FILES[$fileElementName]["name"] as $i => $file) {
			if(!empty($_FILES[$fileElementName]['error'][$i])) {
				switch($_FILES[$fileElementName]['error'][$i]) {
					case '1':
						$error[] = 'Размер загруженного файла превышает размер установленный параметром upload_max_filesize  в php.ini ';
						break;
					case '2':
						$error[] = 'Размер загруженного файла превышает размер установленный параметром MAX_FILE_SIZE в HTML форме. ';
						break;
					case '3':
						$error[] = 'Загружена только часть файла ';
						break;
					case '4':
						$error[] = 'Файл не был загружен (Пользователь в форме указал неверный путь к файлу). ';
						break;
					case '6':
						$error[] = 'Неверная временная дирректория';
						break;
					case '7':
						$error[] = 'Ошибка записи файла на диск';
						break;
					case '8':
						$error[] = 'Загрузка файла прервана';
						break;
					case '999':
					default:
						$error[] = 'Неизвестный код ошибки';
				}
			} elseif(empty($_FILES[$fileElementName]['tmp_name'][$i]) || $_FILES[$fileElementName]['tmp_name'][$i] == 'none') {
				$error[] = 'Файл не загружен...';
			} else {
				$msg[] = " " . $_FILES[$fileElementName]['name'][$i];
				$file  = $this->get('util')->getNextFileName($upload_ref.$_FILES[$fileElementName]['name'][$i]);
				move_uploaded_file($_FILES[$fileElementName]['tmp_name'][$i], PRJ_DIR.$file);
				$name = $_FILES[$fileElementName]['name'][$i];
				$filesize = @filesize($upload_path.$_FILES[$fileElementName]['name'][$i]);
				$mimetype = $_FILES[$fileElementName]['type'][$i];
				$width = 0;
				$height = 0;
				if ($fileInfo = @GetImageSize(PRJ_DIR.$file)) {
					$width = $fileInfo[0];
					$height = $fileInfo[1];
				}
				$this->get('connection')->insert('system_files', array(
					'name' => $name,
					'mimetype' => $mimetype,
					'file' => $file,
					'width' => $width,
					'height' => $height,
					'filesize' => $filesize,
					'table_name' => $this->get('request')->request->get('table_name'),
					'entity_id' => $this->get('request')->request->get('entity_id', true, 0),
					'created' => date('Y-m-d H:i:s')
				));
				$sizes = $this->get('request')->request->get('sizes');
				if ($sizes) {
					$this->get('imagestorage')->afterSave($file, array('sizes' => $sizes));
				}
			}
		}

		$response = new Response();
		$response->setContent(count($error) ? implode('<br>', $error) : "Добавлены файлы: ".implode(', ', $msg));

		return $response;
	}
	
}