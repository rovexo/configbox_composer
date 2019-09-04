<?php
class KenedoRouterHelper {

	protected static $menuItemsByLink = NULL;

	/**
	 *
	 * Gets all published Joomla menu items in a deep array, grouped by language.
	 * Array keys of first level is the language tag, keys of the language's items are the 'Joomla link' (non-SEF
	 * URI+query string, e.g. index.php?option=com_configbox&view=product&id=1).
	 * Language independent menu items are placed in each language's sub array.
	 * Example path in response array: $response['de-DE']['index.php?option=com_configbox&view=cart']
	 *
	 * @return array[]
	 */
	protected static function &getMenuItems() {

		if (self::$menuItemsByLink === NULL) {
			$db = KenedoPlatform::getDb();
			$tag = KenedoPlatform::p()->getLanguageTag();

			if (KenedoPlatform::getName() == 'joomla') {

				// Get all published frontend menu items
				if (KenedoPlatform::p()->getVersionShort() == 1.5) {
					$dbQuery = "SELECT `id`,`link`, '".$db->getEscaped($tag)."' AS `language` FROM `#__menu` WHERE `published` = '1' AND `type` = 'component'";
					$db->setQuery($dbQuery);
					$menuItems = $db->loadAssocList();
				}
				else {
					$dbQuery = "SELECT `id`,`link`,`language` FROM `#__menu` WHERE `client_id` = 0 AND `published` = '1' AND `type` = 'component' ORDER BY `language` DESC";
					$db->setQuery($dbQuery);
					$menuItems = $db->loadAssocList();
				}

				$itemsGroupedByLang = array();
				$languageIndependentItems = array();

				// Push language specific items in grouped array, collect independent ones for mixing them in later
				foreach ($menuItems as $key=>$menuItem) {
					if ($menuItem['language'] == '*') {
						$languageIndependentItems[$menuItem['link']] = $menuItem;
					}
					else {
						$itemsGroupedByLang[$menuItem['language']][$menuItem['link']] = $menuItem;
					}
				}

				// Workaround for in case there is not a single language-specific menu item.
				// Populate the $itemsGroupedByLang with the current language and all non-specifics
				if (empty($itemsGroupedByLang)) {
					foreach ($menuItems as $key=>$menuItem) {
						if ($menuItem['language'] == '*') {
							$itemsGroupedByLang[$tag][$menuItem['link']] = $menuItem;
						}
					}
				}

				// Now mix in the lang-independent ones in each language specific group
				foreach ($itemsGroupedByLang as $langTag=>&$specificItems) {
					foreach ($languageIndependentItems as $independentItem) {
						if (!isset($specificItems[$independentItem['link']])) {
							$specificItems[$independentItem['link']] = $independentItem;
						}
					}
				}

				self::$menuItemsByLink = $itemsGroupedByLang;

			}

		}

		return self::$menuItemsByLink;

	}

	static function getItemIdByLink($link, $languageTag = NULL) {

		if ($languageTag == NULL) {
			$languageTag = KenedoPlatform::p()->getLanguageTag();
		}

		$items = self::getMenuItems();

		if (isset($items[$languageTag][$link])) {
			return $items[$languageTag][$link]['id'];
		}
		else {
			return NULL;
		}

	}

	static function isFirstPage($prodId, $pageId) {
		$firstPage = self::getFirstPageId($prodId);
		return ($firstPage && $firstPage == $pageId);
	}

	static function getFirstPageId($prodId) {
		$query = "
		SELECT `id`
		FROM `#__configbox_pages`
		WHERE `product_id` = ".intval($prodId)." AND `published` = '1'
		ORDER BY `ordering`
		LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getFakedConfigurationItemId($prodId,$pageId) {

		// Item with both params set
		$searchLinks[] = 'index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id='.$pageId;
		// Item with only prod_id param set
		$searchLinks[] = 'index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id=0';
		// Item with only prod_id param set (J1.5 does not append a 0 param if not set AFAIK)
		$searchLinks[] = 'index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId;
		// Item with both params set to zero
		$searchLinks[] = 'index.php?option=com_configbox&view=configuratorpage&prod_id=0&page_id=0';
		// Item with no params
		$searchLinks[] = 'index.php?option=com_configbox&view=configuratorpage';

		// Check for a matching menu item
		foreach ($searchLinks as $searchLink) {
			$id = self::getItemIdByLink($searchLink);
			if ($id) {
				return $id;
				break;
			}
		}

		return null;
	}

	static function getProdId($label) {
		$db = KenedoPlatform::getDb();

		$query = "
		SELECT str.key AS id FROM `#__configbox_strings` AS str
		WHERE str.text = '".$db->getEscaped($label)."'  AND str.type = 17 AND str.language_tag = '".$db->getEscaped(KenedoPlatform::p()->getLanguageTag())."'
		LIMIT 1";
		$db->setQuery($query);
		$prodid =  $db->loadResult();
		return $prodid;

	}

	static function getProductLabel($prodId) {
		return ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId);
	}

	static function getPageLabel($pageId) {
		return ConfigboxCacheHelper::getTranslation('#__configbox_strings', 18, $pageId);
	}

	static function getListingIds($productId) {

		$assignments = ConfigboxCacheHelper::getAssignments();
		return (isset($assignments['product_to_listing'][$productId])) ? $assignments['product_to_listing'][$productId] : array();

	}

	static function getPageId($prodLabelOrId,$pageLabel) {

		$db = KenedoPlatform::getDb();

		if (is_numeric($prodLabelOrId)) {
			$where = "WHERE p.id = ".(int)$prodLabelOrId." AND pagestr.text = '".$db->getEscaped($pageLabel)."' AND pagestr.language_tag = '".$db->getEscaped(KenedoPlatform::p()->getLanguageTag())."'";
		}
		else {
			$where = "WHERE pagestr.text = '".$db->getEscaped($pageLabel)."' AND prodstr.text = '".$db->getEscaped($prodLabelOrId)."' AND pagestr.language_tag = '".$db->getEscaped(KenedoPlatform::p()->getLanguageTag())."'";
		}

		$query = "
		SELECT c.id
		FROM `#__configbox_pages` AS c
		LEFT JOIN `#__configbox_products` AS p ON c.product_id = p.id
		LEFT JOIN `#__configbox_strings` AS pagestr ON pagestr.key = c.id AND pagestr.type = 18
		LEFT JOIN `#__configbox_strings` AS prodstr ON prodstr.key = p.id AND prodstr.type = 17
		 ";
		$query .= $where;
		$query .= " LIMIT 1";

		$db->setQuery($query);
		$pageId = $db->loadResult();
		return $pageId;
	}

	static function getLabelFromSlug($slug) {
		$exp = explode(':',$slug);
		if (isset($exp[1])) return $exp[1];
		else return '';
	}

	/**
	 * Get's you the joomla menu item id of a product listing page that is the closest parent of the current page
	 * The method goes by the current URL and not the current menu item id because the current page URL may only
	 * just be derived from a menu item.
	 *
	 * @param array $listingIds   Array of product listing ids
	 * @param string $languageTag (optional) to search within a certain language, method will use current site
	 *                            language if not provided
	 *
	 * @return int|NULL $listingId - id of a parent menu item or NULL if none are found
	 */
	static function getParentListingMenuItemId($listingIds, $languageTag = NULL) {

		if (count($listingIds) == 0) {
			return NULL;
		}

		if ($languageTag === NULL) {
			$languageTag = KenedoPlatform::p()->getLanguageTag();
		}

		$db = KenedoPlatform::getDb();

		switch (KenedoPlatform::p()->getVersionShort()) {

			case '1.5':

				// Prepare an array with links (quoted and escaped)
				$links = array();
				foreach ($listingIds as $listingId) {
					$links[] = "'".$db->getEscaped('index.php?option=com_configbox&view=listings&listing_id='.intval($listingId))."'";
				}

				// Simply get the deepest menu item that has a matching listing page
				$query = "SELECT `id`, `link` FROM `#__menu` WHERE `published` = '1' AND `link` IN (".implode(',', $links).") ORDER BY `sublevel` DESC LIMIT 1";
				$db->setQuery($query);
				$id = $db->loadResult();
				return ($id) ? intval($id) : NULL;

				break;

			default:

				// Get the current URI (drop starting slash and the last item (conveniently removing any query string or SEF suffix in the process)
				$segments = dirname($_SERVER['REQUEST_URI']);

				// Get the path part of the base URL..
				$base = str_replace(KPATH_SCHEME .'://'. KPATH_HOST, '', KPATH_URL_BASE);

				// ..and take it off
				$segments = substr($segments, strlen($base));

				// Get the language code..
				$langCode = '/'.KenedoPlatform::p()->getLanguageUrlCode($languageTag);

				// ..and take it off
				if (strpos($segments, $langCode) === 0) {
					$segments = substr($segments, strlen($langCode));
				}

				// Make an array of the individual segments
				$segArray = explode('/',ltrim($segments, '/'));

				// Make an array of parent paths from deepest to 'flattest'
				$parentPaths = array();
				while(count($segArray)) {
					$parentPaths[] = "'".$db->getEscaped(implode('/', $segArray))."'";
					array_pop($segArray);
				}

				// Get all menu items with matching path (note that we sort by level descending, level is the depth of the menu item)
				$query = "SELECT `id`, `link` FROM `#__menu` WHERE `published` = '1' AND `path` IN (".implode(',', $parentPaths).") AND `language` IN ('*', '".$languageTag."') ORDER BY `level` DESC";
				$db->setQuery($query);
				$possibleItems = $db->loadAssocList();

				// Check links for a product listing with a matching listing id
				$match = NULL;
				foreach ($listingIds as $listingId) {
					foreach ($possibleItems as $possibleItem) {
						if (strstr($possibleItem['link'], 'option=com_configbox') && strstr($possibleItem['link'], 'view=productlisting') && strstr($possibleItem['link'],'listing_id='.$listingId)) {
							$match = $possibleItem['id'];
							break;
						}
					}
				}

				return ($match) ? intval($match) : NULL;

				break;

		}

	}

	static function getParentProductMenuItemId($productId, $languageTag = NULL) {

		if (!$productId) {
			return NULL;
		}

		$db = KenedoPlatform::getDb();

		switch (KenedoPlatform::p()->getVersionShort()) {

			case '1.5':

				$link = 'index.php?option=com_configbox&view=product&prod_id='.intval($productId);

				// Simply get the deepest menu item that has a matching listing page
				$query = "SELECT `id`, `link` FROM `#__menu` WHERE `published` = '1' AND `link` = '".$link."' ORDER BY `sublevel` DESC LIMIT 1";
				$db->setQuery($query);
				$id = $db->loadResult();
				return ($id) ? intval($id) : NULL;

				break;

			default:

				// Get the current URI (drop starting slash and the last item (conveniently removing any query string or SEF suffix in the process)
				$segments = dirname($_SERVER['REQUEST_URI']);

				// Get the path part of the base URL..
				$base = str_replace(KPATH_SCHEME .'://'. KPATH_HOST, '', KPATH_URL_BASE);

				// ..and take it off
				$segments = substr($segments, strlen($base));

				// Get the language code..
				$langCode = '/'.KenedoPlatform::p()->getLanguageUrlCode($languageTag);

				// ..and take it off
				if (strpos($segments, $langCode) === 0) {
					$segments = substr($segments, strlen($langCode));
				}

				// Make an array of the individual segments
				$segArray = explode('/',ltrim($segments, '/'));

				// Make an array of parent paths from deepest to 'flattest'
				$parentPaths = array();
				while(count($segArray)) {
					$parentPaths[] = "'".$db->getEscaped(implode('/', $segArray))."'";
					array_pop($segArray);
				}

				// Get all menu items with matching path (note that we sort by level descending, level is the depth of the menu item)
				$query = "SELECT `id`, `link` FROM `#__menu` WHERE `published` = '1' AND `path` IN (".implode(',', $parentPaths).") AND `language` IN ('*', '".$languageTag."') ORDER BY `level` DESC";
				$db->setQuery($query);
				$possibleItems = $db->loadAssocList();

				// Check links for a product listing with a matching listing id
				$match = NULL;
				foreach ($possibleItems as $possibleItem) {
					if (strstr($possibleItem['link'], 'option=com_configbox') && strstr($possibleItem['link'], 'view=product') && strstr($possibleItem['link'],'prod_id='.$productId)) {
						$match = $possibleItem['id'];
						break;
					}
				}

				return ($match) ? intval($match) : NULL;

				break;

		}

	}

	static function getMenuItemsRoute() {

		if (KenedoPlatform::getName() == 'joomla' && KenedoPlatform::p()->getVersionShort() == '1.5') {

			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__menu` WHERE `published` = '1' ORDER BY `sublevel`";
			$db->setQuery($query);
			$items = $db->loadObjectList('id');

			foreach ($items as $itemId=>&$item) {

				$item->route = $item->alias;

				if ($item->sublevel != 0) {
					if (isset($items[$item->parent])) {
						$item->route = $items[$item->parent]->route.'/'.$item->route;
					}
					else {
						unset($items[$itemId]);
					}
				}

			}
			return $items;

		}
		else {
			return array();
		}

	}

	/**
	 *
	 * @param string $viewName The name of the view in the URL that we try to make SEF
	 * @param string[] $query $query array parameter from Joomla's *BuildRoute function
	 * @return string[] $segments segments to send back
	 */
	static function getSegmentsFromCustomView($viewName, &$query) {

		$activeItemId = (!empty($query['Itemid'])) ? $query['Itemid'] : null;

		switch ($viewName) {

			case 'bcshowcase':

				// Get the view from the currently active menu item
				$activeViewName = self::getViewNameFromMenuItem($activeItemId);

				// If the active view is the bcshowcases, then append the showcase ID as segment
				// and remove query parameters view and id
				if ($activeViewName == 'bcshowcases') {


					unset($query['view'], $query['id']);

					return array(
						$query['id'],
					);

				}
				// Otherwise there's nothing to do.
				else {
					return array();
				}
				break;

			default:
				return array();
				break;

		}

	}

	static function getSegmentMatchingFromCustomView($activeViewName, $segments) {

		switch ($activeViewName) {

			case 'bcshowcases':
				return array('id');
				break;

			default:
				return array();
				break;

		}

	}

	protected static function getViewNameFromMenuItem($menuItemId) {

		$item = JFactory::getApplication()->getMenu()->getItem($menuItemId);

		if (empty($item)) {
			throw new Exception('Requested info from a non existent Joomla menu item (ID was '.$menuItemId.')');
		}

		if (empty($item->query['view'])) {
			return '';
		}

		// In case we have a customview, return viewname
		if ($item->query['view'] == 'customview') {
			return $item->query['viewname'];
		}
		else {
			return $item->query['view'];
		}

	}

	/**
	 * @param int $id Joomla menu item ID
	 * @return null|object
	 */
	static function getJoomlaMenuItemData($id) {

		// Get the menu item record
		$query = "SELECT `link` FROM `#__menu` WHERE `id` = ".intval($id);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$activeItem = $db->loadObject();

		if (!$activeItem) {
			return NULL;
		}

		// Parse the menu item's link and put it into the record (this way
		$queryString = parse_url($activeItem->link, PHP_URL_QUERY);
		$query = array();
		parse_str($queryString, $query);
		$activeItem->query = $query;

		return $activeItem;

	}

	/**
	 * Changes old view names and other things that changed between 2.6 and 3.0
	 * @param string[] $query
	 * @see ConfigboxBuildRoute()
	 */
	static function doLegacyFixesBuildRoute(&$query) {

		// Do some legacy links normalization
		if (!empty($query['view'])) {

			// Translate legacy view names (remove in CB 4)
			if ($query['view'] == 'grandorder') {
				$query['view'] = 'cart';
			}

			// Legacy, remove with 4.0
			if ($query['view'] == 'category') {
				$query['view'] = 'configuratorpage';
			}

			// Legacy, remove with 4.0
			if ($query['view'] == 'products') {
				$query['view'] = 'productlisting';
			}

			// Legacy, remove with 4.0 - Change cat_id to page_id
			if ($query['view'] == 'configuratorpage' && !empty($query['cat_id'])) {

				$ref = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
				$msg = 'A link with an outdated URL for configurator pages was found.';
				if ($ref) {
					$msg .= ' The link was found on page "'.$ref.'". Most likely an article, module or custom Configbox template. cat_id should be replaced by page_id. The link you see on the page may be processed. Check the source of the link, where you will see the URL parameters.';
				}
				$msg .= ' We keep supporting the old link until version 4 only, please change the link as soon as you can.';
				KLog::log($msg, 'deprecated');

				$query['page_id'] = $query['cat_id'];
				unset($query['cat_id']);

			}

			// Legacy, remove with 4.0 - Make checkout links com_configbox and normalize cborder_id to order_id
			if ($query['view'] == 'checkout') {
				// In case we got links from old checkout component, manipulate option param in $query
				$query['option'] = 'com_configbox';

				// In case we got a CB 2.6 cborder_id parameter, move it over to 3.0 order_id
				if (!empty($query['cborder_id'])) {
					$query['order_id'] = $query['cborder_id'];
					unset($query['cborder_id']);
				}
			}

		}

	}

	static function doLegacyFixesParseRoute(&$activeMenuItem) {

		if (empty($activeMenuItem)) {
			return;
		}

		if ($activeMenuItem->query['view'] == 'grandorder') {
			$activeMenuItem->query['view'] = 'cart';
		}
		if ($activeMenuItem->query['view'] == 'category') {
			$activeMenuItem->query['view'] = 'configuratorpage';
		}
		if ($activeMenuItem->query['view'] == 'products') {
			$activeMenuItem->query['view'] = 'productlisting';
		}

	}
}
