<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminuserfields extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminuserfields';

	/**
	 * @var object[] Holds the settings for each user field, keys are the field names
	 */
	public $userFields;

	/**
	 * @var string[] Holds the localized titles of each field, keys are the field names
	 */
	public $userFieldTranslations;

    /**
     * @inheritDoc
     */
	function getJsInitCallsEach() {
        $calls = parent::getJsInitCallsEach();
        $calls[] = 'configbox/adminUserFields::initUserFieldsEach';
        return $calls;
    }

    /**
	 * @return ConfigboxModelAdminuserfields
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminuserfields');
	}

	function getPageTitle() {
		return KText::_('Customer Fields');
	}

	function prepareTemplateVars() {
				
		$model = KenedoModel::getModel('ConfigboxModelAdminuserfields');
		
		$this->assignRef('pageTitle', $this->getPageTitle());
		$this->assignRef('pageTasks', $model->getDetailsTasks());
		
		$userFields = ConfigboxUserHelper::getUserFields();
		$userFieldTranslations = ConfigboxUserHelper::getUserFieldTranslations();
		
		foreach ($userFields as $key=>$userField) {
			$userField->title = $userFieldTranslations[$key];
		}
		
		usort($userFields, array('ConfigboxViewAdminuserfields', 'sortUserFields'));
		
		$this->assignRef('userFields', $userFields );
		$this->assignRef('userFieldTranslations', $userFieldTranslations);
		
		$this->addViewCssClasses();

	}
	
	static function sortUserFields($a, $b) {
		return strcmp($a->title, $b->title);
	}
	
}
