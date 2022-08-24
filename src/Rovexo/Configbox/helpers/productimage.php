<?php
class ConfigboxProductImageHelper {
	
	protected static $productImages = array();

	/**
	 * usort compare function for getVisualizationData
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	static function compareStackingOrder($a, $b) {
		if ($a->visualization_stacking == $b->visualization_stacking) {
			return 0;
		}
		return ($a->visualization_stacking < $b->visualization_stacking) ? -1 : 1;
	}

	/**
	 * @param int $cartPositionId
	 * @return object[]
	 */
	public static function getVisualizationData($cartPositionId) {

		// Get the selections
		$selections = ConfigboxConfiguration::getInstance($cartPositionId)->getSelections();

		$data = array();
		foreach ($selections as $questionId => $selection) {
			$question = ConfigboxQuestion::getQuestion($questionId);
			if (count($question->answers)) {
				if (!empty($question->answers[$selection]->visualization_image)) {
					$data[$questionId] = new stdClass();
					$data[$questionId]->element_id = $questionId;
					$data[$questionId]->option_id = $question->answers[$selection]->option_id;
					$data[$questionId]->visualization_image = $question->answers[$selection]->visualization_image;
					$data[$questionId]->visualization_stacking = $question->answers[$selection]->visualization_stacking;
				}

			}

		}
		
		if (count($data)) {
			usort($data, array('ConfigboxProductImageHelper', 'compareStackingOrder'));
		}

		return $data;

	}

	/**
	 * @param ConfigboxCartPositionData $cartPositionDetails
	 * @return string
	 */
	static function getProductImageHtml($cartPositionDetails) {

		$productId = ConfigboxConfiguration::getInstance($cartPositionDetails->id)->getProductId();
		$product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($productId);

		if ($product->visualization_type == 'composite') {

			$images = self::getVisualizationData($cartPositionDetails->id);

			ob_start();

			if (self::hasProductImage($cartPositionDetails->id)) { ?>

				<div class="visualization-frame">
					<?php if (!empty($cartPositionDetails->productData->baseimage)) { ?>
						<div class="base-image">
							<img src="<?php echo KenedoPlatform::p()->getUrlDataStore().'/public/vis_product_images/'.$cartPositionDetails->productData->baseimage;?>" alt="" />
						</div>
					<?php } ?>

					<?php foreach ($images as $image) { ?>
						<div class="visualization-image">
							<img src="<?php echo KenedoPlatform::p()->getUrlDataStore().'/public/vis_answer_images/'.$image->visualization_image;?>" alt="" />
						</div>
					<?php } ?>
				</div>
				<?php
			}

			$output = ob_get_clean();

			return $output;

		}
		else {
			ob_start();
			?>
			<img src="<?php echo $product->prod_image_href;?>" alt="<?php echo hsc($product->title);?>" />
			<?php
			$output = ob_get_clean();

			return $output;
		}

	}

	/**
	 * @param ConfigboxCartPositionData $cartPositionDetails
	 * @param string $destinationPath Path of the image to create
	 * @return bool True on success, false in case there is no image data to merge
	 * @throws Exception in case of problems with images or merging
	 */
	static function createMergedVisualizationImage($cartPositionDetails, $destinationPath) {

		if (!is_writable(dirname($destinationPath))) {
			KLog::log('Cannot write visualization image because of missing write permission. Directory was '.dirname($destinationPath).'.', 'error');
			throw new Exception('Cannot write visualization image to directory due to missing write permissions.');
		}

		$imagePaths = [];

		if (!empty($cartPositionDetails->productData->baseimage)) {
			$path = KenedoPlatform::p()->getDirDataStore() . '/public/vis_product_images/' . $cartPositionDetails->productData->baseimage;
			if (is_file($path)) {
				$imagePaths[] = $path;
			}
			else {
				KLog::log('Product base image in "'.$path.'" is missing. Skipping it for visualisation image merge.', 'warning');
			}
		}

		$answerImages = self::getVisualizationData($cartPositionDetails->id);

		foreach ($answerImages as $answerImage) {
			$path = KenedoPlatform::p()->getDirDataStore().'/public/vis_answer_images/'.$answerImage->visualization_image;
			if (is_file($path)) {
				$imagePaths[] = $path;
			}
			else {
				KLog::log('Visualization image in "'.$path.'" is missing. Skipping it for visualisation image merge.', 'warning');
			}
		}

		if (count($imagePaths) == 0) {
			return false;
		}

		$maxWidth = 0;
		$maxHeight = 0;

		foreach ($imagePaths as $imagePath) {
			$dims = KenedoFileHelper::getImageDimensions($imagePath);
			if ($dims['width'] > $maxWidth) {
				$maxWidth = $dims['width'];
			}
			if ($dims['height'] > $maxHeight) {
				$maxHeight = $dims['height'];
			}
		}

		// Create an empty base image
		$base = imagecreatetruecolor($maxWidth, $maxHeight);
		imagesavealpha($base, true);
		$color = imagecolorallocatealpha($base, 0, 0, 0, 127);
		imagefill($base, 0, 0, $color);

		foreach ($imagePaths as $imagePath) {

			$ext = KenedoFileHelper::getExtension($imagePath);

			if ($ext == 'png') {
				$visImage = imagecreatefrompng($imagePath);
			}
			elseif ($ext == 'jpg' or $ext == 'jpeg') {
				$visImage = imagecreatefromjpeg($imagePath);
			}
			else {
				KLog::log('Visualization image in "'.$imagePath.'" is neither a png, jpg or jpeg image. Skipping it for visualisation image merge.', 'warning');
				continue;
			}

			imagealphablending($visImage, true);
			imagesavealpha($visImage, true);

			self::mergeImages($base, $visImage, 0, 0, 0, 0, $maxWidth, $maxHeight);
			imagedestroy($visImage);

		}

		$success = imagepng($base, $destinationPath);

		if ($success === false) {
			KLog::log('Could not create visualization image.', 'error');
			throw new Exception('Could not create visualization image');
		}
		else {
			return true;
		}

	}

	/**
	 * @param ConfigboxCartPositionData $cartPositionDetails
	 * @param string $destination full filesystem path to where it should be saved
	 * @return bool|string|NULL false on failure, NULL if no product image, path to image on success
	 * @depecated Use createMergedVisualizationImage instead
	 */
	static function getMergedProductImage($cartPositionDetails, $destination = '') {

		if (empty($destination)) {
			$destinationPath = KenedoPlatform::p()->getTmpPath().'/'.uniqid().'.png';
		}
		elseif (is_dir($destination)) {
			$destinationPath = $destination.'/'.uniqid().'.png';
		}
		else {
			$destinationPath = $destination;
		}

		try {
			$success = self::createMergedVisualizationImage($cartPositionDetails, $destinationPath);
		}
		catch (Exception $e) {
			return false;
		}

		if ($success === false) {
			return null;
		}
		else {
			return $destinationPath;
		}

	}

	/**
	 * Tells if product in the given cart position uses visualization
	 * @param int $cartPositionId
	 * @return bool
	 */
	static function hasProductImage($cartPositionId) {
		$productId = ConfigboxConfiguration::getInstance($cartPositionId)->getProductId();
		$product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($productId);
		return ($product->visualization_type == 'composite');
	}

	/**
	 * Gives you an array of objects with all visualization images and info practical for display
	 * @param int $cartPositionId
	 * @return object[]
	 */
	static function getVisualizationImageSlots($cartPositionId) {
		
		$configuration = ConfigboxConfiguration::getInstance($cartPositionId);
		$productId = $configuration->getProductId();

		if (empty($productId)) return array();
	
		$db = KenedoPlatform::getDb();

		$query = "
		SELECT 
			e.id AS question_id, 
			e.behavior_on_activation, 
			c.id as page_id, 
			xref.id AS answer_id, 
			xref.visualization_image, 
			xref.default AS answer_default,
			xref.visualization_stacking
			
		FROM `#__configbox_elements` AS e
		
		LEFT JOIN `#__configbox_xref_element_option` AS xref ON e.id = xref.element_id
		LEFT JOIN `#__configbox_pages` AS c ON c.id = e.page_id
		LEFT JOIN `#__configbox_products` AS p ON p.id = c.product_id
			
		WHERE
			
		p.id = ".intval($productId)." AND p.published = '1' AND c.published = '1' AND xref.visualization_image != ''
			
		ORDER BY visualization_stacking";

		$db->setQuery($query);
		$slots = $db->loadObjectList();
	
		foreach ($slots as $slot) {
				
			$slot->type = ($slot->answer_id) ? 'answer-image':'question-image';
			$slot->css_classes = 'visualization-image';
			$slot->css_classes .= ' image-question-id-'.$slot->question_id;
			$slot->css_classes .= ' image-type-'.$slot->type;
				
				
			if ($slot->type == 'answer-image') {
	
				$slot->css_id = 'image-answer-id-'.$slot->answer_id;
				$slot->css_classes .= ' image-answer-id-'.$slot->answer_id;
	
				$slot->visualization_image = KenedoPlatform::p()->getUrlDataStore().'/public/vis_answer_images/'. $slot->visualization_image;
	
				if ($configuration->getSelection($slot->question_id) == $slot->answer_id) {
					$slot->selected = true;
				}
				else {
					$slot->selected = false;
				}
	
			}

		}
	
		return $slots;
	}

	/**
	 * merge two true colour images with variable opacity while maintaining alpha
	 * transparency of both images.
	 *
	 * @param  resource $dst  Destination image link resource
	 * @param  resource $src  Source image link resource
	 * @param  int      $dstX x-coordinate of destination point
	 * @param  int      $dstY y-coordinate of destination point
	 * @param  int      $srcX x-coordinate of source point
	 * @param  int      $srcY y-coordinate of source point
	 * @param  int      $w    Source width
	 * @param  int      $h    Source height
	 * @param  int      $pct  Opacity of source image (0-100)
	 **/
	static function mergeImages($dst, $src, $dstX, $dstY, $srcX, $srcY, $w, $h, $pct = 100) {
		$pct /= 100;
	
		/* make sure opacity level is within range before going any further */
		$pct  = max(min(1, $pct), 0);
	
		if ($pct == 0) {
			/* 0% opacity? then we have nothing to do */
			return;
		}
	
		/* work out if we need to bother correcting for opacity */
		if ($pct < 1) {
			/* we need a copy of the original to work from, only copy the cropped */
			/* area of src                                                        */
			$srccopy  = imagecreatetruecolor($w, $h);
	
			/* attempt to maintain alpha levels, alpha blending must be *off* */
			imagealphablending($srccopy, false);
			imagesavealpha($srccopy, true);
	
			imagecopyresized($srccopy, $src, 0, 0, $srcX, $srcY, $w, $h, imagesx($src), imagesy($src));
				
			/* we need to know the max transaprency of the image */
			$max_t = 0;
	
			for ($y = 0; $y < $h; $y++) {
				for ($x = 0; $x < $w; $x++) {
					$src_c = imagecolorat($srccopy, $x, $y);
					$src_a = ($src_c >> 24) & 0xFF;
	
					$max_t = $src_a > $max_t ? $src_a : $max_t;
				}
			}
			/* src has no transparency? set it to use full alpha range */
			$max_t = $max_t == 0 ? 127 : $max_t;
	
			/* $max_t is now being reused as the correction factor to apply based */
			/* on the original transparency range of  src                         */
			$max_t /= 127;
	
			/* go back through the image adjusting alpha channel as required */
			for ($y = 0; $y < $h; $y++) {
				for ($x = 0; $x < $w; $x++) {
					$src_c  = imagecolorat($src, $srcX + $x, $srcY + $y);
					$src_a  = ($src_c >> 24) & 0xFF;
					$src_r  = ($src_c >> 16) & 0xFF;
					$src_g  = ($src_c >>  8) & 0xFF;
					$src_b  = ($src_c)       & 0xFF;
	
					/* alpha channel compensation */
					$src_a = ($src_a + 127 - (127 * $pct)) * $max_t;
					$src_a = ($src_a > 127) ? 127 : (int)$src_a;
	
					/* get and set this pixel's adjusted RGBA colour index */
					$rgba  = imagecolorallocatealpha($srccopy, $src_r, $src_g, $src_b, $src_a);
	
					/* ImageColorAllocateAlpha returns -1 for PHP versions prior  */
					/* to 5.1.3 when allocation failed                               */
					if ($rgba === false || $rgba == -1) {
						$rgba = imagecolorclosestalpha($srccopy, $src_r, $src_g, $src_b, $src_a);
					}
	
					imagesetpixel($srccopy, $x, $y, $rgba);
				}
			}
	
			/* call imagecopy passing our alpha adjusted image as src */
			imagecopyresized($dst, $srccopy, $dstX, $dstY, 0, 0, $w, $h, imagesx($src), imagesy($src));
	
			/* cleanup, free memory */
			imagedestroy($srccopy);
			return;
		}
		
		/* still here? no opacity adjustment required so pass straight through to */
		/* imagecopy rather than imagecopymerge to retain alpha channels          */
		imagecopyresized($dst, $src, $dstX, $dstY, $srcX, $srcY, $w, $h, imagesx($src), imagesy($src));
		return;
	}
	
}

