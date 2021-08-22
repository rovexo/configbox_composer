<?php
class KenedoProperty {

	/**
	 * @var string $propertyName Name of the property as part of the type it is currently used
	 */
	public $propertyName;

	/**
	 * An array of arrays holding all information about a property. Written down in each Type.
	 * @see ConfigboxTypeProduct
	 * @var array $propertyDefinition
	 */
	protected $propertyDefinition = array();

	/**
	 * Data set by the Model (holds the data of all properties of the type)
	 * @var object $data
	 */
	protected $data;

	/**
	 * @var KenedoModel $model A reference to the model the property belongs to
	 */
	protected $model;

	/**
	 * @var string[] $cssClasses CSS classes used in the property's wrapping div when shown as form item
	 */
	protected $cssClasses = array();

	/**
	 * @var string $cssId CSS ID used in the property's wrapping div when shown as form item
	 */
	protected $cssId = '';

	/**
	 * @see KenedoProperty::getErrors, KenedoProperty::getError, KenedoProperty::setError
	 * @var string[] $errors Array of strings with error messages
	 * @var KenedoModel $model The model the property belongs to
	 */
	protected $errors = array();

	protected $default;
	protected $label;
	protected $labelList;
	protected $positionList;
	protected $positionForm;

	protected $makeEditLink;
	protected $component;
	protected $controller;

	protected $listCellWidth;
	protected $canSortBy;
	protected $addSearchBox;
	protected $addDropdownFilter;
	protected $required;
	protected $tooltip;
	protected $hideAdminLabel;

	protected $optionTags;

	protected $storeExternally;
	protected $foreignTableName;
	protected $foreignTableAlias;
	protected $foreignTableKey;

	function __construct($propertyDefinition, KenedoModel $model) {

		$this->modernizeOldPropertySettings($propertyDefinition);

		$this->propertyName = $propertyDefinition['name'];
		$this->propertyDefinition = $propertyDefinition;
		$this->model = $model;

		$this->cssId = 'property-name-'.$this->propertyDefinition['name'];

		$this->cssClasses[] = 'property-name-'.$this->propertyDefinition['name'];
		$this->cssClasses[] = 'kenedo-property';
		$this->cssClasses[] = 'property-type-'.$this->propertyDefinition['type'];
		$this->cssClasses[] = 'form-group';

		if ($this->isRequired()) {
			$this->cssClasses[] = 'required';
		}
		
		if ($this->isVisible() == false) {
			$this->cssClasses[] = 'invisible-field';
		}
		
		if (isset($this->propertyDefinition['options'])) {
			$optionsArray = explode(' ',$this->propertyDefinition['options']);
			foreach ($optionsArray as $fieldOption) {
				$this->propertyDefinition['optionTags'][$fieldOption] = true;
			}
		}
		else {
			$this->propertyDefinition['optionTags'] = array();
		}
		
	}

	/**
	 * Changes old property settings to new names and values
	 * @param array $propDef
	 */
	function modernizeOldPropertySettings(&$propDef) {

		if (isset($propDef['listingwidth'])) {
			$propDef['listCellWidth'] = $propDef['listingwidth'];
			unset($propDef['listingwidth']);
		}

		if (isset($propDef['listing'])) {
			$propDef['positionList'] = $propDef['listing'];
			unset($propDef['listing']);
		}

		if (isset($propDef['order'])) {
			if ($propDef['order'] !== null) {
				$propDef['canSortBy'] = true;
			}
			unset($propDef['order']);
		}

		if (isset($propDef['search'])) {
			if ($propDef['search'] != '0') {
				$propDef['addSearchBox'] = true;
			}
			unset($propDef['search']);
		}

		if (isset($propDef['filter'])) {
			if ($propDef['filter'] != '0') {
				$propDef['addDropdownFilter'] = true;
			}
			unset($propDef['filter']);
		}

		if (isset($propDef['listinglink'])) {
			$propDef['makeEditLink'] = ($propDef['listinglink']) ? true : false;
			unset($propDef['listinglink']);
		}

		if ($propDef['type'] == 'file') {
			if (isset($propDef['size'])) {
				$propDef['maxFileSizeKb'] = $propDef['size'];
				unset($propDef['size']);
			}
		}

	}

	/**
	 * Get's you the property's type name (e.g. 'join', 'translatable' etc)
	 * @return string $propertyType
	 */
	function getType() {
		return $this->getPropertyDefinition('type', '');
	}

	/**
	 * Sets the model's record data. It contains the whole data, not just what belongs to the property. To find the info
	 * that 'comes' from that prop, get it via $data->{$this->propertyName}.
	 * @param object $data
	 */
	function setData( $data ) {
		$this->data = $data;
	}

	/**
	 * Tells if the property 'applies'. Applies means that the prop should show in edit forms and (if required) should
	 * be validated. See the propDef appliesWhen, with it you conditions for the prop to apply.
	 *
	 * @param object $data Typical record data
	 *
	 * @return bool
	 */
	function applies($data) {

		$appliesWhens = $this->getPropertyDefinition('appliesWhen', array());

		if (count($appliesWhens) == 0) {
			return true;
		}

		$conditionNotMet = false;

		foreach ($appliesWhens as $propertyName => $shouldValues) {

			if (!is_array($shouldValues)) {
				$shouldValues = array($shouldValues);
			}

			$isValue = !empty($data->$propertyName) ? $data->{$propertyName} : null;

			$operator = 'is';
			if (count($shouldValues) == 1 && substr($shouldValues[0], 0, 1) == '1') {
				$shouldValues[0] = substr($shouldValues[0], 1);
				$operator = 'is not';
			}

			if ($operator == 'is') {

				if ($isValue !== null && in_array('*', $shouldValues) == true) {
					continue;
				}

				if (in_array($isValue, $shouldValues) == false) {
					$conditionNotMet = true;
					break;
				}

			}
			else {

				if (in_array($isValue, $shouldValues) == true) {
					$conditionNotMet = true;
					continue;
				}

			}

		}

		if ($conditionNotMet) {
			return false;
		}
		else {
			return true;
		}

	}

	/**
	 * Looks into the query string or POST data and populates the model record's data object with whatever the prop is
	 * dealing with. And sanitizes the input.
	 *
	 * @param $data
	 */
	function getDataFromRequest( &$data ) {

		if (KRequest::getVar($this->propertyName, NULL) === NULL) {
			$data->{$this->propertyName} = NULL;
		}
		else {
			
			if (isset($this->propertyDefinition['optionTags']['ALLOW_RAW'])) {
				$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '', 'METHOD');
				$data->{$this->propertyName} = stripslashes($data->{$this->propertyName});
			}
			elseif (isset($this->propertyDefinition['optionTags']['ALLOW_HTML'])) {
				$data->{$this->propertyName} = KRequest::getHtml($this->propertyName, '');
			}
			else {
				$data->{$this->propertyName} = KRequest::getString($this->propertyName, '');
			}
		}
		
	}

	/**
	 * Runs any manipulation of the model's record data before storage. That runs before data validation.
	 *
	 * @see KenedoModel::prepareForStorage
	 * @param $data
	 *
	 * @return bool
	 */
	function prepareForStorage( &$data ) {
		return true;
	}

	/**
	 * Validates the model's record data before storage. Use setError to leave user feedback.
	 *
	 * @param $data
	 *
	 * @return bool true if all is well, false otherwise
	 */
	function check( $data ) {

		$this->resetErrors();

		if ($this->isRequired() && $this->applies($data)) {

			if (empty($data->{$this->propertyName}) && $data->{$this->propertyName} !== '0') {
				$this->setError(KText::sprintf('Field %s cannot be empty.', $this->propertyDefinition['label']));
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Tells which keys of our data object have values that go into the base table during storing.
	 *
	 * IMPORTANT: You must make sure that you add those things that are stored in the base table or they wont't be.
	 *
	 * @param object $data
	 * @return string[] keys of the data object that hold values that get stored in the model's base table.
	 */
	function getDataKeysForBaseTable($data) {
		if ($this->getPropertyDefinition('storeExternally')) {
			return array();
		}
		else {
			return array($this->propertyName);
		}
	}

	/**
	 * Runs any storage functionality specific to that property. Whatever you store yourself here you need to remove
	 * from $data (simple unset). What's left of $data will be stored by the model's store method itself.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	function store(&$data) {

		// In case the property has a foreignTableName in its definition, it means it got to be stored in a different
		// table, there is 'foreignTableName', 'foreignTableAlias', 'foreignTableKey' that make the functionality
		// happen
		if ($this->getPropertyDefinition('storeExternally')) {

			// Get those in variables for convenience
			$foreignTableName = $this->getPropertyDefinition('foreignTableName'); // Foreign as in the table we store the property's data in
			$foreignTableKey = $this->getPropertyDefinition('foreignTableKey');
			$baseTableKeyValue = $data->{$this->model->getTableKey()};

			$db = KenedoPlatform::getDb();

			// Make sure that NULL values are in fact stored as NULL
			if ($data->{$this->propertyName} === NULL) {
				$value = 'NULL';
			}
			else {
				$value = "'".$db->getEscaped($data->{$this->propertyName})."'";
			}

			// Insert the property's data (or update the regarding column)

			$query = "
			INSERT INTO `".$foreignTableName."` 
			SET 
				`".$this->getTableColumnName()."`   = ".$value.",
				`".$foreignTableKey."`      = '".$db->getEscaped($baseTableKeyValue)."' 
			ON DUPLICATE KEY UPDATE `".$this->getTableColumnName()."` = ".$value;
			$db->setQuery($query);
			$success = $db->query();

			// Now remove the property's var from the data object (so that it won't be included when the model stores the basic data)
			unset($data->{$this->propertyName});

			// Say how things went
			if ($success == false) {
				return false;
			}
			else {
				return true;
			}

		}

		// Nothing to do here, so all went well
		return true;

	}

	/**
	 * @param object $data
	 * @param int $newId
	 * @param int $oldId
	 * @return bool
	 */
    function copy($data, $newId, $oldId) {

        if ($this->getPropertyDefinition('storeExternally') == true) {

        	$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';

        	KLog::log($logPrefix.'Copying data from external table. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

            $db = KenedoPlatform::getDb();

            $foreignTableName = $this->getPropertyDefinition('foreignTableName');
            $foreignTableKey = $this->getPropertyDefinition('foreignTableKey');

            // Make sure that NULL values are in fact stored as NULL
            if (!isset($data->{$this->propertyName})) {
                $value = 'NULL';
            }
            else {
                $value = "'".$db->getEscaped($data->{$this->propertyName})."'";
            }

            try {
				$query = "
				INSERT INTO `".$foreignTableName."` 
				SET 
					`".$this->propertyName."`   = ".$value.",
					`".$foreignTableKey."`      = '".$db->getEscaped($newId)."' 
				ON DUPLICATE KEY UPDATE `".$this->propertyName."` = ".$value;
				$db->setQuery($query);
				$response = $db->query();
            }
            catch(Exception $e) {
            	$msg = 'Property '.get_class($this).'\\'.$this->propertyName.' encountered an SQL error during insert in external table.';
            	KLog::log($msg, 'db_error');
				KLog::log($msg, 'error');
				KLog::log($msg, 'custom_copying');
            	$this->setError('A system error occured during copying property "'.$this->getPropertyDefinition('label'));
            	return false;
            }

			return ($response !== false) ? true : false;

        }

        return true;
    }

	/**
	 * Tells if the record can be deleted (as far as the prop is concerned)
	 * @param int $recordId
	 *
	 * @return boolean
	 */
	function canDelete($recordId) {
		return true;
	}

	/**
	 * Delete what needs deleting for that prop specifically. Model's delete method does the rest.
	 *
	 * @param int $id Record ID
	 * @param string $tableName (Probably phased out already)
	 *
	 * @return bool
	 */
	function delete ( $id, $tableName ) {

		if ($this->getPropertyDefinition('storeExternally')) {

			// Get those in variables for convenience
			$foreignTableName = $this->getPropertyDefinition('foreignTableName');
			$foreignTableKey = $this->getPropertyDefinition('foreignTableKey');

			$db = KenedoPlatform::getDb();
			$query = "
			DELETE FROM `".$foreignTableName."` 
			WHERE `".$foreignTableKey."` = '".$db->getEscaped($id)."'";
			$db->setQuery($query);
			$success = $db->query();

			// Say how things went
			if ($success == false) {
				return false;
			}
			else {
				return true;
			}

		}

		return true;
	}

	/**
	 * @param object $data Data from KenedoModel::getRecord()
	 * @return string HTML with the property's form output
	 * @see KenedoModel::getRecord()
	 */
	function getPropertyFormOutput($data) {
		$this->setData($data);

		ob_start();

		if ($this->usesWrapper()) {
			?>
			<div id="<?php echo $this->getCssId();?>" class="<?php echo $this->renderCssClasses();?>" data-property-definition="<?php echo hsc(json_encode($this->getPropertyDefinition()));?>">
				<?php if ($this->doesShowAdminLabel()) { ?>
					<div class="property-label"><?php echo $this->getLabelAdmin();?></div>
				<?php } ?>
				<div class="property-body"><?php echo $this->getBodyAdmin();?></div>
			</div>
			<?php
		}
		else {
			echo $this->getBodyAdmin();
		}

		return ob_get_clean();
	}

	/**
	 * Returns the prop's label for admin forms
	 * @see KenedoView::prepareTemplateVarsForm
	 * @return string
	 */
	function getLabelAdmin() {
		
		if (isset($this->propertyDefinition['tooltip'])) {
			return KenedoHtml::getTooltip(hsc($this->propertyDefinition['label']), $this->propertyDefinition['tooltip']);
		}
		else {
			return hsc(isset($this->propertyDefinition['label']) ? $this->propertyDefinition['label'] : '');
		}
	}

	/**
	 * Returns CSS classes for the prop's HTML wrapper in the admin form
	 * @return string
	 */
	function renderCssClasses() {
		return implode(' ',$this->cssClasses);
	}

	/**
	 * Returns the ID attribute value for  the prop's HTML wrapper in the admin form
	 * @return string
	 */
	function getCssId() {
		return $this->cssId;
	}

	/**
	 * Returns the name of the prop
	 * @return string
	 */
	function getName() {
		return $this->propertyName;
	}

	/**
	 * With a $key, it returns the corresponding value from THAT prop's definition (e.g. 'tooltip' -> 'some value'
	 * Without a $key, you get the whole array of THIS props definition.
	 *
	 * @see ConfigboxModelAdminexamples::getPropertyDefinitions
	 * @param string $key
	 * @param mixed $default Fallback value to return in case there is no value for the $key
	 *
	 * @return array|mixed|null
	 */
	final function getPropertyDefinition($key = NULL, $default = NULL) {

		if ($key) {
			if (isset($this->propertyDefinition[$key])) {
				return $this->propertyDefinition[$key];
			}
			else {
				return $default;
			}
		}
		else {
			return $this->propertyDefinition;
		}
	}

	/**
	 * Returns the cell content for that record's prop in the admin listing table. Can be (sanitized) HTML.
	 * @param object $record
	 *
	 * @return string
	 */
	function getCellContentInListingTable($record) {

		$content = $this->getOutputValueFromRecordData($record);

		if ($this->getPropertyDefinition('makeEditLink')) {
			$content = $this->wrapWithEditLink($content, $record);
		}

		return $content;

	}

	/**
	 * Gives you a nice, human-readable version of what the property stores. E.g. "Yes" in boolean prop, etc.
	 *
	 * @param $record
	 *
	 * @return string
	 */
	function getOutputValueFromRecordData($record) {
		return $record->{$this->propertyName};
	}

	/**
	 * @param string $cellContent HTML-escaped string with cell content
	 * @param object $record Record shown
	 * @see getCellContentInListingTable
	 *
	 * @return string
	 */
	function wrapWithEditLink($cellContent, $record) {

		$option = $this->getPropertyDefinition('component');
		$controller = $this->getPropertyDefinition('controller');
		$recordId = $record->{$this->model->getTableKey()};
		$returnUrl = KLink::base64UrlEncode(KLink::getRoute( 'index.php?option='.hsc($option).'&controller='.hsc($controller), false ));
		$href = KLink::getRoute('index.php?option='.hsc($option).'&controller='.hsc($controller).'&task=edit&id='.intval($recordId) . '&return=' . $returnUrl);

		ob_start();
		?>
		<a class="listing-link"
		   data-controller="<?php echo hsc($controller);?>"
		   data-task="edit"
		   data-id="<?php echo intval($recordId);?>"
		   data-return-url="<?php echo hsc($returnUrl);?>"
		   href="<?php echo $href;?>">
			<?php echo $cellContent;?>
		</a>
		<?php
		return ob_get_clean();


	}

	/**
	 * Returns whatever should go into the cells of the header row of the kenedo listing.
	 *
	 * @param array[] $orderingInstructions
	 * @return string (HTML or just the escaped label)
	 */
	function getHeaderCellContentInListingTable($orderingInstructions) {

		if ($this->getPropertyDefinition('labelList')) {
			$label = $this->getPropertyDefinition('labelList');
		}
		elseif($this->getPropertyDefinition('label')) {
			$label = $this->getPropertyDefinition('label');
		}
		else {
			$label = $this->propertyName;
		}

		if ($this->getPropertyDefinition('canSortBy') == true) {

			ob_start();

			// Prime settings for ordering
			$isActive = false;
			$direction = '';

			// Check ordering instructions and get settings for this prop
			foreach ($orderingInstructions as $orderingInfoItem) {
				if ($orderingInfoItem['propertyName'] == $this->propertyName) {
					$isActive = true;
					$direction = (strtolower($orderingInfoItem['direction']) == 'desc') ? 'desc':'asc';
					break;
				}
			}
			?>
			<a data-property-name="<?php echo hsc($this->propertyName);?>"
			   data-current-direction="<?php echo ($direction);?>"
			   class="trigger-order-list <?php echo ($isActive) ? 'active':'inactive';?>">
				<span class="property-listing-label"><?php echo hsc($label);?></span>
				<i class="fa <?php echo ($direction == 'desc') ? 'fa-sort-amount-down' : 'fa-sort-amount-up';?>"></i>
			</a>
			<?php
			return ob_get_clean();

		}
		else {
			return hsc($label);
		}

	}
	
	function getBodyAdmin() {
		
		$templateFileAdmin = $this->getAdminTemplateFile();

		if ($templateFileAdmin) {

			if (!is_file($templateFileAdmin)) {
				KLog::log('Template file for property "'.$this->getType().'" not found in "'.$templateFileAdmin.'"', 'error');
				return '';
			}
			else {
				ob_start();
				require($templateFileAdmin);
				return ob_get_clean();
			}

		}
		else {
			return NULL;
		}
		
	}
	
	function getLabelDisplay() {	
		return '';
	}
	
	function getBodyDisplay() {
		return '';
	}
	
	function isRequired() {
		if (!isset($this->propertyDefinition['required'])) {
			return false;
		}
		return ($this->propertyDefinition['required'] == true);
	}
	
	function isVisible() {
		if (!isset($this->propertyDefinition['invisible'])) {
			return true;
		}
		return ( $this->propertyDefinition['invisible'] == false);
	}
	
	function isInListing() {
		return ( !empty($this->propertyDefinition['positionList']) );
	}
	
	function getListingPosition() {
		return isset($this->propertyDefinition['positionList']) ? $this->propertyDefinition['positionList'] : 0;
	}
	
	function usesWrapper() {
		return true;
	}
	
	function doesShowAdminLabel() {
		$return = ($this->getPropertyDefinition('hideAdminLabel',0) == false);
		return $return;
	}

	protected function getAdminTemplateFile() {

		$regularFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/external/kenedo/properties/tmpl';
		$customFolder = KenedoPlatform::p()->getDirCustomization().'/properties/tmpl';
	
		$filename = strtolower( $this->propertyDefinition['type'] ).'.php';

		// Custom folder
		if ( file_exists($customFolder .'/'. $filename) ) {
			$templateFile = $customFolder .'/'. $filename;
		}
		// Regular folder
		elseif ( file_exists($regularFolder .'/'. $filename) ) {
			$templateFile = $regularFolder .'/'. $filename;
		}
		// If needed, deal with non-existent prop templates
		else {
			return NULL;
		}
		
		return $templateFile;
	
	}

	/**
	 * Returns an array of strings with SQL query text for the field list (e.g. table.column as alias).
	 * Used for KenedoModel::getRecord and getRecords.
	 *
	 * @see KenedoModel::getRecord(), KenedoModel::getRecords()
	 *
	 * @param string $selectAliasPrefix Prefix to use on the select alias of the selected column
	 * @param string $selectAliasOverride Used to force the supplied select alias (prefix will be taken into account)
	 * @return string[]
	 */
	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		$selects = array();

		$selectAlias = ($selectAliasOverride) ? $selectAliasPrefix.$selectAliasOverride : $selectAliasPrefix.$this->getSelectAlias();
		$selects[] = $this->getTableAlias().'.'.$this->getTableColumnName().' AS `'.$selectAlias.'`';

		return $selects;
	}

	/**
	 * Returns an array of strings with SQL for JOIN statements to be added to the KenedoModel::getRecord() or
	 * KenedoModel::getRecords() SQL query.
	 * @see KenedoModel::getRecord(), KenedoModel::getRecords()
	 * @return string[]
	 */
	public function getJoinsForGetRecord() {

		$joins = array();

		// In case the property is configured to use an external table's column, make sure we get the join in
		if ($this->getPropertyDefinition('storeExternally')) {

			$foreignTableName = $this->getPropertyDefinition('foreignTableName');
			$foreignTableAlias = $this->getPropertyDefinition('foreignTableAlias');
			$foreignTableKey = $this->getPropertyDefinition('foreignTableKey'); // Foreign table as in the custom table

			$baseTableAlias = $this->model->getModelName();
			$baseTableKey = $this->model->getTableKey();

			$db = KenedoPlatform::getDb();
			$joins[$foreignTableAlias] = "
			LEFT JOIN `".$db->getEscaped($foreignTableName)."` AS `".$db->getEscaped($foreignTableAlias)."` 
			ON `".$db->getEscaped($foreignTableAlias)."`.`".$db->getEscaped($foreignTableKey)."` = `".$db->getEscaped($baseTableAlias)."`.`".$db->getEscaped($baseTableKey)."`";
		}

		return $joins;
	}

	/**
	 * Returns an array of strings with columns to group by. They will be used in building the getRecord(s) query in
	 * KenedoModel::getRecord() and KenedoModel::getRecords().
	 * @see KenedoModel::getRecord(), KenedoModel::getRecords()
	 * @return string[]
	 */
	public function getGroupingColumnsForGetRecord() {
		return array();
	}

	/**
	 * Used to append any kind of data after KenedoModel::getRecord or ::getRecords is done fetching from the DB.
	 *
	 * @see KenedoModel::getRecord(), KenedoModel::getRecords()
	 * @param object $data
	 */
	public function appendDataForGetRecord(&$data) {
		return;
	}

	/**
	 * Used in CacheHelper to have each prop update/add uncachable data (e.g. complete URLs to files, translatable
	 * field default value goes into current system language.
	 *
	 * @param object $data
	 */
	public function appendDataForPostCaching(&$data) {
		return;
	}

	/**
	 * Returns the SQL field alias for that property. This will affect how the property will be named in the record
	 * object.
	 * @return string
	 */
	public function getSelectAlias() {
		return $this->propertyName;
	}

	/**
	 * Returns the column name that this property refers to.
	 * @return string
	 */
	public function getTableColumnName() {
		return $this->propertyName;
	}

	/**
	 * Returns the table alias to be used in possible JOIN statements for that property.
	 * @return string
	 */
	public function getTableAlias() {
		if ($this->getPropertyDefinition('storeExternally')) {
			return $this->getPropertyDefinition('foreignTableAlias');
		}
		else {
			return $this->model->getModelName();
		}
	}

	/**
	 * Returns the filter name (or names) of that property. A filter name is simply the table and column name
	 * (table.col), exactly as you would reference to that field in the getRecord query.
	 *
	 * @return string|string[]
	 */
	public function getFilterName() {

		if ($this->getPropertyDefinition('addDropdownFilter') != true && $this->getPropertyDefinition('addSearchBox') != true) {
			return '';
		}

		return $this->getTableAlias().'.'.$this->getTableColumnName();

	}

	/**
	 * Returns the filter name (or names) as it needs to be for GET/POST data (PHP changes dots to underscores, so
	 * $ is used as placeholder for the dot.
	 * @return string|string[] String or array of strings, depending on what KenedoProperty::getFilterName does
	 */
	public function getFilterNameRequest() {

		$filterNames = $this->getFilterName();
		if (is_array($filterNames) == false) {
			$filterNames = array($filterNames);
		}
		$requestFilterNames = array();
		foreach ($filterNames as $filterName) {
			$requestFilterNames[] = 'filter_'.str_replace('.', '$', $filterName);
		}
		if (count($requestFilterNames) == 1) {
			$requestFilterNames = $requestFilterNames[0];
		}
		return $requestFilterNames;

	}
	
	public function resetErrors() {
		$this->errors = array();
	}
	
	protected function setError($msg) {
		$this->errors[] = $msg;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function getError() {
		if (count($this->errors)) {
			return end($this->errors);
		}
		else {
			return NULL;
		}
	}

	/**
	 * Gets you the HTML for a list filter. Output depends on the property's settings (see search and filter)
	 *
	 * @param KenedoView $view
	 * @param string[]   $filters
	 *
	 * @return string
	 */
	public function getFilterInput(KenedoView $view, $filters) {

		if ($this->getPropertyDefinition('addDropdownFilter') != true && $this->getPropertyDefinition('addSearchBox') != true) {
			return '';
		}

		$filterName = $this->getFilterName();
		$filterNameRequest = $this->getFilterNameRequest();
		$filterNameHtml = str_replace('.', '_', $filterName);
		$chosenValue = !empty($filters[$filterName]) ? $filters[$filterName] : NULL;

		$html = '';

		if ($this->getPropertyDefinition('addSearchBox') == true) {
			ob_start();
			?>
			<input
				class="listing-filter form-control"
				placeholder="<?php echo KText::sprintf('Filter by %s', $this->getPropertyDefinition('label'));?>"
				type="text" value="<?php echo hsc($chosenValue); ?>"
				name="<?php echo hsc($filterNameRequest);?>"
				id="<?php echo hsc($filterNameHtml);?>" />

			<a class="kenedo-search input-group-append">
				<span class="input-group-text"><?php echo KText::_('Filter');?></span>
			</a>
			<?php
			$html = ob_get_clean();
		}
		elseif ($this->getPropertyDefinition('addDropdownFilter')) {
			$options = $this->getPossibleFilterValues();
			$html = KenedoHtml::getSelectField($filterNameRequest, $options, $chosenValue, 'all', false, 'listing-filter', $filterNameHtml);
		}

		return $html;

	}

	protected function getPossibleFilterValues() {

		// Get all records for that model
		$records = $this->model->getRecords();

		// Prepare the options array, set default value
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		$groupKey = $this->getPropertyDefinition('groupby');

		// Group if necessary
		if ($this->getPropertyDefinition('groupby')) {

			$groupedRecords = array();
			foreach ($records as $record) {
				$groupedRecords[$record->{$groupKey}][] = $record;
			}
			$records = $groupedRecords;
		}

		// Extract the infos for the select field
		foreach ($records as $record) {
			if (!is_array($record)) {

				$value = $record->{$this->getSelectAlias()};

				// Make values nice for yes/no fields
				if (in_array($this->getType(), array('boolean', 'published', 'checkbox') ) ) {
					$value = ($value) ? KText::_('CBYES') : KText::_('CBNO');
				}
				// Get the items/radios for radio and dropdown types
				if (in_array($this->getType(), array('radio', 'dropdown') ) ) {
					$map = array_merge($this->getPropertyDefinition('choices', array()), $this->getPropertyDefinition('radios', array()));
					$value = $map[$value];
				}
				// Joins do their funny business
				if (in_array($this->getType(), array('join') ) ) {
					$value = $record->{$this->propertyName.'_display_value'};
				}

				$id = $record->{$this->model->getTableKey()};

				if (!in_array($value, $options)) {
					$options[$id] = $value;
				}

			}
			else {

				foreach ($record as $groupedRecord) {

					$value = $groupedRecord->{$this->getSelectAlias()};

					// Make values nice for yes/no fields
					if (in_array($this->getType(), array('boolean', 'published', 'checkbox') ) ) {
						$value = ($value) ? KText::_('CBYES') : KText::_('CBNO');
					}
					// Get the items/radios for radio and dropdown types
					if (in_array($this->getType(), array('radio', 'dropdown') ) ) {
						$map = array_merge($this->getPropertyDefinition('choices', array()), $this->getPropertyDefinition('radios', array()));
						$value = $map[$value];
					}
					// Joins do their funny business
					if (in_array($this->getType(), array('join') ) ) {
						$value = $record->{$this->propertyName.'_display_value'};
					}

					$id = $groupedRecord->{$this->model->getTableKey()};

					if (!in_array($value, $options[$groupedRecord->{$groupKey}])) {
						$options[$groupedRecord->{$groupKey}][$id] = $value;
					}

				}

			}
		}

		return $options;

	}
}