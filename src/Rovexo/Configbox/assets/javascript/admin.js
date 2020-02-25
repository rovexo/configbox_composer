/* global define, cbrequire: false */
/* jshint -W116 */
/**
 * @module configbox/admin
 */
define(['cbj', 'kenedo', 'configbox/server', 'cbj.ui', 'configbox/ruleEditor', 'configbox/calcEditor'], function(cbj, kenedo, server) {

	"use strict";

	/**
	 * @exports configbox/admin
	 */
	var admin = {

		initBackend: function() {

			// ALL BACKEND: Tree toggles for the product tree
			cbj('body').on('click', '.view-item-picker .sub-list-trigger', function() {

				if (cbj(this).is('.trigger-opened')) {
					cbj(this).removeClass('trigger-opened');
					cbj(this).closest('li').children('.sub-list').removeClass('list-opened');
				}
				else {
					cbj(this).addClass('trigger-opened');
					cbj(this).closest('li').children('.sub-list').addClass('list-opened');
				}
			});

			// ALL BACKEND: Toggle the off-canvas menu
			cbj(document).on('click', '.trigger-toggle-offcanvas', function() {
				cbj('.row-offcanvas').toggleClass('active');
				cbj(this).toggleClass('fa-bars').toggleClass('fa-window-close');
			});

			// DASHBOARD: Set up the toggles in the middle
			cbj('.toggle-wrapper').on('click', '.toggle-handle', function(){
				cbj(this).closest('.toggle-wrapper').find('.toggle-content').toggle();
				cbj(this).toggleClass('opened');
			});

			// DASHBOARD: Even out the height of the three boxes
			var height = cbj('.left-part').outerHeight();
			cbj('.news').outerHeight(height);
			cbj('.configbox-mainmenu').css('min-height',parseInt(height) - 27);

			// LICENSE FORM: Store license key button
			cbj(document).on('click', '.trigger-store-license-key', function(){
				cbj(this).closest('form').submit();
			});

			kenedo.registerSubviewReadyFunction('view-adminelement', function() {
				if (cbj('#view-adminelement .not-using-shapediver').length !== 0) {
					cbj('.property-group-shapediver_start').addClass('hidden');
				}
			});

			kenedo.registerSubviewReadyFunction('view-adminoptionassignment', function() {
				if (cbj('#view-adminoptionassignment .not-using-shapediver').length !== 0) {
					cbj('.property-group-shapediver_start').addClass('hidden');
				}
			});

			kenedo.registerSubviewReadyFunction('view-adminmainmenu', function() {

				// Tree toggles for the main menu
				cbj(document).on('click', '#view-adminmainmenu .trigger-toggle-sub-items',function(){
					cbj(this).toggleClass('opened');
					cbj(this).closest('.menu-list-item').children('.sub-items').toggleClass('opened');
				});

			});

			kenedo.registerSubviewReadyFunction('view-admincalculation', function() {

				// Copy feature for calculations
				cbj(document).on('click', '#view-admincalculation .task-copy', function(event){
					event.stopPropagation();
					cbj('#id').val('');
					cbj('#task').val('apply');
					cbj('.task-apply').click();
				});

				cbj('#view-admincalculation #property-name-product_id select').change(function() {

					if (cbj(this).val()) {
						cbj('#view-admincalculation #property-name-type').show();
						cbj('#view-admincalculation #property-name-type input:checked').change();
					}

				});

				// Load the right calculation type view when type is changed in the form
				cbj('#view-admincalculation #property-name-type input').change(function(){

					var selectedType = cbj(this).val();
					var url = cbj('.calc-type-subview').data('url-' + selectedType);

					var productId = cbj('#view-admincalculation #property-name-product_id #product_id').val();

					if (server.config.platformName === 'magento') {
						url += 'productId/' + productId;
					}
					else {
						url += (url.indexOf('?') === -1) ? '?productId=' + productId : '&productId=' + productId;
					}

					kenedo.loadSubview(url, '.calc-type-subview', null, false);

				});

			});

			kenedo.registerSubviewReadyFunction('view-admincalccode', function(){

				// Add IDs to the placeholders in calculation code
				cbj('#view-admincalccode select option').each(function() {
					if (cbj(this).attr('value') != 0) {
						var text = cbj(this).text() + " (ID: " + cbj(this).attr('value') + ")";
						cbj(this).text(text);
					}
				});

				cbrequire(['cbj.chosen'], function() {
					cbj('#view-admincalccode select').chosen();
				});

			});

			kenedo.registerSubviewReadyFunction('view-adminorders', function() {

				if (cbj('.in-frontend').length !== 0) {
					cbj('.listing-link').click(function(event){
						event.stopPropagation();
						window.location.href = cbj(this).attr('href');
					});
				}

				cbj('#view-adminorders .kenedo-limit-select').attr('name','limit');

				cbj('#view-adminorders .kenedo-limit-select').change(function(){
					cbj(this).closest('form').submit();
				});

				cbj('#view-adminorders .kenedo-pagination-list a').click(function(){
					var limitStart = cbj(this).attr('class').replace('page-start-','');
					cbj('#start').val(limitStart);
					cbj(this).closest('form').submit();
				});

				cbj('#view-adminorders .kenedo-filter-list select').change(function(){
					cbj(this).closest('form').submit();
				});

				cbj('#view-adminorders .kenedo-task-list li.task').click(function(){
					var task = cbj(this).attr('class').replace('task-','').replace('task','').replace(' ','');
					cbj('#task').val(task);
					cbj(this).closest('form').submit();
				});

			});

			kenedo.registerSubviewReadyFunction('view-adminusers', function() {

				cbj('#view-adminusers .kenedo-listing-form .order-property').on('click',function() {

					var fieldName = cbj(this).attr('id').replace('order-property-','');

					var direction = 'asc';

					if (cbj(this).hasClass('active')) {
						direction = (cbj(this).hasClass('direction-asc')) ? 'desc' : 'asc';
					}

					cbj('#order_field').val(fieldName);
					cbj('#order_dir').val(direction);
					cbj(this).closest('form').submit();

				});

				cbj('#view-adminusers .kenedo-limit-select').attr('name','limit');

				cbj('#view-adminusers .kenedo-limit-select').change(function(){
					cbj(this).closest('form').submit();
				});

				cbj('#view-adminusers .kenedo-pagination-list a').click(function(){
					var limitStart = cbj(this).attr('class').replace('page-start-','');
					cbj('#start').val(limitStart);
					cbj(this).closest('form').submit();
				});

				cbj('#view-adminusers .kenedo-filter-list select').change(function() {
					cbj(this).closest('form').submit();
				});

			});

			kenedo.registerSubviewReadyFunction('view-adminuser', function() {
				cbj('.kenedo-details-form').addClass('no-validation');
			});

			/* ADMIN CALCULATION PAGE - END */

			/* ADMIN TEMPLATE EDITOR - START */
			kenedo.registerSubviewReadyFunction('view-admintemplate', function(){

				// We load the stylesheet ahead of time because init goes bad if the file isn't loaded yet.
				cbrequire(['configbox/server'], function(server) {
					var url = server.config.urlSystemAssets + '/kenedo/external/codemirror-5.30.0/lib/codemirror.css?version=' + server.config.cacheVar;
					kenedo.addStylesheet(url);
				});

				cbrequire(['codemirror', 'codemirror/mode/htmlmixed/htmlmixed', 'codemirror/mode/php/php', 'codemirror/mode/javascript/javascript'], function(CodeMirror) {

					// Create the CodeMirror editor with content of template-code
					var codeMirrorEditor = CodeMirror.fromTextArea(document.getElementById("template-code"), {
						lineNumbers: true,
						matchBrackets: true,
						mode: "php",
						indentUnit: 4,
						indentWithTabs: true,
						viewportMargin: Infinity
					});

					// Make CodeMirror copy over the contents to the right form field on change
					codeMirrorEditor.on('change', function(cm){
						cbj('#template-code').val(cm.getValue());
					});



				});

			});

			/* ADMIN TEMPLATE EDITOR - END */

			kenedo.registerSubviewReadyFunction('view-adminoptionassignment', function(){

				cbj(document).on('change','#view-adminoptionassignment #option_id', function(){
					var optionId = cbj(this).val();
					var urlTemplate = cbj('#option_assignment_load_url').val();
					var url = urlTemplate.replace('placeholder_option_id', optionId);

					cbrequire(['tinyMCE'], function(tinyMCE) {
						if (tinyMCE.majorVersion > 3) {
							tinyMCE.remove();
						}
					});

					cbj('.option-data .option-fields-target').animate( {'opacity':'.0'}, 200, function(){
						cbj('.option-data .option-fields-target').load(url + ' .kenedo-properties', function(){
							kenedo.initNewContent( cbj('.option-data .option-fields-target') );
							kenedo.afterInitNewContent( cbj('.option-data .option-fields-target') );
							cbj('.option-data .option-fields-target').animate( {'opacity':'1'}, 400);
						});
					});

				});

			});

			kenedo.registerSubviewReadyFunction('view-adminuserfields', function(){

				cbj('.browser-validation input, .server-validation input').each(function(){
					cbj(this).data('invalidEntry',false);
				});

				cbj('.server-validation input').focus(function(){
					cbj(this).data('invalidEntry',null);
				});

				cbj('.server-validation input').change(function(){

					var expression = cbj(this).val();
					var testElement = cbj(this);

					if (!expression) {
						testElement.css('border','1px solid #ccc');
						testElement.data('invalidEntry',false);
					}

					if (expression) {

						testElement.parent().find('.loading-symbol').show();

						// Use the entry file property since J1.6 redirects without a perfect URL
						var url = server.config.urlXhr;

						var params = {};
						params.option = 'com_configbox';
						params.expression = expression;
						params.controller = 'ajaxapi';
						params.view = 'ajaxapi';
						params.task = 'validateRegex';
						params.format = 'raw';
						params.tmpl = 'component';
						params.lang = server.config.languageCode;

						var options = {};
						options.data = params;
						options.async = true;
						options.success = function(data) {
							var response = cbj.trim(data);

							if (response !== 'OK') {
								testElement.css('border','1px solid red');
								window.alert(response);
								testElement.data('invalidEntry',true);
							}
							else {
								testElement.css('border','1px solid #ccc');
								testElement.data('invalidEntry',false);
							}

							testElement.parent().find('.loading-symbol').hide();

						};

						cbj.ajax(url,options);
					}

				});

				cbj('.browser-validation input').change(function(){
					var expression = cbj(this).val();
					var testElement = cbj(this);
					testElement.data('invalidEntry',null);
					testElement.css('border','1px solid #ccc');
					var testString = 'dummytext';
					try {
						var regex = new RegExp(expression);
						regex.exec(testString);

					} catch (e) {
						testElement.css('border','1px solid red');
						window.alert(e.message);
						testElement.data('invalidEntry',true);
					}

				});

				var disabledValidation		  = ['salutation_id','country','email','language','billingsalutation_id','billingcountry','billingemail','billinglanguage'];
				var disabledCheckoutDisplay   = ['salutation_id','firstname','lastname','country','email','billingsalutation_id','billingfirstname','billinglastname','billingcountry','billingemail'];
				var disabledCheckoutRequire   = ['salutation_id','firstname','lastname','country','email','billingsalutation_id','billingfirstname','billinglastname','billingcountry','billingemail'];

				var disabledQuotationDisplay  = ['billingsalutation_id','billingfirstname','billinglastname','billingcountry','billingemail'];
				var disabledQuotationRequire  = ['billingsalutation_id','billingfirstname','billinglastname','billingcountry','billingemail'];

				cbj.each(disabledValidation,function(key,value){
					cbj('.userfield-' + value + ' .browser-validation input, .userfield-' + value + ' .server-validation input').prop('disabled',true);
				});

				cbj.each(disabledCheckoutDisplay,function(key,value){

					cbj('.userfield-' + value + ' .show-checkout input').click(function(event) {
						if (!cbj(this).prop('checked')) {
							event.stopPropagation();
							event.preventDefault();
							window.alert('This field is needed by the system and cannot be hidden');
						}
					});

				});

				cbj.each(disabledCheckoutRequire,function(key,value){

					cbj('.userfield-' + value + ' .require-checkout input').click(function(event) {
						if (!cbj(this).prop('checked')) {
							event.stopPropagation();
							event.preventDefault();
							window.alert('This field is needed by the system and cannot be made optional');
						}
					});

				});

				cbj.each(disabledQuotationDisplay,function(key,value){

					cbj('.userfield-' + value + ' .show-quotation input').click(function(event) {
						if (!cbj(this).prop('checked')) {
							event.stopPropagation();
							event.preventDefault();
							window.alert('This field is needed by the system and cannot be hidden');
						}
					});

				});

				cbj.each(disabledQuotationRequire,function(key,value){

					cbj('.userfield-' + value + ' .require-quotation input').click(function(event) {
						if (!cbj(this).prop('checked')) {
							event.stopPropagation();
							event.preventDefault();
							window.alert('This field is needed by the system and cannot be made optional');
						}
					});

				});

			}); // register subview function

			kenedo.registerSubviewReadyFunction('view-adminproduct', function() {

				// Clicks on the shapediver load model data button
				cbj(document).on('click', '.trigger-load-model-data', function() {

					var button = cbj(this);

					if (button.hasClass('processing')) {
						return;
					}

					button.addClass('processing');
					button.css('width', button.css('width'));
					button.data('ready-text', button.text());
					button.text(button.data('processing-text'));

					var wrapper = cbj(this).closest('.property-type-shapedivermodel');

					var urlInput = wrapper.find('.model-url');
					var parameterInput = wrapper.find('.model-parameters');
					var geometriesInput = wrapper.find('.model-textured-geometries');
					var iframe = wrapper.find('.shapediver-iframe');

					var url = urlInput.val();

					cbrequire(['configbox/shapediver'], function(shapeDiver) {

						// Sanitize the iframe URL: extract the URL if input is the whole iframe HTML
						if (url.substr(0,4) !== 'http') {
							var regex = /<iframe.*?src="(.*?)"/;
							var matches = regex.exec(url);
							if (matches && typeof(matches[1]) !== 'undefined') {
								url = regex.exec(url)[1];
							}
							else {
								regex = /<iframe.*?src='(.*?)'/;
								matches = regex.exec(url);
								if (matches && typeof(matches[1]) !== 'undefined') {
									url = regex.exec(url)[1];
								}
							}
						}

						// Sanitize the iframe URL: Remove the query string
						var pos = url.indexOf('?');

						if (pos !== -1) {
							url = url.substr(0, pos);
						}

						// Insert the sanitized URL
						urlInput.val(url).change();

						shapeDiver.addMessageListener();

						// We have to collect parameters and textures. Each come from async calls, so this
						// thing here 'waits' for both jobs done and fires a function when all is done.
						var supervisor = {

							doneJobs: [],

							requiredJobs: ['getParameters', 'getTextures'],

							/**
							 *
							 * @param {String} job
							 */
							registerDoneJob: function(job) {

								this.doneJobs.push(job);

								var jobIsMissing = false;

								for (var i in this.requiredJobs) {
									if (this.requiredJobs.hasOwnProperty(i)) {
										if (this.doneJobs.indexOf(this.requiredJobs[i]) === -1) {
											jobIsMissing = true;
										}
									}
								}

								if (jobIsMissing === false) {
									this.doneCallback();
								}

							},

							doneCallback: function() {

								// Change the button to show all went ok
								button.text(button.data('done-text'));
								button.addClass('btn-success');

								// Revert back to initial state after 1s
								window.setTimeout(function(){
									button.text(button.data('ready-text'));
									button.removeClass('btn-success');
									button.removeClass('processing');
								}, 1000);

							}

						};

						// Set the load handler for the iframe (note that at this point the iframe content isn't loaded -> it has about:blank as URL)
						iframe.on('load', function() {

							var payload;

							payload = {
								command: 'getParameterDefinitions',
								arguments: []
							};

							shapeDiver.sendMessage(payload, 'shapediver-iframe', function(response) {

								var parameters = [];

								if (response.errorCode !== 0 ) {
									window.alert('Cannot get parameter definitions from model. Error code is '+ response.errorCode +' Error message is ' + response.errorMessage + '. Assuming that there are no parameters in model.');
								}
								else {
									parameters = response.result;
								}

								// Temporary fix for missing type on choices parameters
								for (var i in parameters) {
									if (parameters.hasOwnProperty(i) === true) {
										if (typeof(parameters[i].type) === 'undefined' && typeof(parameters[i].choices) !== 'undefined') {
											parameters[i].type = 'choices';
										}
									}
								}

								parameterInput.val(JSON.stringify(parameters)).change();

								supervisor.registerDoneJob('getParameters');

							});

							// That event is fired in the in the shapediver module's onReceiveMessage
							cbj(document).on('sdGeometryUpdateDone', function() {

								var payload = {
									command: 'findTexturedGeometry',
									arguments: []
								};

								shapeDiver.sendMessage(payload, 'shapediver-iframe', function(response) {

									var geometries = [];

									if (response.errorCode !== 0 ) {
										window.alert('Cannot get texturedGeometries from model. Error code is '+ response.errorCode +' Error message is ' + response.errorMessage + '. Assuming that there are no textures in model.');
									}
									else {
										geometries = response.result;
									}

									geometriesInput.val(JSON.stringify(geometries)).change();

									supervisor.registerDoneJob('getTextures');

								});

							});


						});

						// Append the right version flag
						url += '?version=' + iframe.data('api-version');

						// If the new URL isn't different, we just change it to force a load in the next step
						if (url !== iframe.attr('src')) {
							iframe.attr('src', 'about:blank');
						}

						// Finally, load the iframe content
						iframe.attr('src', url);

					});

				});

				// Changes to SD data inputs update the hidden JSON string
				cbj(document).on('change', '.model-parameters, .model-textured-geometries, .model-url, .model-ratio', function() {

					var wrapper = cbj(this).closest('.property-type-shapedivermodel');

					var urlInput = wrapper.find('.model-url');
					var parameterInput = wrapper.find('.model-parameters');
					var geometriesInput = wrapper.find('.model-textured-geometries');
					var ratioInput = wrapper.find('.model-ratio');

					var dataInput = wrapper.find('.shapediver-model-data');

					var data = {
						iframeUrl: urlInput.val(),
						ratioDimensions: ratioInput.val(),
						parameterData: JSON.parse(parameterInput.val()),
						texturedGeometries: JSON.parse(geometriesInput.val())
					};

					dataInput.val(JSON.stringify(data));

				});

				// Just makes sure that ratio changes get copied over on keyup
				cbj(document).on('keyup', '.model-ratio', function() {
					cbj(this).trigger('change');
				});

			});

			/* Rule editor property - START */
			cbj(document).on('click', '.trigger-delete-rule', function() {
				cbj(this).closest('.kenedo-property').find('.data-field').val('').trigger('change');
				cbj(this).closest('.kenedo-property').find('.rule-wrapper').removeClass('has-rule').addClass('has-no-rule');
			});

			cbj(document).on('click', '.trigger-edit-rule', function() {

				var dataField = cbj(this).closest('.kenedo-property').find('.data-field');
				var modal = cbj(this).closest('.kenedo-property').find('.modal');

				cbrequire(['cbj.bootstrap'], function() {

					var url = dataField.data('editor-url');

					var data = {
						returnFieldId: dataField.data('return-field-id'),
						usageIn: dataField.data('usage-in'),
						productId: dataField.data('product-id'),
						pageId: dataField.data('page-id'),
						rule: dataField.val()
					};

					modal.find('.modal-content').html('');

					modal.one('show.bs.modal', function() {
						modal.find('.modal-content').load(url, data, function() {
							cbj(document).trigger('cbViewInjected');
						});
					});

					modal.modal();

					// cbj('<div class="kenedo-modal off-top"></div>').appendTo('body').load(url, data, function() {
					// 	cbj(document).trigger('cbViewInjected');
					// 	cbj(this).removeClass('off-top');
					// });

				});

			});
			/* Rule editor property - END */

			var initGroupPriceFunctionality = function() {

				/* Price override property (see property KenedoPropertyGroupPrice) - START */

				cbj(document).on('click', '.property-type-groupPrice .trigger-show-group-picker', function() {
					var wrapper = cbj(this).closest('.property-type-groupPrice');

					wrapper.find('.group-picker').show();
					wrapper.find('.trigger-show-group-picker').hide();
					wrapper.find('.trigger-cancel-group-picker').show();
				});

				cbj(document).on('click', '.property-type-groupPrice .trigger-cancel-group-picker', function() {
					var wrapper = cbj(this).closest('.property-type-groupPrice');
					wrapper.find('.group-picker').hide();
					wrapper.find('.trigger-show-group-picker').show();
					wrapper.find('.trigger-cancel-group-picker').hide();
				});

				/**
				 * Price overrides ultimately get stored as a JSON string holding all overrides. This function gets called
				 * after any change made in the UI. It collects the overrides in the HTML and writes a the JSON in the
				 * hidden input (with class .overrides-json-data).
				 * It also hides the 'add override' button if there are overrides for all groups (and vice versa).
				 *
				 * @param {jQuery} wrapper jQuery object with the property
				 */
				var updateOverrideJson = function(wrapper) {

					// Prime the array to store, loop through all overrides to collect group ids and prices..
					var overrides = [];
					wrapper.find('.price-overrides .price-override').each(function(){
						overrides.push({
							'group_id': cbj(this).data('group-id'),
							'price': cbj(this).find('.chosen-price').val()
						});
					});

					// ..then store the array as JSON in the hidden input
					wrapper.find('.overrides-json-data').val(window.JSON.stringify(overrides));

					// Check if we got overrides for all groups already - if so, hide the 'add override' button (or show it)
					var countOverrides = overrides.length;
					var countGroups = wrapper.find('.trigger-add-price-override').length;

					if (countOverrides >= countGroups) {
						wrapper.find('.trigger-show-group-picker').addClass('hidden');
					}
					else {
						wrapper.find('.trigger-show-group-picker').removeClass('hidden');
					}

				};

				cbj(document).on('keyup change', '.property-type-groupPrice .chosen-price', function() {
					var wrapper = cbj(this).closest('.property-type-groupPrice');
					updateOverrideJson(wrapper);
				});

				cbj(document).on('click', '.property-type-groupPrice .trigger-add-price-override', function() {
					var wrapper = cbj(this).closest('.property-type-groupPrice');
					var groupId = cbj(this).data('group-id');
					var html = cbj(wrapper).find('.price-override-blueprint .price-override').clone();

					html.attr('data-group-id', groupId);
					html.find('.group-title-field').text(cbj(this).text());

					wrapper.find('.price-overrides').append(html);
					cbj(this).addClass('used-already');
					wrapper.find('.trigger-cancel-group-picker').trigger('click');

					updateOverrideJson(wrapper);

				});

				cbj(document).on('click', '.property-type-groupPrice .trigger-remove-price-override', function() {
					var wrapper = cbj(this).closest('.property-type-groupPrice');
					var groupId = cbj(this).closest('.price-override').data('group-id');

					cbj(this).closest('.price-override').remove();
					wrapper.find('.group-picker .group-id-' + groupId).removeClass('used-already');

					updateOverrideJson(wrapper);

				});

				/* Price override property - END */


			};

			var initCalculationOverrideFunctionality = function() {

				/* Price override property (see property KenedoPropertycalculationOverride) - START */

				cbj(document).on('click', '.property-type-calculationOverride .trigger-show-group-picker', function() {
					var wrapper = cbj(this).closest('.property-type-calculationOverride');

					wrapper.find('.group-picker').show();
					wrapper.find('.trigger-show-group-picker').hide();
					wrapper.find('.trigger-cancel-group-picker').show();
				});

				cbj(document).on('click', '.property-type-calculationOverride .trigger-cancel-group-picker', function() {
					var wrapper = cbj(this).closest('.property-type-calculationOverride');
					wrapper.find('.group-picker').hide();
					wrapper.find('.trigger-show-group-picker').show();
					wrapper.find('.trigger-cancel-group-picker').hide();
				});

				/**
				 * Price overrides ultimately get stored as a JSON string holding all overrides. This function gets called
				 * after any change made in the UI. It collects the overrides in the HTML and writes a the JSON in the
				 * hidden input (with class .overrides-json-data).
				 * It also hides the 'add override' button if there are overrides for all groups (and vice versa).
				 *
				 * @param {{jQuery}} wrapper jQuery object with the property
				 */
				var updateOverrideJson = function(wrapper) {

					// Prime the array to store, loop through all overrides to collect group ids and prices..
					var overrides = [];
					wrapper.find('.price-overrides .price-override').each(function(){
						overrides.push({
							'group_id': parseInt(cbj(this).data('group-id')),
							'calculation_id': parseInt(cbj(this).find('.chosen-calculation').val())
						});
					});

					// ..then store the array as JSON in the hidden input
					wrapper.find('.overrides-json-data').val(window.JSON.stringify(overrides));

					// Check if we got overrides for all groups already - if so, hide the 'add override' button (or show it)
					var countOverrides = overrides.length;
					var countGroups = wrapper.find('.trigger-add-price-override').length;

					if (countOverrides >= countGroups) {
						wrapper.find('.trigger-show-group-picker').addClass('hidden');
					}
					else {
						wrapper.find('.trigger-show-group-picker').removeClass('hidden');
					}

				};

				cbj(document).on('change', '.property-type-calculationOverride .chosen-calculation', function() {
					var wrapper = cbj(this).closest('.property-type-calculationOverride');
					updateOverrideJson(wrapper);

					// Show the right join link as well
					var calculationId = cbj(this).val();
					cbj(this).closest('.price-override').find('.join-link-' + calculationId).show().siblings().hide();

				});

				cbj(document).on('click', '.property-type-calculationOverride .trigger-add-price-override', function() {

					// Get stuff
					var wrapper = cbj(this).closest('.property-type-calculationOverride');
					var groupId = cbj(this).data('group-id');
					var html = cbj(wrapper).find('.price-override-blueprint .price-override').clone();

					// Set group ID and the label
					html.attr('data-group-id', groupId);
					html.find('.group-title-field').text(cbj(this).text());

					// Get a new ID and name for the select tag (cbj.chosen seems to need that)
					var randomId = 'dummy-id-for-chosen-' + Math.floor(Math.random() * 10000);
					html.find('select').attr('id', randomId).attr('name', randomId);

					// In the placeholder, the 'new calculation' buttons have a placeholder in the href. It needs to be
					// the ID of the dropdown (some Kenedo magic will add the calculation after save to the dropdown and
					// select it).
					var hrefNewCalc = html.find('.join-link-0 a').attr('href');
					if (hrefNewCalc) {
						hrefNewCalc = hrefNewCalc.replace('PLACEHOLDER_CALC_SELECT', randomId);
						html.find('.join-link-0 a').attr('href', hrefNewCalc);
					}

					// Put the thing in place
					wrapper.find('.price-overrides').append(html);

					// Make the new select chosen
					cbj('#' + randomId).chosen({
						disable_search_threshold : 10,
						search_contains : true,
						inherit_select_classes : true,
						width:'100%'
					});

					// Mark the group as used
					cbj(this).addClass('used-already');

					// Close the picker on the cheap
					wrapper.find('.trigger-cancel-group-picker').trigger('click');

					// Finally update the JSON data for storage
					updateOverrideJson(wrapper);

				});

				cbj(document).on('click', '.property-type-calculationOverride .trigger-remove-price-override', function() {
					var wrapper = cbj(this).closest('.property-type-calculationOverride');
					var groupId = cbj(this).closest('.price-override').data('group-id');

					cbj(this).closest('.price-override').remove();
					wrapper.find('.group-picker .group-id-' + groupId).removeClass('used-already');

					updateOverrideJson(wrapper);

				});

				/* Price override property - END */


			};

			initGroupPriceFunctionality();
			initCalculationOverrideFunctionality();


			kenedo.initAdminPage();

		}

	};

	return admin;

});