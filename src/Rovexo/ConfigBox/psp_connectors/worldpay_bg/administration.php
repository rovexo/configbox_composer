<?php
function worldpay_bg_get_setting_keys() {
	return array('installation_id','testmode');
}

function worldpay_bg_get_title() {
	return 'WorldPay Business Gateway';
}

function worldpay_bg_get_product_url() {
	return 'http://www.worldpay.com/products/index.php?page=ecom';
}

function worldpay_bg_has_instant_payment_notification() {
	return true;
}