<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxConfiguration {

	protected static $instances;

	protected $positionId;
	protected $productId;
	protected $selections = array();
	protected $simSelections = array();

	/**
	 *
	 * @param int $positionId
	 * @return ConfigboxConfiguration
	 */
	static function &getInstance($positionId = NULL) {

		if ($positionId === NULL) {
			$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
			$positionId = $positionModel->getId();
		}

		if (!isset(self::$instances[$positionId]) ) {
			self::$instances[$positionId] = new self($positionId);
		}

		return self::$instances[$positionId];

	}

	public function getPositionId() {
		return $this->positionId;
	}

	public function getProductId() {
		return $this->productId;
	}

	protected function __construct($positionId) {

		$this->positionId = intval($positionId);

		$selections = $this->loadSelectionsFromSession();

		if (!$selections) {
			$selections = $this->loadSelectionsFromDb();
		}

		if (!$this->productId) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT `prod_id` FROM `#__configbox_cart_positions` WHERE `id` = ".intval($positionId);
			$db->setQuery($query);
			$this->productId = $db->loadResult();
		}

		$assignments = ConfigboxCacheHelper::getAssignments();
		$questionIds = isset($assignments['product_to_element'][$this->productId]) ? $assignments['product_to_element'][$this->productId] : array();

		// Populate the selection (but leave out questions that have been deleted meanwhile)
		$this->selections = array();
		foreach ($selections as $questionId=>$selection) {

			if (isset($questionIds[$questionId])) {
				$this->selections[$questionId] = $selection;
			}

		}

	}

	/**
	 * Stores all selections in session
	 */
	public function storeSelectionsInSession() {
		$selections = $this->getSelections(false);
		KSession::set('selections_'.$this->getPositionId(), json_encode($selections));
	}

	/**
	 * Removes selections for a the current position in session data
	 */
	public function deleteSelectionsFromSession() {
		KSession::delete('selections_'.$this->getPositionId());
	}

	/**
	 * Deletes selections from any position in session data
	 */
	public function deleteSelectionsFromSessionAnyPosition() {

		$sessionData = KSession::$data;
		foreach ($sessionData as $key=>$value) {
			if (strpos($key, 'selections_') === 0) {
				KSession::delete($key);
			}
		}

	}

	/**
	 * @return string[]
	 */
	public function loadSelectionsFromSession() {
		$data = KSession::get('selections_'.$this->getPositionId(), '');
		if (!empty($data)) {
			return json_decode($data, true);
		}
		else {
			return array();
		}
	}

	/**
	 * Makes selections permanent by storing them in the DB
	 */
	public function storeSelectionsInDb() {

		$db = KenedoPlatform::getDb();

		// Remove any existing selections for that position id
		$query = "DELETE FROM `#__configbox_cart_position_configurations` WHERE `cart_position_id` = ".intval($this->getPositionId());
		$db->setQuery($query);
		$success = $db->query();

		if (!$success) {
			throw new Exception('An error occured during storing selections.');
		}

		$selections = $this->getSelections(false);

		foreach ($selections as $questionId=>$selection) {
			// Collect all selection data
			$record = new stdClass();
			$record->cart_position_id = $this->getPositionId();
			$record->prod_id = $this->getProductId();
			$record->element_id = $questionId;
			$record->selection = $selection;

			// Insert a row
			$success = $db->insertObject('#__configbox_cart_position_configurations', $record);

			if (!$success) {
				throw new Exception('An error occured during storing selections.');
			}

		}

		// Just for keeping things slim in session data
		$this->deleteSelectionsFromSession();

	}

	public function deleteSelectionsFromDb() {

		// Remove any existing selections for that position id
		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_cart_position_configurations` WHERE `cart_position_id` = ".intval($this->getPositionId());
		$db->setQuery($query);
		$success = $db->query();

		if (!$success) {
			throw new Exception('An error occured during storing selections.');
		}

	}

	/**
	 * @return string[]
	 */
	public function loadSelectionsFromDb() {
		$positionId = $this->getPositionId();
		$db = KenedoPlatform::getDb();
		$query = "	SELECT o.`element_id`, o.`selection`
					FROM `#__configbox_cart_position_configurations` AS o
					LEFT JOIN `#__configbox_elements` AS e ON e.id = o.element_id
					WHERE o.`cart_position_id` = ".intval($positionId)." AND e.published = '1'";
		$db->setQuery($query);
		$data = $db->loadResultList('element_id', 'selection');
		return $data;
	}

	/**
	 * @param string[] $simSelections See setSimSelection
	 * @see ConfigboxConfiguration::setSimSelection
	 */
	public function addSimSelections($simSelections) {
		foreach ($simSelections as $elementId => $simSelection) {
			$this->setSimSelection($elementId, $simSelection);
		}
	}

	/**
	 * Let's you set a 'faked' selection (used for simulating a configuration during rule processing).
	 * All functions returning configuration data will have simulated selections merged over the real ones.
	 *
	 * @see ConfigboxConfiguration::unsetSimSelections(), ConfigboxConfiguration::unsetSimSelection()
	 * @throws Exception if $elementId isn't right (int higher than 0)
	 * @param int $questionId
	 * @param string $selection
	 */
	public function setSimSelection($questionId, $selection) {

		if ($questionId == 0) {
			throw new Exception('Tried to set a simSelection with no element ID. Value was '.var_export($questionId, true));
		}

		// Setting NULL (instead of unsetting the entry) is important here. We need to 'mask' a real selection with an unset one with this
		if ($selection === null) {
			$this->simSelections[$questionId] = NULL;
		}
		else {
			$this->simSelections[$questionId] = $selection;
		}

	}

	/**
	 * Tells if selection for $question currently is a sim selection
	 * @param int $questionId
	 * @return bool
	 */
	public function isSimSelection($questionId) {
		return (!empty($this->simSelections[$questionId]));
	}

	/**
	 * Tells if selection for $questionId currently has an actual selection (no matter if a simSelection covers it)
	 * @param int $questionId
	 * @return bool
	 */
	public function hasRealSelection($questionId) {
		return (!empty($this->selections[$questionId]));
	}

	/**
	 * @return string[]|null[]
	 */
	public function getSimSelections() {
		return $this->simSelections;
	}

	/**
	 * @param int $questionId
	 *
	 * @return string|null
	 */
	public function getSimSelection($questionId) {
		return (!empty($this->simSelections[$questionId])) ? $this->simSelections[$questionId] : NULL;
	}


	/**
	 * Remove any simulated selections
	 */
	public function unsetSimSelections() {
		$this->simSelections = array();
	}

	/**
	 * Removed any simulated selection for the given question ID
	 * @param int $questionId
	 */
	public function unsetSimSelection($questionId) {
		unset($this->simSelections[$questionId]);
	}

	/**
	 * @param int $questionId
	 * @param string $selection
	 *
	 * @throws Exception
	 */
	public function setSelection($questionId, $selection) {

		if (empty($questionId)) {
			throw new Exception(__METHOD__.' called with no question id');
		}

		$prevSelection = $this->getSelection($questionId);

		$question = ConfigboxQuestion::getQuestion($questionId);
		$question->onBeforeSetSelection($selection, $prevSelection, $this->positionId);

		if ($selection === null) {
			unset($this->selections[$questionId]);
		}
		else {
			$this->selections[$questionId] = $selection;
		}

		$question->onAfterSetSelection($selection, $prevSelection, $this->positionId);

		$this->storeSelectionsInSession();

	}


	/**
	 * Use this to get the current selections in a cart position
	 *
	 * - Got the simulated selections merged in
	 * - Expects the instance to be set to the desired cart position id (see getInstance).
	 *
	 * @see ConfigboxConfiguration::getInstance for setting the right cart position ID
	 * @param bool $includeSimSelections If simulated selections should be merged in
	 * @return string[] Array of selections, key is the question id, value is the selection
	 */
	public function getSelections($includeSimSelections = true) {

		$selections = $this->selections;

		if ($includeSimSelections == true) {
			foreach ($this->simSelections as $questionId => $simSelection) {
				if ($simSelection === NULL) {
					// Mind that this unsets it in the COPY of $this->selections
					unset($selections[$questionId]);
				}
				else {
					$selections[$questionId] = $simSelection;
				}
			}
		}

		return $selections;

	}

	/**
	 * @param int $questionId
	 * @return null|string $selection
	 */
	public function getSelection($questionId) {

		if (isset($this->simSelections[$questionId])) {
			return $this->simSelections[$questionId];
		}
		elseif (isset($this->selections[$questionId])) {
			return $this->selections[$questionId];
		}
		else {
			return null;
		}

	}

	/**
	 * Use this to get the question IDs that have a selection currencly (including simulated selections)
	 * Expects the instance to be set to the desired cart position id (see getInstance).
	 * @param bool $includeSimSelections If simulated selections should be merged in
	 * @return int[] Array of question IDs
	 */
	public function getQuestionIdsWithSelection($includeSimSelections = true) {

		$selections = $this->getSelections($includeSimSelections);
		if (is_array($selections)) {
			return array_keys($selections);
		}
		else {
			return array();
		}

	}

	/**
	 * @param int $questionId
	 * @return null|string $selection
	 * @deprecated Use getSelection instead
	 */
	public function getElementXrefId($questionId) {
		return $this->getSelection($questionId);
	}

	/**
	 * @param $questionId
	 * @return null|string The text entry for that element
	 * @deprecated Use getSelection instead
	 */
	public function getElementTextEntry($questionId) {
		return $this->getSelection($questionId);
	}

	/**
	 * Use this to get the question IDs that have a selection currencly (including simulated selections)
	 * Expects the instance to be set to the desired cart position id (see getInstance).
	 * @param bool $includeSimSelections If simulated selections should be merged in
	 * @return int[] Array of question IDs
	 * @deprecated use identical getQuestionIdsWithSelection instead
	 */
	public function getSelectionElementIds($includeSimSelections = true) {
		return $this->getQuestionIdsWithSelection($includeSimSelections);
	}

}