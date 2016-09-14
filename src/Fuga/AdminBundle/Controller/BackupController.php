<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BackupController extends AdminController
{
	public function createAction() {
		$my_time = time();
		$my_key = $this->get('util')->genKey(8);

		$filename = date('YmdHi',$my_time).'_'.$my_key.'.tar.gz';
		$filename_sql = date('YmdHi',$my_time).'_'.$my_key.'.sql';
		$filename_sql2 = date('YmdHi',$my_time).'_'.$my_key.'_after_connect.sql';
		$this->get('fs')->dumpFile(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql2, "/*!41000 SET NAMES 'utf8' */;");
		set_time_limit(0);
		$this->get('container')->backupDB(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql);
		$cwd = getcwd();
		chdir(PRJ_DIR.'/');
		system('tar -czf '.BACKUP_DIR.DIRECTORY_SEPARATOR.$filename.' --exclude=*.lock --exclude=autoload.php --exclude=*.tar.gz --exclude=./vendor/* --exclude=./.git --exclude=./.idea --exclude=./app/cache/smarty/* --exclude=./app/cache/twig/* ./');
		chdir($cwd);
		if ($this->get('fs')->exists(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename)) {
			$this->get('fs')->chmod(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename, 0664);
		}
		$text = '<strong>Архив создан</strong><br>
		Размер архива: '.$this->get('filestorage')->size(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename);
		@unlink(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql);
		@unlink(BACKUP_DIR.DIRECTORY_SEPARATOR.$filename_sql2);
		$this->get('session')->getFlashBag()->add('archive.report', $text);

		$response = new JsonResponse();
		$response->setData(array('content' => $text));
		$response->prepare($this->get('request'));

		return $response;
	}

	public function getAction($file)
	{
		$filepath = BACKUP_DIR.'/'.$file;

		if (!$this->get('fs')->exists($filepath)) {
			throw $this->createNotFoundException('File not found');
		}

		$response = new BinaryFileResponse($filepath);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$file
		);
		$response->prepare($this->get('request'));

		return $response;
	}

	public function deleteAction($file)
	{
		$this->get('fs')->remove(BACKUP_DIR.'/'.$file);

		return $this->redirect($this->generateUrl('admin_service'));
	}

	public function restoreAction(Request $request) {
		$filepath = PRJ_DIR . '/app/restore.php';
		$filename = 'restore.php';

		if (!$this->get('fs')->exists($filepath)) {
			throw $this->createNotFoundException('File not found');
		}

		$response = new BinaryFileResponse($filepath);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$filename
		);
		$response->prepare($request);

		return $response;
	}
} 