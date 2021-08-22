<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminnotifications extends KenedoModel {

	function getTableName() {
		return '#__configbox_notifications';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'positionForm'=>100,
		);

		$propDefs['name'] = array(
			'name'=>'name',
			'tooltip'=>KText::_('Internal name of the email notification.'),
			'type'=>'string',
			'label'=>KText::_('Name'),
			'required'=>1,
			'canSortBy'=>true,
			'positionList'=>1,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminnotifications',
			'positionForm'=>200,
		);

		$propDefs['statuscode'] = array(
			'name'=>'statuscode',
			'label'=>KText::_('Status Code'),
			'tooltip'=>KText::_('The numeric status code of the order, which defines if the order is paid, saved etc.'),
			'type'=>'join',
			'isPseudoJoin'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Status'),

			'modelClass'=>'ConfigboxModelOrderrecord',
			'modelMethod'=>'getOrderStatuses',

			'required'=>1,
			'positionList'=>3,
			'canSortBy'=>true,
			'listCellWidth'=>'120px',
			'positionForm'=>300,
		);


		$propDefs['emailcustomerstart'] = array(
			'name'=>'emailcustomerstart',
			'type'=>'groupstart',
			'title'=>KText::_('E-Mail to Customer'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>400,
		);

		$propDefs['send_customer'] = array(
			'name'=>'send_customer',
			'label'=>KText::_('Send email'),
			'tooltip'=>KText::_('Choose yes to send the the email notification to the customer.'),
			'type'=>'boolean',
			'default'=>1,
			'required'=>1,
			'positionForm'=>500,
		);

		$propDefs['subject'] = array(
			'name'=>'subject',
			'label'=>KText::_('Subject'),
			'tooltip'=>KText::_('Email subject of the notification.'),
			'type'=>'translatable',
			'langType'=>32,
			'required'=>1,
			'positionForm'=>600,
			'appliesWhen'=>array(
				'send_customer'=>'1'
			),
		);

		$propDefs['body'] = array(
			'name'=>'body',
			'label'=>KText::_('Email HTML'),
			'tooltip'=>KText::_('HTML version of the email notification for email readers that display HTML.'),
			'type'=>'translatable',
			'langType'=>39,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'required'=>1,
			'positionForm'=>700,
			'appliesWhen'=>array(
				'send_customer'=>'1'
			),
		);

		$propDefs['emailcustomerend'] = array(
			'name'=>'emailcustomerend',
			'type'=>'groupend',
			'positionForm'=>800,
		);

		$propDefs['emailmanagerstart'] = array(
			'name'=>'emailmanagerstart',
			'type'=>'groupstart',
			'title'=>KText::_('E-Mail to Shop Manager'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>900,
		);

		$propDefs['send_manager'] = array(
			'name'=>'send_manager',
			'label'=>KText::_('Send email'),
			'tooltip'=>KText::_('Choose yes to send the the email notification to the shop manager.'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>1000,
		);

		$propDefs['subjectmanager'] = array(
			'name'=>'subjectmanager',
			'label'=>KText::_('Subject'),
			'tooltip'=>KText::_('Email subject of the notification.'),
			'type'=>'translatable',
			'langType'=>36,
			'required'=>0,
			'positionForm'=>1100,
			'appliesWhen'=>array(
				'send_manager'=>'1'
			),
		);

		$propDefs['bodymanager'] = array(
			'name'=>'bodymanager',
			'label'=>KText::_('Email HTML'),
			'tooltip'=>KText::_('HTML version of the email notification for email readers that support HTML.'),
			'type'=>'translatable',
			'langType'=>37,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'required'=>1,
			'positionForm'=>1200,
			'appliesWhen'=>array(
				'send_manager'=>'1'
			),
		);

		$propDefs['emailmanagerend'] = array(
			'name'=>'emailmanagerend',
			'type'=>'groupend',
			'positionForm'=>1300,
		);

		return $propDefs;

	}

	function getUserInfoKeys() {

		$user = ConfigboxUserHelper::getUser();
		
		$userArray = (array)$user;
		
		unset($userArray['order_id'], $userArray['gender'], $userArray['is_temporary'], $userArray['salutation_id']);
		
		$keys = array_keys($userArray);

		// Prepend user custom field keys with customer_ so it won't clash with order custom fields
		foreach ($keys as &$key) {
			if (strpos($key, 'custom_') === 0) {
				$key = 'customer_'.$key;
			}
		}

		sort($keys);
		
		return $keys;
		
	}
		
	function getOrderInfoKeys() {
		return array('order_id','transaction_id','comment', 'order_custom_1', 'order_custom_2', 'order_custom_3', 'order_custom_4', 'order_custom_5', 'order_custom_6', 'order_custom_7', 'order_custom_8', 'order_custom_9', 'order_custom_10');
	}
	
	function getStoreInfoKeys() {

		$store = ConfigboxStoreHelper::getStoreRecord();
		
		foreach ($store as $key=>$value) {
			if (strpos($key,'toggle-') === 0) {
				unset($store->$key);
			}
			if ($key == 'toggle') {
				unset($store->$key);
			}
			if ($key == 'shoplogo') {
				unset($store->$key);
			}
			if ($key == 'invoice') {
				unset($store->$key);
			}
			
			if (strrpos($key,'-') !== false) {
				$newKey = substr($key,0,strrpos($key,'-'));
				$store->$newKey = '';
				unset($store->$key);
			}
		}

		ConfigboxStoreHelper::forgetStoreRecords();

		$keys = array_keys((array)$store);
		return $keys;
	}
	
}