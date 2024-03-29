<?php
function wirecard_qpay_get_setting_keys() {
	return array('customer_id_production','secret_production','customer_id_test','secret_test','testmode', 'service_url', 'paymenttype' );
}

function wirecard_qpay_get_title() {
	return 'Wirecard Checkout Page';
}

function wirecard_qpay_get_product_url() {
	return 'https://www.wirecard.at/en/solutions/products/checkout-page/';
}

function wirecard_qpay_has_instant_payment_notification() {
	return true;
}