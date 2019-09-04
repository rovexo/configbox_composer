<?php
function quickpay_get_setting_keys() {
	return array('merchant','md5secret','testmode');
}

function quickpay_get_title() {
	return 'QuickPay';
}

function quickpay_get_product_url() {
	return 'http://quickpay.net';
}

function quickpay_has_instant_payment_notification() {
	return true;
}