
cbj(document).ready(function() {

	// CART: Quantity dropdown functionality
	cbj(document).on('change', 'select.input-product-quantity', function(){
		cbj(this).closest('form').submit();
	});

	// CART: Update quantity button functionality
	cbj(document).on('click', '.button-update-quantity', function(){
		cbj(this).closest('form').submit();
	});

	// ANYWHERE: New tab links	
	cbj(document).on('click', '.new-tab', function(event){
		window.open(this.href);
		event.preventDefault();
	});

	// ANYWHERE: Form submit buttons
	cbj(document).on('click', '.form-submit-button', function(){
		cbj(this).closest('form').submit();
	});

	// ANYWHERE: Do chosen dropdowns where needed
	cbj('.cb-content .chosen-dropdown').each(function(){
		cbj(this).chosen();
	});

});

// var com_configbox = (function(com_configbox) {
//
// 	com_configbox.urlBase = '';
// 	com_configbox.tinyMceBaseUrl = '';
// 	com_configbox.canQuickEdit = false;
// 	com_configbox.isRequestError = false;
// 	com_configbox.redirectUrl = '';
// 	com_configbox.lockPageOnRequired = '';
// 	com_configbox.entryFile = '';
// 	com_configbox.decimal_symbol = '';
// 	com_configbox.configuratorUpdateUrl = '';
// 	com_configbox.langTag = '';
// 	com_configbox.langSuffix = '';
// 	com_configbox.modalOverlayOpacity = '';
// 	com_configbox.agreeToTerms = null;
// 	com_configbox.agreeToRefundPolicy = null;
// 	com_configbox.pspBridgeViewUrl = '';
// 	com_configbox.addressViewUrl = '';
// 	com_configbox.deliveryViewUrl = '';
// 	com_configbox.paymentViewUrl = '';
// 	com_configbox.recordViewUrl = '';
// 	com_configbox.customerFieldDefinitions = '';
// 	com_configbox.questions = {
// 		1 : {
// 			answers : {
// 				1 : {
// 					'applies' : true,
// 					'id' : 1
// 				}
// 			}
// 		}
// 	};
// 	com_configbox.lang = com_configbox.lang || {};
// 	com_configbox.lang.DATEFORMAT_CALENDAR_JS = '';
// 	com_configbox.cartPositionId = null;
//
//
// })(com_configbox || {});


var com_configbox = {

	refreshOrderAddress : function() {
		// Refresh the order address display and toggle
		cbj('.wrapper-order-address .order-address-display').load(com_configbox.addressViewUrl,function(){
			cbj('.wrapper-order-address .order-address-display').slideDown();
			cbj('.wrapper-order-address .order-address-form').slideUp();
		});
	},

	refreshDeliveryOptions : function() {
		cbj('.wrapper-delivery-options').load(com_configbox.deliveryViewUrl);
	},

	refreshPaymentOptions : function() {
		cbj('.wrapper-payment-options').load(com_configbox.paymentViewUrl);
	},

	refreshOrderRecord : function() {
		cbj('.wrapper-order-record').load(com_configbox.recordViewUrl);
	},

	refreshPositionDetails : function() {

	}

};


/* ORDER FILE UPLOADS - START */



cbj(document).ready(function(){

	if (cbj('#view-userorder').length) {
		Kenedo.runSubviewReadyFunctions('view-adminorder');
	}

	if (cbj('#view-user #layout-login').length) {
		cbj('#configbox_email').focus();
	}

	if (cbj('#view-user #layout-register').length) {
		cbj('#configbox_firstname').focus();
	}

	cbj('.button-cancel-info-request').click(function(){
		if (typeof(window.parent.cbj) != 'undefined') {
			window.parent.cbj.colorbox.close();
		}
	});

	cbj('#modalbox table td').click(function() {

		var value = cbj(this).text();

		var target = cbj(this).closest('#modalbox').data('target');
		var displayTarget = cbj(this).closest('#modalbox').data('displayTarget');

		window.parent.cbj('#'+target).val(value);
		window.parent.cbj('#'+displayTarget).html(value);
		window.parent.cbj('#'+target).trigger('change');
		window.parent.cbj.colorbox.close();

	});

});

/* ORDER FILE UPLOADS - END */



/* CHECKOUT PAGE - START */

cbj(document).ready(function(){

	// Show the order address form
	cbj(document).on('click', '.trigger-show-order-address-form', function(){
		cbj('.wrapper-order-address .order-address-display').slideUp();
		cbj('.wrapper-order-address .order-address-form').slideDown();
	});

	// Store the order address
	cbj(document).on('click', '.trigger-save-order-address', function(){

		// Add the spinner to the button
		cbj(this).addClass('processing');

		// Get the customer data from the customer form
		var requestData = com_configbox.getCustomerFormData();

		// Add the application parameters
		requestData.option = 'com_configbox';
		requestData.controller = 'checkout';
		requestData.task = 'storeOrderAddress';
		requestData.format = 'raw';
		requestData.lang = com_configbox.langSuffix;
		requestData.comment = cbj('#comment').val();

		// Submit the order address information
		cbj.ajax({
			url: com_configbox.entryFile,
			data: requestData,
			dataType: 'json',
			type: 'post',
			context: cbj(this)
		})

			// When done..
			.done(function(response) {

				// Remove the spinner
				cbj('.trigger-save-order-address').removeClass('processing');

				// Show validation issues (or errors)
				if (response.success === false) {
					if (response.validationIssues.length) {
						com_configbox.displayValidationIssues(response.validationIssues);
						return;
					}
					else {
						alert(response.errors.join("\n"));
						return;
					}
				}

				// Remove all invalid classes from fields
				cbj('.order-address-field').removeClass('invalid').removeClass('valid');

				// Refresh the rest of the checkout sub-views
				com_configbox.refreshOrderAddress();
				com_configbox.refreshDeliveryOptions();
				com_configbox.refreshPaymentOptions();
				com_configbox.refreshOrderRecord();
				com_configbox.refreshPositionDetails();

			});

	});

	// Store delivery method
	cbj(document).on('change','#subview-delivery .option-control',function(){
		cbj(this).closest('li').addClass('processing');
		var id = cbj(this).val();
		com_configbox.setDeliveryOption(id)
			.done(function() {
				// Refresh the sub views
				com_configbox.refreshPaymentOptions();
				com_configbox.refreshOrderRecord();
				cbj('#subview-delivery li.processing').removeClass('processing');
			});
	});

	// Store payment method
	cbj(document).on('change','#subview-payment .option-control',function(){
		cbj(this).closest('li').addClass('processing');
		var id = cbj(this).val();
		com_configbox.setPaymentOption(id)
			.done(function() {
				// Refresh the sub views
				com_configbox.refreshOrderRecord();
				cbj('#subview-payment li.processing').removeClass('processing');
			});
	});

	// Place the order
	cbj(document).on('click','.trigger-place-order',function(){

		// Don't process if clicked already
		if (cbj(this).hasClass('processing')) {
			return;
		}
		// Add the css class flag
		cbj(this).addClass('processing');

		var termsAgreementMissing = false;
		var refundsAgreementMissing = false;

		if (com_configbox.agreeToTerms) {
			var agreed = cbj('#agreement-terms').prop('checked');
			if (agreed == false) {
				termsAgreementMissing = true;
			}
		}
		if (com_configbox.agreeToRefundPolicy) {
			var agreedRp = cbj('#agreement-refund-policy').prop('checked');
			if (agreedRp == false) {
				refundsAgreementMissing = true;
			}
		}

		var agreementsMissingMessage = '';

		// If both terms and refund policy missing
		if (termsAgreementMissing && refundsAgreementMissing) {
			agreementsMissingMessage = com_configbox.lang.checkoutPleaseAgreeBoth;
		}
		// If the terms missing
		if (termsAgreementMissing && !refundsAgreementMissing) {
			agreementsMissingMessage = com_configbox.lang.checkoutPleaseAgreeTerms;
		}
		// If the refund policy missing
		if (!termsAgreementMissing && refundsAgreementMissing) {
			agreementsMissingMessage = com_configbox.lang.checkoutPleaseAgreeRefundPolicy;
		}

		// Show and bounce if agreements are missing
		if (agreementsMissingMessage) {
			cbj(this).removeClass('processing');
			alert(agreementsMissingMessage);
			return false;
		}

		// Place the order (set's the status to 'ordered');
		com_configbox.placeOrder()
			.done(function(response){

				if (response.success == false) {
					cbj('.trigger-place-order').removeClass('processing');
					alert(response.errors.join("\n"));
				}
				else {
					// Then load the PSP bridge and get going with the payment
					cbj('.wrapper-psp-bridge').load(com_configbox.pspBridgeViewUrl,function(){
						cbj('.trigger-redirect-to-psp').trigger('click');
					});
				}
			});

	});

	// Make sure firing click events on the .trigger-redirect-to-psp link redirect (apparantly there are cases where
	// it's not the case). After order placement the system triggers that click.
	cbj(document).on('click', '.trigger-redirect-to-psp', function(){
		if (typeof(cbj(this).attr('href')) != 'undefined') {
			window.location = cbj(this).attr('href');
		}
		if(cbj(this).closest('form').length) {
			cbj(this).closest('form').submit();
		}
	});

});

/* CHECKOUT PAGE - END */


/* RULE EDITOR FIELD - START */
cbj(document).ready(function(){



});
/* RULE EDITOR FIELD - END */



/* CUSTOMER FORM - START */
cbj(document).ready(function(){

	// Hide or show the delivery address section
	cbj('body').on('change', '#view-customerform .trigger-toggle-same-delivery', function(){

		if (cbj(this).prop('checked') == true) {
			// Hide the delivery fields
			cbj(this).closest('.customer-form-sections').removeClass('show-delivery-fields');
		}
		else {

			// Copy over billing to delivery (but only if user didn't toggle before)
			if (cbj(this).data('got-toggled') == 'undefined') {

				cbj(this).data('got-toggled', true);

				var customerFields = cbj(this).closest('#view-customerform').data('customer-fields');
				var formType = cbj(this).closest('#view-customerform').find('#form_type').val();

				// Copy info over from billing
				for (var key in customerFields) {
					if (customerFields.hasOwnProperty(key)) {
						var obj = customerFields[key];
						if (obj['show_'+formType] == '1') {
							if (cbj('#'+obj.field_name).length && cbj('#billing'+obj.field_name).length) {
								var billingFieldValue = cbj('#billing'+obj.field_name).val();
								cbj('input[name='+obj.field_name+']').val(billingFieldValue);
							}
						}
					}
				}

			}

			// Make the delivery fields show up
			cbj(this).closest('.customer-form-sections').addClass('show-delivery-fields');

		}

	});

	// Checking the box 'I have an account' makes the login box appear (and vice versa)
	cbj('body').on('change', '.recurring-customer-login #show-login', function() {
		var loginBox = cbj(this).closest('.recurring-customer-login').find('.login-wrapper').show();
		if (cbj(this).prop('checked') == true) {
			loginBox.show();
		}
		else {
			loginBox.hide();
		}
	});

	// Clicks on the customer form login button make a call to the user controller and then the customer form reloads
	cbj('body').on('click', '.recurring-customer-login .trigger-login', function() {

		// Deal with multiple mouse clicks
		if (cbj(this).hasClass('processing')) {
			return;
		}

		// Add the spinner to the button
		cbj(this).addClass('processing');

		var wrapper = cbj(this).closest('.recurring-customer-login');

		// Reset any feedback
		wrapper.find('.feedback').text('');

		var username = wrapper.find('.input-username').val();
		var password = wrapper.find('.input-password').val();

		com_configbox.requestLogin(username, password)

			.done(function(response){
				if (response.success === false) {
					if (wrapper.find('.login-box .feedback').length !== 0) {
						wrapper.find('.login-box .feedback').text(response.errorMessage);
					}
					else {
						alert(response.errorMessage);
					}
				}
				else {
					var refreshUrl = cbj('#view-customerform').data('view-url');
					cbj('#view-customerform').load(refreshUrl);
				}
			})
			.always(function(){
				wrapper.find('.login-box .processing').removeClass('processing');
			});

	});

	// Clicking on 'Recover Password' makes the right box appear
	cbj('body').on('click', '.recurring-customer-login .trigger-recover-password', function() {

		// Copy the email address from login-box over to recover box
		var email = cbj(this).closest('.login-wrapper').find('.input-username').val();
		if (email) {
			cbj(this).closest('.recurring-customer-login').find('.recover-box .input-username').val(email);
		}

		cbj(this).closest('.recurring-customer-login').find('.login-box').hide();
		cbj(this).closest('.recurring-customer-login').find('.recover-box').show();
		cbj(this).closest('.recurring-customer-login').find('.change-password-box').hide();
	});

	// Clicking on 'Cancel' brings back the login box
	cbj('body').on('click', '.recurring-customer-login .trigger-cancel-recovery', function() {
		cbj(this).closest('.recurring-customer-login').find('.login-box').show();
		cbj(this).closest('.recurring-customer-login').find('.recover-box').hide();
		cbj(this).closest('.recurring-customer-login').find('.change-password-box').hide();
	});

	// Clicks on 'Recover Password' make the server send out an email with a code and the next panel appears
	cbj('body').on('click', '.recurring-customer-login .trigger-request-verification-code', function() {

		// Deal with multiple mouse clicks
		if (cbj(this).hasClass('processing')) {
			return;
		}

		// Add the spinner to the button
		cbj(this).addClass('processing');

		// Get the email address from the form
		var email = cbj(this).closest('.recover-box').find('.input-username').val();

		// Get a reference to the box wrapper
		var wrapper = cbj(this).closest('.recurring-customer-login');

		// Reset any feedback
		wrapper.find('.feedback').text('');

		// Get the verification email sent
		com_configbox.requestPasswordChangeVerificationCode(email)
			.done(function(response){
				if (response.success === false) {
					if (wrapper.find('.recover-box .feedback').length !== 0) {
						wrapper.find('.recover-box .feedback').text(response.errorMessage);
					}
					else {
						alert(response.errorMessage);
					}
				}
				else {
					wrapper.find('.recover-box').hide();
					wrapper.find('.change-password-box').show();
				}
			})
			.always(function(){
				wrapper.find('.recover-box .processing').removeClass('processing');
			});

	});

	// Clicking on 'Change Password' sends code and new password to the server. If all goes well, the customer
	// gets logged in and the customer form reloads.
	cbj('body').on('click', '.recurring-customer-login .trigger-change-password', function() {

		// Deal with multiple mouse clicks
		if (cbj(this).hasClass('processing')) {
			return;
		}

		// Add the spinner to the button
		cbj(this).addClass('processing');

		// Get the email address from the form
		var code = cbj(this).closest('.change-password-box').find('.input-verification').val();
		var password = cbj(this).closest('.change-password-box').find('.input-new-password').val();

		// Get a reference to the box wrapper
		var wrapper = cbj(this).closest('.recurring-customer-login');

		// Reset any feedback
		wrapper.find('.feedback').text('');

		// Send the email for the verification code
		com_configbox.requestPasswordChange(code, password, true)

			/**
			 * @param {JsonResponses.submitPasswordAndCode} response
			 */
			.done(function(response) {

				if (response.success === false) {
					cbj.each(response.errors, function(i, error){
						wrapper.find('.change-password-box .feedback').append('<div>'+error.message+'</div>');
					});
				}
				else {
					var refreshUrl = cbj('#view-customerform').data('view-url');
					cbj('#view-customerform').load(refreshUrl);
				}
			})
			.always(function(){
				wrapper.find('.processing').removeClass('processing');
			});

	});

	// Hitting the enter key on the customer form login form triggers a click on the respective primary button
	cbj('body').on('keyup', '.recurring-customer-login', function(event) {

		// We're only interested in 'Enter'
		if(event.which != 13) {
			return;
		}

		// Prepare the wrapper for convenience
		var wrapper = cbj(this).closest('.recurring-customer-login');

		// See what's the origin, afterwards check in which box we're in
		var origin = cbj(event.target);

		if (origin.closest('.login-box').length !== 0) {
			wrapper.find('.trigger-login').trigger('click');
		}

		if (origin.closest('.recover-box').length !== 0) {
			wrapper.find('.trigger-request-verification-code').trigger('click');
		}
		if (origin.closest('.change-password-box').length !== 0) {
			wrapper.find('.trigger-change-password').trigger('click');
		}

	});

});

/**
 * Returns the current contents of a customer form
 * @returns {{String}}
 * @see ConfigboxViewCustomerform
 */
com_configbox.getCustomerFormData = function() {

	var customerData = {};

	// Loop through all inputs and collect customer data
	cbj('#view-customerform :input').each(function(i, item) {

		if (!cbj(item).attr('name')) {
			return;
		}
		if (cbj(item).is('input[type=radio]') && cbj(item).prop('checked') == false) {
			return;
		}
		if (cbj(item).is('input[type=checkbox]') && cbj(item).prop('checked') == false) {
			return;
		}
		if (cbj(item).is('input[type=checkbox]') && cbj(item).prop('checked') == true) {
			customerData[cbj(item).attr('name')] = '0';
		}

		customerData[cbj(item).attr('name')] = cbj(item).val();

	});

	// If delivery is same, replace any delivery values with their billing counterparts
	if (customerData.samedelivery) {
		cbj.each(customerData, function(fieldName, value) {
			if (fieldName.indexOf('billing') == 0) {
				var pendant = fieldName.substr(7);

				if (typeof(customerData[pendant]) != 'undefined') {
					customerData[pendant] = value;
				}
			}
		});
	}

	return customerData;

};

/**
 * Shows validation issues in customer form
 * @param {(Array|JsonResponses.storeCustomerResponseData.validationIssues)} issues
 * @see ConfigboxViewCustomerform
 */
com_configbox.displayValidationIssues = function(issues) {

	// Remove any css flags for invalid fields
	cbj('#view-customerform .customer-field:visible').removeClass('invalid').addClass('valid');

	// Set flags for fields with issues, set the issue message
	for (var i in issues) {
		if (issues.hasOwnProperty(i)) {
			cbj('.customer-field-'+issues[i].fieldName).removeClass('valid').addClass('invalid');
			cbj('.customer-field-'+issues[i].fieldName).find('.validation-tooltip').data('message', issues[i].message);
		}
	}

	// Set up the tooltips
	com_configbox.initValidationTooltips();

};

/**
 *
 */
com_configbox.removeValidationIssues = function() {
	// Remove any css flags for invalid fields
	cbj('#view-customerform .customer-field:visible').removeClass('invalid').removeClass('valid');
	cbj('#view-customerform .validation-tooltip').data('message', '');
	com_configbox.initValidationTooltips();
};

/**
 * Inits jQueryUI tooltips on the customer data form
 */
com_configbox.initValidationTooltips = function() {

	// Set up the tooltips
	cbj(document).tooltip({
		items: ".validation-tooltip",
		content : function(){
			return cbj(this).data('message');
		},
		position : {
			my: "center bottom-20",
			at: "center top",
			using: function( position, feedback ) {
				cbj( this ).css( position );
				cbj( "<div>" )
					.addClass( "arrow" )
					.addClass( feedback.vertical )
					.addClass( feedback.horizontal )
					.appendTo( this );
			}
		}
	});

};



/* CUSTOMER FORM - END */

cbj(document).ready(function(){

	Kenedo.setFormTaskHandler('admincustomer', function(viewName, task) {

		if (task == 'cancel') {
			window.location.href = Kenedo.base64UrlDecode(cbj('#return').val());
		}

		// Add the spinner to the button
		cbj(this).addClass('processing');

		var requestData = com_configbox.getCustomerFormData();

		requestData.option = 'com_configbox';
		requestData.controller = 'user';
		requestData.task = 'store';
		requestData.format = 'raw';
		requestData.lang = com_configbox.langSuffix;

		// Do the request, pass it back
		cbj.ajax({
			url: com_configbox.entryFile,
			data: requestData,
			dataType: 'json',
			type: 'post',
			context: cbj(this),
			success: function(response) {
				// Remove the spinner
				cbj('.trigger-store-customer-form').removeClass('processing');

				if (response.success === false) {
					if (response.validationIssues.length) {
						com_configbox.displayValidationIssues(response.validationIssues);
						return;
					}
					else {
						alert(response.errors.join("\n"));
						return;
					}
				}

				// Remove all invalid classes from fields
				cbj('.customer-field').removeClass('invalid').removeClass('valid');

				if (task == 'apply') {
					Kenedo.clearMessages();
					Kenedo.addMessage(response.message, 'notice');
					Kenedo.showMessages();
				}
				if (task == 'store') {
					window.location.href = Kenedo.base64UrlDecode(cbj('#return').val());
				}


			}
		});

	});


});
