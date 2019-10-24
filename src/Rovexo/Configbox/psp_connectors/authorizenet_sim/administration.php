<?php
function authorizenet_sim_get_setting_keys() {
	return array('api_login_id','transactionkey','testmode');
}

function authorizenet_sim_get_title() {
	return 'Authorize.Net SIM';
}

function authorizenet_sim_get_product_url() {
	return 'http://www.authorize.net/solutions/merchantsolutions/onlinemerchantaccount/';
}

function authorizenet_has_instant_payment_notification() {
	return true;
}