<?php
function billsafe_invoice_get_setting_keys() {
	return array('testmode','merchant_id','merchant_license_test','merchant_license_live','application_signature');
}

function billsafe_invoice_get_title() {
	return 'BillSAFE Rechnung';
}

function billsafe_invoice_get_product_url() {
	return 'http://www.billsafe.de/';
}

function billsafe_invoice_has_instant_payment_notification() {
	return true;
}