<?php
function paypal_wpstandard_get_setting_keys() {
	return array('paypalid','testmode');
}

function paypal_wpstandard_get_title() {
	return 'PayPal Payments Standard';
}

function paypal_wpstandard_get_product_url() {
	return 'https://www.paypal.com/webapps/mpp/paypal-payments-standard';
}

function paypal_wpstandard_has_instant_payment_notification() {
	return true;
}