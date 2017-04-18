<?php

namespace Fuga\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class BogsbootsController extends AdminController
{
	protected $articuls = array();

	public function indexAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			if (!empty($_FILES['stock']) && !empty($_FILES['stock']['name'])) {
				$row = 1;
				if (($handle = fopen($_FILES['stock']['tmp_name'], "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
						$num = count($data);
						if ($num < 2) {
							continue;
						}
						$row++;

						$articulParts = explode('-', $data[1]);
						$sizeParts = explode('/', $data[2]);

						if (isset($articulParts[2])){
							unset($articulParts[2]);
						}

						$articul = implode('-', $articulParts);

						$product = $this->get('container')->getItem('catalog_product', 'articul="'.$articul.'"');

						if (!$product) {
							continue;
						}

						$sku = $this->get('container')->getItem(
							'catalog_sku',
							'product_id='.$product['id'].' AND size='.$sizeParts[0]
						);

						if (!$sku) {
							if (isset($sizeParts[1])) {
								$sku = $this->get('container')->getItem(
									'catalog_sku',
									'product_id='.$product['id'].' AND size='.$sizeParts[1]
								);

								if (!$sku) {
									continue;
								}
							}
						}

						$this->get('container')->updateItem(
							'catalog_sku',
							array(
								'quantity3' => intval($data[4]),
								'updated' => date('Y-m-d H:i:s'),
							),
							array('id' => $sku['id'])
						);

					}

					fclose($handle);
				}
			}

			$this->get('session')->set('warning', 'Остатки по складу bogsboots.ru импортированы');

			return $this->reload();
		}


		$state = 'content';
		$module = 'catalog';
		$message = $this->flash('warning');

		return new Response($this->render('admin/import/bogsboots/index.html.twig', compact('state', 'module', 'message')));
	}

}