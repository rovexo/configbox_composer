<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincustomers extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincustomers';

	/**
	 * @return ConfigboxModelAdmincustomers
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomers');
	}

	function getPageTitle() {
		return KText::_('Customers');
	}

	protected function prepareTemplateVarsList() {

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

		$model = $this->getDefaultModel();

		$this->pageTitle = $this->getPageTitle();

		$this->filters = array_merge($this->filters, $this->getFiltersFromUpdatedState());

		$this->filters['admincustomers.is_temporary'] = '0';

		$this->paginationInfo = $this->getPaginationFromUpdatedState();
		$this->orderingInfo = $this->getOrderingFromUpdatedState();

		$this->records = $model->getRecords($this->filters, $this->paginationInfo, $this->orderingInfo);
		$this->properties = $model->getPropertiesForListing();

		$this->filterInputs = $this->getFilterInputs($this->filters);

		// Add pagination HTML
		$totalCount = $model->getRecords($this->filters, array(), array(), NULL, true);

		$this->pagination = KenedoViewHelper::getListingPagination($totalCount, $this->paginationInfo);

		$this->pageTasks = $model->getListingTasks();

		$listingData = array(
			'base-url'				=> KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode())),
			'option'				=> hsc($this->component),
			'controller'            => hsc($this->controllerName),
			'task'					=> 'display',
			'output_mode'			=> 'view_only',
			'groupKey'				=> hsc(KenedoViewHelper::getGroupingKey($this->properties)),
			'limitstart'			=> hsc($this->paginationInfo['start']),
			'limit'					=> hsc($this->paginationInfo['limit']),
			'listing_order_property_name'	=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['propertyName'] : ''),
			'listing_order_dir'				=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['direction'] : ''),
			'return'				=> KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode()), false) ),
			'ids'					=> '',
			'ordering-items'		=> '',
			'foreignKeyField'		=> KRequest::getKeyword('foreignKeyField', (!empty($this->foreignKeyField)) ? $this->foreignKeyField : ''),
			'foreignKeyPresetValue'	=> KRequest::getKeyword('foreignKeyPresetValue', (!empty($this->foreignKeyPresetValue)) ? $this->foreignKeyPresetValue : ''),
		);

		// START - Prepare the href for for the add button
		$addLink = 'index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit&id=0';

		if (!empty($this->foreignKeyField)) {
			$addLink .= '&prefill_'.$this->foreignKeyField.'='.$this->foreignKeyPresetValue;
		}
		if (KRequest::getKeyword('foreignKeyField')) {
			$addLink .= '&prefill_'.KRequest::getKeyword('foreignKeyField').'='.KRequest::getInt('foreignKeyPresetValue', '0');
		}
		$addLink .= '&return='.$listingData['return'];

		$listingData['add-link'] = KLink::base64UrlEncode( KLink::getRoute($addLink, false) );
		// END - Prepare the href for for the add button

		$this->listingData = $listingData;

	}

}
