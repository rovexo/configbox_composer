<?php
function postfinance_get_setting_keys() {
	return array('psp_id_test','psp_id_production','testmode', 'sha_in_passphrase','sha_out_passphrase');
}

function postfinance_get_title() {
	return 'Postfinance E-Commerce';
}

function postfinance_get_product_url() {
	return 'https://www.postfinance.ch/en/biz/prod/eserv/epay/providing/offer.html';
}

function postfinance_has_instant_payment_notification() {
	return true;
}