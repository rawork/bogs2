<?php

namespace Fuga\AdminBundle\Controller;


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\DateTime;

class DocController extends AdminController
{
	public function billAction($id)
	{
		$order = $this->get('container')->getItem('basket_order', $id);
		if (!$order) {
			throw $this->createNotFoundException('Не найден заказ');
		}

		$file = 'bill_'.$order['id'].'.xlsx';


		$filepath = RES_DIR.'/bills/'.$file;

		$this->get('fs')->copy(RES_DIR.'/bills/bill_template.xlsx', $filepath, true);

		$objPHPExcel = \PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objPHPExcel->load($filepath);
		$objPHPExcel->setActiveSheetIndex(0);

		$i = 21;
		$sum = 0;
		$num = 1;

		$products = json_decode($order['detail_json'], true);

		$created = new \DateTime($order['created']);

		$gdImage = imagecreatefromjpeg(RES_DIR.'/bills/logo.jpg');

		$objDrawing = new \PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logotype');
		$objDrawing->setImageResource($gdImage);
		$objDrawing->setRenderingFunction(\PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(\PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setWidth(200);
		$objDrawing->setHeight(60);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		$objDrawing->setCoordinates('A1');


		$objPHPExcel->getActiveSheet()->setCellValue('F2', date('d.m.Y').'г.');

		$objPHPExcel->getActiveSheet()->setCellValue('A6', 'НАКЛАДНАЯ № ТН-'.sprintf('%06d', $order['id']));

		$objPHPExcel->getActiveSheet()->getStyle('A8:A11')->getFont()->setSize(14);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Получатель: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['name'].' '.$order['lastname'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A8', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Контактный телефон: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['phone'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A9', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Заказ: № ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['id'].' от '.$created->format('d.m.Y'))->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A10', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Адрес доставки: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['address'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A11', $objRichText);

		$objPHPExcel->getActiveSheet()->getStyle('A8:A11')->getFont()->setSize(14);

		foreach ($products as $product) {
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getBorders()
				->getAllBorders()
				->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->getStyle('B'.$i)
				->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getAlignment()
				->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);

			$objPHPExcel->getActiveSheet()->mergeCells('B'.$i.':C'.$i);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $num);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $product['name'].', размер '.$product['size']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $product['price']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $product['amount']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $product['price']*$product['amount']);

			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$i++;
			$num++;
			$sum += $product['price']*$product['amount'];
		}

		if (intval($order['delivery_cost']) > 0) {
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getBorders()
				->getAllBorders()
				->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->mergeCells('B'.$i.':C'.$i);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $num);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Доставка');
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $order['delivery_cost']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 1);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $order['delivery_cost']);

			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$i++;
			$sum += $order['delivery_cost'];
		}

		$objPHPExcel->getActiveSheet()
			->getStyle('E'.$i.':F'.$i)
			->getBorders()
			->getAllBorders()
			->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Итого:');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $sum);
		$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
		$i += 4;
		$objPHPExcel->getActiveSheet()
			->getStyle('B'.$i)
			->getBorders()
			->getBottom()
			->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		$i++;
		$objPHPExcel->getActiveSheet()
			->getStyle('B'.$i)
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'М.П.');


		$objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($filepath);

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

}