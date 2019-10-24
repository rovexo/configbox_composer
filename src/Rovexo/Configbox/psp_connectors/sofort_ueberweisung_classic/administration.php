<?php
function sofort_ueberweisung_classic_get_setting_keys() {
	return array('sofortueberweisung_user_id', 'sofortueberweisung_project_id', 'sofortueberweisung_notification_password', 'sofortueberweisung_project_password');
}

function sofort_ueberweisung_classic_get_title() {
	return 'SOFORT Überweisung Classic';
}

function sofort_ueberweisung_classic_get_product_url() {
	return 'https://www.sofort.com/eng-DE/merchant/products/sofort-ueberweisung/';
}

function sofort_ueberweisung_has_instant_payment_notification() {
	return true;
}