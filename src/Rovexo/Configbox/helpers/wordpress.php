<?php
class ConfigboxWordpressHelper {

	static function isWcIntegration() {
		return (KenedoPlatform::getName() == 'wordpress' && KenedoPlatform::p()->usesWcIntegration());
	}

	static function renewPages() {
		$products = KenedoModel::getModel('ConfigboxModelAdminproducts')->getRecords();
		foreach ($products as $product) {
			self::makePageForProduct($product);
		}
		$pages = KenedoModel::getModel('ConfigboxModelAdminpages')->getRecords();
		foreach ($pages as $page) {
			self::makePageForConfiguratorPage($page);
		}
		$lists = KenedoModel::getModel('ConfigboxModelAdminlistings')->getRecords();
		foreach ($lists as $list) {
			self::makePageForListing($list);
		}
		$config = KenedoModel::getModel('ConfigboxModelAdminconfig')->getRecord(1);
		self::makePageForAccount($config);
		self::makePageForCart($config);
	}

	static function makePageForConfiguratorPage($record) {

		$db = KenedoPlatform::getDb();

		$query = "
		SELECT `post_id` 
		FROM `#__postmeta` 
		WHERE 
		      `meta_key` = 'cb_page_id' 
		      AND 
		      `meta_value` = ".intval($record->id);
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		foreach ($postIds as $id) {
			wp_delete_post($id, true);
		}

		$languageTag = CbSettings::getInstance()->get('language_tag');

		$product = KenedoModel::getModel('ConfigboxModelAdminproducts')->getRecord($record->product_id);
		$postName = $product->{'label-'.$languageTag} .'/'.$record->{'label-'.$languageTag};

		$array = [
			'post_type' => 'cb_page',
			'post_title' => $record->{'title-'.$languageTag},
			'post_status' => ($record->published == '1') ? 'publish' : 'draft',
			'post_name' => $postName,
			'post_content' => sprintf('[configbox view="configuratorpage" id="%d"]', $record->id),
		];

		$postId = wp_insert_post($array, true);
		update_post_meta($postId, 'cb_page_id', $record->id);
		update_post_meta($postId, 'language_tag', $languageTag);

		if (KenedoPlatform::p()->hasWpml() == false) {
			return;
		}

		$trid = apply_filters( 'wpml_element_trid', null, $postId, 'post_cb_page');

		do_action( 'wpml_admin_make_post_duplicates', $postId);

		$translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_cb_page' );

		foreach ($translations as $langCode=>$translation) {
			if ($translation->original == 1) {
				continue;
			}

			$languageTag = self::getLanguageTagFromCode($langCode);

			// In case the WPML language just doesn't match any CB active language, skip translating the post
			if (!$languageTag) {
				continue;
			}

			$postName = $product->{'label-'.$languageTag} .'/'.$record->{'label-'.$languageTag};
			$translationPostId = $translation->element_id;

			$arr = [
				'ID'=>$translationPostId,
				'post_name'=>$postName,
				'post_title' => $record->{'title-'.$languageTag},
			];
			wp_update_post($arr, true);
			update_post_meta($translationPostId, 'language_tag', $languageTag);

		}

	}


	static function makePageForProduct($record) {

		$db = KenedoPlatform::getDb();

		$query = "
		SELECT `post_id` 
		FROM `#__postmeta` 
		WHERE 
		      `meta_key` = 'cb_product_id' 
		      AND 
		      `meta_value` = ".intval($record->id);
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		foreach ($postIds as $id) {
			wp_delete_post($id, true);
		}

		$languageTag = CbSettings::getInstance()->get('language_tag');

		$postName = $record->{'label-'.$languageTag};

		$array = [
			'post_type' => 'cb_product',
			'post_title' => $record->{'title-'.$languageTag},
			'post_status' => ($record->published == '1') ? 'publish' : 'draft',
			'post_name' => $postName,
			'post_content' => sprintf('[configbox view="product" id="%d"]', $record->id),
		];

		$postId = wp_insert_post($array, true);
		update_post_meta($postId, 'cb_product_id', $record->id);
		update_post_meta($postId, 'language_tag', $languageTag);

		if (KenedoPlatform::p()->hasWpml() == false) {
			return;
		}

		$trid = apply_filters( 'wpml_element_trid', null, $postId, 'post_cb_product');

		do_action( 'wpml_admin_make_post_duplicates', $postId);

		$translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_cb_product' );

		foreach ($translations as $langCode=>$translation) {
			if ($translation->original == 1) {
				continue;
			}

			$languageTag = self::getLanguageTagFromCode($langCode);

			// In case the WPML language just doesn't match any CB active language, skip translating the post
			if (!$languageTag) {
				continue;
			}

			$postName = $record->{'label-'.$languageTag};
			$translationPostId = $translation->element_id;

			$arr = [
				'ID'=>$translationPostId,
				'post_name'=>$postName,
				'post_title' => $record->{'title-'.$languageTag},
			];
			wp_update_post($arr, true);
			update_post_meta($translationPostId, 'language_tag', $languageTag);

		}

	}

	static function makePageForListing($record) {

		$db = KenedoPlatform::getDb();

		$query = "
		SELECT `post_id` 
		FROM `#__postmeta` 
		WHERE 
		      `meta_key` = 'cb_listing_id' 
		      AND 
		      `meta_value` = ".intval($record->id);
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		foreach ($postIds as $id) {
			wp_delete_post($id, true);
		}

		$languageTag = CbSettings::getInstance()->get('language_tag');

		$postName = $record->{'title-'.$languageTag};
		$postName = strtolower(str_replace(' ', '-', $postName));

		$array = [
			'post_type' => 'cb_product_listing',
			'post_title' => $record->{'title-'.$languageTag},
			'post_status' => ($record->published == '1') ? 'publish' : 'draft',
			'post_name' => $postName,
			'post_content' => sprintf('[configbox view="productlisting" id="%d"]', $record->id),
		];

		$postId = wp_insert_post($array, true);
		update_post_meta($postId, 'cb_listing_id', $record->id);
		update_post_meta($postId, 'language_tag', $languageTag);

		if (KenedoPlatform::p()->hasWpml() == false) {
			return;
		}

		$trid = apply_filters( 'wpml_element_trid', null, $postId, 'post_cb_product_listing');

		do_action( 'wpml_admin_make_post_duplicates', $postId);

		$translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_cb_product_listing' );

		foreach ($translations as $langCode=>$translation) {
			if ($translation->original == 1) {
				continue;
			}

			$languageTag = self::getLanguageTagFromCode($langCode);

			// In case the WPML language just doesn't match any CB active language, skip translating the post
			if (!$languageTag) {
				continue;
			}

			$postName = $record->{'title-'.$languageTag};
			$postName = strtolower(str_replace(' ', '-', $postName));
			$translationPostId = $translation->element_id;

			$arr = [
				'ID'=>$translationPostId,
				'post_name'=>$postName,
				'post_title' => $record->{'title-'.$languageTag},
			];
			wp_update_post($arr, true);
			update_post_meta($translationPostId, 'language_tag', $languageTag);

		}

	}

	static function makePageForAccount($record) {

		$db = KenedoPlatform::getDb();

		$query = "
		SELECT `post_id` 
		FROM `#__postmeta` 
		WHERE 
		      `meta_key` = 'type' 
		      AND 
		      `meta_value` = 'user'";
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		foreach ($postIds as $id) {
			wp_delete_post($id, true);
		}

		$languageTag = CbSettings::getInstance()->get('language_tag');

		$postName = $record->{'url_segment_user-'.$languageTag};
		$postName = strtolower(str_replace(' ', '-', $postName));

		$array = [
			'post_type' => 'cb_internal',
			'post_title' => KText::_('Account'),
			'post_status' => 'publish',
			'post_name' => $postName,
			'post_content' => '[configbox view="user"]',
		];

		$postId = wp_insert_post($array, true);
		update_post_meta($postId, 'type', 'user');
		update_post_meta($postId, 'language_tag', $languageTag);

		if (KenedoPlatform::p()->hasWpml() == false) {
			return;
		}

		$trid = apply_filters( 'wpml_element_trid', null, $postId, 'post_cb_internal');

		do_action( 'wpml_admin_make_post_duplicates', $postId);

		$translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_cb_internal' );

		$originalLanguage = $languageTag;

		foreach ($translations as $langCode=>$translation) {
			if ($translation->original == 1) {
				continue;
			}

			$languageTag = self::getLanguageTagFromCode($langCode);

			// In case the WPML language just doesn't match any CB active language, skip translating the post
			if (!$languageTag) {
				continue;
			}

			$postName = $record->{'url_segment_user-'.$languageTag};
			$postName = strtolower(str_replace(' ', '-', $postName));

			KText::setLanguage($languageTag);

			$translationPostId = $translation->element_id;
			$arr = [
				'ID'=>$translationPostId,
				'post_name'=>$postName,
				'post_title' => KText::_('Account'),
			];
			wp_update_post($arr, true);
			update_post_meta($translationPostId, 'language_tag', $languageTag);

		}

		KText::setLanguage($originalLanguage);

	}

	static function makePageForCart($record) {

		$db = KenedoPlatform::getDb();

		$query = "
		SELECT `post_id` 
		FROM `#__postmeta` 
		WHERE 
		      `meta_key` = 'type' 
		      AND 
		      `meta_value` = 'cart'";
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		foreach ($postIds as $id) {
			wp_delete_post($id, true);
		}

		$languageTag = CbSettings::getInstance()->get('language_tag');

		$postName = $record->{'url_segment_cart-'.$languageTag};
		$postName = strtolower(str_replace(' ', '-', $postName));

		$array = [
			'post_type' => 'cb_internal',
			'post_title' => KText::_('Cart'),
			'post_status' => 'publish',
			'post_name' => $postName,
			'post_content' => '[configbox view="cart"]',
		];

		$postId = wp_insert_post($array, true);
		update_post_meta($postId, 'type', 'cart');
		update_post_meta($postId, 'language_tag', $languageTag);

		if (KenedoPlatform::p()->hasWpml() == false) {
			return;
		}

		$trid = apply_filters( 'wpml_element_trid', null, $postId, 'post_cb_internal');

		do_action( 'wpml_admin_make_post_duplicates', $postId);

		$translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_cb_internal' );

		$originalLanguage = $languageTag;

		foreach ($translations as $langCode=>$translation) {
			if ($translation->original == 1) {
				continue;
			}

			$languageTag = self::getLanguageTagFromCode($langCode);

			// In case the WPML language just doesn't match any CB active language, skip translating the post
			if (!$languageTag) {
				continue;
			}

			$postName = $record->{'url_segment_cart-'.$languageTag};
			$postName = strtolower(str_replace(' ', '-', $postName));

			KText::setLanguage($languageTag);

			$translationPostId = $translation->element_id;
			$arr = [
				'ID'=>$translationPostId,
				'post_name'=>$postName,
				'post_title' => KText::_('Cart'),
			];
			wp_update_post($arr, true);
			update_post_meta($translationPostId, 'language_tag', $languageTag);

		}

		KText::setLanguage($originalLanguage);

	}

	static function getPostId($postType, $recordKey, $recordId, $languageTag) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT `meta_record_key`.`post_id`
		FROM `#__postmeta` AS `meta_record_key`
		LEFT JOIN `#__postmeta` AS `meta_language` ON `meta_language`.`post_id` = `meta_record_key`.`post_id`
		LEFT JOIN `#__posts` AS `posts` ON `posts`.`ID` = `meta_record_key`.`post_id`
		
		WHERE 
			(`meta_record_key`.`meta_key` = '".$db->getEscaped($recordKey)."' and `meta_record_key`.`meta_value` = '".$db->getEscaped($recordId)."')
			AND
			(`meta_language`.`meta_key` = 'language_tag' and `meta_language`.`meta_value` = '".$db->getEscaped($languageTag)."')
			AND
			(`posts`.`post_type` = '".$db->getEscaped($postType)."')
			
		";
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getLanguageTagFromCode($code) {
		$languageTags = KenedoLanguageHelper::getActiveLanguageTags();
		foreach ($languageTags as $languageTag) {
			if (strpos($languageTag, $code) === 0) {
				return $languageTag;
			}
		}
	}

}