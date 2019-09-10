/**
 * @module configbox/server
 */
define(['cbj'], function (cbj) {
	"use strict";

	/**
	 * @exports configbox/server
	 */
	var server = {

		/**
		 * This data gets defined in PHP method ConfigboxViewHelper::getAmdLoaderJs
		 */
		config: {
			platformName: '',
			urlSystemAssets: '',
			urlCustomAssets: '',
			urlBase: '',
			urlTinyMceBase: '',
			languageCode: '',
			languageTag: '',
			decimalSymbol: '',
			thousandsSeparator: '',
			cbVersion: '',
			cacheVar: '',
			urlXhr: '',
			useMinifiedJs: true,
			useMinifiedCss: true,
			useAssetsCacheBuster: true
		},

		/**
		 *
		 * @param {string|jQuery} target - CSS selector or jQuery collection
		 * @param {string} controller - CB controller name
		 * @param {string} task - CB controller task
		 * @param {array|object} data - POST data to send
		 * @param {function=} callback - Callback to send
		 */
		injectHtml: function(target, controller, task, data, callback) {

			var requestData = data;
			requestData.option = 'com_configbox';
			requestData.controller = controller;
			requestData.task = task;
			requestData.format = 'raw';

			var collection = (typeof target === 'string') ? cbj(target) : target;

			collection.load(server.config.urlXhr, requestData, function() {
				cbj(document).trigger('cbViewInjected');
				if (callback) {
					callback();
				}
			})
		},

		/**
		 * Makes an http request to configbox and gives you jQuery's jqXHR to chain a done, fail and always call.
		 * Your backend method must return a JSON string.
		 *
		 * @param {string} controller - Name of the controller to receive the call
		 * @param {string} task - Task name within given controller
		 * @param {array|object} data - Can contain File objects for file uploads
		 * @returns {jqXHR}
		 */
		makeRequest: function(controller, task, data) {

			if (typeof(controller) !== 'string' || typeof(task) !== 'string') {
				throw('1st and 2nd parameter needs to be a string');
			}

			if (typeof(data) !== 'undefined' && typeof(data) !== 'object') {
				throw('3rd parameter needs to be a flat array or object consisting of strings');
			}

			// See if there is File data
			var hasFiles = false;

			if (typeof(data) !== 'undefined') {
				cbj.each(data, function(key, value) {
					if (value instanceof File) {
						hasFiles = true;
					}
					if (value instanceof FileList) {
						hasFiles = true;
					}
				});
			}

			var requestData;

			if (hasFiles === true) {

				// If we got files, use FormData and override some things in the AJAX call
				requestData = new FormData();

				requestData.append('lang', server.config.languageCode);
				requestData.append('option', 'com_configbox');
				requestData.append('controller', controller);
				requestData.append('task', task);
				requestData.append('format', 'raw');

				cbj.each(data, function(key, value) {
					if (value instanceof FileList) {
						requestData.append(key, value[0]);
					}
					else {
						requestData.append(key, value);
					}
				});

				return cbj.ajax({
					url: server.config.urlXhr,
					data: requestData,
					processData: false,
					contentType: false,
					dataType: 'json',
					type: 'post'
				});

			}
			else {

				// Otherwise, make the call in the regular way
				requestData = {
					lang: server.config.languageCode,
					option: 'com_configbox',
					controller: controller,
					task: task,
					format: 'raw'
				};

				// Mix in the data
				if (typeof(data) !== 'undefined') {
					cbj.each(data, function(i, item) {
						requestData[i] = item;
					});
				}

				return cbj.ajax({
					url: server.config.urlXhr,
					data: requestData,
					dataType: 'json',
					type: 'post'
				});

			}

		},

		checkoutCart: function(cartId) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'cart',
				task: 'checkoutCart',
				format: 'json',
				cartId: cartId
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		/**
		 * 
		 * @param {number} cartPositionId
		 * @param {number} quantity
		 */
		updateCartPositionQuantity: function(cartPositionId, quantity) {
			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'cart',
				task: 'setCartPositionQuantity',
				format: 'json',
				cart_position_id: cartPositionId,
				quantity: quantity
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});
		},

		/**
		 * Submits the payment method ID for the current order
		 *
		 * @param {int} id
		 * @returns {xhr}
		 */
		setPaymentOption: function (id) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'checkout',
				task: 'storePaymentOption',
				format: 'raw',
				id: id
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		/**
		 * Submits the delivery method ID for the current order
		 *
		 * @param {int} id
		 * @returns {xhr}
		 */
		setDeliveryOption: function (id) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'checkout',
				task: 'storeDeliveryOption',
				format: 'raw',
				id: id
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		placeOrder: function () {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'checkout',
				task: 'placeOrder',
				format: 'raw'
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		/**
		 *
		 * @param {Number} cartId
		 * @returns {jqXHR}
		 */
		prepareQuote: function (cartId) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'rfq',
				task: 'createQuotation',
				format: 'json',
				cartId: cartId
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'get'
			});

		},

		requestLogin: function (username, password) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'user',
				task: 'loginUser',
				format: 'json',
				username: username,
				password: password
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post',
				context: cbj(this)
			});

		},

		requestLogout: function () {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'user',
				task: 'logoutUser',
				format: 'json'
			};

			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post',
				context: cbj(this)
			});

		},

		requestPasswordChangeVerificationCode: function (email) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'user',
				task: 'sendPasswordChangeVerificationCode',
				format: 'raw',
				email: email
			};

			// Do the request, pass it back
			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		requestPasswordChange: function (code, password, loginUser) {

			var requestData = {
				lang: server.config.languageCode,
				option: 'com_configbox',
				controller: 'user',
				task: 'changePasswordWithCode',
				format: 'json',
				code: code,
				password: password,
				login: (loginUser === true) ? '1' : '0'
			};

			// Do the request, pass it back
			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		},

		storeOrderAddress: function (customerData) {

			var requestData = customerData;

			requestData.option = 'com_configbox';
			requestData.controller = 'checkout';
			requestData.task = 'storeOrderAddress';
			requestData.format = 'json';
			requestData.lang = server.config.languageCode;

			// Do the request, pass it back
			return cbj.ajax({
				url: server.config.urlXhr,
				data: requestData,
				dataType: 'json',
				type: 'post'
			});

		}

	};

	server.api = {
		makeRequest: function(controller, task, data) {
			window.console.log('Go use makeRequest instead of api.makeRequest');
			return server.makeRequest(controller, task, data);
		}
	};


	// Read the configuration and put it in the config
	if (document.getElementById('cb-require-tag')) {
		server.config = JSON.parse(document.getElementById('cb-require-tag').dataset.appConfig);
	}
	else if(document.getElementById('cb-main-file-tag')) {
		server.config = JSON.parse(document.getElementById('cb-main-file-tag').dataset.appConfig);
	}

	return server;

});