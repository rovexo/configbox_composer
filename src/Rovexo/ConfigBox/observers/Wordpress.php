<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Class ObserverWordpress
 *
 * That observer creates/updates custom type posts for each listing/product/page with short codes as content
 * This is for having SEF links. The post name (used for URL generation) come from the product's or page's SEF segments.
 * For the listings, it uses the title transliterated for URLs.
 */
class ObserverWordpress {

	function onAfterStoreRecord($modelName, $data) {

		if (KenedoPlatform::getName() != 'wordpress') {
			return;
		}

		// Translations must get updated
		ConfigboxCacheHelper::purgeCache();


		// We keep the code generic for all types and have settings here for listings/products/pages
		$postSettings = [

			'adminlistings' =>
				[
					'class_name' => 'ConfigboxModelAdminlistings',
					'meta_key' => 'cb_listing_id',
					'meta_value_field' => 'id',
					'post_type' => 'cb_product_listing',
					'title_field' => 'title',
					'content_template' => '[configbox view="productlisting" id="%s"]',
					'status_field' => 'published',
					'post_name_field' => 'title',
					'post_name_filter_callback' => function($record, $setting) { return strtolower(str_replace(' ', '-', $record->title)); },
				],

			'adminproducts' =>
				[
					'class_name' => 'ConfigboxModelAdminproducts',
					'meta_key' => 'cb_product_id',
					'meta_value_field' => 'id',
					'post_type' => 'cb_product',
					'title_field' => 'title',
					'content_template' => '[configbox view="product" id="%s"]',
					'status_field' => 'published',
					'post_name_field' => 'label',
					'post_name_filter_callback' => null,
				],

			'adminpages' =>
				[
					'class_name' => 'ConfigboxModelAdminpages',
					'meta_key' => 'cb_page_id',
					'meta_value_field' => 'id',
					'post_type' => 'cb_page',
					'title_field' => 'title',
					'content_template' => '[configbox view="configuratorpage" id="%s"]',
					'status_field' => 'published',
					'post_name_field' => 'title',
					'post_name_filter_callback' => function($record, $setting) {
						$product = KenedoModel::getModel('ConfigboxModelAdminproducts')->getRecord($record->product_id);
						return $product->label .'/'.$record->label;
					},
				],

		];

		if ($modelName == 'adminconfig') {
			$this->handleUrls($modelName, $data);
			return;
		}

		if (!isset($postSettings[$modelName])) {
			return;
		}

		$setting = $postSettings[$modelName];

		$record = KenedoModel::getModel($setting['class_name'])->getRecord($data->id);

		// We go into postmeta and find posts with the right ID (we look for multiple ones for in case there is a mess)
		$db = KenedoPlatform::getDb();
		$query = "SELECT `post_id` FROM `#__postmeta` WHERE `meta_key` = '".$db->getEscaped($setting['meta_key'])."' and `meta_value` = ".intval($record->{$setting['meta_value_field']});
		$db->setQuery($query);
		$postIds = $db->loadResultList();

		$postId = null;

		// In case we somehow got more than one, we delete them and start over
		if (count($postIds) > 1) {
			foreach ($postIds as $id) {
				wp_delete_post($id, true);
			}
		}
		elseif(count($postIds) == 1) {
			$postId = $postIds[0];
		}

		$array = [
			'post_type' => $setting['post_type'],
			'post_title' => $record->{$setting['title_field']},
			'post_status' => (!$setting['status_field'] || $record->{$setting['status_field']} == '1') ? 'publish' : 'draft',
			'post_name' => is_callable($setting['post_name_filter_callback']) ? call_user_func($setting['post_name_filter_callback'], $record, $setting) : $record->{$setting['post_name_field']},
			'post_content' => sprintf($setting['content_template'], $record->id),
		];

		if ($postId) {
			$array['ID'] = $postId;
			$response = wp_update_post($array, true);
		}
		else {
			$response = wp_insert_post($array, true);
		}

		if (!is_int($response)) {
			KLog::log(var_export($response, true), 'error');
			return;
		}

		update_post_meta($response, $setting['meta_key'], $record->id);

		return;

	}

	function onAfterCopyRecord($modelName, $newData) {

		if (KenedoPlatform::getName() != 'wordpress') {
			return;
		}

		// We can do the same as for storing
		$this->onAfterStoreRecord($modelName, $newData);

	}

	/**
	 * This one looks at the URL segments from the config record and makes custom post types for cart and checkout in
	 * all languages.
	 *
	 * The page type is always 'cb_internal' and it has two meta data fields ('page_type' = cart|checkout and 'language_tag')
	 *
	 * Just like for listings/products/pages the content is a short code (configbox_cart and configbox_checkout)
	 *
	 * @param string $modelName
	 * @param object $data
	 */
	protected function handleUrls($modelName, $data) {

		$tags = KenedoLanguageHelper::getActiveLanguageTags();

		$db = KenedoPlatform::getDb();

		$record = KenedoModel::getModel('ConfigboxModelAdminconfig')->getRecord($data->id);

		$settings = array(

			'cart' => array(
				'type' => 'cart',
				'short_code' => '[configbox view="cart"]',
				'record_var' => 'url_segment_cart',
				'post_title' => KText::_('Cart'),
			),
			'user' => array(
				'type' => 'user',
				'short_code' => '[configbox view="user"]',
				'record_var' => 'url_segment_user',
				'post_title' => KText::_('Account'),
			),

		);

		foreach ($settings as $setting) {

			foreach ($tags as $tag) {

				// First get all meta data that is about carts
				$query = "
				SELECT `type`.`post_id`
				FROM `#__postmeta` AS `type`
				LEFT JOIN `#__postmeta` AS `lang` ON `lang`.`post_id` = `type`.`post_id`
				
				WHERE 
					(`type`.`meta_key` = 'type' and `type`.`meta_value` = '".$db->getEscaped($setting['type'])."')
					AND
					(`lang`.`meta_key` = 'language_tag' and `lang`.`meta_value` = '".$db->getEscaped($tag)."')
					
				";
				$db->setQuery($query);
				$postIds = $db->loadResultList();

				$postId = null;

				// In case we somehow got duplicates, we delete them and start over
				if (count($postIds) > 1) {
					foreach ($postIds as $id) {
						wp_delete_post($id, true);
					}
				}
				elseif(count($postIds) == 1) {
					$postId = $postIds[0];
				}

				$array = [
					'post_type' => 'cb_internal',
					'post_title' => $setting['post_title'] . (count($tags) > 1 ? ' - '.$tag : ''),
					'post_status' => 'publish',
					'post_name' => $record->{$setting['record_var'].'-'.$tag},
					'post_content' => $setting['short_code'],
				];

				if ($postId) {
					$array['ID'] = $postId;
					$response = wp_update_post($array, true);
				}
				else {
					$response = wp_insert_post($array, true);
				}

				if (!is_int($response)) {
					KLog::log(var_export($response, true), 'error');
					return;
				}

				update_post_meta($response, 'type', $setting['type']);
				update_post_meta($response, 'language_tag', $tag);

			}

		}

	}

}