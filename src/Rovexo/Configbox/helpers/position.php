<?php
class ConfigboxPositionHelper {

	/**
	 * Deprecated method, used to be used in pre 2.6.0-RC8 order record view
	 * @param ConfigboxOrderData $record
	 * @param ConfigboxOrderPositionData $position
	 * @deprecated Use ConfigboxPositionHelper::getPositionHtml instead
	 */
	static function getPositionPopup($record, $position) {
		self::getPositionHtml($record, $position, 'popup');
	}

	/**
	 * @param ConfigboxOrderData $order
	 * @param ConfigboxOrderPositionData $position
	 * @param string $showIn
	 * @param bool $showSkus
	 * @param bool $inAdmin
	 * @return string
	 */
	static function getPositionHtml($order, $position, $showIn = 'popup', $showSkus = NULL, $inAdmin = false) {

		$view = KenedoView::getView('ConfigboxViewPosition');
		$view->setOrder($order);
		$view->setPositionId($position->id);
		$view->setDisplayPurpose($showIn);
		$view->setInAdmin($inAdmin);
		return $view->getHtml();

	}

}
