<?php
class ConfigboxRatingsHelper {

	static function getRatingCountHtml($productId, $count) {

		$url = KLink::getRoute('index.php?option=com_configbox&view=reviews&output_mode=view_only&product_id='.$productId, false);
		$triggerClass = 'trigger-show-reviews';

		if ($count == 0) {
			$text = KText::_('Write a review');
			$url = KLink::getRoute('index.php?option=com_configbox&view=reviewform&output_mode=view_only&product_id='.$productId, false);
			$triggerClass = 'trigger-show-review-form-modal';
		}
		elseif ($count == 1) {
			$text = KText::_('1 review');
		}
		else {
			$text = KText::sprintf('COUNT_REVIEWS', $count);
		}


		ob_start()
		?>
		<a class="<?php echo $triggerClass;?>" data-url-reviews="<?php echo $url;?>"><?php echo $text;?></a>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param float $rating
	 * @return string HTML for the stars (needs general.css for styling)
	 */
	static function getRatingStarHtml($rating) {

		if ($rating === NULL) {
			return '';
		}

		$full = floor($rating);
		$half = ($rating - $full > 0) ? true : false;

		$html = '<div class="rating-stars" data-rating="'.round($rating,1).'">';
		for ($i = 1; $i <= 5; $i++) {
			if ($i <= $full) {
				$class = 'full-star';
			}
			elseif ($half) {
				$class = 'half-star';
				$half = false;
			}
			else {
				$class = 'empty-star';
			}
			$html .= '<div class="rating-star '.$class.'"></div>';
		}

		$html .= '</div>';
		return $html;

	}

}