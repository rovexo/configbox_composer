/**
 * @module configbox/dashboard
 */
define(['cbj', 'configbox/server'], function(cbj, server) {
	"use strict";
	/**
	 * @exports configbox/dashboard
	 */
	var module = {

		initDashboard: function() {

			// Update news and software update check
			module.addDashboardInfo();
			module.addLicenseInfo();

			cbj(document).on('click', '.view-admindashboard .trigger-remove-file-structure-warning', function() {

				server.makeRequest('admindashboard', 'removeFileStructureWarning', []);

				cbj(this).closest('.issue-item').remove();

			});

		},

		addDashboardInfo : function() {

			var parameters = {
				'platform': server.config.platformName,
				'lang': server.config.languageCode,
				'version': cbj('.kenedo-view.view-admindashboard').data('configbox-version')
			};

			cbj.ajax({
				url: cbj('.kenedo-view.view-admindashboard').data('endpoint-url-dashboard-info'),
				dataType: 'jsonp',
				crossDomain: true,
				data: parameters,
				success: function(data){
					module.injectDataDashboardInfo(data);
				},
				error: function() {
					cbj('.view-admindashboard .news').html('Cannot load news at this time.');
				}

			});

		},

		/**
		 * Inserts json-derived data into the dashboard
		 * @param {JsonResponses.dashboardData} data
		 */
		injectDataDashboardInfo: function(data) {
			cbj('.view-admindashboard .news .news-target').html(data.news);
			cbj('.view-admindashboard .news').css('visibility','visible');

			cbj('.checking-for-update').hide();

			if (data.softwareUpdate.url) {
				cbj('.software-update-link').attr('href',data.softwareUpdate.url);
			}

			if (data.softwareUpdate.patchLevel) {
				cbj('.latest-version-patchlevel').text(data.softwareUpdate.patchLevel);
				cbj('.patchlevel-update-available').show();
			}

			if (data.softwareUpdate.major) {
				cbj('.latest-version-major').text(data.softwareUpdate.major);
				cbj('.major-update-available').show();
			}

			if (!data.softwareUpdate.major && !data.softwareUpdate.patchLevel) {
				cbj('.no-update-available').show();
			}

		},

		addLicenseInfo : function() {

			var parameters = {
				'license_key': cbj('.kenedo-view.view-admindashboard').data('license-key'),
				'lang': server.config.languageCode
			};

			cbj.ajax({
				url: cbj('.kenedo-view.view-admindashboard').data('endpoint-url-license-info'),
				dataType: 'jsonp',
				crossDomain: true,
				data: parameters,
				success: function(data) {
					if (data.success === true) {
						cbj('.wrapper-license-data').html(data.html);
					}
					else {
						cbj('.wrapper-license-data').html(data.errors.join('<br />'));
					}
				},
				error: function() {
					cbj('.wrapper-license-data').html('an error occurred loading your license data.');
				}

			});

		}

	};

	return module;

});
