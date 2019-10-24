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

	function getPageTitle() {
		return KText::_('Orders');
	}

	function prepareTemplateVars() {

		$model = KenedoModel::getModel('ConfigboxModelAdminorders');

		$paginationInfo = $this->getPaginationFromUpdatedState();
		$orderingInfo = $this->getOrderingFromUpdatedState('o.created_on', 'DESC');

		$this->assignRef('orderingInfo', $orderingInfo);
		$this->assignRef('paginationInfo', $paginationInfo);

		$this->assignRef( 'pageTitle',		$this->getPageTitle() );
		$this->assignRef( 'pageTasks',		$model->getListingTasks());
		
		$orders = $model->getOrders($paginationInfo, $orderingInfo);
		$this->assignRef('orders', $orders);
		
		$statusDropdown = $model->getStatusDropdown();
		$this->assignRef('statusDropdown', $statusDropdown);

		$totalCount = $model->getTotalRecords();
		$pagination = KenedoViewHelper::getListingPagination($totalCount, $paginationInfo);
		$this->assignRef('pagination', $pagination);
		
		$lists['order'] = 				KenedoViewHelper::getUpdatedState('com_configbox.filter_order'				, 'filter_order'		,'a.created'	,'string');
		$lists['order_Dir'] = 			KenedoViewHelper::getUpdatedState('com_configbox.filter_order_Dir'			, 'filter_order_Dir'	,''				,'string');
		$lists['filter_nameorder'] = 	KenedoViewHelper::getUpdatedState('com_configbox.filters.filter_nameorder'	, 'filter_nameorder'	,''				,'string');
		$lists['filter_startdate'] = 	KenedoViewHelper::getUpdatedState('com_configbox.filters.filter_startdate'	, 'filter_startdate'	,''				,'string');
		$lists['filter_enddate'] = 		KenedoViewHelper::getUpdatedState('com_configbox.filters.filter_enddate'	, 'filter_enddate'		,''				,'string');

		$this->assignRef('lists',$lists);
		
		$this->addViewCssClasses();

	}
	
}
