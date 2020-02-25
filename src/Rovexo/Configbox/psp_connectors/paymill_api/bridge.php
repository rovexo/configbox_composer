<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

// Get the public API key
$testMode = ($this->orderRecord->payment->params->get('testmode',0) == 1);

if ($testMode) {
	$apiKey = $this->orderRecord->payment->params->get('public_api_key_test','');
}
else {
	$apiKey = $this->orderRecord->payment->params->get('public_api_key_production','');
}
?>

<a class="trigger-redirect-to-psp"></a>

<h2 class="step-title"><?php echo KText::_('Payment');?></h2>

<div class="payment-form row">
	<div class="col-md-6 col-md-offset-3">


		<div class="paymill-payment-info">
			<?php echo $this->orderRecord->payment->description; ?>
		</div>

		<div class="payment-form-credit-card">

			<div class="form-items">

				<div class="form-item form-item-card-number">
					<label class="label-card-number" for="cc-card-number"><?php echo KText::_('Credit Card');?></label>
					<input class="form-control input-card-number" id="cc-card-number" type="text" value="<?php echo ($testMode) ? '4111111111111111':'';?>" />
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="form-item form-item-card-cvc">
							<label class="label-card-cvc" for="cc-card-cvc"><?php echo KText::_('CVC');?></label>
							<input class="form-control input-card-cvc" id="cc-card-cvc" type="text" value="<?php echo ($testMode) ? '111':'';?>" />
						</div>
					</div>
					<div class="col-xs-6">


						<label class="label-expiry" for="cc-card-expiry-month"><?php echo KText::_('Valid until');?></label>

						<div class="row">
							<div class="col-xs-6">
								<select class="input-card-expiry-month form-control" id="cc-card-expiry-month">
									<?php for ($i = 1; $i <= 12; $i++) { ?>
										<option value="<?php echo sprintf("%02s", $i);?>"><?php echo sprintf("%02s", $i);?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-xs-6">
								<label class="sr-only" for="cc-card-expiry-year">Year</label>
								<select class="input-card-expiry-year form-control" id="cc-card-expiry-year">
									<?php $selected = date("Y") + 1;?>
									<?php for ($i = date("Y"); $i <= (date("Y") + 15); $i++) { ?>
										<option value="<?php echo $i;?>"<?php echo ($i == $selected) ? ' selected="selected"':'';?>><?php echo $i;?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="form-item form-item-card-holder">
					<label class="label-card-holder" for="cc-card-holder"><?php echo KText::_('Card Holder');?></label>
					<input class="form-control input-card-holder" id="cc-card-holder" type="text" value="<?php echo ($this->orderRecord->orderAddress->billingcompanyname) ? hsc($this->orderRecord->orderAddress->billingcompanyname) : hsc($this->orderRecord->orderAddress->billingfirstname.' '.$this->orderRecord->orderAddress->billinglastname);?>" />
				</div>

			</div>

			<div class="payment-feedback"></div>

			<div class="form-buttons">
				<a class="trigger-capture-payment btn btn-primary" type="submit"><span class="btn-text"><?php echo KText::_('Pay');?></span></a>
			</div>

		</div>

	</div>
</div>

<div class="clear"></div>

<script type="text/javascript" src="https://bridge.paymill.com/"></script>


<script type="text/javascript">
//TODO: see how to make this AMD-like
// Set the public key
var PAYMILL_PUBLIC_KEY = '<?php echo hsc($apiKey);?>';


var formlang = 'dummy';

//Creditcard
var translation = [];
translation[formlang] = {};
translation[formlang]["error"] = {};
translation[formlang]["error"]["form"] = {};
translation[formlang]["error"]["form"]["card-paymentname"] = '<?php echo KText::_('Credit card');?>';
translation[formlang]["error"]["form"]["card-number"] = '<?php echo KText::_('Card number');?>';
translation[formlang]["error"]["form"]["card-cvc"] = '<?php echo KText::_('CVC');?>';
translation[formlang]["error"]["form"]["card-holdername"] = '<?php echo KText::_('Card holder');?>';
translation[formlang]["error"]["form"]["card-expiry"] = '<?php echo KText::_('Valid until');?>';
translation[formlang]["error"]["form"]["amount"] = '<?php echo KText::_('Amount');?>';
translation[formlang]["error"]["form"]["currency"] = '<?php echo KText::_('Currency');?>';
translation[formlang]["error"]["form"]["submit-button"] = '<?php echo KText::_('Submit');?>';

//Elv
translation[formlang]["error"]["form"]["elv-paymentname"] = '<?php echo KText::_('Direct Debit');?>';
translation[formlang]["error"]["form"]["elv-account"] = '<?php echo KText::_('Account number');?>';
translation[formlang]["error"]["form"]["elv-holdername"] = '<?php echo KText::_('Account holder');?>';
translation[formlang]["error"]["form"]["elv-bankcode"] = '<?php echo KText::_('Bankcode');?>';

//Error
translation[formlang]["error"] = {};
translation[formlang]["error"]["field_invalid_card_number"] = '<?php echo KText::_('Invalid card number.');?>';
translation[formlang]["error"]["field_invalid_card_cvc"] = '<?php echo KText::_('Invalid CVC.');?>';
translation[formlang]["error"]["field_invalid_card_exp"] = '<?php echo KText::_('Invalid expiration date.');?>';
translation[formlang]["error"]["field_invalid_card_holder"] = '<?php echo KText::_('Please enter the card holders name.');?>';
translation[formlang]["error"]["invalid-elv-holdername"] = '<?php echo KText::_('Please enter the account holders name.');?>';
translation[formlang]["error"]["invalid-elv-accountnumber"] = '<?php echo KText::_('Please enter a valid account number.');?>';
translation[formlang]["error"]["invalid-elv-bankcode"] = '<?php echo KText::_('Please enter a valid bank code.');?>';


if (typeof(com_configbox) == 'undefined') {
	com_configbox = {};
}

com_configbox.amount = <?php echo number_format($this->orderRecord->payableAmount,2);?>;
com_configbox.amountInt = <?php echo number_format($this->orderRecord->payableAmount,2) * 100;?>;
com_configbox.currencyCode = '<?php echo hsc($this->orderRecord->currency->code);?>';
com_configbox.successUrl = '<?php echo $this->successUrl;?>';

cbrequire(['cbj'], function(cbj) {

	// Show the payment form, hide other buttons
	cbj('.wrapper-psp-bridge').show();
	cbj('.button-back-to-cart').hide();
	cbj('.trigger-place-order').hide();

	cbj('#agreement-terms').prop('disabled',true);
	cbj('#agreement-refund-policy').prop('disabled',true);

	if (typeof(cbj.scrollTo) != 'undefined') {
		cbj.scrollTo('.wrapper-psp-bridge', 800);
	}


	cbj(document).on('click', '.trigger-capture-payment', function(){

		// Block clicks while processing
		if (cbj(this).hasClass('processing')) {
			return;
		}

		// Set the CSS class flag for processing
		cbj(this).addClass('processing');

		// Set the language for translations
		var formlang = 'dummy';

		// Prepare the params for the token request
		var params = {
			amount_int	: com_configbox.amountInt,
			currency	: com_configbox.currencyCode,
			number		: cbj(this).closest('.payment-form').find('.input-card-number').val(),
			exp_month	: cbj(this).closest('.payment-form').find('.input-card-expiry-month').val(),
			exp_year	: cbj(this).closest('.payment-form').find('.input-card-expiry-year').val(),
			cvc			: cbj(this).closest('.payment-form').find('.input-card-cvc').val(),
			cardholder	: cbj(this).closest('.payment-form').find('.input-card-holder').val()
		};

		// Initialize validation
		cbj('.form-item.invalid').removeClass('invalid');
		var dataValid = true;

		// Check card number
		if (paymill.validateCardNumber(params.number) == false) {
			cbj(".payment-feedback").text(translation[formlang]["error"]["field_invalid_card_number"]);
			cbj('.form-item-card-number').addClass('invalid');
			dataValid = false;
		}

		// Check CVC
		if (cbj.trim(params.cvc) == '') {
			cbj(".payment-feedback").text(translation[formlang]["error"]["field_invalid_card_cvc"]);
			cbj('.form-item-card-cvc').addClass('invalid');
			dataValid = false;
		}

		// Check card holder
		if (cbj.trim(params.cardholder) == '') {
			cbj(".payment-feedback").text(translation[formlang]["error"]["field_invalid_card_holder"]);
			cbj('.form-item-card-holder').addClass('invalid');
			dataValid = false;
		}

		// Check expiry date
		if (paymill.validateExpiry(params.exp_month, params.exp_year) == false) {
			cbj(".payment-feedback").text(translation[formlang]["error"]["field_invalid_card_exp"]);
			cbj('.form-item-card-expiry').addClass('invalid');
			dataValid = false;
		}

		// Show the payment feedback and bounce
		if (dataValid == false) {
			cbj(this).removeClass('processing');
			cbj(".payment-feedback").show();
			return;
		}
		else {
			cbj(".payment-feedback").hide();
		}

		// Get the token
		paymill.createToken(params, function(error, result){


			if (error) {

				// Show feedback on errors
				cbj(".payment-feedback").text(translation[formlang]["error"][error.apierror]).show();

				// Init the validation flags
				cbj('.form-item.invalid').removeClass('invalid');

				// Mark the invalid fields
				switch (error.apierror) {
					case 'field_invalid_card_number':
						cbj('.form-item-card-number').addClass('invalid');
						break;

					case 'field_invalid_card_cvc':
						cbj('.form-item-card-cvc').addClass('invalid');
						break;

					case 'field_invalid_card_exp':
						cbj('.form-item-card-expiry').addClass('invalid');
						break;
				}

				cbj(".trigger-capture-payment").removeClass('processing');
			}
			else {

				// Hide any leftover feedback
				cbj(".payment-feedback").hide();

				// Go and capture that payment
				cbj.ajax({
					url: com_configbox.entryFile,
					dataType: 'json',

					data: {
						option			: 'com_configbox',
						controller		: 'payments',
						task			: 'capturePayment',
						lang			: com_configbox.langSuffix,
						format			: 'raw',
						tmpl			: 'component',
						token			: result.token,
						connector_name 	: 'paymill_api'
					},

					success: function(data) {

						cbj(".trigger-capture-payment").removeClass('processing');

						// Go to the successUrl
						if (data.success == true) {
							window.location.href = com_configbox.successUrl;
						}
						// Show the feedback
						else {
							if (data.errors) {
								cbj('.payment-feedback').text(data.errors.join('<br />')).show();
							}
							else {
								cbj('.payment-feedback').hide();
							}
						}
					}

				});

			}

		});

		return;

	});


});

</script>