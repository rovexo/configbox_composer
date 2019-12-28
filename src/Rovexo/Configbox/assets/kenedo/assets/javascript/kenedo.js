/**
 * @module kenedo
 */
define(['cbj', 'configbox/server'], function(cbj, server) {

	"use strict";

	/**
	 * @exports kenedo
	 */
	var kenedo = {

		initAdminPage : function() {

			this.initNewContent(cbj('.view-admin, .view-blank'));
			this.afterInitNewContent(cbj('.view-admin, .view-blank'));

			window.addEventListener('popstate', function(event) {

				if (typeof(event.state) != 'undefined' && event.state !== null && event.state.isSubview === true) {
					kenedo.loadSubview(event.state.url, event.state.targetSelector, event.state.callbackFunction, false);
					event.preventDefault();
				}

			});

			// ajax-target-links do a pushState and load via loadSubview
			cbj(document).on('click', '.ajax-target-link', function(event) {

				// If the user held shift or the Win/Cmd button, let the browser open a new tab/window
				if (event.shiftKey == true && event.metaKey == true) {
					return;
				}

				// Otherwise do the push-state loadSubView thing
				kenedo.loadSubview( cbj(this).attr('href') );
				event.preventDefault();

			});

			cbj(document).on('click', '.task-toggle-help', function() {
				cbj(this).toggleClass('active');
				cbj('.kenedo-details-form').toggleClass('show-help');
			});

			// Handler for opening modals on clicks on .trigger-open-modal
			cbj(document).on('click', '.trigger-open-modal', function(event) {

				event.preventDefault();

				var modalWidth	= cbj(this).data('modal-width');
				var modalHeight	= cbj(this).data('modal-height');
				var href = cbj(this).attr('href');

				if (!modalWidth) {
					modalWidth = 900;
				}
				if (!modalHeight) {
					modalHeight = 600;
				}

				cbrequire(['cbj.colorbox'], function() {
					cbj.colorbox({
						transition 		: 'fade',
						href 			: href,
						overlayClose 	: true,
						iframe 			: true,
						fastIframe		: false,
						width			: modalWidth,
						height			: modalHeight
					});
				});

			});

			// New tab links
			cbj(document).on('click','.kenedo-new-tab, .new-tab', function(event) {
				window.open(this.href);
				event.preventDefault();
			});

			/* MODAL WINDOW FOR MENU ITEM PARAMETER PICKERS - START */

			// Opening a modal window in Joomla menu item parameter boxes
			cbj(document).on('click', '.trigger-picker-select', function(event){
				event.preventDefault();

				var href = cbj(this).attr('href');

				cbrequire(['cbj.colorbox'], function() {

					cbj.colorbox({
						transition 		: 'fade',
						href 			: href,
						overlayClose 	: true,
						iframe 			: true,
						fastIframe		: false,
						width			: '1200px',
						height			: '600px'
					});

				});

			});

			// Parameter picker functionality (for menu item parameters)
			cbj(document).on('click', '.listing-link', function(event){

				// Do not act if the link is within a param-picker view (these are views used for Joomla menu item parameters)
				if (cbj(this).closest('.param-picker').length == 0) {
					return;
				}

				event.preventDefault();

				var id = cbj(this).closest('.item-row').data('item-id');
				var title = cbj(this).text();

				var pickerObject = cbj(this).closest('.kenedo-listing-form').find('.listing-data-pickerobject').data('value');
				var pickerMethod = cbj(this).closest('.kenedo-listing-form').find('.listing-data-pickermethod').data('value');

				if (pickerMethod && typeof(parent.window[pickerMethod]) != 'undefined') {
					parent.window[pickerMethod](id);
				}

				if (parent.window.document.getElementById(pickerObject + '_id')) {
					parent.window.document.getElementById(pickerObject + '_id').value = id;
					parent.window.document.getElementById(pickerObject + '_name').value = title;
				}

				if (window.parent.cbrequire) {

					window.parent.cbrequire(['cbj', 'cbj.colorbox'], function(parentCbj) {
						parentCbj.colorbox.close();
					});

				}

				if (window.parent.jQuery) {

					if (window.parent.jQuery('#'+pickerObject+'-modal').modal) {
						window.parent.jQuery('#'+pickerObject+'-modal').modal('hide');
					}

				}

			});

			/* MODAL WINDOW FOR MENU ITEM PARAMETER PICKERS - END */

			// Language switcher for translatables
			cbj(document).on('click', '.property-type-translatable .language-switcher', function(){

				cbj(this).siblings().removeClass('active');
				cbj(this).addClass('active');

				var selector = cbj(this).attr('for');
				cbj('#translation-' + selector).show().siblings().hide();

			});

			// Trigger event when form content changes
			cbj(document).on('change','.kenedo-property :input', function(){

				var propName = cbj(this).closest('.kenedo-property').attr('id').replace('property-name-','');
				var value = cbj(this).val();
				var record = cbj(this).closest('.kenedo-details-form').data('record');

				if (typeof(record[propName]) != 'undefined') {
					record[propName] = value;
				}

				/**
				 * @event kenedoFormDataChanged
				 */
				cbj(this).closest('.kenedo-details-form').trigger('kenedoFormDataChanged', [record, propName, value]);

			});

			// Toggle form field groups display
			cbj(document).on('click','.property-group-using-toggles>.property-group-legend', function (){

				if (cbj(this).closest('.property-group').hasClass('property-group-opened')) {
					cbj(this).closest('.property-group').removeClass('property-group-opened').addClass('property-group-closed');
					cbj(this).closest('.property-group').find('.property-group-toggle-state').val('closed');
				}
				else {
					cbj(this).closest('.property-group').removeClass('property-group-closed').addClass('property-group-opened');
					cbj(this).closest('.property-group').find('.property-group-toggle-state').val('opened');
				}

			});

			// JOIN LINKS: Show the right edit link when join selects are changed
			cbj(document).on('change', '.join-select', function(){
				cbj(this).closest('.property-body').find('.join-link').hide();
				cbj(this).closest('.property-body').find('.join-link-' + cbj(this).val() ).show();
			});

			// Link to show the file upload input for file upload fields
			cbj(document).on('click', '.show-file-uploader', function(event){

				event.preventDefault();

				// Store the empty input field for replacement on cancel (this makes it possible to 'reset'
				// the file upload input element when clicking cancel and preventing unwanted storage)
				var inputField = cbj(this).closest('.property-body').find('.file-upload-field').clone();
				cbj(this).data('inputfield',inputField);

				cbj(this).closest('.property-body').find('.file-uploader').slideDown(300);
				cbj(this).closest('.property-body').find('.file-current-file, .file-delete, .file-upload').slideUp(300);

			});

			// Link to hide it again
			cbj(document).on('click', '.file-upload-cancel', function(event){

				event.preventDefault();

				cbj(this).closest('.property-body').find('.file-uploader').slideUp(300);
				cbj(this).closest('.property-body').find('.file-current-file, .file-delete, .file-upload').slideDown(300);

				// Get the saved empty input field and replace it (See handler above for reference)
				var inputField = cbj(this).closest('.property-body').find('.show-file-uploader').data('inputfield');
				cbj(this).closest('.property-body').find('.file-upload-field').replaceWith(inputField);

			});

			// Event handlers for common Kenedo form and listing tasks
			cbj(document).on('click', '.order-property', 									kenedo.changeListingOrder );
			cbj(document).on('change','.listing-filter', 									kenedo.changeListingFilters );
			cbj(document).on('click', '.link-save-item-ordering', 							kenedo.storeOrdering );
			cbj(document).on('click', '.kenedo-check-all-items', 							kenedo.toggleCheckboxes );
			cbj(document).on('click', '.kenedo-pagination-list a', 							kenedo.changeListingStart );
			cbj(document).on('change','.kenedo-limit-select', 								kenedo.changeListingLimit );
			cbj(document).on('click', '.kenedo-trigger-toggle-active', 						kenedo.toggleActive );
			cbj(document).on('click', '.kenedo-details-form .kenedo-task-list .task', 		kenedo.executeFormTask );
			cbj(document).on('click', '.kenedo-listing-form .kenedo-task-list .task', 		kenedo.executeListingTask );
			cbj(document).on('click', '.listing-link', 										kenedo.openListingLink );

			/* SORTABLE SETUP - START */

			// Show the save button for ordering after a change in ordering
			cbj(document).on('keyup', '.ordering-text-field', function() {
				cbj(this).closest('.kenedo-listing').find('.link-save-item-ordering').show();
			});

			/* SORTABLE SETUP - END */

			/* EVENT HANDLERS FOR KENEDO POPUPS - START */

			// Open the popup when the trigger was entered
			cbj(document).on('mouseenter', '.kenedo-popup-trigger', KenedoPopup.cancelClosing);
			cbj(document).on('mouseenter', '.kenedo-popup-trigger', KenedoPopup.scheduleOpening);

			// Close when trigger was left
			cbj(document).on('mouseleave', '.kenedo-popup-trigger', KenedoPopup.scheduleClosing);
			cbj(document).on('mouseleave', '.kenedo-popup-trigger', KenedoPopup.cancelOpening);

			// Close when popup was left
			cbj(document).on('mouseleave', '.kenedo-popup', KenedoPopup.scheduleClosing);

			// Cancel closing when popup is entered (so also when re-entered)
			cbj(document).on('mouseenter', '.kenedo-popup', KenedoPopup.cancelClosing);

			// Prevent clicks on trigger making selections (or whatever the click would trigger)
			cbj('.kenedo-popup-trigger').click(function(event){
				event.stopPropagation();
				event.preventDefault();
				cbj(this).trigger('mouseenter');
			});

			/* EVENT HANDLERS FOR KENEDO POPUPS - END */

			cbj(document).on('click', '.trigger-select-all-multi', function() {
				cbj(this).closest('.kenedo-property').find('.property-body option').prop('selected', true);
				cbj(this).closest('.kenedo-property').find('.property-body input').prop('checked', true);
			});

			cbj(document).on('click', '.trigger-deselect-all-multi', function() {

				cbj(this).closest('.kenedo-property').find('.property-body option').prop('selected', false);
				cbj(this).closest('.kenedo-property').find('.property-body input').prop('checked', false);

			});

		},

		/**
		 * Loads a subview into the ajax target div (currently hard-coded as .configbox-ajax-target)
		 * Takes care of things like of executing the subview's loading scripts
		 * @param {(String|Event)} parameter - URL to load or event object (takes the URL from jQuery(this).attr('href') then)
		 * @param {String=} targetSelector - CSS selector for the element to put the view in
		 * @param {Function=} callbackFunction - Optional callback function
		 * @param {Boolean=} skipPushState - Optional, defaults to false. Use true to avoid a pushState call
		 */
		loadSubview: function(parameter, targetSelector, callbackFunction, skipPushState) {

			if (!targetSelector) {
				targetSelector = '.configbox-ajax-target';
			}
			// The URL we will load eventually
			var url;

			// If used as a click handler, get the URL through cbj(this)
			if (typeof(parameter) == 'object') {

				// This is for listing links in intra-listings (i.e. lists in settings page)
				// These open in modals, we prevent subview loading on the cheap here
				if (cbj(this).closest('.intra-listing').length) {
					return;
				}

				// Get the URL from the link
				url = parameter.value;

			}

			// If used as a regular function call, get the URL from the parameter
			if (typeof(parameter) == 'string' && parameter != '') {
				url = parameter;
			}

			if (!url) {
				throw('loadSubview called, but no URL could be determined.');
			}

			// Change the history state and push one in (unless we deal with a load via back button)
			if (skipPushState !== false) {
				var state = {
					isSubview: true,
					url : url,
					targetSelector: targetSelector,
					callbackFunction: callbackFunction
				};

				window.history.pushState(state, '', url);
			}

			// Add the ajax_sub_view parameter in case it's not there
			if (url.indexOf('ajax_sub_view') == -1) {
				if (server.config.platformName == 'magento') {
					url += 'ajax_sub_view/1/format/raw';
				}
				else {
					url += (url.indexOf('?') == -1) ? '?ajax_sub_view=1&format=raw' : '&ajax_sub_view=1&format=raw';
				}
			}

			cbrequire(['tinyMCE'], function(tinyMCE) {
				if (tinyMCE.majorVersion > 3) {
					tinyMCE.remove();
				}
			});

			// Load the sub view with a nice fade effect

			// First fade out the current target area
			cbj(targetSelector).animate( {'opacity':'.0'}, 200, function() {

				// Then load the new content into the target area (look careful - we're injecting only the first
				// .kenedo-view element we find in the response.
				cbj(targetSelector).load(url + ' .kenedo-view:first', function(responseText, textStatus, jqXHR){

					if (jqXHR.status !== 200) {

						var text = '<div id="view-error" class="kenedo-view kenedo-ajax-sub-view">';
						text += '<div class="kenedo-listing-form">';
						text += '<p>Encountered a system error: HTTP code is ' + jqXHR.status + '. Text message is: ' + jqXHR.statusText + '</p>';
						text += '</div>';
						text += '</div>';
						cbj(targetSelector).html(text);

					}

					// When done, init the the new content
					kenedo.initNewContent(cbj(targetSelector));

					// Now fade in the content
					cbj(targetSelector).animate( {'opacity':'1'}, 400, function(){

						// When done, do the rest of the initialization
						kenedo.afterInitNewContent(cbj(targetSelector));

						if (typeof(callbackFunction) == 'function') {
							callbackFunction(cbj(targetSelector));
						}

					});

				});

			});

		},

		/**
		 * This method initializes any JS-dependent stuff on the page (use wrapper to limit the 'area' to run it)
		 * @param {jQuery} wrapper - jQuery object representing the DOM elements with stuff that needs initialization
		 */
		initNewContent : function(wrapper) {

			if (cbj(wrapper).length == 0) {
				return;
			}

			if (cbj(wrapper).find('select.listing-filter, select.join-select, .property-type-dropdown select, .make-me-chosen, .chosen-dropdown').length) {

				cbrequire(['cbj.chosen'], function() {

					// Init the chosen select form items
					cbj(wrapper).find('select.listing-filter, select.join-select, .property-type-dropdown select, .make-me-chosen, .chosen-dropdown').chosen({
						disable_search_threshold : 10,
						search_contains : true,
						inherit_select_classes : true,
						width:'100%'
					});

					// Deal with the width problem of initially hidden drop-downs
					cbj(wrapper).find('.chosen-container:hidden').each(function(){
						if (cbj(this).is('.join-select')) {
							cbj(this).css('width','100%');
						}
						else {
							if (cbj(this).closest('.kenedo-properties').length) {
								cbj(this).css('width','100%');
							}
							else {
								cbj(this).css('width','auto');
							}

						}
					});

				});

			}

			// Init jQueryUI sortables
			if (cbj(wrapper).find('.sortable-listing tbody').length) {
				cbrequire(['cbj.ui'], function() {
					cbj(wrapper).find('.sortable-listing tbody').sortable(kenedo.listingSortableSettings);
				});
			}

			// Init jQueryUI date pickers
			if (cbj(wrapper).find('.datepicker').length) {
				cbrequire(['cbj.ui'], function() {
					cbj(wrapper).find('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
				});
			}

			// Go through all views and run the ready functions
			cbj(wrapper).find('.kenedo-view').each(function() {
				var viewId = cbj(this).attr('id');
				if (viewId) {
					kenedo.runSubviewReadyFunctions(viewId);
				}
			});

			cbj(wrapper).find('.kenedo-details-form').each(function(){
				var record = cbj(this).closest('.kenedo-details-form').data('record');
				kenedo.setPropertyVisibility(null, record);
				cbj(this).on('kenedoFormDataChanged', kenedo.setPropertyVisibility);
			});


			// Turn off auto-complete for ordering fields in listing
			cbj(wrapper).find('.kenedo-listing .ordering-text-field').attr('autocomplete','off');

			cbj(document).trigger('cbViewInjected');

		},

		/**
		 * This method is supposed to run after kenedo.initNewContent and contains the code that may be heavier. This
		 * is simply for making subview loading appear a bit quicker.
		 *
		 * @see kenedo.initNewContent
		 * @see kenedo.loadSubview
		 * @param {jQuery=} wrapper - jQuery object representing the DOM elements with stuff that needs initialization
		 */
		afterInitNewContent : function(wrapper) {

			if (wrapper.find('.kenedo-html-editor').length !== 0) {

				cbrequire(['tinyMCE', 'configbox/server'], function(tinyMCE, server) {

					try {
						tinyMCE.init({
							convert_urls : false,
							document_base_url : server.config.urlBase,
							documentBaseURL : server.config.urlBase,
							baseURL : server.config.urlTinyMceBase,
							suffix : (server.config.useMinifiedJs === true) ? '.min' : '',

							// General options
							mode 		: "textareas",
							selector 	: '.kenedo-html-editor',
							plugins		: [
								"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
								"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking",
								"save table directionality emoticons template paste"
							],
							toolbar		: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
							template_external_list_url 	: "js/template_list.js",
							external_link_list_url 		: "js/link_list.js",
							external_image_list_url 	: "js/image_list.js",
							media_external_list_url 	: "js/media_list.js",

							style_formats: [
								{title: 'Headers', items: [
										{title: 'Header 1', format: 'h1'},
										{title: 'Header 2', format: 'h2'},
										{title: 'Header 3', format: 'h3'},
										{title: 'Header 4', format: 'h4'},
										{title: 'Header 5', format: 'h5'},
										{title: 'Header 6', format: 'h6'}
									]},
								{title: 'Inline', items: [
										{title: 'Bold', icon: 'bold', format: 'bold'},
										{title: 'Italic', icon: 'italic', format: 'italic'},
										{title: 'Underline', icon: 'underline', format: 'underline'},
										{title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'},
										{title: 'Superscript', icon: 'superscript', format: 'superscript'},
										{title: 'Subscript', icon: 'subscript', format: 'subscript'},
										{title: 'Code', icon: 'code', format: 'code'}
									]},
								{title: 'Blocks', items: [
										{title: 'Paragraph', format: 'p'},
										{title: 'Blockquote', format: 'blockquote'},
										{title: 'Div', format: 'div'},
										{title: 'Pre', format: 'pre'}
									]},
								{title: 'Alignment', items: [
										{title: 'Left', icon: 'alignleft', format: 'alignleft'},
										{title: 'Center', icon: 'aligncenter', format: 'aligncenter'},
										{title: 'Right', icon: 'alignright', format: 'alignright'},
										{title: 'Justify', icon: 'alignjustify', format: 'alignjustify'}
									]},
								{title: 'Others', items: [
										{title : 'Custom 1', selector : 'p', classes : 'custom-1'},
										{title : 'Custom 2', selector : 'p', classes : 'custom-2'},
										{title : 'Custom 3', selector : 'p', classes : 'custom-3'},
										{title : 'Custom 4', selector : 'p', classes : 'custom-4'}
									]}
							]

						});
					} catch(error) {
						console.log('Init of TinyMCE failed. Error message was ' + error);
					}

				});



			}

		},

		addedStylesheets: [],

		/**
		 * Adds a stylesheet to the doc's head
		 * @param {String} url
		 */
		addStylesheet: function(url) {

			if (this.addedStylesheets.indexOf(url.toLowerCase()) === -1) {
				this.addedStylesheets.push(url.toLowerCase());
				var link = '<link rel="stylesheet" type="text/css" href="' + url + '">';
				cbj('head').append(link);
			}

		},

		/**
		 *  @var {{Function[]}} subviewReadyFunctions
		 *  @see kenedo.registerSubviewReadyFunction
		 *	@see kenedo.runSubviewReadyFunctions
		 *	@see kenedo.initNewContent
		 */
		subviewReadyFunctions : {},

		base64UrlEncode : function(input) {
			var encoded = Base64.encode(input);
			return encoded.replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,',');
		},

		base64UrlDecode : function(input) {
			var unreplaced = input.replace(/-/g,'+').replace(/_/g,'/').replace(/,/g,'=');
			return Base64.decode(unreplaced);
		},

		openListingLink : function(event) {

			// Links within intra-listings are handled differently: They will open in a modal window
			if (cbj(this).closest('.intra-listing').length) {

				event.preventDefault();

				// Prepare a function that resizes the modal (to be used in callbacks later on)
				var maximizeHeight = function() {
					cbj.colorbox.resize({
						height:'95%',
						width:'95%'
					});
				};

				var params = {
					transition 		: 'fade',
					href 			: cbj(this).attr('href'),
					overlayClose	: true,
					iframe 			: true,
					fastIframe		: false,
					width			: '95%',
					height			: '95%',

					onComplete: function() {

						// Maximize height on window resize
						cbj(window.parent).on('resize', maximizeHeight);

						// Prevent scrolling outside the modal
						cbj(window.top.document).find('body').css('overflow','hidden');

						// FF/Win positions the modal badly if overflow:hidden is active, this works around it
						cbj(window.top).trigger('resize');

					},

					onClosed: function() {

						// Turn off the resize handler
						cbj(window.parent).off('resize', maximizeHeight);

						// Allow scrolling outside the modal again
						cbj(window.top.document).find('body').css('overflow','auto');

					}

				};

				// Add param in_modal to the URL (so that the view is loaded accordingly
				// (see KenedoController::wrapViewAndDisplay() for reference)

				if (server.config.platformName == 'magento') {
					params.href += 'in_modal/1/tmpl/component';
				}
				else {
					if (params.href.indexOf('?') == -1) {
						params.href += '?in_modal=1&tmpl=component';
					}
					else {
						params.href += '&in_modal=1&tmpl=component';
					}
				}

				cbrequire(['cbj.colorbox'], function() {
					cbj.colorbox(params);
				});

				return;
			}
			else {
				var url = cbj(this).attr('href');
				kenedo.loadSubview(url);
			}

			event.preventDefault();

		},

		/**
		 * @listens Event:kenedoFormDataChanged
		 * @param event
		 * @param settings
		 */
		setPropertyVisibility : function(event, settings) {

			var propDef = {};
			var testProp;
			var currentValue;
			var shouldValues;
			var foundMatch;
			var conditionUnfulfilled;
			var showProperty;
			var groupId;

			if (!settings) {
				settings = {};
			}

			// Groupstart/Groupend properties do not show up in the record data, so we add them for easier processing later.
			cbj('.kenedo-details-form .property-group').each(function(){
				groupId = cbj(this).attr('id');
				groupId = groupId.replace('property-name-', '');
				settings[groupId] = '';
			});

			// Loop through all current settings
			for (var propertyName in settings) {
				if (settings.hasOwnProperty(propertyName)) {

					// Get the prop defs of the setting (somehow either jQuery or HTML5 gives you a JS object already, not JSON)
					propDef = cbj('#property-name-' + propertyName).data('propertyDefinition');

					// Just in case somehow there is no propdef JSON
					if (!propDef) {
						continue;
					}

					// Start with assuming that we gonna show the property (later on we might find reasons not to)
					showProperty = true;

					// In case we deal with an invisible field, leave it be (invisible-field comes from propDef 'invisible')
					if (cbj('#property-name-' + propertyName).hasClass('invisible-field')) {
						continue;
					}

					// If the prop has appliesWhen instructions, look into them
					if (propDef.appliesWhen) {

						conditionUnfulfilled = true;

						// Loop through the appliesWhens
						for (testProp in propDef.appliesWhen) {
							if (propDef.appliesWhen.hasOwnProperty(testProp)) {

								// Get the prop's current value..
								currentValue = settings[testProp];
								// ..and the should values for the appliesWhen
								shouldValues = propDef.appliesWhen[testProp];

								// Convert the shouldValues to an array if they aren't already
								if (typeof(shouldValues) != 'object') {
									shouldValues = [shouldValues];
								}

								foundMatch = false;
								// Loop through the shouldValues and see if we got a match with the currentValue
								for (var i in shouldValues) {
									if (shouldValues.hasOwnProperty(i)) {

										var operator = 'is';

										// An exclamation mark in the beginning of the shouldValue means 'is not'
										if (typeof(shouldValues[i]) == 'string' && shouldValues[i].substr(0, 1) == '!') {
											shouldValues[i] = shouldValues[i].substr(1);
											operator = 'is not';
										}

										if (operator === 'is') {

											// Asterisk as shouldValue means that any non-empty value is good
											if (shouldValues[i] == '*') {
												if (currentValue && currentValue != '0') {
													foundMatch = true;
													break;
												}
											}

											if (shouldValues[i] == currentValue) {
												// If we got one, make a mark
												foundMatch = true;
												break;
											}

										}
										else {

											// Asterisk as shouldValue means that any non-empty value is good
											if (shouldValues[i] == '*') {
												if (!currentValue || currentValue == '0') {
													foundMatch = true;
													break;
												}
											}

											if (shouldValues[i] != currentValue) {
												// If we got one, make a mark
												foundMatch = true;
												break;
											}

										}

									}
								}

								if (foundMatch == false) {
									showProperty = false;
								}

							}
						}
					}

					if (showProperty) {

						if (cbj('#property-name-' + propertyName).css('display') == 'none') {

							cbj('#property-name-' + propertyName).show();

							var defaultValue = (typeof(propDef['default']) !== 'undefined') ? propDef['default'] : '';

							if (defaultValue) {

								if (cbj('#' + propertyName).is('select')) {
									cbj('#' + propertyName).val(defaultValue).change().trigger('chosen:updated');
								}
								else {
									cbj('#' + propertyName).val(defaultValue);
								}

								settings[propertyName] = defaultValue;

							}

						}

					}
					else {

						if (cbj('#property-name-' + propertyName).css('display') != 'none') {

							cbj('#property-name-' + propertyName).hide();

							var nullValue = (typeof(propDef['default']) !== 'undefined') ? propDef['default'] : '';

							if (cbj('#' + propertyName).is('select')) {

								if (nullValue == '') {
									nullValue = 0;
								}

								cbj('#' + propertyName).val(nullValue).trigger('chosen:updated');

							}
							else {
								cbj('#' + propertyName).val(nullValue).trigger('chosen:updated');
							}

							cbj('#property-name-' + propertyName + '.property-type-translatable .form-control').val(nullValue);

							settings[propertyName] = nullValue;

						}

					}

				}
			}

		},

		listingSortableSettings : {

			handle 		: '.sort-handle',
			items		: 'tr',
			axis		: 'y',
			scroll		: false,

			helper		: function(e, ui) {
				ui.children().each(function() {
					cbj(this).width(cbj(this).width());
				});
				return ui;
			},

			start		: function() {

				// Init the current ordering arrays
				var currentOrdering = [];
				var currentItemIds = [];

				var sortableArray = cbj(this).sortable('toArray');
				// sortableArray.pop();

				// Loop through the current sortable ordering and set current values
				for (var i in sortableArray) {
					if (sortableArray.hasOwnProperty(i) === true) {
						currentOrdering.push(cbj('#'+sortableArray[i] + ' .ordering-text-field').val());
						currentItemIds.push(sortableArray[i].replace('item-id-',''));
					}
				}

				// Store them in the its data object
				cbj(this).data('currentOrdering',currentOrdering);
				cbj(this).data('currentItemIds',currentItemIds);

			},

			update: function() {

				// Show the save symbol
				cbj(this).closest('.kenedo-listing-form').find('.link-save-item-ordering').show();

				// Init the arrays for new ordering values
				var updatedOrdering = [];
				var updatedItemIds = [];

				// Get current ordering from sortable's data
				var currentOrdering = cbj(this).data('currentOrdering');

				// Loop through the updated ordering and set the update arrays
				var ordering = cbj(this).sortable('toArray');

				var i;

				// Put together the new ordering
				for (i in ordering) {
					if (ordering.hasOwnProperty(i) === true) {
						updatedItemIds.push( ordering[i].replace('item-id-','') );
						updatedOrdering.push( currentOrdering[i] );
					}
				}

				// Update the ordering text fields
				for (i in updatedOrdering) {
					if (updatedOrdering.hasOwnProperty(i) === true) {
						cbj(this).find('#item-id-'+updatedItemIds[i] + ' .ordering-text-field').val(currentOrdering[i]);
					}
				}

				// Renew the current arrays for next sort
				currentOrdering = updatedOrdering;
				var currentItemIds = updatedItemIds;

				// Store them in its data object
				cbj(this).data('currentOrdering',currentOrdering);
				cbj(this).data('currentItemIds',currentItemIds);

			}
		},

		requiredFields : [],
		messages : { error : [], notice : [] },
		success : false,

		reloadIntraListings : function() {
			cbj('.intra-listing').each(function(){
				var url = cbj(this).data('listing-url');
				cbj(this).load(url, function(){
					cbj(this).find('.sortable-listing tbody').sortable(kenedo.listingSortableSettings);
				});
			});
		},

		toggleActive: function() {

			var id = cbj(this).data('id');
			var task = (cbj(this).data('active')) ? 'unpublish' : 'publish';

			cbj(this).find('.fa').addClass('fa-spinner fa-spin');

			cbj(this).closest('.kenedo-listing-form').find('.listing-data-ids').data('value',id);
			cbj(this).closest('.kenedo-listing-form').find('.listing-data-task').data('value',task);

			kenedo.updateListingForm(cbj(this));
		},

		executeListingTask: function(event) {
			event.stopPropagation();
			var task = cbj(this).attr('class').replace('task-','').replace('task','').replace('non-ajax','').replace(' ','');

			var checkedIds = [];
			cbj(this).closest('.kenedo-listing-form').find('.kenedo-item-checkbox').each(function(){
				if (cbj(this).prop('checked') == true) {
					checkedIds.push(cbj(this).val());
				}
			});

			var ids = checkedIds.join(',');

			cbj(this).closest('.kenedo-listing-form').find('.listing-data-ids').data('value',ids);

			if (task == 'add') {

				// The URL for adding records is always stored in a data attribute, all that is set in the template for listings
				var url = kenedo.base64UrlDecode(cbj(this).closest('.kenedo-listing-form').find('.listing-data-add-link').data('value'));

				if (cbj(this).closest('.intra-listing').length) {

					var maximizeHeight = function() {
						cbj.colorbox.resize({
							height:'95%',
							width:'95%'
						});
					};

					var params = {
						transition 		: 'fade',
						href 			: url,
						overlayClose 	: true,
						iframe 			: true,
						fastIframe		: false,
						width			: 1000,
						height			: '95%',

						onComplete: function() {

							// Maximize height on window resize
							cbj(window.parent).on('resize', maximizeHeight);

							// Prevent scrolling outside the modal
							cbj(window.top.document).find('body').css('overflow','hidden');

							// FF/Win positions the modal badly if overflow:hidden is active, this works around it
							cbj(window.top).trigger('resize');

						},

						onClosed: function() {

							// Turn off the resize handler
							cbj(window.parent).off('resize', maximizeHeight);

							// Allow scrolling outside the modal again
							cbj(window.top.document).find('body').css('overflow','auto');

						}

					};

					cbrequire(['cbj.colorbox'], function() {
						cbj.colorbox(params);
					});

					return;
				}

				if (cbj(this).closest('.kenedo-listing-form').find('.listing-data-ajax_sub_view').data('value') == 1) {
					kenedo.loadSubview(url);
				}
				else {
					window.location.href = url;
				}

			}
			else {

				cbj(this).closest('.kenedo-listing-form').find('.listing-data-task').data('value',task);
				kenedo.updateListingForm(cbj(this));
			}

		},



		/**
		 * Registers a function to be executed when a certain subview is loaded via kenedo.loadSubview().
		 * This is a similar concept to document.ready, but these functions are fired when kenedo.loadSubview()
		 * injects the sub view of that name into the document.
		 * @param {String} viewId Content of the ID HTML attribute of the subview's wrapping div
		 * @param {Function} fn Ready function
		 *
		 */
		registerSubviewReadyFunction : function(viewId, fn) {

			if (typeof(kenedo.subviewReadyFunctions[viewId]) == 'undefined') {
				kenedo.subviewReadyFunctions[viewId] = [];
			}

			kenedo.subviewReadyFunctions[viewId].push(fn);

		},

		/**
		 * Checks for a registered ready function and executes the function
		 * This is used to run any JS needed for the content of an ajax subview.
		 * @see kenedo.loadSubview for reference on loading subviews
		 * @see kenedo.initNewContent for reference on initializing content
		 * @see kenedo.registerSubviewReadyFunction for reference on registering subview ready functions
		 * @param {String} viewId Content of the ID HTML attribute of the subview's wrapping div
		 */
		runSubviewReadyFunctions : function (viewId) {
			if (typeof(kenedo.subviewReadyFunctions[viewId]) != 'undefined') {
				cbj.each(kenedo.subviewReadyFunctions[viewId],function(key, fn){
					if (typeof(fn) == 'function') {
						fn();
					}
				});
			}
		},

		changeListingStart: function() {
			var start = cbj(this).attr('class').replace('page-start-','');
			cbj(this).closest('.kenedo-listing-form').find('.listing-data-limitstart').data('value',start);
			kenedo.updateListingForm(cbj(this));
		},

		changeListingFilters: function() {
			kenedo.updateListingForm(cbj(this));
		},

		changeListingLimit: function() {
			var limit = cbj(this).val();
			cbj(this).closest('.kenedo-listing-form').find('.listing-data-limit').data('value',limit);
			kenedo.updateListingForm(cbj(this));
		},

		/**
		 * Update (Refresh) the listing. Depending on its location within the html doc
		 *
		 * @param {jQuery} item - Simply regard it as some element within the kenedo-listing-form
		 */
		updateListingForm: function(item) {

			if (!item) {
				item = cbj(this);
			}

			// Get the 'base' url dealing with the given type of record (see kenedoView template default-table.php)
			var url = cbj(item).closest('.kenedo-listing-form').find('.listing-data-base-url').data('value');

			// Add ? for the query string in case
			if (url.indexOf('?') == -1) {
				url += '?';
			}

			// Loop through the listing-data fields and append them to the query string
			cbj(item).closest('.kenedo-listing-form').find('.listing-data').each(function(){
				var key = cbj(this).data('key');
				// Leave out base-url, no use for it plus probably trouble
				if (key == 'base-url') {
					return;
				}
				var value = cbj(this).data('value');
				url += '&' + key + '=' + encodeURIComponent(value);
			});

			// Do the same for the filters
			cbj(item).closest('.kenedo-listing-form').find('.listing-filter').each(function(){
				var key = cbj(this).attr('name');
				var value = cbj(this).val();
				url += '&' + key + '=' + encodeURIComponent(value);
			});

			// If the link has the .non-ajax class, do a regular page load
			if (item.hasClass('non-ajax')) {
				window.location.href = url;
				return;
			}

			// Init the target div where the view should be inserted in
			var target = cbj('.view-admin');

			// If we're dealing with a link in an intra-listing, target is the parent intra-listing
			if (cbj(item).closest('.intra-listing').length) {
				target = cbj(item).closest('.intra-listing');
			}
			else if(cbj(item).closest('.kenedo-view.param-picker').length !== 0) {
				target = cbj(item).closest('.kenedo-view.view-blank');
			}
			else {
				// In case we got a link within the ConfigBox target area, use that one
				if (cbj(item).closest('.configbox-ajax-target').length) {
					target = cbj(item).closest('.configbox-ajax-target');
				}
			}

			// Load the contents
			target.load(url + ' .kenedo-view:first', function() {

				// Init the new content
				kenedo.initNewContent(target);
				kenedo.afterInitNewContent(target);

				// Note: We do not (yet) deal with messages in listings like we do in forms because listing views are still
				// completely reloaded and carry messages with them already (as opposed to populating and showing messages
				// using JS.

				// Show messages
				if (cbj(this).find('.kenedo-messages li').length != 0) {
					cbj(this).find('.kenedo-messages').slideDown(200);
				}

				// Show error messages if any
				if (cbj(this).find('.kenedo-messages-error li').length != 0) {
					cbj(this).find('.kenedo-messages-error').slideDown(200);
				}

				// Show notices if any
				if (cbj(this).find('.kenedo-messages-notice li').length != 0) {
					cbj(this).find('.kenedo-messages-notice').slideDown(200);
				}

			});

		},

		toggleCheckboxes: function() {
			var checked = cbj(".kenedo-listing .kenedo-check-all-items").prop('checked');
			cbj(this).closest('.kenedo-listing').find('.kenedo-item-checkbox').prop('checked',checked);
		},

		storeOrdering: function() {

			if (cbj(this).hasClass('clicked')) {
				return;
			}

			cbj(this).addClass('clicked');
			cbj(this).find('.fa').removeClass('fa-floppy-o').addClass('fa-spinner').addClass('fa-spin');

			cbj(this).closest('.kenedo-listing-form').find('.listing-data-task').data('value', 'storeOrdering');

			var orderingPairs = [];
			cbj(this).closest('.kenedo-listing-form').find('.item-row').each(function(){
				var itemId = cbj(this).data('item-id');
				var order = cbj(this).find('.ordering-text-field').val();
				orderingPairs.push( '"' + itemId + '":' + order);
			});

			var string = '{' + orderingPairs.join(',') + '}';

			cbj(this).closest('.kenedo-listing-form').find('.listing-data-ordering-items').data('value', string);

			kenedo.updateListingForm(cbj(this));
		},

		changeListingOrder: function() {

			var propertyName = cbj(this).attr('id').replace('order-property-name-','');
			var direction = '';

			if (cbj(this).hasClass('active')) {
				direction = (cbj(this).hasClass('direction-asc')) ? 'desc' : 'asc';
			}
			else {
				direction = 'asc';
			}

			cbj(this).closest('.kenedo-listing-form').find('.listing-data-listing_order_property_name').data('value', propertyName);
			cbj(this).closest('.kenedo-listing-form').find('.listing-data-listing_order_dir').data('value', direction);

			kenedo.updateListingForm(cbj(this));
		},

		isViewInModal: function() {
			return ( cbj('#in_modal').val() === '1');
		},

		isViewAjaxView: function() {
			return ( cbj('#ajax_sub_view').val() === '1');
		},

		getReturnUrl: function() {
			return kenedo.base64UrlDecode(cbj('#return').val());
		},

		getTask: function() {
			return cbj('#task').val();
		},

		getViewName: function() {
			return cbj('.kenedo-details-form').data('view');
		},

		/**
		 * @see kenedo.getFormTaskHandler
		 * @see kenedo.setFormTaskHandler
		 */
		formTaskHandlers : {},

		getFormTaskHandler: function(viewName) {
			return this.formTaskHandlers[viewName] || kenedo.defaultFormTaskHandler;
		},

		setFormTaskHandler: function(viewName, fn) {
			this.formTaskHandlers[viewName] = fn;
		},

		/**
		 * @see kenedo.getFormTaskResponseHandler
		 * @see kenedo.setFormTaskResponseHandler
		 */
		formTaskResponseHandlers : {},

		getFormTaskResponseHandler: function(viewName) {
			return this.formTaskResponseHandlers[viewName] || kenedo.defaultFormTaskResponseHandler;
		},

		setFormTaskResponseHandler: function(viewName, fn) {
			this.formTaskResponseHandlers[viewName] = fn;
		},

		/**
		 * Event handler for clicks on task buttons. Figures out what task to run, get's the right handler to run it.
		 */
		executeFormTask: function(event) {

			// Whoops, that is actually a listing task (when listings are within detail forms)!
			// Let's just move away before someone notices us...
			if (cbj(this).closest('.kenedo-listing-form').length) {
				return;
			}

			// Alright, looks like we're in the clear. Get the task name
			var task = cbj(this).attr('class').replace('task-','').replace('task','').replace(' ','');
			var viewName = kenedo.getViewName();

			// Set the task, gotta be done at some point.
			cbj('#task').val(task);

			// Get the handler for tasks
			var formTaskHandler = kenedo.getFormTaskHandler(viewName);

			// Run the handler
			formTaskHandler(viewName, task, event);

		},

		defaultFormTaskHandler: function(viewName, task, event) {

			switch (task) {

				case 'cancel':
					kenedo.defaultFormTaskHandlerCancel(viewName, event);
					break;
				case 'store':
					kenedo.defaultFormTaskHandlerStore(viewName, event);
					break;
				case 'storeAndNew':
					kenedo.defaultFormTaskHandlerStore(viewName, event);
					break;
				case 'apply':
					kenedo.defaultFormTaskHandlerApply(viewName, event);
					break;
			}

		},

		defaultFormTaskHandlerCancel: function(viewName, event) {

			if (cbj(event.target).closest('.modal').length) {
				cbrequire('cbj.bootstrap', function() {
					cbj(event.target).closest('.modal').modal('hide');
				});
				return;
			}

			kenedo.isViewInModal();

			var inIframe = false;

			// May throw an error if we got an iframe, but parent window is from a different source
			try {
				inIframe = (window.parent !== window.self);
			}
			catch(e) {
				inIframe = true;
			}

			if (inIframe == true) {
				window.parent.cbrequire(['cbj', 'cbj.colorbox'], function(parentCbj) {
					parentCbj.colorbox.close();
				});
				return;
			}

			if (kenedo.isViewAjaxView()) {
				kenedo.loadSubview(kenedo.getReturnUrl());
			}
			else {
				window.location.href = kenedo.getReturnUrl();
			}

		},

		defaultFormTaskHandlerStore: function(viewName, event) {

			cbrequire(['tinyMCE'], function(tinyMCE) {

				tinyMCE.triggerSave();

				var form = cbj('.kenedo-details-form');
				var xhr = new XMLHttpRequest();
				xhr.onload = kenedo.getFormTaskResponseHandler(viewName);
				xhr.open("post", form[0].action, true);
				xhr.send(new FormData(form[0]));

			});

		},

		defaultFormTaskHandlerApply: function(viewName, event) {

			cbrequire(['tinyMCE'], function(tinyMCE) {

				tinyMCE.triggerSave();

				var form = cbj('.kenedo-details-form');
				var xhr = new XMLHttpRequest();
				xhr.onload = kenedo.getFormTaskResponseHandler(viewName);
				xhr.open("post", form[0].action, true);
				xhr.send(new FormData(form[0]));

			});

		},

		/**
		 * Default callback function for receiving Kenedoform submits
		 * @see kenedo.getFormTaskResponseHandler
		 */
		defaultFormTaskResponseHandler: function() {

			var response = JSON.parse(this.responseText);
			var task = kenedo.getTask();
			var viewName = kenedo.getViewName();

			// On inserts, run addNewJoinRecord. It checks if we should add the new record to some join drop-down.
			if (response.wasInsert) {
				kenedo.addNewJoinRecord(response);
			}

			cbj(document).trigger('kenedoFormResponseReceived', {'response': response, 'viewName': viewName, 'task': task} );

			switch (task) {

				case 'store':
					kenedo.defaultFormTaskResponseHandlerStore(viewName, response);
					break;
				case 'storeAndNew':
					kenedo.defaultFormTaskResponseHandlerStoreAndNew(viewName, response);
					break;
				case 'apply':
					kenedo.defaultFormTaskResponseHandlerApply(viewName, response);
					break;
			}

		},

		/**
		 * @param {String} viewName
		 * @param {JsonResponses.kenedoStoreResponse} response
		 */
		defaultFormTaskResponseHandlerStore: function(viewName, response) {

			// Show messages
			if (response.success === false) {
				kenedo.processResponseMessages(response);
				return;
			}

			// If we're in a modal, reload any intra listings and close the modal
			if (kenedo.isViewInModal()) {
				parent.window.cbrequire(['cbj', 'kenedo', 'cbj.colorbox'], function(parentCbj, parentKenedo) {
					parentKenedo.reloadIntraListings();
					parentCbj.colorbox.close();
				});
				return;
			}

			// Redirect to the return URL
			if (kenedo.isViewAjaxView()) {
				kenedo.loadSubview(kenedo.getReturnUrl());
			}
			else {
				window.location.href = kenedo.getReturnUrl();
			}

		},

		/**
		 * @param {String} viewName
		 * @param {JsonResponses.kenedoStoreResponse} response
		 */
		defaultFormTaskResponseHandlerStoreAndNew: function(viewName, response) {

			// Show any error or notice messages
			kenedo.processResponseMessages(response);

			// Set all data in the form, overwriting what may needs overwriting
			if (response.data) {
				cbj.each(response.data, function(propertyName, value) {
					cbj('#'+propertyName).val(value);
				});
			}

			// Reset id field to 0 to allow for new insert
			cbj('#id').val('0');

			// If we're in a modal, reload any intra listings and close the modal
			if (kenedo.isViewInModal()) {
				parent.window.cbrequire(['cbj', 'kenedo', 'cbj.colorbox'], function(parentCbj, parentKenedo) {
					parentKenedo.reloadIntraListings();
				});
				return;
			}

			// Redirect to the return URL
			if (kenedo.isViewAjaxView()) {
				kenedo.loadSubview(kenedo.getReturnUrl());
			}
			else {
				window.location.href = kenedo.getReturnUrl();
			}
		},

		/**
		 *
		 * @param {String} viewName
		 * @param {JsonResponses.kenedoStoreResponse} response
		 */
		defaultFormTaskResponseHandlerApply: function(viewName, response) {

			// Show any error or notice messages
			kenedo.processResponseMessages(response);

			// All done in case storing didn't work out
			if (response.success === false) {
				return;
			}

			// Set all data in the form, overwriting what may needs overwriting
			if (response.data) {
				cbj.each(response.data, function(propertyName, value) {
					cbj('#'+propertyName).val(value);
				});
			}

			// If we're in a modal, reload any intra listings
			if (kenedo.isViewInModal()) {
				parent.window.cbrequire(['kenedo'], function(parentKenedo) {
					parentKenedo.reloadIntraListings();
				});
				return;
			}

			// The server's controller may set the redirectUrl (normally for changing the URL after an insert - replacing
			// the URL query string's ID value).
			if (response.redirectUrl) {

				// Redirect to the return URL
				if (kenedo.isViewAjaxView()) {
					kenedo.loadSubview(response.redirectUrl);
				}
				else {
					window.location.href = response.redirectUrl;
				}

			}

		},

		/**
		 * Adds an item to the join dropdowns (and makes a join link) once a related form gets saved.
		 * I know I deserve jail time for this code.
		 * @param {JsonResponses.kenedoStoreResponse} response
		 */
		addNewJoinRecord: function(response) {

			// This input holds the HTML id for the drop-down (Field is set automatically by setting the form_custom_4 param in the URL)
			var joinSelectId = cbj('#form_custom_4').val();

			// Check if the id is actually there, to avoid 'misunderstandings'
			if (joinSelectId) {

				var recordId = response.data.id;

				// Get a title, simply using typical fields (consider using a form_custom field to have it specified clearly)
				var title = (response.data.name) ? response.data.name : '';

				if (!title) {
					title = (response.data.title) ? response.data.title : '';
				}
				if (!title) {
					title = 'New item';
				}

				window.parent.cbrequire(['cbj'], function(parentCbj) {

					// Finally, get the drop-down in the parent..
					var select = parentCbj('#' + joinSelectId);

					// ..do a quick check, add the record, set the value
					if (select.is('select')) {
						if (select.find('option[value='+recordId+']').length === 0) {
							select.append('<option value="'+recordId+'">'+title+'</option>');
							select.val(recordId).change().trigger('chosen:updated');
						}
					}

					var joinLinks = select.closest('.property-body').find('.join-links');
					if (joinLinks.length) {

						var newLink = select.closest('.property-body').find('.join-link-0').clone();
						newLink.removeClass('join-link-0').addClass('join-link-' + recordId);
						var href = newLink.find('a').attr('href');

						href = href.replace('id=0', 'id=' + recordId); // Joomla way
						href = href.replace('/id/0/', '/id/' + recordId + '/'); // Magento way

						newLink.find('a').attr('href', href);

						newLink.css('display', 'inline-block');

						var text = joinLinks.find('.join-link:not(.join-link-0) a').eq(1).text();
						if (!text) {
							text = 'Open';
						}
						newLink.find('a').text(text);

						joinLinks.append(newLink);

					}
				});

			}

		},

		getFormSettings: function() {

			var values = cbj('.kenedo-property :input').serializeArray();
			var settings = [];

			cbj.each(values, function() {
				settings[this.name] = this.value;
			});

			return settings;
		},

		/**
		 * Takes the kenedo form's response data, set's messages and displays them
		 * @param {JsonResponses.kenedoStoreResponse} response
		 */
		processResponseMessages: function(response) {

			kenedo.clearMessages();
			if (response.success === false) {
				cbj.each(response.errors, function(i, error) {
					kenedo.addMessage(error, 'error');
				});
			}
			else {
				cbj.each(response.messages, function(i, messages) {
					kenedo.addMessage(messages, 'notice');
				});
			}
			kenedo.showMessages();

		},

		/**
		 * Looks into kenedo.messages and renders the HTML for the messages. Makes them show
		 */
		showMessages: function() {

			if (this.messages.error.length !== 0) {
				cbj('.kenedo-messages-error').html('<ul></ul>');
				cbj.each(this.messages.error, function(i, item) {
					cbj('.kenedo-messages-error ul').append('<li>'+item+'</li>');
				});
				cbj('.kenedo-messages-error:first').show();

				cbj('.kenedo-messages-error').addClass('flash');
				window.setTimeout(function(){
					cbj('.kenedo-messages-error').removeClass('flash');
				}, 1000);
			}

			if (this.messages.notice.length !== 0) {
				cbj('.kenedo-messages-notice').html('<ul></ul>');
				cbj.each(this.messages.notice, function(i, item) {
					cbj('.kenedo-messages-notice ul').append('<li>'+item+'</li>');
				});
				cbj('.kenedo-messages-notice:first').show();

				cbj('.kenedo-messages-notice').addClass('flash');
				window.setTimeout(function(){
					cbj('.kenedo-messages-notice').removeClass('flash');
				}, 1000);
			}

			cbj('.kenedo-messages:first').show();
		},

		addMessage: function(message, type) {

			if (typeof(this.messages[type]) == 'undefined') {
				this.messages[type] = [];
			}

			this.messages[type].push(message);

		},

		clearMessages: function() {
			cbj('.kenedo-messages').hide();
			cbj('.kenedo-messages-error').html('').hide();
			cbj('.kenedo-messages-notice').html('').hide();
			this.messages = { error : [], notice : [] };
		}

	};


	/* KENEDO POPUP - START */

	var KenedoPopup = {

		openingDelay : 200,
		closingDelay : 200,

		openers : [],
		closers : [],

		/**
		 * Every popup gets opened by a timeout function. This method clears all opening timeouts.
		 */
		clearOpeners : function() {
			cbj.each(KenedoPopup.openers, function(i,item) {
				if (typeof(item) != 'undefined') {
					window.clearTimeout(item.timeout);
				}

			});
			KenedoPopup.openers = [];
		},

		/**
		 * Every popup gets closed by a timeout function. This method clears all closing timeouts.
		 */
		clearClosers : function() {
			cbj.each(KenedoPopup.closers, function(i,item) {
				if (typeof(item) != 'undefined') {
					window.clearTimeout(item.timeout);
				}

			});
			KenedoPopup.closers = [];
		},

		/**
		 * This method sets a timeout function that actually opens the popup.
		 * Before setting one, all existing opening timeout functions get cleared.
		 */
		scheduleOpening: function() {

			// Get the ID of the popup
			var popupId = cbj(this).attr('id').replace('kenedo-popup-trigger-', '');

			// If that popup has already got a scheduled opening, cancel it
			if (KenedoPopup.openers[popupId]) {
				window.clearTimeout(KenedoPopup.openers[popupId].timeout);
			}

			// Set the timeout, add it to the openers
			KenedoPopup.openers[popupId] = {
				popupId : popupId,
				timeout : window.setTimeout(
					function(){
						KenedoPopup.open(popupId);
					},
					KenedoPopup.openingDelay)
			};

		},

		/**
		 * This method sets a timeout function that actually closes the popup.
		 * Before setting one, all existing closing timeout functions get cleared.
		 */
		scheduleClosing: function() {

			var popupId;

			if (cbj(this).is('.kenedo-popup')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-', '');
			}
			if (cbj(this).is('.kenedo-popup-trigger')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-trigger-', '');
			}

			// If that popup has already got a scheduled closing, cancel it
			if (KenedoPopup.closers[popupId]) {
				window.clearTimeout(KenedoPopup.closers[popupId].timeout);
			}

			KenedoPopup.closers[popupId] = {
				popupId : popupId,
				timeout :  window.setTimeout(
					function() {
						KenedoPopup.close(popupId);
					},
					KenedoPopup.closingDelay
				)
			};

		},

		cancelOpening: function() {

			var popupId;

			if (cbj(this).is('.kenedo-popup')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-', '');
			}
			if (cbj(this).is('.kenedo-popup-trigger')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-trigger-', '');
			}

			if (KenedoPopup.openers[popupId]) {
				window.clearTimeout(KenedoPopup.openers[popupId].timeout);
			}

		},

		cancelClosing: function() {

			var popupId;

			if (cbj(this).is('.kenedo-popup')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-', '');
			}
			if (cbj(this).is('.kenedo-popup-trigger')) {
				popupId = cbj(this).attr('id').replace('kenedo-popup-trigger-', '');
			}

			if (KenedoPopup.closers[popupId]) {
				window.clearTimeout(KenedoPopup.closers[popupId].timeout);
			}

		},

		open : function( popupId ) {

			var popup = cbj('#kenedo-popup-original-' + popupId).clone(true);

			// Delete any existing popups
			cbj('#kenedo-popup-' + popupId).remove();

			popup.hide();
			popup.attr('id', 'kenedo-popup-' + popupId);
			popup.appendTo('body');

			KenedoPopup.position(popupId);

			popup.fadeIn();

			cbj('#kenedo-popup-trigger-' + popupId).trigger('popup-open');

		},

		close : function( popupId ) {

			cbj('#kenedo-popup-' + popupId).fadeOut(200, function() {
				cbj(this).remove();
			});

			cbj('#kenedo-popup-trigger-' + popupId).trigger('popup-close');

		},

		position : function( popupId ) {

			var popupTrigger = cbj('#kenedo-popup-trigger-' + popupId);
			var popup = cbj('#kenedo-popup-' + popupId);

			popup.css('visibility', 'hidden');
			popup.css('display', 'block');

			cbj('body').append(popup);

			var triggerContent = popupTrigger.find('.kenedo-popup-trigger-content');

			var triggerWidth = triggerContent.outerWidth();
			var triggerHeight = triggerContent.outerHeight();
			var triggerPos = triggerContent.offset();

			var popupWidth = popup.find('.kenedo-popup-content').outerWidth();
			var popupHeight = popup.find('.kenedo-popup-content').outerHeight();

			var arrowBoxWidth = 26;
			var arrowBoxHeight = 26;

			var arrowHeight = Math.sqrt(arrowBoxWidth * arrowBoxWidth + arrowBoxHeight * arrowBoxHeight) / 2;

			popup.outerWidth(popupWidth);
			popup.outerHeight(popupHeight + arrowHeight);

			var viewPortWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
			var viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
			var scrollTop = cbj('body').scrollTop();
			var scrollLeft = cbj('body').scrollLeft();


			var popupLeftEdge = triggerPos.left + triggerWidth / 2 - popupWidth / 2;

			var topPosTopEdge = triggerPos.top - popupHeight - arrowHeight;

			var bottomPosTopEdge = triggerPos.top + triggerHeight + arrowHeight;
			var bottomPosBottomEdge = bottomPosTopEdge + popupHeight;

			var newLeft, delta;

			// If popup goes over the viewport left edge, adjust
			if (popupLeftEdge + scrollLeft < 0) {

				newLeft = 10 + scrollLeft;
				delta = popupLeftEdge - newLeft;

				var styleEl = document.createElement('style');
				document.head.appendChild(styleEl);
				styleEl.sheet.insertRule('.kenedo-popup-content:before {left:'+ delta +'px;}', styleEl.sheet.cssRules.length);
				styleEl.sheet.insertRule('.kenedo-popup-content:after {left:'+ delta +'px;}', styleEl.sheet.cssRules.length);

				popupLeftEdge = newLeft;

			}
			else {
				var styleEl1 = document.createElement('style');
				document.head.appendChild(styleEl1);
				styleEl1.sheet.insertRule('.kenedo-popup-content:before {left:auto;}', styleEl1.sheet.cssRules.length);
				styleEl1.sheet.insertRule('.kenedo-popup-content:after {left:auto;}', styleEl1.sheet.cssRules.length);
			}

			var popupRightEdge = popupWidth + popupLeftEdge;

			// If popup goes over the viewport right edge, adjust
			if (popupRightEdge > viewPortWidth) {

				newLeft = viewPortWidth - popupWidth - 10;
				delta = popupLeftEdge - newLeft;

				var styleEl2 = document.createElement('style');
				document.head.appendChild(styleEl2);
				styleEl2.sheet.insertRule('.kenedo-popup-content:before {left:' + delta + 'px;}', styleEl2.sheet.cssRules.length);
				styleEl2.sheet.insertRule('.kenedo-popup-content:after {left:' + delta + 'px;}', styleEl2.sheet.cssRules.length);

				popupLeftEdge = newLeft;

			}

			var fitsTop = topPosTopEdge > scrollTop;
			var fitsBottom = bottomPosBottomEdge < viewPortHeight + scrollTop;

			// Figure out if popup should show on top or bottom of the trigger
			var showOnTop = null;

			// With class prefer-top and if it fits, to top
			if (popup.hasClass('position-prefer-top') && fitsTop) {
				showOnTop = true;
			}
			// Vice versa
			if (popup.hasClass('position-prefer-bottom') && fitsBottom) {
				showOnTop = false;
			}
			// Preference doesn't go, so use top if it filts, bottom otherwise
			if (showOnTop === null) {
				showOnTop = fitsTop;
			}

			if (showOnTop) {
				popup.addClass('position-top').removeClass('position-bottom');
				popup.offset({left:popupLeftEdge, top:topPosTopEdge});
			}
			else {
				popup.addClass('position-bottom').removeClass('position-top');
				popup.offset({left:popupLeftEdge, top:bottomPosTopEdge});
			}

			popup.css('display', 'none');
			popup.css('visibility', 'visible');

			return popup;

		}

	};

	/* KENEDO POPUP - END */


	/**
	 *
	 *  Base64 encode / decode
	 *  http://www.webtoolkit.info/
	 *
	 **/


	var Base64 = {

		// private property
		_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

		// public method for encoding
		encode : function (input) {
			/* jshint -W016 */
			var output = "";
			var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
			var i = 0;

			input = Base64._utf8_encode(input);

			while (i < input.length) {

				chr1 = input.charCodeAt(i++);
				chr2 = input.charCodeAt(i++);
				chr3 = input.charCodeAt(i++);

				enc1 = chr1 >> 2;
				enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
				enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
				enc4 = chr3 & 63;

				if (isNaN(chr2)) {
					enc3 = enc4 = 64;
				} else if (isNaN(chr3)) {
					enc4 = 64;
				}

				output = output +
					this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
					this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

			}

			return output;
		},

		// public method for decoding
		decode : function (input) {
			/* jshint -W016 */
			var output = "";
			var chr1, chr2, chr3;
			var enc1, enc2, enc3, enc4;
			var i = 0;

			input = input.replace(/[^A-Za-z0-9\+\/=]/g, "");

			while (i < input.length) {

				enc1 = this._keyStr.indexOf(input.charAt(i++));
				enc2 = this._keyStr.indexOf(input.charAt(i++));
				enc3 = this._keyStr.indexOf(input.charAt(i++));
				enc4 = this._keyStr.indexOf(input.charAt(i++));

				chr1 = (enc1 << 2) | (enc2 >> 4);
				chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
				chr3 = ((enc3 & 3) << 6) | enc4;

				output = output + String.fromCharCode(chr1);

				if (enc3 != 64) {
					output = output + String.fromCharCode(chr2);
				}
				if (enc4 != 64) {
					output = output + String.fromCharCode(chr3);
				}

			}

			output = Base64._utf8_decode(output);

			return output;

		},

		// private method for UTF-8 encoding
		_utf8_encode : function (string) {
			/* jshint -W016 */
			string = string.replace(/\r\n/g,"\n");
			var utftext = "";

			for (var n = 0; n < string.length; n++) {

				var c = string.charCodeAt(n);

				if (c < 128) {
					utftext += String.fromCharCode(c);
				}
				else if((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				}
				else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}

			}

			return utftext;
		},

		// private method for UTF-8 decoding
		_utf8_decode : function (utftext) {
			/* jshint -W016 */
			var string = "";
			var i = 0;
			var c = 0;
			var c2 = 0;
			var c3 = 0;

			while ( i < utftext.length ) {

				c = utftext.charCodeAt(i);

				if (c < 128) {
					string += String.fromCharCode(c);
					i++;
				}
				else if((c > 191) && (c < 224)) {
					c2 = utftext.charCodeAt(i+1);
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					i += 2;
				}
				else {
					c2 = utftext.charCodeAt(i+1);
					c3 = utftext.charCodeAt(i+2);
					string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}

			}

			return string;
		}

	};

	return kenedo;

});
