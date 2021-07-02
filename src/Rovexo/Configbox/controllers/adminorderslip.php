<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminorderslip extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdminorderslip
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdminorderslip');
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	function display() {
		
		$orderId = KRequest::getInt('order_id');
		
		$slipView = KenedoView::getView('ConfigboxViewAdminorderslip');
		$slipView->orderId = $orderId;
		
		// Generate the PDF
		ob_start();
		$slipView->display();
		$html = ob_get_clean();

		$filename = 'manufacturing-slip-'.$orderId;

		$domPdf = ConfigboxDomPdfHelper::getDomPdfObject();
		$domPdf->loadHtml($html, 'UTF-8');
		$domPdf->render();
		$domPdf->stream($filename, array('Attachment'=>1));
		die();
		
	}
	
}