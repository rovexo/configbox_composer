<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminorders extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminorders';

	/**
	 * @var object[]
	 * @see ConfigboxModelAdminorders::getOrders
	 */
	public $orders;

	/**
	 * @var string[] Data about current pagination, sorting and filtering
	 */
	public $lists;

	/**
	 * @var string HTML for the order status filter
	 */
	public $statusDropdown;

	/**
	 * @return ConfigboxModelAdminorders
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminorders');
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/adminOrders::initOrdersListEach';
		return $calls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/adminOrders::initOrdersListOnce';
		return $calls;
	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.ui-1.12.1/jquery-ui.css';
		return $urls;
	}

	function getPageTitle() {
		return KText::_('Orders');
	}

	function prepareTemplateVars() {

		$model = KenedoModel::getModel('ConfigboxModelAdminorders');

		$this->filters = array_merge($this->getFiltersFromUpdatedState(), $this->filters);
		$this->orderingInfo = $this->getOrderingFromUpdatedState('o.created_on', 'DESC');
		$this->paginationInfo = $this->getPaginationFromUpdatedState();
		$this->pageTitle = $this->getPageTitle();
		$this->pageTasks = $model->getListingTasks();

		$this->orders =  $model->getOrders($this->filters, $this->paginationInfo, $this->orderingInfo);
		$this->statusDropdown = $model->getStatusDropdown($this->filters);

		$totalCount = $model->getTotalRecords($this->filters);
		$this->pagination = KenedoViewHelper::getListingPagination($totalCount, $this->paginationInfo);

		$this->listingData = array(
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

	}

	function getFiltersFromUpdatedState() {
		$filters['filter_nameorder'] = 	KenedoViewHelper::getUpdatedState('com_configbox.admin_orders.filter_nameorder', 'filter_nameorder','','string');
		$filters['filter_startdate'] = 	KenedoViewHelper::getUpdatedState('com_configbox.admin_orders.filter_startdate', 'filter_startdate','','string');
		$filters['filter_enddate'] = 	KenedoViewHelper::getUpdatedState('com_configbox.admin_orders.filter_enddate'	, 'filter_enddate','' ,'string');
		$filters['filter_status'] = 	KenedoViewHelper::getUpdatedState('com_configbox.admin_orders.filter_status'	, 'filter_status','','int');
		$filters['filter_user_id'] = 	KenedoViewHelper::getUpdatedState('com_configbox.admin_orders.filter_user_id'	, 'filter_user_id','','int');

		return $filters;
	}
	
}
