<?php
function paymill_api_get_setting_keys() {
	return array(
		'testmode', 
		'public_api_key_test', 
		'public_api_key_production',
		'private_api_key_test',
		'private_api_key_production',
		);
}

function paymill_api_get_title() {
	return 'Paymill API';
}

function paymill_api_get_product_url() {
	return 'https://www.paymill.com/en-gb/';
}

function paymill_api_has_instant_payment_notification() {
	return true;
}