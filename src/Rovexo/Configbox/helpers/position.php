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
	 * @param ConfigboxOrderData $orderRecord
	 * @param ConfigboxOrderPositionData $position
	 * @param string $showIn
	 * @param bool $showSkus
	 * @param bool $inAdmin
	 * @return string
	 */
	static function getPositionHtml($orderRecord, $position, $showIn = 'popup', $showSkus = NULL, $inAdmin = false) {

		if ($showSkus === NULL) {
			$showSkus = CbSettings::getInstance()->get('sku_in_order_record');
		}

		// Get the full URL to the position image
		if ($position->product_image) {
			$positionImageSrc = CONFIGBOX_URL_POSITION_IMAGES.'/'.$position->product_image;
		}
		else {
			$positionImageSrc = '';
		}

		// Get the position image dimensions
		$width = 0;
		$height = 0;

		// Sneak in file upload's a tag
		if (!empty($position->configuration)) {
			foreach ($position->configuration as $selection) {
				if ($selection->element_type == 'upload') {
					if ($selection->value) {
						$data = json_decode($selection->value, true);
						$selection->output_value = '<a href="'.$data['url'].'" download>'.$selection->output_value.'</a>';
					}
				}
			}
		}

		if ($showIn == 'quotation') {
			$maxWidth = 1000;
			$maxHeight = 300;
		}
		else {
			$maxWidth = 500;
			$maxHeight = 500;
		}

		if ($position->product_image) {

			$filePath = CONFIGBOX_DIR_POSITION_IMAGES.DS.$position->product_image;
			$dimensions = getimagesize($filePath);
			$width = $dimensions[0];
			$height = $dimensions[1];

			if ($width > $maxWidth) {
				$ratio = $height / $width;
				$width = $maxWidth;
				$height = intval($width * $ratio);
			}

			if ($height > $maxHeight) {
				$ratio = $width / $height;
				$height = $maxHeight;
				$width = intval($height * $ratio);
			}

		}

		$view = KenedoView::getView('ConfigboxViewPosition');
		$view->assignRef('record', $orderRecord);
		$view->assignRef('position', $position);
		$view->assign('positionImageSrc', $positionImageSrc);
		$view->assign('positionImageWidth', $width);
		$view->assign('positionImageHeight', $height);
		$view->assign('inAdmin', $inAdmin);
		$view->assign('showIn', $showIn);
		$view->assign('showSkus', $showSkus);
		return $view->getViewOutput();
	}

}
