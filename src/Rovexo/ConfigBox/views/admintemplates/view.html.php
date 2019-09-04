<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmintemplates extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admintemplates';

	/**
	 * @var array $originalTemplates Data for default templates
	 * @see ConfigboxModelAdmintemplates::getOriginalTemplates
	 */
	public $originalTemplates;

	/**
	 * @var array $customTemplates Data for custom templates
	 * @see ConfigboxModelAdmintemplates::getCustomTemplates
	 */
	public $customTemplates;

	/**
	 * @return ConfigboxModelAdmintemplates
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmintemplates');
	}

	function getPageTitle() {
		return KText::_('Templates');
	}

	function prepareTemplateVars() {
		
		$model = KenedoModel::getModel('ConfigboxModelAdmintemplates');
		
		$this->assignRef('pageTasks', $model->getListingTasks());
						
		$originalTemplates = $model->getOriginalTemplates();
		$this->assignRef('originalTemplates',$originalTemplates);
		
		$customTemplates = $model->getCustomTemplates();
		$this->assignRef('customTemplates',$customTemplates);

		$this->assign('returnUrl', KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName), false));

		$this->addViewCssClasses();
		
	}
	
}