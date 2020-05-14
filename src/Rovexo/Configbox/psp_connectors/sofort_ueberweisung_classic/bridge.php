<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

$url = 'https://www.sofortueberweisung.de/payment/start';

$user_id = $this->orderRecord->payment->params->get('sofortueberweisung_user_id','');
$project_id = $this->orderRecord->payment->params->get('sofortueberweisung_project_id','');
$project_password = $this->orderRecord->payment->params->get('sofortueberweisung_project_password','');

$hash_data = array(
		$user_id, // user_id
		$project_id, // project_id
		'', // sender_holder
		'', // sender_account_number
		'', // sender_bank_code
		hsc($this->orderRecord->orderAddress->billingcountry_2_code), // sender_country_id
		number_format($this->orderRecord->payableAmount, 2, '.', ''), // amount
		$this->orderRecord->currency->code, // currency_id, Pflichtparameter bei Hash-Berechnung
		KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname),// reason_1
		'', // reason_2
		$this->orderRecord->id, // user_variable_0
		'', // user_variable_1
		$this->successUrl, // user_variable_2
		$this->cancelUrl, // user_variable_3
		$this->notificationUrl, // user_variable_4
		'', // user_variable_5
		$project_password // project_password
);
$hash_data = hash('sha256', implode('|', $hash_data));

?>
<form id="checkoutform" action="<?php echo $url;?>" method="post">
	<div>
		<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
		<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
		<input type="hidden" name="sender_country_id" value="<?php echo hsc($this->orderRecord->orderAddress->billingcountry_2_code); ?>" />
		<input type="hidden" name="amount" value="<?php echo number_format($this->orderRecord->payableAmount,2, '.', ''); ?>" />
		<input type="hidden" name="currency_id" value="<?php echo hsc($this->orderRecord->currency->code); ?>" />
		<input type="hidden" name="language_id" value="<?php echo strtoupper(KText::getCountryCode());?>" />
		<input type="hidden" name="reason_1" value="<?php echo KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);?>" />
		<input type="hidden" name="user_variable_0" value="<?php echo $this->orderRecord->id;?>" />
		<input type="hidden" name="user_variable_1" value="" />
		<input type="hidden" name="user_variable_2" value="<?php echo $this->successUrl;?>" />
		<input type="hidden" name="user_variable_3" value="<?php echo $this->cancelUrl;?>" />
		<input type="hidden" name="user_variable_4" value="<?php echo $this->notificationUrl;?>" />
		<input type="hidden" name="hash" value="<?php echo $hash_data;?>" />
	</div>

	<div>
		<a class="trigger-redirect-to-psp"></a>
	</div>

</form> 
