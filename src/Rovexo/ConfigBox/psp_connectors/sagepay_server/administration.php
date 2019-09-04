<?php
function sagepay_server_get_setting_keys() {
	return array('vendor_name_test','vendor_name_production', 'testmode');
}

function sagepay_server_get_title() {
	return 'SagePay Server Integration';
}

function sagepay_server_get_product_url() {
	return 'http://www.sagepay.co.uk/our-payment-solutions/online-payments';
}

function sagepay_server_has_instant_payment_notification() {
	return true;
}