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

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=raw', false);

		$model = $this->getDefaultModel();

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=raw', false);

		$this->assignRef('pageTitle', $this->getPageTitle());

		$filters = array_merge($this->filters, $this->getFiltersFromUpdatedState());

		$fixedFilters = array(
			'admincustomers.is_temporary'=>'0',
		);

		$filters = array_merge($filters, $fixedFilters);

		$paginationInfo = $this->getPaginationFromUpdatedState();
		$orderingInfo = $this->getOrderingFromUpdatedState();

		$records = $model->getRecords($filters, $paginationInfo, $orderingInfo);
		$properties = $model->getPropertiesForListing();

		$filterInputs = $this->getFilterInputs($filters);

		$this->assignRef('filterInputs', $filterInputs);
		$this->assignRef('orderingInfo', $orderingInfo);
		$this->assignRef('paginationInfo', $paginationInfo);
		$this->assignRef('records', $records);
		$this->assignRef('properties', $properties);
		$this->assignRef('filters', $filters);

		// Add pagination HTML
		$totalCount = $model->getRecords($filters, array(), array(), NULL, true);

		$pagination = KenedoViewHelper::getListingPagination($totalCount, $paginationInfo);
		$this->assignRef('pagination', $pagination);

		$this->assignRef('pageTasks', $model->getListingTasks());

		$listingData = array(
			'base-url'				=> KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode())),
			'option'				=> hsc($this->component),
			'task'					=> 'display',
			'ajax_sub_view'			=> ($this->isAjaxSubview()) ? '1':'0',
			'tmpl'					=> hsc(KRequest::getKeyword('tmpl','component')),
			'in_modal'				=> hsc(KRequest::getInt('in_modal','0')),
			'intralisting'			=> $this->isIntralisting,
			'format'				=> 'raw',
			'groupKey'				=> hsc(KenedoViewHelper::getGroupingKey($this->properties)),
			'limitstart'			=> hsc($paginationInfo['start']),
			'limit'					=> hsc($paginationInfo['limit']),
			'listing_order_property_name'	=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['propertyName'] : ''),
			'listing_order_dir'				=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['direction'] : ''),
			'parampicker'			=> hsc(KRequest::getInt('parampicker',0)),
			'pickerobject'			=> hsc(KRequest::getKeyword('pickerobject','')),
			'pickermethod'			=> hsc(KRequest::getKeyword('pickermethod','')),
			'return'				=> KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode()), false) ),
			'ids'					=> '',
			'ordering-items'		=> '',
			'foreignKeyField'		=> KRequest::getKeyword('foreignKeyField', (!empty($this->foreignKeyField)) ? $this->foreignKeyField : ''),
			'foreignKeyPresetValue'	=> KRequest::getKeyword('foreignKeyPresetValue', (!empty($this->foreignKeyPresetValue)) ? $this->foreignKeyPresetValue : ''),
		);

		// Prepare the href for for the add button
		$link = 'index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit&id=0';

		if ($this->isInModal()) {
			$link.= '&in_modal=1';
		}

		if (!empty($this->foreignKeyField)) {
			$link .= '&'.$this->foreignKeyField.'='.$this->foreignKeyPresetValue;
		}
		if (KRequest::getKeyword('foreignKeyField')) {
			$link .= '&'.KRequest::getKeyword('foreignKeyField').'='.KRequest::getInt('foreignKeyPresetValue', '0');
		}
		$link .= '&return='.$listingData['return'];

		$listingData['add-link'] = KLink::base64UrlEncode( KLink::getRoute($link, false) );

		$this->assignRef('listingData', $listingData);

	}

}
