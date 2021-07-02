<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewUser extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'user';

	/**
	 * @var string HTML attributes for the view (contains URLs for loading various sub views)
	 */
	public $viewAttributes;

	/**
	 * @var ConfigboxUserData Customer data record
	 * @see ConfigboxUserHelper::getUser
	 */
	public $customer;

	/**
	 * @var object[] Order records that belong to the current user
	 * @see ConfigboxModelOrderrecord::getOrderrecord
	 */
	public $orderRecords;

	/**
	 * @var boolean Indicates if the current user has a temporary account (hasn't been registered yet)
	 */
	public $isTemporaryAccount;

	/**
	 * @var string $customerFormHtml
	 * @see ConfigboxViewCustomerform
	 */
	public $customerFormHtml;

	/**
	 * @var string $urlCustomerAccount SEF-URL to customer account page
	 */
	public $urlCustomerAccount;

	/**
	 * @var string SEF URL to customer edit form
	 */
	public $urlEditForm;

	/**
	 * @var string $urlPasswordReset The URL comes from the platform method
	 * @see InterfaceKenedoPlatform::getPasswordResetLink
	 */
	public $urlPasswordReset;


	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}


	function getJsInitCallsOnce() {

		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/user::initUserPage';

		return $calls;
	}

	function prepareTemplateVars() {

		if (ConfigboxUserHelper::getUserId() == 0) {
			$userId = ConfigboxUserHelper::createNewUser();
			ConfigboxUserHelper::setUserId($userId);
		}

		$view = KenedoView::getView('ConfigboxViewCustomerform');
		$view->setFormType('profile');
		$view->prepareTemplateVars();
		$this->customerFormHtml = $view->getViewOutput('default');

		$this->urlCustomerAccount = KLink::getRoute('index.php?option=com_configbox&view=user');
		$this->urlEditForm = KLink::getRoute('index.php?view=user&layout=editprofile', true, CbSettings::getInstance()->get('securecheckout'));

		$customer = ConfigboxUserHelper::getUser();
		$this->customer = $customer;
		$this->isTemporaryAccount = $customer->id == 0 || $customer->is_temporary == 1;

		$orderModel  	= KenedoModel::getModel('ConfigboxModelOrderrecord');
		$ordersModel  	= KenedoModel::getModel('ConfigboxModelAdminorders');

		$orderRecords = $ordersModel->getUserOrders();
		$orderStatuses = $orderModel->getOrderStatuses();

		$orderPageStatuses = array(2,3,4,5,6,7,11,13,14);
		
		foreach ($orderRecords as $orderRecord) {
			$orderRecord->statusString = $orderStatuses[$orderRecord->status]->title;
			$orderRecord->toUserOrders = (in_array($orderRecord->status,$orderPageStatuses));
		}

		$this->orderRecords = $orderRecords;

		$attributes = array(
			'data-url-after-store' => KLink::getRoute('index.php?option=com_configbox&view=user'),
		);

		$viewAttributes = array();
		foreach ($attributes as $key=>$value) {
			$viewAttributes[] = $key . '="'.hsc($value).'"';
		}
		$this->viewAttributes = implode(' ', $viewAttributes);

	}
}
