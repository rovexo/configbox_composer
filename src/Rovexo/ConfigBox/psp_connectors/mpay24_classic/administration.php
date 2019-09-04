<?php
function mpay24_classic_get_setting_keys() {
	return array('merchant_id_test','merchant_id_production','password_test', 'password_production', 'testmode');
}

function mpay24_classic_get_title() {
	return 'mPAY24 Classic';
}

function mpay24_classic_get_product_url() {
	return 'https://www.mpay24.com/web/de/produkte/mpay24-classic.html';
}

function mpay24_classic_has_instant_payment_notification() {
	return true;
}