<?php
function multisafepay_connect_get_setting_keys() {
	return array(
			'account_id_test',
			'account_id_production',
			'site_id_test',
			'site_id_production',
			'site_code_test',
			'site_code_production',
			'testmode');
}

function multisafepay_connect_get_title() {
	return 'MultiSafepay Connect';
}

function multisafepay_connect_get_product_url() {
	return 'https://www.multisafepay.com/en/connect/';
}

function multisafepay_connect_has_instant_payment_notification() {
	return true;
}