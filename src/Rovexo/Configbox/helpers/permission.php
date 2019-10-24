<?php
class ConfigboxPermissionHelper {

	static function canSeePricing($userId = NULL) {

		$groupId = ConfigboxUserHelper::getGroupId($userId);
		$group = ConfigboxUserHelper::getGroupData($groupId);
		return (boolean)$group->enable_see_pricing;

	}

	static function canSaveOrder($userId = NULL) {

		$groupId = ConfigboxUserHelper::getGroupId($userId);
		$group = ConfigboxUserHelper::getGroupData($groupId);
		return (boolean)$group->enable_save_order;

	}

	static function canCheckoutOrder($userId = NULL) {

		$groupId = ConfigboxUserHelper::getGroupId($userId);
		$group = ConfigboxUserHelper::getGroupData($groupId);
		return (boolean)$group->enable_checkout_order;

	}

	static function canRequestQuotation($userId = NULL) {

		$groupId = ConfigboxUserHelper::getGroupId($userId);
		$group = ConfigboxUserHelper::getGroupData($groupId);
		return (boolean)$group->enable_request_quotation;

	}

	static function canGetB2BMode($userId = NULL) {

		$groupId = ConfigboxUserHelper::getGroupId($userId);
		$group = ConfigboxUserHelper::getGroupData($groupId);
		return (boolean)$group->b2b_mode;

	}

	static function canEditTemplates($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.edit_templates', $userId, 25);

	}

	static function canEditConnectors($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.edit_connectors', $userId, 25);

	}

	static function canQuickEdit($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.quickedit', $userId, 19);

	}

	static function canSeeOrders($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.see_orders', $userId, 23);

	}

	static function canDownloadInvoices($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.download_invoices', $userId, 23);

	}

	static function canUploadInvoices($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.upload_invoices', $userId, 23);

	}

	static function canChangeInvoices($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.change_invoices', $userId, 23);

	}

	static function canEditVisualization($userId = NULL) {

		return KenedoPlatform::p()->isAuthorized('com_configbox.core.manage', $userId, 18);

	}

	static function canEditOrders($userId = NULL) {
		return KenedoPlatform::p()->isAuthorized('com_configbox.core.edit_orders', $userId, 23);
	}

	static function isPermittedAction($action,$cartDetails) {

		if ($action == 'checkoutOrder' && ( ConfigboxPermissionHelper::canCheckoutOrder() !== true) ) {
			return false;
		}
		elseif ($action == 'requestQuote' && ( ConfigboxPermissionHelper::canRequestQuotation() !== true )) {
			return false;
		}
		elseif ($action == 'saveOrder' && ConfigboxPermissionHelper::canSaveOrder() !== true ) {
			return false;
		}

		$responses = KenedoObserver::triggerEvent('onConfigBoxGetActionPermission', array(&$action,&$cartDetails));

		$result = true;
		foreach ($responses as $response) {
			if ($response === false) {
				$result = false;
				break;
			}
		}
		return $result;


	}

}