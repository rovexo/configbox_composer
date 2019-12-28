<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelCartposition extends KenedoModelLight {

	protected $id;
	public $position;
	public $positionDetails;

	function __construct($component = '') {
		$this->setId();
	}

	function setId($id = 0, $remember = true) {

		if ($id) {
			KLog::log('Order ID to be set to "'.$id.'".');
		}

		// Check input
		if ((int)$id != $id) {
			KLog::log('Non integer value provided as parameter. Value was "'.var_export($id,true).'"','error', KText::_('A system error occured.'));
			return false;
		}

		// If no input or 0, auto-determine id
		if ($id === 0) {
			$id = $this->autoDetermineId();
		}

		$this->forgetMemoizedData();

		if ($id !== 0) {
			KLog::log('Cart position id set to "'.$id.'".');
			$this->id = $id;
			if ($remember) {
				KSession::set('cart_position_id',$id,'com_configbox');
			}
			return true;
		}
		else {
			KLog::log('Could not set cart position id.');
			return false;
		}

	}

	function getId() {
		return $this->id;
	}

	function autoDetermineId() {

		// Check if an cart_position_id comes from request and check it (belongs to user)
		$requestId = KRequest::getInt('cart_position_id',0);
		KLog::log('During autoDetermineId found cart position id in session as "'.$requestId.'" (by request).', 'debug');
		if ($requestId) {
			$userId = ConfigboxUserHelper::getUserId();
			if ($this->userOwnsPosition($requestId, $userId) == true) {
				KLog::log('Determined cart_position_id "'.$requestId.'" by request. User check passed.', 'debug');
				return $requestId;
			}
			else {
				$msg = 'Determined cart_position_id "'.$requestId.'" by request, but user check not passed. User ID was "'.$userId.'"';
				if ($userId != 0) {
					KLog::log($msg, 'warning');
				}
				else {
					KLog::log($msg, 'debug');
				}

			}
		}

		// Else get the session id
		$sessionOrderId = KSession::get('cart_position_id',0,'com_configbox');
		KLog::log('During autoDetermineId the cart position id was found to be "'.$sessionOrderId.'" (by session).', 'debug');

		$cartId = KSession::get('cart_id',0,'com_configbox');
		KLog::log('During autoDetermineId the cart id was found to be "'.$cartId.'".', 'debug');

		// If no product id is requested, session is our best guess
		$prodId = KRequest::getInt('prod_id');
		if (!$prodId) {
			return $sessionOrderId;
		}

		$db = KenedoPlatform::getDb();

		// Check if the user has an unfinished configuration mathing the product id and cart id and session id
		$query = "SELECT `id` FROM `#__configbox_cart_positions` WHERE `prod_id` = ".(int)$prodId." AND `id` = ".(int)$sessionOrderId." AND `cart_id` = ".(int)$cartId." AND `finished` = '0' ORDER BY `created` DESC  LIMIT 1";
		$db->setQuery($query);
		$exists = (boolean)$db->loadResult();
		if ($exists) {
			return $sessionOrderId;
		}

		// If not, do the same without the cart position id
		$query = "
			SELECT `id`
			FROM `#__configbox_cart_positions`
			WHERE `prod_id` = ".(int)$prodId." AND `finished` = '0' AND `cart_id` = ".(int)$cartId."
			ORDER BY `created` DESC
			LIMIT 1";

		$db->setQuery($query);
		$dbCartPositionId = $db->loadResult();
		if ($dbCartPositionId) {
			KLog::log('During autoDetermineId the cart position id was found to be "'.$dbCartPositionId.'" (by DB, first unfinished position in cart with matching product id).', 'debug');
			return $dbCartPositionId;
		}

		KLog::log('Could not autodetermine a position id (which is fine).', 'debug');
		return 0;

	}

	function createPosition($cartId, $productId = NULL, $void = NULL, $selections = array()) {

		if (!$cartId) {
			KLog::log('Could not create position, no cart id was passed','warning');
			return false;
		}

		if (!$productId) {
			KLog::log('Could not create position, no product id was passed','warning');
			return false;
		}

		$db = KenedoPlatform::getDb();

		// Insert the position
		$time = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		$query = "
		INSERT INTO `#__configbox_cart_positions` 
			SET 
			    `cart_id` = ".intval($cartId).", 
			    `prod_id` = ".intval($productId).", 
			    `created` = '".$db->getEscaped($time)."',
			    `finished` = 0";
		$db->setQuery($query);
		$succ = $db->query();
		if (!$succ) {
			KLog::log('Could not insert position with cart id "'.$cartId.'" and prod id "'.$productId.'". Insert id was "'.$db->getQuery().'".','error',KText::_('A system error occured.'));
			return false;
		}

		$positionId = $db->insertid();

		KLog::log('Created position with cart id "'.$cartId.'" and product id "'.$productId.'". Insert id was "'.$positionId.'".');
		$this->setId($positionId);


		// Init the selections

		if (count($selections)) {
			$selections = $this->getSelectionsFromRawValues($selections);
		}
		else {
			// Get defaults
			$selections = $this->getSelectionsFromDefaults($productId);
			// Get request selections (to be removed once we got a standard way of passing through selections)
			$requestSelections = $this->getSelectionsFromRequest();
			// Merge in request selections
			$selections = $requestSelections + $selections;
		}

		// Get the configuration and set simSelections (so we can dermine later if making the selections is possible)
		$configuration = ConfigboxConfiguration::getInstance();
		$configuration->addSimSelections($selections);

		foreach ($selections as $questionId => $selection) {

			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->applies()) {
				// For questions with answers, see if the answer exists and if it is possible
				if (!empty($question->answers)) {
					if (isset($question->answers[$selection])) {
						if ($question->answers[$selection]->applies()) {
							$configuration->setSelection($questionId, $selection);
						}
					}
				}
				else {
					$configuration->setSelection($questionId, $selection);
				}

			}

		}

		$configuration->unsetSimSelections();

		return $positionId;

	}

	protected function getSelectionsFromDefaults($productId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$questionIds = $ass['product_to_element'][$productId];

		$selections = array();

		foreach ($questionIds as $questionId) {
			$question = ConfigboxQuestion::getQuestion($questionId);
			if ($question->getInitialValue() !== null) {
				$selections[$question->id] = $question->getInitialValue();
			}
		}

		return $selections;

	}

	/**
	 * @param string[] $rawValues Keys are question IDs, values are selections
	 *
	 * @return string[] $selections Selections compatible with ConfigboxCartPosition::setSelections
	 */
	protected function getSelectionsFromRawValues($rawValues) {

		$selections = array();

		foreach ($rawValues as $questionId=>$selection) {
			$selection = urldecode($selection);
			$question = ConfigboxQuestion::getQuestion($questionId);
			if ($question) {
				if (count($question->answers)) {
					if (!empty($question->answers[$selection])) {
						$selections[$questionId] = $selection;
					}
				}
				else {
					$selections[$questionId] = $selection;
				}
			}
		}

		return $selections;
	}

	protected function getSelectionsFromRequest() {

		$selections = array();
		$requests = KRequest::getArray('selections', array());

		foreach ($requests as $questionId=>$selection) {
			$selection = urldecode($selection);
			$question = ConfigboxQuestion::getQuestion($questionId);
			if ($question) {
				if (count($question->answers)) {
					if (!empty($question->answers[$selection])) {
						$selections[$questionId] = $selection;
					}
				}
				else {
					$selections[$questionId] = $selection;
				}
			}
		}

		return $selections;

	}

	function forgetMemoizedData() {
		$this->position = NULL;
		$this->positionDetails = NULL;
	}

	function resetPositionDataCache() {

		$this->id = NULL;
		$this->position = NULL;
		$this->positionDetails = NULL;

		KSession::set('cart_position_id', 0, 'com_configbox');
	}

	function userOwnsPosition($positionId, $userId = NULL) {

		// Get the user ID
		if ($userId === NULL) {
			$userId = ConfigboxUserHelper::getUserId();
		}

		$query = "
		SELECT c.user_id
		FROM `#__configbox_cart_positions` AS p
		LEFT JOIN `#__configbox_carts` AS c ON c.id = p.cart_id
		WHERE p.id = ".intval($positionId);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$cartUserId = $db->loadResult();

		KLog::log('Comparing user ID for position id "'.$positionId.'": Current user id is "'.$userId.'", cart user ID is "'.$cartUserId.'".', 'debug');

		if ($cartUserId == $userId) {
			return true;
		}
		else {
			return false;
		}

	}

	function getPosition($positionId) {

		if (!$positionId) {
			return NULL;
		}

		if (!$this->position) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_cart_positions` WHERE `id` = ".intval($positionId);
			$db->setQuery($query);
			$this->position = $db->loadObject();
		}

		return $this->position;

	}

	/**
	 * Gives you all details about the position. Expects the right cart position id set (using setId)
	 *
	 * @param null|object $baseData You can provide the raw position data (from getPosition) if you got them for performance
	 * @return null|ConfigboxCartPositionData
	 * @throws Exception If no position id was set
	 */
	function getPositionDetails($baseData = NULL) {

		$positionId = $this->getId();

		if (!$positionId) {
			throw new Exception('getPositionDetails called without a set cart position id.');
		}

		if ($baseData == NULL) {
			$position = $this->getPosition($positionId);
		}
		else {
			$position = $baseData;
		}
		/** @var ConfigboxCartPositionData $position */

		if (!$position) {
			KLog::log('getPositionDetails called. Could not load base position data', 'warning');
			return NULL;
		}

		if (empty($position->quantity)) {
			$position->quantity = 1;
		}

		$productModel = KenedoModel::getModel('ConfigboxModelProduct');

		// Get the product data
		$position->productData = $productModel->getProduct($position->prod_id);

		// Store if the product is configurable
		$position->isConfigurable = ($position->productData->isConfigurable != 0);

		// Set the order's product title (see later for product title override)
		$position->productTitle = $position->productData->title;

		// Product base prices
		$position->baseProductBasePriceNet		= $position->productData->basePriceNet;
		$position->baseProductBasePriceTax		= $position->productData->basePriceTax;
		$position->baseProductBasePriceGross	= $position->baseProductBasePriceNet + $position->baseProductBasePriceTax;

		// Same for recurring
		$position->baseProductBasePriceRecurringNet 	= $position->productData->basePriceRecurringNet;
		$position->baseProductBasePriceRecurringTax 	= $position->productData->basePriceRecurringTax;
		$position->baseProductBasePriceRecurringGross 	= $position->baseProductBasePriceRecurringNet + $position->baseProductBasePriceRecurringTax;

		// Init the order totals - Not to be confused with the baseProductBasePrice, which is just the product base price and not any total
		$position->baseTotalUnreducedNet 	= $position->productData->basePriceNet;
		$position->baseTotalUnreducedTax 	= $position->productData->basePriceTax;
		$position->baseTotalUnreducedGross 	= $position->baseTotalUnreducedNet + $position->baseTotalUnreducedTax;

		// Same for recurring
		$position->baseTotalUnreducedRecurringNet 	= $position->productData->basePriceRecurringNet;
		$position->baseTotalUnreducedRecurringTax 	= $position->productData->basePriceRecurringTax;
		$position->baseTotalUnreducedRecurringGross = $position->baseTotalUnreducedRecurringNet + $position->baseTotalUnreducedRecurringTax;

		// Init order weight
		$position->weight = $position->productData->baseweight;


		$configuration = ConfigboxConfiguration::getInstance($position->id);
		$selectedQuestionIds = $configuration->getQuestionIdsWithSelection(false);

		// Sort the questions per page and question
		$selectedQuestionIds = $this->getQuestionIdsSorted($selectedQuestionIds, $position->productData->id);


		$position->selections = array();

		foreach ($selectedQuestionIds as $questionId) {

			/**
			 * @var ConfigboxCartPositionSelectionsData $selection
			 */
			$selection = new stdClass;

			$question = ConfigboxQuestion::getQuestion($questionId);

			// Base data
			$selection->type = $question->getType();
			$selection->questionId = $questionId;
			$selection->questionTitle = $question->title;
			$selection->selection = $configuration->getSelection($questionId);

			// Workaround for any no-longer-selected checkbox questions that are still selected with '' value
			if ($selection->type == 'checkbox' && $selection->selection == '') {
				continue;
			}

			$selection->outputValue = $question->getOutputValue($configuration->getSelection($questionId));
			$selection->showInOverviews = ($question->show_in_overview != 0);

			// Selection pricing
			$selection->basePriceNet 	= $question->getPrice(true, true);
			$selection->basePriceGross 	= $question->getPrice(false, true);
			$selection->basePriceTax 	= $selection->basePriceGross - $selection->basePriceNet;

			// Selection recurring pricing
			$selection->basePriceRecurringGross 	= $question->getPriceRecurring(false, true);
			$selection->basePriceRecurringNet 		= $question->getPriceRecurring(true, true);
			$selection->basePriceRecurringTax 		= $selection->basePriceRecurringGross - $selection->basePriceRecurringNet;

			// Add static price overrides
			$selection->priceOverrides = '[]';
			$selection->priceRecurringOverrides = '[]';
			if (count($question->answers) && isset($question->answers[$selection->selection])) {
				$selection->priceOverrides = $question->answers[$selection->selection]->price_overrides;
				$selection->priceRecurringOverrides = $question->answers[$selection->selection]->price_recurring_overrides;
			}

			// Add price calculation overrides
			$selection->priceCalculationOverrides = '[]';
			$selection->priceRecurringCalculationOverrides = '[]';
			if (count($question->answers) && isset($question->answers[$selection->selection])) {
				$selection->priceCalculationOverrides = $question->answers[$selection->selection]->price_calculation_overrides;
				$selection->priceRecurringCalculationOverrides = $question->answers[$selection->selection]->price_recurring_calculation_overrides;
			}

			// Selection weight
			$selection->weight = $question->getWeight();

			// Append currency prices
			ConfigboxCurrencyHelper::appendCurrencyPrices($selection);

			// Add the selection to the position's selections
			$position->selections[] = $selection;

		}

		// Add selection's price and weight to the cart position
		foreach ($position->selections as $selection) {

			// Increment the position totals
			$position->baseTotalUnreducedNet 			+= round($selection->basePriceNet, 4);
			$position->baseTotalUnreducedRecurringNet 	+= round($selection->basePriceRecurringNet, 4);

			// Increment the weight
			$position->weight += $selection->weight;

		}

		// Deal with position product title overrides
		foreach ($position->selections as $selection) {

			$question = ConfigboxQuestion::getQuestion($selection->questionId);

			// Set the position's product title if the question setting is there
			if (!empty($question->asproducttitle) && $question->getOutputValue($selection->selection)) {
				$position->productTitle = $question->getOutputValue($selection->selection);
				break;
			}
		}

		// Multiply position prices with quantity now
		$position->baseTotalUnreducedNet = $position->baseTotalUnreducedNet * $position->quantity;
		$position->baseTotalUnreducedRecurringNet = $position->baseTotalUnreducedRecurringNet * $position->quantity;

		$position->baseTotalUnreducedGross = ConfigboxPrices::getPositionPriceGross($position->baseTotalUnreducedNet, $position->productData->id, true);
		$position->baseTotalUnreducedTax = $position->baseTotalUnreducedGross - $position->baseTotalUnreducedNet;

		$position->baseTotalUnreducedRecurringGross = ConfigboxPrices::getPositionPriceRecurringGross($position->baseTotalUnreducedRecurringNet, $position->productData->id, true);
		$position->baseTotalUnreducedRecurringTax = $position->baseTotalUnreducedRecurringGross - $position->baseTotalUnreducedRecurringNet;

		// Indicate if position got recurring pricing
		$position->usesRecurring = ($position->baseTotalUnreducedRecurringNet != 0);

		// Append the currency prices
		ConfigboxCurrencyHelper::appendCurrencyPrices($position);

		if (function_exists('postGetPositionDetails')) {
			postGetPositionDetails($position);
		}

		return $position;
	}

	function copyPosition($positionId = NULL) {

		if ($positionId !== NULL) {
			$this->setId($positionId);
		}

		$position = $this->getPosition($positionId);
		if (!$position) return false;

		// Get the current date/time
		$time = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		$configuration = ConfigboxConfiguration::getInstance($positionId);
		$selections = $configuration->getSelections();

		// Insert the order
		$db = KenedoPlatform::getDb();
		$query = "
		INSERT INTO `#__configbox_cart_positions` 
		SET 
			`cart_id` = ".intval($position->cart_id).", 
			`prod_id` = ".intval($position->prod_id).", 
			`created` = '".$db->getEscaped($time)."'";
		$db->setQuery($query);
		$db->query();
		$newPositionId = $db->insertid();

		$this->setId($newPositionId);

		$configuration = ConfigboxConfiguration::getInstance($newPositionId);
		foreach ($selections as $questionId=>$selection) {
			$configuration->setSelection($questionId, $selection);
		}

		return $newPositionId;

	}

	/**
	 * Get's you the element ids that apply given the current selections in ConfigboxConfiguration
	 * Takes the elements of the ConfigboxConfiguration's product
	 * @return int[]
	 */
	function getCurrentlyApplyingQuestionIds() {

		$configuration = ConfigboxConfiguration::getInstance();
		$productId = $configuration->getProductId();

		$ass = ConfigboxCacheHelper::getAssignments();
		$questionIds = $ass['product_to_element'][$productId];

		$applyingQuestionIds = array();

		foreach ($questionIds as &$elementId) {

			$question = ConfigboxQuestion::getQuestion($elementId);

			if ($question && $question->applies()) {

				if (count($question->answers) == 0) {
					$good = true;
				}
				else {
					$good = false;
					foreach ($question->answers as $answer) {
						if ($answer->applies()) {
							$good = true;
							break;
						}
					}
				}

				if ($good) {
					$applyingQuestionIds[] = $question->id;
				}

			}
		}

		return $applyingQuestionIds;

	}

	/**
	 * Returns $questionIds sorted by page and question ordering
	 * @param int[] $questionIds
	 * @param int $productId
	 * @return int[]
	 */
	function getQuestionIdsSorted($questionIds, $productId) {

		if (!$productId) {
			return $questionIds;
		}

		if(count($questionIds) == 0) {
			return $questionIds;
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$questionIds = array_flip($questionIds);

		$return = array();
		foreach ($ass['product_to_element'][$productId] as $id) {
			if (isset($questionIds[$id])) {
				$return[] = $id;
			}
		}
		return $return;
	}

	/**
	 * @throws Exception When it there is a question ID in the selections that does not exist
	 * @param array[] $proposedChanges The proposed changes (see method description for structure)
	 * @param int[] $originallyApplyingElementIds IDs of elements that applied before processing a change started (these will be ignored for auto-selection)
	 * @return array $inconsistencies The inconsistencies (see method description for structure)
	 * @deprecated Use ConfigboxRulesHelper::getInconsistencies instead
	 */
	function getInconsistencies($proposedChanges = array(), $originallyApplyingElementIds = array()) {
		return ConfigboxRulesHelper::getInconsistencies($this->getId(), $proposedChanges, $originallyApplyingElementIds);
	}
	/**
	 * Returns an array with instructions on which elements/options to show/hide on the configurator page
	 * Data is used in the JS after a selection update
	 *
	 * @param int $pageId Page id of the configurator page you want to check
	 * @return array Array with data see method description
	 */
	function getPageItemVisibility($pageId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$questionIds = $ass['page_to_element'][$pageId];
		$pageQuestions = array();
		$pageAnswers = array();
		foreach ($questionIds as $questionId) {
			$question = ConfigboxQuestion::getQuestion($questionId);
			$pageQuestions[$questionId] = $question->applies();
			foreach ($question->answers as $answer) {
				if ($pageQuestions[$questionId] == false) {
					$pageAnswers[$questionId][$answer->id] = false;
				}
				else {
					$pageAnswers[$questionId][$answer->id] = $answer->applies();
				}
			}
		}
		return array('questions'=>$pageQuestions, 'answers'=>$pageAnswers);

	}

	/**
	 *
	 * Gets min/max values for each element on the new configuration
	 *
	 * $values[$element->id]['minval'] = $element->getMinimumValue();
	 * $values[$element->id]['maxval'] = $element->getMaximumValue();
	 *
	 * @param int $pageId Page id of the configurator page you want to check
	 * @return array Array holding info on new min/max values per element, see method description
	 */
	function getDynamicValidationValues($pageId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$elementIds = $ass['page_to_element'][$pageId];
		$values = array();
		foreach ($elementIds as $elementId) {
			$element = ConfigboxQuestion::getQuestion($elementId);
			if (!$element->calcmodel_id_min_val && !$element->calcmodel_id_min_val) {
				continue;
			}
			$values[$elementId]['minval'] = $element->getMinimumValue();
			$values[$elementId]['maxval'] = $element->getMaximumValue();
		}

		return $values;

	}

	/**
	 *
	 * This function returns a nested associative array with all prices and selection output values used for updating prices and the selection overview
	 * Prices are in user currency and net/gross by configuration
	 *
	 * STRUCTURE OF RETURN ARRAY
	 *
	 * For all price related values there is a counterpart with suffix Formatted, giving you the localized and currency formatted value (100 -> EUR 100,00)
	 *
	 * $pricing['total']['productPrice'] 			float product base price (net/gross depending on B2B/B2C mode)
	 * $pricing['total']['productPriceRecurring']	float product base price recurring (net/gross depending on B2B/B2C mode)
	 *
	 * $pricing['total']['priceNet'] 				float product total price * quantity
	 * $pricing['total']['priceGross']				float
	 * $pricing['total']['priceTax']				float
	 * $pricing['total']['priceRecurringNet']		float
	 * $pricing['total']['priceRecurringGross']		float
	 * $pricing['total']['priceRecurringTax']		float
	 *
	 * $pricing['pages'][$pageId]['price']				float	Configurator page subtotal
	 * $pricing['pages'][$pageId]['priceRecurring']
	 *
	 * $pricing['questions'][$questionId]['price'] 				float 	Element price
	 * $pricing['questions'][$questionId]['priceRecurring']
	 * $pricing['questions'][$questionId]['outputValue']		string 	Parsed element selection outputValue @see ConfigboxElement::getOutputValue
	 * $pricing['questions'][$questionId]['showInOverview']		int 	1 if it should be shown in the selection overview block @see view block_pricing, 0 if not
	 *
	 * $pricing['answers'][$answerId]['price']							float 	Price of an xref
	 * $pricing['answers'][$answerId]['priceRecurring']
	 *
	 * $pricing['total']['pricePerItemNet']								float	Product total price per item
	 * $pricing['total']['pricePerItemTax']								float
	 * $pricing['total']['pricePerItemGross']							float
	 *
	 *
	 * $pricing['delivery']['title'] 				string	Delivery rate title
	 * $pricing['delivery']['taxRate'] 				float	Tax rate
	 * $pricing['delivery']['priceNet'] 			float
	 * $pricing['delivery']['priceTax']
	 * $pricing['delivery']['priceGross']
	 *
	 * $pricing['payment']['title'] 				string	Payment method title
	 * $pricing['payment']['taxRate'] 				float	Tax rate
	 * $pricing['payment']['priceNet'] 				float
	 * $pricing['payment']['priceTax'] 				float
	 * $pricing['payment']['priceGross'] 			float
	 *
	 * $pricing['taxes'][$taxRate]					float	Sum of taxes per tax rate
	 *
	 * $pricing['totalPlusExtras']['priceNet']		float	product total * quantity + delivery + shipping
	 * $pricing['totalPlusExtras']['priceTax']		float
	 * $pricing['totalPlusExtras']['priceGross']	float
	 *
	 * @param int $positionId Position ID we deal with
	 * @param boolean $skipDeliveryAndPayment To force skipping of delivery and payment method pricing
	 * @return array $pricing array holding update value (see method description)
	 *
	 */
	function getPricing($positionId = NULL, $skipDeliveryAndPayment = false) {

		if (!$positionId) {
			$positionId = $this->getId();
		}

		$position = $this->getPosition($positionId);

		if (!$position) {
			KLog::log('getPricing called, but could not load position details data', 'warning');
			return array();
		}

		$pricing['quantity'] = $position->quantity;

		$productId = $position->prod_id;

		$productModel = KenedoModel::getModel('ConfigboxModelProduct');
		$product = $productModel->getProduct($productId);

		// Set price labels
		$pricing['priceLabel'] = $product->priceLabel;
		$pricing['priceRecurringLabel'] = $product->priceLabelRecurring;

		$configuration = ConfigboxConfiguration::getInstance($positionId);
		$selections = $configuration->getSelections();

		$pricing['questions'] = array();
		$pricing['answers'] = array();

		// Set product price (see product base price)
		$pricing['total']['productPrice'] 			= ConfigboxPrices::getProductPrice($productId, NULL, false);
		$pricing['total']['productPriceNet'] 			= ConfigboxPrices::getProductPrice($productId, true, false);
		$pricing['total']['productPriceGross'] 			= ConfigboxPrices::getProductPrice($productId, false, false);

		$pricing['total']['productPriceRecurring'] 	= ConfigboxPrices::getProductPriceRecurring($productId, NULL, false);
		$pricing['total']['productPriceNetRecurring'] 	= ConfigboxPrices::getProductPriceRecurring($productId, true, false);
		$pricing['total']['productPriceGrossRecurring'] 	= ConfigboxPrices::getProductPriceRecurring($productId, false, false);

		// Init total price for later adding
		$pricing['total']['price'] 				= $pricing['total']['productPrice'];
		$pricing['total']['priceRecurring'] 	= $pricing['total']['productPriceRecurring'];


		$pricing['total']['priceNet'] 	= ConfigboxPrices::getProductPrice($productId, true, false);
		$pricing['total']['priceGross']	= ConfigboxPrices::getProductPrice($productId, false, false);
		$pricing['total']['priceTax']	= $pricing['total']['priceGross'] - $pricing['total']['priceNet'];

		$pricing['total']['priceRecurringNet']		= ConfigboxPrices::getProductPriceRecurring($productId, true, false);
		$pricing['total']['priceRecurringGross']	= ConfigboxPrices::getProductPriceRecurring($productId, false, false);
		$pricing['total']['priceRecurringTax']		= $pricing['total']['priceRecurringGross'] - $pricing['total']['priceRecurringNet'];

		$pricing['pages'] = array();
		$pricing['questions'] = array();
		$pricing['answers'] = array();

		$ass = ConfigboxCacheHelper::getAssignments();
		$questionIds = !empty($ass['product_to_element'][$productId]) ? $ass['product_to_element'][$productId] : array();

		foreach ($questionIds as $questionId) {

			$pageId = $ass['element_to_page'][$questionId];

			$question = ConfigboxQuestion::getQuestion($questionId);

			foreach($question->answers as $answer) {
				$pricing['answers'][$answer->id]['price'] = $answer->getPrice();
				$pricing['answers'][$answer->id]['priceRecurring'] = $answer->getPriceRecurring();
				$pricing['answers'][$answer->id]['questionId'] = $questionId;
			}

			$pricing['questions'][$questionId]['questionTitle'] = $question->title;
			$pricing['questions'][$questionId]['outputValue'] = $question->getOutputValue($configuration->getSelection($questionId));
			$pricing['questions'][$questionId]['showInOverview'] = $question->show_in_overview;
			$pricing['questions'][$questionId]['showButHidden'] = !isset($selections[$questionId]);

			// Preparing CSS classes for display in the overview view
			$classesListItem = [];
			$classesListItem[] = 'question-item';
			$classesListItem[] = 'question-item-'.$questionId;

			if ($pricing['questions'][$questionId]['showButHidden'] == true) {
				$classesListItem[] = 'hidden-item';
			}

			$pricing['questions'][$questionId]['cssClassesList'] = implode(' ', $classesListItem);
			$pricing['questions'][$questionId]['cssClassesOutputValue'] = 'question-item-outputvalue question-item-outputvalue-'.$questionId;
			$pricing['questions'][$questionId]['cssClassesPrice'] = 'item-price pricing-question pricing-question-'.$questionId;


			$price          = $question->getPrice();
			$priceRecurring = $question->getPriceRecurring();

			$priceNet 	        = $question->getPrice(true);
			$priceRecurringNet 	= $question->getPriceRecurring(true);

			$pricing['questions'][$questionId]['price'] = $price;
			$pricing['questions'][$questionId]['priceRecurring'] = $priceRecurring;

			if (!isset($pricing['pages'][$pageId]['price'])) {
				$pricing['pages'][$pageId]['price'] = 0;
				$pricing['pages'][$pageId]['priceRecurring'] = 0;
			}

			$pricing['pages'][$pageId]['price'] += $price;
			$pricing['pages'][$pageId]['priceRecurring'] += $priceRecurring;

			if (isset($selections[$questionId])) {

				$pricing['total']['price'] 					+= $price;
				$pricing['total']['priceRecurring'] 		+= $priceRecurring;

				$pricing['total']['priceNet']				+= $priceNet;
				$pricing['total']['priceRecurringNet']		+= $priceRecurringNet;

			}

		} // End of question loop
		unset($question);

		$pricing['total']['priceGross'] 			= ConfigboxPrices::getPositionPriceGross($pricing['total']['priceNet'], $productId);
		$pricing['total']['priceTax'] 				= $pricing['total']['priceGross'] - $pricing['total']['priceNet'];

		$pricing['total']['priceRecurringGross'] 	= ConfigboxPrices::getPositionPriceRecurringGross($pricing['total']['priceRecurringNet'], $productId);
		$pricing['total']['priceRecurringTax'] 		= $pricing['total']['priceRecurringGross'] - $pricing['total']['priceRecurringNet'];

		$pricing['total']['pricePerItem'] 					= $pricing['total']['price'] / $pricing['quantity'];
		$pricing['total']['pricePerItemRecurring'] 			= $pricing['total']['priceRecurring'] / $pricing['quantity'];

		$pricing['total']['pricePerItemNet']				= $pricing['total']['priceNet'] / $pricing['quantity'];
		$pricing['total']['pricePerItemTax']				= $pricing['total']['priceTax'] / $pricing['quantity'];
		$pricing['total']['pricePerItemGross']				= $pricing['total']['priceGross'] / $pricing['quantity'];

		$pricing['total']['pricePerItemRecurringNet']		= $pricing['total']['priceRecurringNet'] / $pricing['quantity'];
		$pricing['total']['pricePerItemRecurringTax']		= $pricing['total']['priceRecurringTax'] / $pricing['quantity'];
		$pricing['total']['pricePerItemRecurringGross']		= $pricing['total']['priceRecurringGross'] / $pricing['quantity'];


		foreach ($pricing['answers'] AS &$item) {
			$item['priceFormatted'] = cbprice($item['price']);
			$item['priceRecurringFormatted'] = cbprice($item['priceRecurring']);
		}
		foreach ($pricing['questions'] AS &$item) {
			$item['priceFormatted'] = cbprice($item['price']);
			$item['priceRecurringFormatted'] = cbprice($item['priceRecurring']);
		}
		foreach ($pricing['pages'] AS &$item) {
			$item['priceFormatted'] = cbprice($item['price']);
			$item['priceRecurringFormatted'] = cbprice($item['priceRecurring']);
		}

		// Set the product's tax rates
		$pricing['total']['productTaxRate'] 				= ConfigboxPrices::getProductTaxRate($productId);
		$pricing['total']['productTaxRateRecurring'] 		= ConfigboxPrices::getProductTaxRateRecurring($productId);

		// Init the totals plus delivery and payment (delivery and payment amounts come later in the method)
		$pricing['totalPlusExtras']['priceNet']		= $pricing['total']['priceNet'];
		$pricing['totalPlusExtras']['priceTax']		= $pricing['total']['priceTax'];
		$pricing['totalPlusExtras']['priceGross']	= $pricing['total']['priceGross'];

		// Init the taxes
		$pricing['taxes'] = array();
		// Normalize the tax rate to 3 decimals to avoid duplicate entries like 20 and 20.00
		$taxRateKey = number_format($pricing['total']['productTaxRate'], 3, '.', '');
		$pricing['taxes'][$taxRateKey] = $pricing['total']['priceTax'];

		// Add delivery amounts
		$pricing['delivery'] = NULL;
		// Add payment amounts
		$pricing['payment'] = NULL;

		if ($skipDeliveryAndPayment == false) {

			if ($product->pm_show_delivery_options || $product->pm_show_payment_options) {

				$positionDetails = $this->getPositionDetails();

				if ($product->pm_show_delivery_options) {
					$this->addDeliveryData($pricing, $positionDetails);
				}

				if ($product->pm_show_payment_options) {
					$this->addPaymentData($pricing, $positionDetails);
				}

			}

		}

		// Create the formatted prices
		$pricing['total']['pricePerItemNetFormatted']				= cbprice($pricing['total']['pricePerItemNet']);
		$pricing['total']['pricePerItemTaxFormatted']				= cbprice($pricing['total']['pricePerItemTax']);
		$pricing['total']['pricePerItemGrossFormatted']				= cbprice($pricing['total']['pricePerItemGross']);

		$pricing['total']['pricePerItemRecurringNetFormatted']		= cbprice($pricing['total']['pricePerItemRecurringNet']);
		$pricing['total']['pricePerItemRecurringTaxFormatted']		= cbprice($pricing['total']['pricePerItemRecurringTax']);
		$pricing['total']['pricePerItemRecurringGrossFormatted']	= cbprice($pricing['total']['pricePerItemRecurringGross']);

		$pricing['total']['pricePerItemFormatted'] 			= cbprice($pricing['total']['pricePerItem']);
		$pricing['total']['pricePerItemRecurringFormatted'] = cbprice($pricing['total']['pricePerItemRecurring']);

		$pricing['total']['priceFormatted'] 				= cbprice($pricing['total']['price']);
		$pricing['total']['priceRecurringFormatted'] 		= cbprice($pricing['total']['priceRecurring']);

		$pricing['total']['priceNetFormatted']				= cbprice($pricing['total']['priceNet']);
		$pricing['total']['priceTaxFormatted']				= cbprice($pricing['total']['priceTax']);
		$pricing['total']['priceGrossFormatted']			= cbprice($pricing['total']['priceGross']);

		$pricing['total']['priceRecurringNetFormatted']		= cbprice($pricing['total']['priceRecurringNet']);
		$pricing['total']['priceRecurringTaxFormatted']		= cbprice($pricing['total']['priceRecurringTax']);
		$pricing['total']['priceRecurringGrossFormatted']	= cbprice($pricing['total']['priceRecurringGross']);

		$pricing['total']['productPriceFormatted'] 			= cbprice($pricing['total']['productPrice']);
		$pricing['total']['productPriceNetFormatted'] 		= cbprice($pricing['total']['productPriceNet']);
		$pricing['total']['productPriceGrossFormatted'] 	= cbprice($pricing['total']['productPriceGross']);

		$pricing['total']['productPriceRecurringFormatted'] 		= cbprice($pricing['total']['productPriceRecurring']);
		$pricing['total']['productPriceNetRecurringFormatted'] 		= cbprice($pricing['total']['productPriceNetRecurring']);
		$pricing['total']['productPriceGrossRecurringFormatted'] 	= cbprice($pricing['total']['productPriceGrossRecurring']);

		$pricing['totalPlusExtras']['priceNetFormatted']	= cbprice($pricing['totalPlusExtras']['priceNet']);
		$pricing['totalPlusExtras']['priceTaxFormatted']	= cbprice($pricing['totalPlusExtras']['priceTax']);
		$pricing['totalPlusExtras']['priceGrossFormatted']	= cbprice($pricing['totalPlusExtras']['priceGross']);

		// Add the formatted tax values
		$pricing['taxesFormatted'] = array();
		foreach ($pricing['taxes'] as $key=>$tax) {
			// Normalize the tax rate to 3 decimals to avoid duplicate entries like 20 and 20.00
			$taxRateKey = number_format($key, 3, '.', '');
			$pricing['taxesFormatted'][$taxRateKey] = cbprice($tax);
		}

		$tree = array();
		$tree['pages'] = array();

		foreach ($questionIds as $questionId) {
			$pageId = $ass['element_to_page'][$questionId];

			if (!isset($tree['pages'][$pageId]['questions'][$questionId])) {
				$tree['pages'][$pageId]['questions'][$questionId] = array();
			}

			// Set titles
			$tree['pages'][$pageId]['pageTitle'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 3, $pageId);

			// Set output value
			$tree['pages'][$pageId]['questions'][$questionId] = $pricing['questions'][$questionId];

			// Set page prices
			$tree['pages'][$pageId]['price'] = $pricing['pages'][$pageId]['price'];
			$tree['pages'][$pageId]['priceRecurring'] = $pricing['pages'][$pageId]['priceRecurring'];

		}

		$pricing['tree'] = $tree;

		// Send the data through the override function
		if (function_exists('postGetPricing')) {
			postGetPricing($pricing);
		}

		return $pricing;
	}

	function addDeliveryData(&$pricing, $positionDetails) {

		if (CbSettings::getInstance()->get('disable_delivery') == false) {

			$cartModel = KenedoModel::getModel('ConfigboxModelCart');
			$cartDetails = $cartModel->getCartDetails($positionDetails->cart_id);

			$weight        = $cartDetails->weight + $positionDetails->weight;
			$maxDimensions = array();
			$cheapestOnly  = true;

			$deliveryOptions = KenedoObserver::triggerEvent('onConfigboxGetDeliveryOptions', array(
				$cartDetails->id,
				$cartDetails->userInfo,
				$weight,
				$maxDimensions,
				$cheapestOnly
			), true);

			if (count($deliveryOptions)) {
				$pricing['delivery']['title']               = $deliveryOptions[0]->rateTitle;
				$pricing['delivery']['taxRate']             = $deliveryOptions[0]->taxRate;
				$pricing['delivery']['priceNet']            = $deliveryOptions[0]->priceNet;
				$pricing['delivery']['priceTax']            = $deliveryOptions[0]->priceTax;
				$pricing['delivery']['priceGross']          = $deliveryOptions[0]->priceGross;
				$pricing['delivery']['priceNetFormatted']   = cbprice($pricing['delivery']['priceNet']);
				$pricing['delivery']['priceTaxFormatted']   = cbprice($pricing['delivery']['priceTax']);
				$pricing['delivery']['priceGrossFormatted'] = cbprice($pricing['delivery']['priceGross']);

				$pricing['totalPlusExtras']['priceNet'] += $pricing['delivery']['priceNet'];
				$pricing['totalPlusExtras']['priceTax'] += $pricing['delivery']['priceTax'];
				$pricing['totalPlusExtras']['priceGross'] += $pricing['delivery']['priceGross'];

				if ($pricing['delivery']['taxRate'] != 0 && $pricing['delivery']['priceTax'] != 0) {

					// Normalize the tax rate to 3 decimals to avoid duplicate entries like 20 and 20.00
					$taxRateKey = number_format($pricing['delivery']['taxRate'], 3, '.', '');

					// Init tax rate entry if necessary
					if (!isset($pricing['taxes'][$taxRateKey])) {
						$pricing['taxes'][$taxRateKey] = 0;
					}
					// Add tax amount to tax rate entry
					$pricing['taxes'][$taxRateKey] += $pricing['delivery']['priceTax'];

				}

			}

		}

	}

	function addPaymentData(&$pricing, $positionDetails) {

		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartDetails = $cartModel->getCartDetails($positionDetails->cart_id);

		$baseDeliveryGross = isset($pricing['delivery']['priceGross']) ? $pricing['delivery']['priceGross'] : 0;
		$baseTotalGross = $cartDetails->baseTotalGross + $positionDetails->baseTotalUnreducedGross + $baseDeliveryGross;

		$paymentOptions = KenedoObserver::triggerEvent('onConfigboxGetPaymentOptions',array($cartDetails->userInfo, $baseTotalGross), true);

		if (count($paymentOptions)) {
			$pricing['payment']['title'] 				= $paymentOptions[0]->title;
			$pricing['payment']['taxRate'] 				= $paymentOptions[0]->taxRate;
			$pricing['payment']['priceNet'] 			= $paymentOptions[0]->priceNet;
			$pricing['payment']['priceTax'] 			= $paymentOptions[0]->priceTax;
			$pricing['payment']['priceGross'] 			= $paymentOptions[0]->priceGross;
			$pricing['payment']['priceNetFormatted']	= cbprice($pricing['payment']['priceNet']);
			$pricing['payment']['priceTaxFormatted']	= cbprice($pricing['payment']['priceTax']);
			$pricing['payment']['priceGrossFormatted']	= cbprice($pricing['payment']['priceGross']);

			$pricing['totalPlusExtras']['priceNet']		+= $pricing['payment']['priceNet'];
			$pricing['totalPlusExtras']['priceTax']		+= $pricing['payment']['priceTax'];
			$pricing['totalPlusExtras']['priceGross']	+= $pricing['payment']['priceGross'];

			if ($pricing['payment']['taxRate'] != 0 && $pricing['payment']['priceTax'] != 0) {

				// Normalize the tax rate to 3 decimals to avoid duplicate entries like 20 and 20.00
				$taxRateKey = number_format($pricing['payment']['taxRate'], 3, '.', '');

				// Init tax rate entry if necessary
				if (!isset($pricing['taxes'][strval($pricing['payment']['taxRate'])])) {
					$pricing['taxes'][$taxRateKey] = 0;
				}
				// Add tax amount to tax rate entry
				$pricing['taxes'][$taxRateKey] += $pricing['payment']['priceTax'];
			}

		}

	}

	function removePosition($positionId) {

		// Remove configurations
		$configuration = ConfigboxConfiguration::getInstance($positionId);
		// Remove possibly presend selections in session data
		$configuration->deleteSelectionsFromSession();
		// Remove selections from the DB table
		$configuration->deleteSelectionsFromDb();

		// Remove entry in positions table
		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_cart_positions` WHERE `id` = ".intval($positionId);
		$db->setQuery($query);
		if (!$db->query()) return false;

		// Reset order and reset cart data
		$this->resetPositionDataCache();

		// Avoid any stale memo-cache problems
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartModel->forgetMemoizedData();

		return true;

	}

	function getMissingSelections($pageId = NULL, $positionId = NULL) {

		$positionId = ($positionId) ? $positionId : $this->getId();

		$configuration = ConfigboxConfiguration::getInstance($positionId);
		$productId = $configuration->getProductId();

		$ass = ConfigboxCacheHelper::getAssignments();

		if ($pageId) {
			$questionIds = $ass['page_to_element'][$pageId];
		}
		else {
			$questionIds = $ass['product_to_element'][$productId];
		}

		$missingQuestions = array();

		$selections = $configuration->getSelections(false);

		foreach ($questionIds as $questionId) {
			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->published && $question->required) {
				if ($question->applies() && !isset($selections[$questionId])) {
					$missingQuestion = array(
						'id' => $question->id,
						'title' => $question->title,
						'productId' => $productId,
						'pageId' => $question->page_id,
						'message' => KText::_('MESSAGE_MISSING_SELECTION'),
					);
					$missingQuestions[] = $missingQuestion;
				}
			}
		}

		return $missingQuestions;

	}

	function updateQuantity($positionId, $qty) {
		return $this->editPosition($positionId, array('quantity'=>$qty));
	}

	function editPosition($positionId, $array) {

		$db = KenedoPlatform::getDb();
		$set = array();
		foreach ($array as $field=>$value) {
			$set[] = "`".$field."` = '".$db->getEscaped($value)."'";
		}
		$set = implode(', ',$set);

		$query = "UPDATE `#__configbox_cart_positions`";
		$query .= " SET ".$set." WHERE `id` = ".intval($positionId);
		$db->setQuery($query);
		$result = $db->query();

		if ($result == false) {
			throw new Exception('Could not edit cart position data. See log files');
		}

		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartModel->forgetMemoizedData();
		$this->forgetMemoizedData();

		return true;

	}

	/**
	 *
	 * @param int $positionId position id to copy
	 * @return int new position id
	 * @deprecated 2.7 Use self::copyPosition instead
	 */
	function copyOrder($positionId = NULL) {
		KLog::logLegacyCall('copyOrder is now called copyPosition');
		return $this->copyPosition($positionId);
	}

	/**
	 * @return object $position Basic position data
	 * @deprecated 2.7 Use self::getPosition instead
	 */
	function &getOrder() {
		KLog::logLegacyCall('getOrder is now called getPosition');
		return $this->getPosition($this->getId());
	}

	/**
	 * @return bool $success;
	 * @deprecated 2.7 Use self::removePosition instead
	 */
	function removeOrder() {
		KLog::logLegacyCall('removeOrder is now called removePosition');
		$positionId = KRequest::getInt('order_id');
		return $this->removePosition($positionId);
	}

	/**
	 *
	 * @param $array array holding new basic position data (key/value like DB field key/value)
	 * @return bool $success
	 * @deprecated 2.7 Use self::editPosition instead
	 */
	function editOrder($array) {
		KLog::logLegacyCall('editOrder is now called editPosition');
		$positionId = KRequest::getInt('order_id');
		return $this->editPosition($positionId, $array);
	}

	/**
	 *
	 * @param object $baseData optional position base data to set (will be loaded if not present)
	 * @return object $positionDetails Object holding detailed position data
	 * @deprecated 2.7 Use self::getPositionDetails instead
	 */
	function &getOrderDetails($baseData = NULL) {
		KLog::logLegacyCall('getOrderDetails is now called getPositionDetails');
		return $this->getPositionDetails($baseData);
	}

	/**
	 * @deprecated 2.7 Use self::createPosition() instead
	 * @param int $cartId Cart id
	 * @param int $productId Product id
	 * @return int $positionId New position id
	 */
	function createOrder($cartId, $productId = NULL) {
		KLog::logLegacyCall('createOrder is now called createPosition');
		return $this->createPosition($cartId, $productId);
	}

	/**
	 * @param int $positionId Position id to check
	 * @return bool
	 * @deprecated 2.7 Use self::userOwnsPosition() instead
	 */
	function userOwnsOrder($positionId) {
		KLog::logLegacyCall('userOwnsOrder is now called userOwnsPosition');
		return $this->userOwnsPosition($positionId);
	}

	/**
	 * @param bool $bool true for finished, false otherwise
	 * @deprecated 2.7 use self::editPosition(array('finished' => 0 or 1) instead
	 */
	function setFinished($bool) {

	}

}
