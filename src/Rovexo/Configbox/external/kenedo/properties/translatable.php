<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyTranslatable extends KenedoProperty {

	function __construct($propertyDefinition, $model) {
		parent::__construct($propertyDefinition, $model);
		
		if (isset($this->propertyDefinition['optionTags']['USE_TEXTAREA']) || isset($this->propertyDefinition['optionTags']['USE_HTMLEDITOR'])) {
			$this->cssClasses[] = 'using-editors';
		}
	}
	
	function getDataFromRequest( &$data ) {
		
		$languages = KenedoLanguageHelper::getActiveLanguages();
		
		foreach ($languages as $language) {
				
			$key = $this->propertyName .'-'.$language->tag;
			
			if (KRequest::getVar($key,NULL) === NULL) {
				$data->$key = NULL;
			}
			else {
				if (isset($this->propertyDefinition['optionTags']['ALLOW_RAW'])) {
					$data->$key = KRequest::getVar($key,'','METHOD');
				}
				elseif (isset($this->propertyDefinition['optionTags']['ALLOW_HTML'])) {
					$data->$key = KRequest::getHtml($key,'');
				}
				else {
					$data->$key = KRequest::getString($key,'');
				}
			}
			
		}

		// If for any reason we get e.g. a 'title' without a language tag, then remove it.
		if (isset($data->{$this->propertyName})) {
			unset( $data->{$this->propertyName} );
		}
		
	}

	function getDataKeysForBaseTable($data) {
		return array();
	}

	function check($data) {

		$this->resetErrors();

		// If field is required, go through all languages, check if values are set
		if ($this->isRequired() && $this->applies($data)) {

			$languages = KenedoLanguageHelper::getActiveLanguages();

			foreach ($languages as $language) {

				$dataFieldKey = $this->propertyName .'-'.$language->tag;

				if (empty($data->$dataFieldKey) && $data->$dataFieldKey !== '0') {
					$this->setError(KText::sprintf('Field %s cannot be empty.', $this->propertyDefinition['label']));
					return false;
				}

			}

		}

		return true;

	}

	function store(&$data) {
		
		$db = KenedoPlatform::getDb();
		
		$languages = KenedoLanguageHelper::getActiveLanguages();
		
		foreach ($languages as $language) {
			$type = $this->propertyDefinition['langType'];
			$key = $data->{$this->model->getTableKey()};
			$dataFieldKey = $this->propertyName .'-'.$language->tag;
			$text = $data->$dataFieldKey;

			if ($text) {
				$query = "REPLACE INTO `#__configbox_strings` SET `type` = ".intval($type).", `key` = ".intval($key).", `language_tag` = '".$db->getEscaped($language->tag)."', `text` = '".$db->getEscaped($text)."'";
				$db->setQuery($query);
				$db->query();
			}
			else {
				$query = "DELETE FROM `#__configbox_strings` WHERE `type` = ".intval($type)." AND `key` = ".intval($key)." AND `language_tag` = '".$db->getEscaped($language->tag)."'";
				$db->setQuery($query);
				$db->query();
			}

//			unset($data->$dataFieldKey);
		}
		
		return true;
		
	}

    /**
     * @param object $data Model data object
     * @param int $newId
     * @param int $oldId
     * @return bool
	 * @throws Exception if insert query failed
     */
    public function copy($data, $newId, $oldId) {

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';
		KLog::log($logPrefix.'Looking for translatable texts to copy. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

        $db = KenedoPlatform::getDb();

        // get languages
        $languages = KenedoLanguageHelper::getActiveLanguages();

        $count = 0;

		foreach ($languages as $language) {
			$type = $this->propertyDefinition['langType'];
			$key = $newId;
			$dataFieldKey = $this->propertyName . '-' . $language->tag;
			$text = $data->$dataFieldKey;

			if ($text) {

				$count++;

				// Change alias for product and page aliases (the URL segment value)
				if ($type == 17 or $type == 18) {
					$text = $text . '-copy-' . $key;
				}

				try {
					// update translatable in current language
					$query = "REPLACE INTO `#__configbox_strings` SET `type` = " . intval($type) . ", `key` = " . intval($key) . ", `language_tag` = '" . $db->getEscaped($language->tag) . "', `text` = '" . $db->getEscaped($text) . "'";
					$db->setQuery($query);
					$db->query();
				}
				catch (Exception $e) {
					$logMsg = 'SQL error occurred during copying. Error was "'.$db->getErrorMsg().'".';
					KLog::log($logMsg, 'error');
					KLog::log($logMsg, 'custom_copying');
					throw new Exception($logMsg);
				}
			}

//			unset($data->$dataFieldKey);
		}

		KLog::log($logPrefix.'Copied '.$count.' translatable texts (Rest was empty). Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

        return true;

    }

	function delete($id, $tableName) {
		
		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_strings` WHERE `type` = ".(int) $this->propertyDefinition['langType']." AND `key` = ".(int)$id;
		$db->setQuery($query);
		$succ = $db->query();
		return $succ;
		
	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {

		$selectAlias = ($selectAliasOverride) ? $selectAliasPrefix.$selectAliasOverride : $selectAliasPrefix.$this->getSelectAlias();

		$select = $this->getTableAlias().".".$this->getTableColumnName()." AS `".$selectAlias."`";

		return array($select);
	}

	public function getJoinsForGetRecord() {
		$db = KenedoPlatform::getDb();
		$tableAlias = $this->getTableAlias();

		$joins = array();
		$joins[$tableAlias] = "LEFT JOIN `#__configbox_strings` AS `".$tableAlias."` ON
		`".$tableAlias."`.type = '".$this->getPropertyDefinition('langType')."' AND
		`".$tableAlias."`.key = `".$this->model->getModelName()."`.`".$this->model->getTableKey()."` AND
		`".$tableAlias."`.language_tag = '".$db->getEscaped($this->model->languageTag)."'";

		return $joins;
	}

	/**
	 * Adds translations since queries have a limit on how many joins can be done
	 * @param $data
	 */
	public function appendDataForGetRecord(&$data) {

		$tags = KenedoLanguageHelper::getActiveLanguageTags();
		foreach ($tags as $tag) {
			$fieldName = $this->propertyName.'-'.$tag;
			$data->$fieldName = ConfigboxCacheHelper::getTranslation('', $this->propertyDefinition['langType'], $data->{$this->model->getTableKey()}, $tag);
		}

		parent::appendDataForGetRecord($data);

	}

	/**
	 * Puts translatable default texts into default language
	 *
	 * @param object $data
	 */
	public function appendDataForPostCaching(&$data) {
		$languageTag = ($this->model->languageTag) ? $this->model->languageTag : KText::getLanguageTag();
		$keyDesiredLanguage = $this->propertyName.'-'.$languageTag;
		if (!isset($data->{$keyDesiredLanguage})) {
			KLog::log('Requested translation from possibly cached item, but translation does not exist in record. Desired key was "'.$keyDesiredLanguage.'", data was '. var_export($data, true), 'error');
			$data->{$this->propertyName} = '';
		}
		else {
			$data->{$this->propertyName} = $data->{$keyDesiredLanguage};
		}

	}

	/**
	 * @return string|string[]
	 */
	public function getFilterName() {

		if ($this->getPropertyDefinition('addDropdownFilter', false) == false && $this->getPropertyDefinition('addSearchBox', false) == false) {
			return '';
		}

		return $this->getTableAlias().'.'.$this->getTableColumnName();

	}

	/**
	 * @return string
	 */
	public function getSelectAlias() {
		return $this->propertyName;
	}

	/**
	 * @return string
	 */
	public function getTableColumnName() {
		return 'text';
	}

	/**
	 * @return string
	 */
	public function getTableAlias() {
		return 'translation_'.$this->model->getModelName().'_'.$this->propertyName;
	}

}