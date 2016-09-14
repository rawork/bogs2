<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class CopyController extends AdminController
{
	public function copyAction($state, $module, $entity, $id, $quantity)
	{
		set_time_limit(0);
		$this->get('session')->getFlashBag()->add(
			'admin.message',
			$this->get('container')->copyItem($module.'_'.$entity, $id, $quantity) ? 'Скопировано' : 'Ошибка копирования'
		);

		return $this->redirect($this->generateUrl(
			'admin_entity_index',
			array('state' => $state, 'module' => $module, 'entity' => $entity)
		));
	}

	public function dialogAction($id)
	{
		$response = new JsonResponse();
		$response->setData(array(
			'title' => 'Копирование элемента',
			'button' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">Закрыть</a><a class="btn btn-success" onclick="startCopy(\'/'.$id.'/copy\')">Копировать</a>',
			'content' => '
<div class="control-group" id="copyInput">
  <label class="control-label" for="inputError">Количество новых (1-10)</label>
  <div class="controls">
    <input type="text" name="quantity" id="copyQuantity" value="1">
    <span class="help-inline" id="copyHelp"></span>
  </div>
</div>'
		));

		return $response;
	}

} 