<?php
function stripe_get_setting_keys() {
	return array(
		'testmode', 
		'public_api_key_test', 
		'public_api_key_production',
		'private_api_key_test',
		'private_api_key_production',
		);
}

function stripe_get_title() {
	return 'Stripe';
}

function stripe_get_product_url() {
	return 'https://stripe.com/';
}

function stripe_has_instant_payment_notification() {
	return true;
}