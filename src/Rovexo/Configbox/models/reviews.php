<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelReviews extends KenedoModel {

	function getReviews($productId) {

		if (function_exists('getReviews')) {
			$reviews = getReviews($productId);
			return $reviews;
		}

		$db = KenedoPlatform::getDb();

		$query = "SELECT * FROM `#__configbox_reviews` WHERE `product_id` = ".intval($productId)." AND `published` = '1' ORDER BY `date_created` DESC";
		$db->setQuery($query);
		$reviews = $db->loadObjectList();
		
		return $reviews;
		
	}

	/**
	 * @param int $listingId
	 * @return array[] Keys are product ids, value is an array with keys average and count
	 */
	function getRatingInfoListing($listingId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$productIds = !empty($ass['listing_to_product'][$listingId]) ? $ass['listing_to_product'][$listingId] : array();

		return $this->getRatingInfoProducts($productIds);

	}

	/**
	 * @param int $productId
	 * @return array with keys average and count
	 */
	function getRatingInfoProduct($productId) {
		$ratingInfo = $this->getRatingInfoProducts(array($productId));
		return $ratingInfo[$productId];
	}

	/**
	 * @param int[] $productIds
	 * @return array[] Keys are product ids, value is an array with keys average and count
	 */
	function getRatingInfoProducts($productIds) {

		if (count($productIds) == 0) {
			return array();
		}

		// Prime the ratings array to return
		$ratings = array();
		foreach ($productIds as $id) {
			$ratings[$id] = array(
				'average' => NULL,
				'count' => 0,
			);
		}

		$query = "
		SELECT AVG(`rating`) AS `average`, COUNT(*) AS `count`, `product_id`
		FROM `#__configbox_reviews`
		WHERE `product_id` IN (".implode(', ', $productIds).") AND `published` = '1'
		GROUP BY `product_id`
		";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$infos = $db->loadAssocList('product_id');

		foreach($infos as $productId=>$info) {
			$ratings[$productId] = array(
				'average' => round($info['average'], 1),
				'count' => $info['count'],
			);
		}

		return $ratings;

	}
	
	function storeReview($productId, $name, $comment, $rating, $languageTag = NULL) {
		
		if (function_exists('storeReview')) {
			$result = storeReview($productId, $name, $comment, $rating, $languageTag);
			return $result;
		}
		
		if (!trim($name) or !trim($comment)) {
			return false;
		}
		
		$object = new stdClass();
		$object->name = $name;
		$object->rating = $rating;
		$object->comment = $comment;
		$object->published = 0;
		$object->language_tag = ($languageTag !== NULL) ? $languageTag : KenedoPlatform::p()->getLanguageTag();
		$object->date_created = KenedoTimeHelper::getNormalizedTime('NOW','datetime');
		$object->product_id = $productId;

		$db = KenedoPlatform::getDb();
		$success = $db->insertObject('#__configbox_reviews', $object, 'id');
		
		return $success;
		
	}
	
	function notifyOnReview($id, $kind, $name, $comment, $rating) {
		
		if (constant('CONFIGBOX_REVIEW_NOTIFICATION_EMAIL') == '') {
			return false;
		}
		
		$originalLanguageTag = KText::getLanguageTag();
		
		if (CbSettings::getInstance()->get('language_tag') != KText::getLanguageTag()) {
			KText::setLanguage( CbSettings::getInstance()->get('language_tag') );
		}
		
		$shopData 	= ConfigboxStoreHelper::getStoreRecord();
				
		$email = new stdClass();
		$email->fromEmail = $shopData->shopemailsales;
		$email->fromName = $shopData->shopname;
		$email->to = CbSettings::getInstance()->get('review_notification_email');
		$email->subject = KText::sprintf('New Review from %s',$name);
		$email->body = KText::_('A new review was created. Please review it at Order Management - Reviews.');
		
		if ($rating) {
			$email->body .= "\n\n".KText::sprintf('Rating is %s stars.',$rating);
		}
		
		if ($comment) {
			$email->body .= "\n\n".KText::_('Review comment is');
			$email->body .= "\n".$comment;
		}
		
		$success = KenedoPlatform::p()->sendEmail($email->fromEmail, $email->fromName, $email->to, $email->subject, $email->body);
		
		if ($originalLanguageTag != KText::getLanguageTag()) {
			KText::setLanguage($originalLanguageTag);
		}
		
		return $success;
	}
	
}