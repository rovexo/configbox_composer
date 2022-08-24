<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelProduct extends KenedoModelLight{

	/**
	 * @param int $productId
	 * @deprecated will be removed in CB 4.0. Use ConfigboxModelConfiguratorpage::getPages instead
	 *
	 * @return object[]
	 */
	function getPages($productId) {
		return KenedoModel::getModel('ConfigboxModelConfiguratorpage')->getPages($productId);
	}

	/**
	 * @param $productId
	 * @return ConfigboxProductData
	 * @throws Exception
	 */
	function getProduct($productId) {

		if (!$productId) {
			$msg = 'Product requested without a valid product ID. $productId was '.var_export($productId, true);
			KLog::log($msg, 'error');
			throw new Exception($msg, '500');
		}

		return ConfigboxCacheHelper::getProduct($productId);

	}

	function fixLabels() {

		$db = KenedoPlatform::getDb();

		$prodId = KRequest::getInt('prod_id',0);
		$pageId = KRequest::getInt('page_id',0);

		$prodLabel = !empty($GLOBALS['productLabel']) ? $GLOBALS['productLabel'] : '';
		$pageLabel = !empty($GLOBALS['pageLabel']) ? $GLOBALS['pageLabel'] : '';

		$view = KRequest::getKeyword('view','');

		KLog::log('Fixing routing');
		KLog::log('Prod id is "'.$prodId.'"');
		KLog::log('Prod label is "'.$prodLabel.'"');
		KLog::log('Page id is "'.$pageId.'"');
		KLog::log('Page label is "'.$pageLabel.'"');

		$noProdId = (empty($prodId) && !empty($prodLabel));
		$noPageId = (empty($pageId) && !empty($pageLabel));

		$prodFixed = false;
		$pageFixed = false;
		$prodFixedBy = '';

		$fromLanguageTag = KText::getLanguageTag();
		$usedLanguageTag = KText::getLanguageTag();

		if ($noProdId) {

			if (!$prodFixed) {
				// Check for a product id by label and current label in old labels
				$query = "	SELECT oldlabels.key AS id, prodlabel.text AS label
							FROM `#__configbox_oldlabels` AS oldlabels
							LEFT JOIN `#__configbox_strings` AS prodlabel ON prodlabel.key = oldlabels.key AND prodlabel.type = 17 AND prodlabel.language_tag = '".$db->getEscaped(KText::getLanguageTag())."'
							WHERE oldlabels.label = '".$db->getEscaped($prodLabel)."' AND oldlabels.type = 17 AND oldlabels.language_tag = '".$db->getEscaped(KText::getLanguageTag())."'
							LIMIT 1";
				$db->setQuery($query);
				$result = $db->loadAssoc();
				if ($result !== NULL && $result['id'] !== NULL && $result['label'] !== NULL) {
					KLog::log('Fixed the prod id and label with old labels. prod ID is "'.$result['id'].'", label is "'.$result['label'].'"');
					$prodId = $result['id'];
					$prodLabel = $result['label'];
					$prodFixed = true;
					$prodFixedBy = 'oldlabel';
					// These two lines might be dispensable
					$fromLanguageTag = KText::getLanguageTag();
					$usedLanguageTag = KText::getLanguageTag();
				}
			}

			if (!$prodFixed) {
				// Try labels from other languages (and not from the actual language, this is a workaround for the languages switch problem).
				$query = "	SELECT `key` AS `id`, `text` AS label, `language_tag`
							FROM `#__configbox_strings`
							WHERE `text` = '".$db->getEscaped($prodLabel)."' AND `type` = 17 AND `language_tag` != '".$db->getEscaped(KText::getLanguageTag())."'
							LIMIT 1";
				$db->setQuery($query);
				$result = $db->loadAssoc();
				if ($result !== NULL && $result['id'] !== NULL && $result['label'] !== NULL) {
					KLog::log('Got a prod id from other languages\' labels. Lang tag is "'.$result['language_tag'].'", product ID is "'.$result['id'].'", label is "'.$result['label'].'"');
					$prodId = $result['id'];
					$fromLanguageTag = $result['language_tag'];

					// Get the label from the actual language
					$fetchedProdLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId);

					if (!empty($fetchedProdLabel)) {
						KLog::log('Fixed the product label in lang tag "'.KText::getLanguageTag().'", label is "'.$fetchedProdLabel.'", prod id is "'.intval($prodId).'"');
						$prodLabel = $fetchedProdLabel;
						$prodFixed = true;
						$prodFixedBy = 'language';
						$usedLanguageTag = KText::getLanguageTag();
					}
				}
			}
		}

		// In case we were able to fix the product OR there was no problem with the product, but with the page
		if (($noProdId && $prodFixed) || (!$noProdId && $noPageId)) {

			KLog::log('Trying to fix missing page id. Currently got page label "'.$pageLabel.'" and page id "'.$pageId.'", prod id is "'.$prodId.'" and prod label "'.$prodLabel.'".');

			if (!$pageFixed) {

				KLog::log('First trying to fix missing page id with old labels info');

				// Check for a page id and label in old labels with matching prod id

				// If the product was fixed by language, only search for page labels in old labels with the same language

				KLog::log('Trying old page labels with lang tag "'.$usedLanguageTag.'"');

				$query = "	SELECT oldlabels.key AS id, pagelabel.text AS label
							FROM `#__configbox_oldlabels` AS oldlabels
							LEFT JOIN `#__configbox_strings` AS pagelabel ON pagelabel.key = oldlabels.key AND pagelabel.type = 18 AND pagelabel.language_tag = '".$db->getEscaped($usedLanguageTag)."'
							WHERE oldlabels.label = '".$db->getEscaped($pageLabel)."' AND oldlabels.type = 18 AND oldlabels.prod_id = ".(int)$prodId." AND oldlabels.language_tag = '".$db->getEscaped($usedLanguageTag)."' 
							LIMIT 1";

				$db->setQuery($query);
				$result = $db->loadAssoc();
				KLog::log('old label check query returned '.var_export($result,true));
				if ($result !== NULL && $result['id'] !== NULL && $result['label'] !== NULL) {
					KLog::log('Fixed the page id and label from old labels. ID is "'.$result['id'].'", label is "'.$result['label'].', lang tag is "'.$usedLanguageTag.'".');

					$pageId = $result['id'];
					$pageLabel = $result['label'];
					$pageFixed = true;
				}
			}

			if (!$pageFixed) {

				KLog::log('Next trying to fix missing page id with other language info');

				// Try labels from other languages (and not from the actual language, this is a workaround for the languages switch problem).
				$query = "	SELECT str.`key` AS `id`
							FROM `#__configbox_strings` AS str
							LEFT JOIN `#__configbox_pages` AS c ON c.id = str.key
							WHERE `text` = '".$db->getEscaped($pageLabel)."' AND `type` = 18 AND c.product_id = ".(int)$prodId." ";

				if ($prodFixed && $prodFixedBy == 'language') $query .= "AND str.language_tag = '".$db->getEscaped($fromLanguageTag)."' ";
				else $query .= "AND str.language_tag != '".$db->getEscaped($usedLanguageTag)."' ";

				$query .= " LIMIT 1";

				$db->setQuery($query);
				KLog::log('other language query was "'.$db->getQuery().'"');
				$result = $db->loadAssoc();
				KLog::log('other language label check query returned '.var_export($result,true));
				if ($result !== NULL && $result['id'] !== NULL) {
					$pageId = $result['id'];
					KLog::log('Got a page id "'.$result['id'].'" from other languages with matching prod id "'.$prodId.'" and language tag "'.$fromLanguageTag.'"');

					if (!empty($fetchedPageLabel)) {

						KLog::log('Fixed page label with other languages\' labels. new page label is "'.$fetchedPageLabel.'", language tag is "'.$usedLanguageTag.'".');
						$pageId = $result['id'];
						$pageFixed = true;
					}
				}
			}

		}

		// If we got any changes
		if ($prodFixed || $pageFixed) {

			// Overwrite prod parameters
			if ($prodFixed) {
				KRequest::setVar('prod_id',$prodId);
			}
			// Overwrite page parameters
			if ($pageFixed) {
				KRequest::setVar('page_id',$pageId);
			}

			// Prepare URL
			if ($view == 'product') {
				$url = 'index.php?option=com_configbox&view='.$view.'&prod_id='.KRequest::getInt('prod_id',0);

			} elseif ($view == 'configuratorpage') {
				$url = 'index.php?option=com_configbox&view='.$view.'&prod_id='.KRequest::getInt('prod_id',0).'&page_id='.KRequest::getInt('page_id',0);
			} else {
				KLog::log('View is neither product or configuratorpage.','error',KText::_('A system error occured.'));
				return false;
			}
			$urlbuilt = KLink::getRoute($url,false);

			$scheme = KenedoPlatform::p()->requestUsesHttps() ? 'https' : 'http';

			// If new URL differs from actual URL, redirect
			if ($urlbuilt != $scheme . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) {
				KLog::log('Redirecting to URL "'.$url.'", built URL is "'.$urlbuilt.'"');
				KenedoPlatform::p()->redirect($urlbuilt, 301);
			}

		}

		return true;

	}

	/**
	* @param $productId
	* @return string[] structured data array (for JSON-LD)
	* @throws Exception
	*/
	public function getStructuredData($productId) {

		if (!$productId) {
			throw new Exception('Product Structured Data requested without a valid product ID. $productId was '.var_export($productId, true), '500');
		}

		// get product data
		$product = $this->getProduct($productId);

		// get reviews model
		$reviewsModel = KenedoModel::getModel('ConfigboxModelReviews');
		$reviewsInfo = $reviewsModel->getRatingInfoProducts([$productId]);

		// setup json-ld output variable
		$productJsonLdModel = [
			'@context'		=> 'http://schema.org',
			'@type' 		=> 'Product',
			'name' 			=> $product->title,
			'image' 		=> $product->prod_image_href,
		];

		if(!empty($product->description)) $productJsonLdModel['description'] = $product->description;

		if(!empty($product->sku)) $productJsonLdModel['sku'] = $product->sku;

		// get rating
		if(!empty($reviewsInfo[$productId]['count'])) {
			$productJsonLdModel["aggregateRating"] = [
				"@type"			=>  "aggregateRating",
				"ratingValue"	=> $reviewsInfo[$productId]['average'],
				"reviewCount"	=> $reviewsInfo[$productId]['count'],
			];
		}

		// get review objects
		$productReviews = $reviewsModel->getReviews($productId);

		if(!empty($productReviews)) {
			foreach ($productReviews as $productReview){
				if($productReview->published){
					$productJsonLdModel['review'][] = [
						"@context" => "http://schema.org/",
						"@type" => "Review",
						"itemReviewed" => [
							'@type' 		=> 'Product',
							'name' 			=> $product->title,
							'image' 		=> $product->prod_image_href,
						],
						"reviewRating" => [
							"@type" => "Rating",
							"ratingValue" => $productReview->rating,
						],
						"author" => [
							"@type" => "Person",
							"name" => $productReview->name,
						],
						"reviewBody" => $productReview->comment,
					];
				}
			}
		}

		// json-ld output array
		return $productJsonLdModel;

	}

}
