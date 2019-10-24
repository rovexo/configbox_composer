<?php
function vrpay_virtuell_get_setting_keys() {
	return array('partner_id','password','brand');
}

function vrpay_virtuell_get_title() {
	return 'VR pay virtuell';
}

function vrpay_virtuell_get_product_url() {
	return 'http://www.vr-pay.de/index.php/ecommerce/vr-pay-virtuell';
}

function vrpay_virtuell_has_instant_payment_notification() {
	return true;
}