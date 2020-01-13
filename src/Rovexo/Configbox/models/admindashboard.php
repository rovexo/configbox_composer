<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminDashboard extends KenedoModel {

	protected static $error;

	function getDbSettings() {
		$db = KenedoPlatform::getDb();
		$query = "SHOW VARIABLES";
		$db->setQuery($query);
		$vars = $db->loadResultList('Variable_name','Value');
		return $vars;
	}

	function getDbStats() {
		$db = KenedoPlatform::getDb();
		$query = "SHOW STATUS";
		$db->setQuery($query);
		$vars = $db->loadResultList('Variable_name','Value');
		return $vars;
	}

	function getPhpExtensions() {
		$extensions = get_loaded_extensions();
		return $extensions;
	}

	function getCurrentStats() {

		$extensions = $this->getPhpExtensions();
		$dbSettings = $this->getDbSettings();
		$dbStats = $this->getDbStats();

		$stats = array();

		// Buffer pool size
		$totalSize = $dbSettings['innodb_buffer_pool_size'];
		$totalPages = $dbStats['Innodb_buffer_pool_pages_total'];
		$freePages = $dbStats['Innodb_buffer_pool_pages_free'];
		$usedPages = $totalPages - $freePages;

		$pageSize = $dbSettings['innodb_buffer_pool_size'] / $totalPages;
		$usedSize = $pageSize * $usedPages;

		$stat = new stdClass();
		$stat->title = KText::_('InnoDB Buffer Pool');
		$stat->unit = 'MB';
		$stat->total = $this->getInMb($totalSize);
		$stat->used = $this->getInMb($usedSize);
		$stat->free = $stat->total - $stat->used;
		$stat->percentageFree = round($stat->free / $stat->total * 100, 1);
		$stat->percentageUsed = round(100 - $stat->percentageFree, 1);
		$stats['bufferpoolsize'] = $stat;

		if ($dbSettings['query_cache_size']) {
			// Query cache size
			$stat = new stdClass();
			$stat->title = KText::_('DB Query cache');
			$stat->unit = 'MB';
			$stat->total = $this->getInMb($dbSettings['query_cache_size'],2);
			$stat->free = $this->getInMb($dbStats['Qcache_free_memory'],2);
			$stat->used = $stat->total - $stat->free;
			$stat->percentageFree = round($stat->free / $stat->total * 100, 1);
			$stat->percentageUsed = round(100 - $stat->percentageFree, 1);
			$stats['querycachesize'] = $stat;
		}

		if (extension_loaded('apcu') && ini_get('apc.enabled') == true) {
			if (function_exists('apcu_sma_info')) {

				$info = apcu_sma_info(true);

				$totalInBytes = $info['seg_size'] * $info['num_seg'];

				$stat = new stdClass();
				$stat->title = KText::_('DASHBOARD_STAT_APCU_TITLE');
				$stat->unit = 'MB';
				$stat->total = $this->getInMb($totalInBytes, 0);
				$stat->free = $this->getInMb($info['avail_mem'],0);
				$stat->used = $stat->total - $stat->free;
				$stat->percentageFree = round($stat->free / $stat->total * 100, 1);
				$stat->percentageUsed = round(100 - $stat->percentageFree, 1);
				$stats['apcu'] = $stat;

			}
		}



		return $stats;
	}


	function getPerformanceTips() {

		$extensions = $this->getPhpExtensions();
		$dbSettings = $this->getDbSettings();
		$dbStats = $this->getDbStats();

		$tips = array();

		$totalSize = $dbSettings['innodb_buffer_pool_size'];

		$totalPages = $dbStats['Innodb_buffer_pool_pages_total'];
		$freePages = $dbStats['Innodb_buffer_pool_pages_free'];
		$usedPages = $totalPages - $freePages;
		$percentageFree = round($freePages / $usedPages * 100,2);

		$recommendedTotal = $this->getInMb($totalSize * 1.25);

		if ($recommendedTotal < 32) {
			$recommendedTotal = 128;
		}

		if ($percentageFree < 15) {
			$tip = new stdClass();
			$tip->title = KText::sprintf('Raise InnoDB Buffer Pool Size to %sMB.',$recommendedTotal);
			$tip->prospect = KText::sprintf('The database server has only %s%% free space in the InnoDB buffer pool of a total %sMB. When buffer space runs out, the database will be much slower.', $percentageFree, $this->getInMb($totalSize));
			$tip->solution = KText::sprintf('Raise the database server variable innodb_buffer_pool_size to %sMB.', $recommendedTotal);
			$tip->access = KText::_('You normally need a VPS or root server to be able to change database server settings. If you are on a shared host or are not familiar with database server administration, consult your hosting provider, server administrator or a service partner.');
			$tips[] = $tip;
		}

		if ($dbSettings['query_cache_size'] == 0) {
			$tip = new stdClass();
			$tip->title = KText::_('Enable database query cache.');
			$tip->prospect = KText::_('The query cache can give you a light to significant performance boost without using too much memory. Set the Query Cache Size to 32MB initially and check back here in 24 hours to see how much has been used.');
			$tip->solution= KText::_('Set the database server variable query_cache_size to 32MB.');
			$tip->access = KText::_('You normally need a VPS or root server to be able to change database server settings. If you are on a shared host or are not familiar with database server administration, consult your hosting provider, server administrator or a service partner.');
			$tips[] = $tip;
		}

		if ($dbStats['Qcache_lowmem_prunes']) {
			$recommendedSize = $this->getInMb($dbSettings['query_cache_size'] * 1.2);
			$tip = new stdClass();
			$tip->title = KText::sprintf('Raise database query cache size to %sMB.',$recommendedSize);
			$tip->prospect = KText::sprintf('The database server reports that it had to remove %s cached queries due to low memory. Current query cache size is %sMB. Raising the memory limit by 25%% to %sMB is recommended.', $dbStats['Qcache_lowmem_prunes'], $this->getInMb($dbSettings['query_cache_size']), $recommendedSize);
			$tip->solution= KText::sprintf('Set the database server variable query_cache_size to %sMB.',$recommendedSize);
			$tip->access = KText::_('You normally need a VPS or root server to be able to change database server settings. If you are on a shared host or are not familiar with database server administration, consult your hosting provider, server administrator or a service partner.');
			$tips[] = $tip;
		}

		$percentageDiskTables = round($dbStats['Created_tmp_disk_tables'] / $dbStats['Created_tmp_tables'] * 100,0);
		$raise = $percentageDiskTables + 5;
		$size = min($dbSettings['tmp_table_size'], $dbSettings['max_heap_table_size']);
		$recommendedSize = $this->getInMb( $size + ($size / ($raise) * 100) );

		if ($percentageDiskTables > 5) {
			$tip = new stdClass();
			$tip->title = KText::sprintf('Raise database tmp_table_size and max_heap_table_size limit to %sMB.',$recommendedSize);
			$tip->prospect = KText::sprintf('%s%% (%s of %s) of the created temporary tables are written on disk instead of in memory which takes considerably more time. Your maximum table size currently is %sMB. It is the lower value of tmp_table_size and max_heap_table_size.', $percentageDiskTables, $dbStats['Created_tmp_disk_tables'], $dbStats['Created_tmp_tables'],  $this->getInMb($size));
			$tip->solution = KText::sprintf('Raise database tmp_table_size and max_heap_table_size limit to %sMB.',$recommendedSize);
			$tip->access = KText::_('You normally need a VPS or root server to be able to change database server settings. If you are on a shared host or are not familiar with database server administration, consult your hosting provider, server administrator or a service partner.');
			$tips[] = $tip;
		}

		if (version_compare($dbSettings['version'], 5.5) < 0 ) {
			$tip = new stdClass();
			$tip->title = KText::_('Upgrade database server to version 5.5.',$recommendedSize);
			$tip->prospect = KText::sprintf('Version 5.5 is significantly faster than previous versions. The server currently uses version %s.',$dbSettings['version']);
			$tip->solution = KText::_('Get the latest database server version and upgrade your installation.');
			$tip->access = KText::_('You normally need a VPS or root server to be able to change database server settings. If you are on a shared host or are not familiar with database server administration, consult your hosting provider, server administrator or a service partner.');
			$tips[] = $tip;
		}

		$hasApcu = extension_loaded('apcu') && ini_get('apc.enabled');
		if ($hasApcu == false) {
			$tip = new stdClass();
			$tip->title = KText::_('DASHBOARD_TIP_GET_APCU_TITLE');
			$tip->prospect = KText::_('DASHBOARD_TIP_GET_APCU_PROSPECT');
			$tip->solution = KText::_('DASHBOARD_TIP_GET_APCU_SOLUTION');
			$tip->access = KText::_('DASHBOARD_TIP_GET_APCU_ACCESS');
			$tips[] = $tip;
		}

		return $tips;
	}

	function getCriticalIssues() {

		$items = array();

		if ((KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') && $this->shopCountryIsMissing()) {
			$warning = new stdClass();
			$warning->title = KText::_('WARNING_NO_SHOP_COUNTRY_TITLE');
			$warning->problem = KText::_('WARNING_NO_SHOP_COUNTRY_PROBLEM');
			$warning->solution = KText::_('WARNING_NO_SHOP_COUNTRY_SOLUTION');
			$warning->access = KText::_('WARNING_NO_SHOP_COUNTRY_ACCESS');
			$items[] = $warning;
		}

		if ($this->isLicenseKeyEntered() == false) {
			$warning = new stdClass();
			$warning->title = KText::_('WARNING_NO_LICENSE_KEY_ENTERED_TITLE');
			$warning->problem = KText::_('WARNING_NO_LICENSE_KEY_ENTERED_PROBLEM');
			$warning->solution = KText::_('WARNING_NO_LICENSE_KEY_ENTERED_SOLUTION');
			$warning->access = KText::_('WARNING_NO_LICENSE_KEY_ENTERED_ACCESS');
			$items[] = $warning;
		}

		if ($this->oldIonCubeVersionDetected()) {

			if (function_exists('ioncube_loader_version')) {
				$version = ioncube_loader_version();
			}
			else {
				$version = '(unknown)';
			}

			$warning = new stdClass();
			$warning->title = KText::_('WARNING_IONCUBE_VERSION_TITLE');
			$warning->problem = KText::sprintf('WARNING_IONCUBE_VERSION_PROBLEM', $version);
			$warning->solution = KText::_('WARNING_IONCUBE_VERSION_SOLUTION');
			$warning->access = KText::_('WARNING_IONCUBE_VERSION_ACCESS');
			$items[] = $warning;
		}

		if ($this->userCanCreateConstraints() == false) {
			$warning = new stdClass();
			$warning->title = KText::_('DASHBOARD_PROBLEM_TITLE_CANNOT_MAKE_CONSTRAINTS');
			$warning->problem = KText::_('DASHBOARD_PROBLEM_PROBLEM_CANNOT_MAKE_CONSTRAINTS');
			$warning->solution = KText::_('DASHBOARD_PROBLEM_SOLUTION_CANNOT_MAKE_CONSTRAINTS');
			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if ($this->upgradeFailureDetected()) {
			$warning = new stdClass();
			$warning->title = KText::_('Failure in software upgrade or installation detected.');
			$logPath = '<span class="force-wrap">'.KenedoPlatform::p()->getLogPath().'/configbox/configbox_upgrade_errors.php'.'</span>';
			$warning->problem = KText::_('An error occured during a ConfigBox software upgrade or installation. This is critical even if you do not notice any problems and you should not continue running the software until this issue is resolved.');
			$warning->solution = KText::sprintf('CRITICAL_ISSUE_UPDATE_FAILURE_SOLUTION', $logPath);
			$warning->access = KText::_('Software service provider, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if (!$this->allDataItemsWritable()) {
			$warning = new stdClass();
			$warning->title = KText::_('Data folders and their content are not writable.');
			$warning->problem = KText::_('Not all files and folders in the data folders are writable. This can lead to errors since data cannot be cached or files like images cannot be uploaded.');
			$warning->solution = KText::sprintf('Make sure the folders %s and their subfolders and files are writable. Best solution is to change file ownership to the web server. You can also use FTP and make files and folders writable to all users.', '/components/com_configbox/data');
			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if ($text = $this->getFolderStructureFailInfo()) {
			$warning = new stdClass();
			$warning->title = KText::_('Automatic folder structure change failed.');
			$warning->problem = KText::_('With version 3.1 the folder structure for images and similar has changed. The automated change has failed. See log file for details. You need to rename and move these folders manually.');
			$warning->solution = $text;
			$warning->access = KText::_('Software service provider, server administrator or hosting provider.');
			$warning->isStructureFail = true;
			$items[] = $warning;
		}

		$extensions = $this->getPhpExtensions();
		if (in_array('mbstring',$extensions) == false) {
			$warning = new stdClass();
			$warning->title = KText::_('PHP extension mbstring not installed.');
			$warning->problem = KText::_('mbstring is a standard PHP extension that is disabled on some hosting providers. It handles proper handling of UTF8 text.');
			$warning->solution = KText::_('Ask your server administrator or hosting provider to install or enable mbstring.');
			$warning->access = KText::_('Server administrator or hosting provider.');
			$items[] = $warning;
		}

		if (!is_dir(KenedoPlatform::p()->getLogPath())) {
			$warning = new stdClass();
			$warning->title = KText::_('Log folder does not exist.');
			$warning->problem = KText::sprintf('The log folder %s does not exist. The system cannot write any logs about system errors this way.',realpath(KenedoPlatform::p()->getLogPath()) );
			$warning->solution = KText::_('Go to the Joomla configuration to tab system and set the correct folder at Path to Log folder.');
			$warning->access = KText::_('Anybody with administrator privileges in the Joomla backend.');
			$items[] = $warning;
		}

		if (class_exists('SoapClient') == false) {
			$warning = new stdClass();
			$warning->title = KText::_('PHP extension SOAP is not installed.');
			$warning->problem = KText::_('The PHP extension SOAP is not installed. This extension is needed to validate VAT identification numbers.');
			$warning->solution = KText::_('Ask your server administrator or hosting provider to install or enable SOAP.');
			$warning->access = KText::_('Server administrator or hosting provider.');
			$items[] = $warning;
		}

		if(is_dir(KenedoPlatform::p()->getLogPath()) && is_writable(KenedoPlatform::p()->getLogPath()) == false) {
			$warning = new stdClass();
			$warning->title = KText::_('Log folder is not writable.');
			$warning->problem = KText::sprintf('The log folder %s is not writable. The system cannot write any logs about system errors this way.',realpath(KenedoPlatform::p()->getLogPath()));
			$warning->solution = KText::_('Change the write permissions on the folder or go to the Joomla configuration to tab System and set a different folder at Path to Log folder.');
			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if (!is_dir(KenedoPlatform::p()->getTmpPath())) {
			$warning = new stdClass();
			$warning->title = KText::_('Temp folder does not exist.');
			$warning->problem = KText::sprintf('The Temp folder %s does not exist. You will not be able to update ConfigBox or any other extension in this situation.', KenedoPlatform::p()->getTmpPath());
			$warning->solution = KText::_('Go to the Joomla configuration to tab Server and set the correct folder at Path to Temp folder.');

			$warning->title = KText::_('WARNING_TMP_FOLDER_MISSING_TITLE');
			$warning->problem = KText::sprintf('WARNING_TMP_FOLDER_MISSING_PROBLEM', KenedoPlatform::p()->getTmpPath());
			$warning->solution = KText::_('WARNING_TMP_FOLDER_MISSING_SOLUTION');

			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if( is_dir(KenedoPlatform::p()->getTmpPath()) && is_writable(KenedoPlatform::p()->getTmpPath()) == false ) {
			$warning = new stdClass();
			$warning->title = KText::_('WARNING_TMP_FOLDER_NOT_WRITABLE_TITLE');
			$warning->problem = KText::sprintf('WARNING_TMP_FOLDER_NOT_WRITABLE_PROBLEM', realpath(KenedoPlatform::p()->getTmpPath()));
			$warning->solution = KText::_('WARNING_TMP_FOLDER_NOT_WRITABLE_SOLUTION');
			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		if (function_exists('mime_content_type') == false && function_exists('finfo_open') == false) {
			$warning = new stdClass();
			$warning->title = KText::_('DASHBOARD_PROBLEM_TITLE');
			$warning->problem = KText::_('DASHBOARD_PROBLEM_MISSING_MIME_TYPE_FUNCTIONS');
			$warning->solution = KText::_('DASHBOARD_SOLUTION_MISSING_MIME_TYPE_FUNCTIONS');
			$warning->access = KText::_('Server administrator or hosting provider.');
			$items[] = $warning;
		}

		// Check if catalog/product_option::groupFactory gives the right model for type configbox
		if (KenedoPlatform::getName() == 'magento') {
			// Get the product_option model
			$model = Mage::getModel('catalog/product_option');
			// See if we get the right model for type 'configbox', we can live with a third party rewrite

			try {
				/** @noinspection PhpUndefinedMethodInspection */
				$optionModel = $model->groupFactory('configbox');
			}
			catch (Exception $e) {
				$optionModel = new stdClass();
			}

			if (is_a($optionModel, 'Elovaris_Configbox_Model_Product_Option_Type_Custom') == false) {
				// Get class and module name from product_option model
				$className = get_class($model);
				$ex = explode('_', $className);
				$moduleName = $ex[0].'_'.$ex[1];

				$warning = new stdClass();
				$warning->title = KText::_('Module conflict for product option model.');
				$warning->problem = KText::sprintf('WARNING_MAGE_MODULE_CONFLICT_PRODUCT_OPTION_GROUP_FACTORY', $moduleName, 'catalog/product_option', $className);
				$warning->solution = KText::_('SOLUTION_MAGE_MODULE_CONFLICT');
				$warning->access = KText::_('Software service provider');
				$items[] = $warning;

			}
		}

		// Check if catalog/product_option::getGroupByType gives the right group for type configbox
		if (KenedoPlatform::getName() == 'magento') {

			// Get the product_option model
			$model = Mage::getModel('catalog/product_option');

			// See if we get the right model for type 'configbox', we can live with a third party rewrite
			try {
				/** @noinspection PhpUndefinedMethodInspection */
				$groupName = $model->getGroupByType('configbox');
			}
			catch (Exception $e) {
				$groupName = '';
			}

			if ($groupName != 'configbox') {
				// Get class and module name from product_option model
				$className = get_class($model);
				$ex = explode('_', $className);
				$moduleName = $ex[0].'_'.$ex[1];

				$warning = new stdClass();
				$warning->title = KText::_('Module conflict for product option model.');
				$warning->problem = KText::sprintf('WARNING_MAGE_MODULE_CONFLICT_PRODUCT_OPTION_GET_GROUP_BY_TYPE', $moduleName, 'catalog/product_option', $className);
				$warning->solution = KText::_('SOLUTION_MAGE_MODULE_CONFLICT');
				$warning->access = KText::_('Software service provider');
				$items[] = $warning;

			}
		}

		if (KenedoPlatform::getName() == 'magento') {

			$rewrites = array(
				'sales/quote' => 'Elovaris_Configbox_Model_Quote',
				'wishlist/wishlist' => 'Elovaris_Configbox_Model_Wishlist',
			);

			foreach($rewrites as $shortCut=>$shouldClass) {
				$model = Mage::getModel($shortCut);

				if (!is_a($model, $shouldClass)) {

					// Get class and module name from product_option model
					$className = get_class($model);
					$ex = explode('_', $className);
					$moduleName = $ex[0].'_'.$ex[1];

					$warning = new stdClass();
					$warning->title = KText::sprintf('TITLE_MODULE_CONFLICT', $moduleName, $shortCut, $className);
					$warning->problem = KText::sprintf('WARNING_MAGE_MODULE_CONFLICT_GENERAL', $moduleName, $shortCut, $className);
					$warning->solution = KText::_('SOLUTION_MAGE_MODULE_CONFLICT');
					$warning->access = KText::_('Software service provider');
					$items[] = $warning;

				}
			}
		}

		if( !ini_get('allow_url_fopen') ) {
			$warning = new stdClass();
			$warning->title = KText::_('TITLE_URL_FOPEN');
			$warning->problem = KText::_('WARNING_URL_FOPEN');
			$warning->solution = KText::_('SOLUTION_URL_FOPEN');
			$warning->access = KText::_('Developer, server administrator or hosting provider.');
			$items[] = $warning;
		}

		// Parse active languages override files
		$languages = KenedoLanguageHelper::getActiveLanguages();
		$dir = KenedoPlatform::p()->getDirCustomization().DS.'language_overrides'.DS;

		foreach($languages as $languageTag => $languageObject) {

			$filename = $dir.$languageTag.DS.'overrides.ini';

			if(!is_file($filename)) {
				continue;
			}

			set_error_handler('ConfigboxModelAdminDashboard::storeErrorMessage');

			$result = parse_ini_file($filename);

			restore_error_handler();

			if ($result === false) {

				$warning = new stdClass();
				$warning->title = sprintf(KText::_('TITLE_LANGUAGE_FILE_ISSUE'), $languageTag);
				$warning->problem = str_replace($filename, 'file', self::getErrorMessage());
				$warning->solution = KText::_('SOLUTION_LANGUAGE_FILE');
				$warning->access = KText::_('Developer, server administrator or hosting provider.');
				$items[] = $warning;

			}

		}

		if (extension_loaded('imagick')) {

			$missingFormats = $this->getMissingImagickFormats();

			if (count($missingFormats)) {
				$warning = new stdClass();
				$warning->title = KText::_('DASHBOARD_ISSUE_MISSING_IMAGICK_FORMATS_TITLE');
				$warning->problem = KText::sprintf('DASHBOARD_ISSUE_MISSING_IMAGICK_FORMATS_PROBLEM', implode(', ',$missingFormats));
				$warning->solution = KText::_('DASHBOARD_ISSUE_MISSING_IMAGICK_FORMATS_SOLUTION');
				$warning->access = KText::_('DASHBOARD_ISSUE_MISSING_IMAGICK_FORMATS_ACCESS');
				$items[] = $warning;
			}

		}

		return $items;

	}

	protected function getMissingImagickFormats() {

		$loaded = extension_loaded('imagick');

		if (!$loaded) {
			return false;
		}

		$supportedFormats = Imagick::queryFormats();

		$requiredFormats = ['JPEG', 'PJPEG', 'PNG', 'GIF', 'BMP'];

		$missingFormats = [];
		foreach($requiredFormats as $format) {
			if (!in_array($format, $supportedFormats)) {
				$missingFormats[] = $format;
			}
		}

		return $missingFormats;
	}

	static function storeErrorMessage($errorNo, $errorStr, $errorFile, $errorLine) {
		self::$error = $errorStr;
	}

	protected function getErrorMessage() {
		$error = self::$error;
		self::$error = '';
		return $error;
	}

	function getIssues() {

		$warnings = array();

		if (in_array('tls', stream_get_transports()) == false) {
			$warning = new stdClass();
			$warning->title = KText::_('WARNING_TLS_TITLE');
			$warning->problem = KText::_('WARNING_TLS_PROBLEM');
			$warning->solution = KText::_('WARNING_TLS_SOLUTION');
			$warning->access = KText::_('WARNING_TLS_ACCESS');
			$warnings[] = $warning;
		}

		$memoryLimit = $this->getInBytes(ini_get('memory_limit'));

		if ($memoryLimit > 0 && $memoryLimit < 134217728) {
			$warning = new stdClass();
			$warning->title = KText::_('PHP memory limit too low.');
			$warning->problem = KText::sprintf('Your PHP memory limit is set to %s MB, which can be too low for some processes like PDF generation.', $this->getInMb($memoryLimit));
			$warning->solution = KText::_('Set memory_limit in your server php.ini or .htaccess file to at least 128MB.');
			$warning->access = KText::_('Developer with access to php.ini or .htaccess, server administrator or hosting provider.');
			$warnings[] = $warning;
		}

		if (ini_get('display_errors')) {
			$warning = new stdClass();
			$warning->title = KText::_('PHP setting display_errors is activated.');
			$warning->problem = KText::_('PHP is set to display messages like notices, warnings and errors on the screen. In rare cases ConfigBox can generate PHP warnings or notices that can deface the website and would also expose file paths of your system.');
			$warning->solution = KText::_('At the Joomla configuration - tab server - turn Error Reporting to None.');
			$warning->access = KText::_('Anybody with administrator privileges in the Joomla backend.');
			$warnings[] = $warning;
		}

		$confTemplate = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'confirmation'.DS.'default.php';

		if (is_file($confTemplate)) {
			$content = file_get_contents($confTemplate);

			if (strstr($content, '(\'#tcconfirmed\').attr(\'checked\')')) {
				$warning = new stdClass();
				$warning->title = KText::_('You need to update your custom order confirmation page template.');
				$warning->problem = KText::_('An API change in jQuery requires a change in a JavaScript function located in the order confirmation template.');
				$warning->solution = KText::sprintf('Open the file %s, search for the text var tcConfirmed and rpConfirmed and change attr into prop on these lines.',$confTemplate);
				$warning->access = KText::_('Developer');
				$warnings[] = $warning;
			}
		}

		return $warnings;

	}

	function shopCountryIsMissing() {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `country_id` FROM `#__configbox_shopdata` WHERE `id` = 1";
		$db->setQuery($query);
		$id = $db->loadResult();
		return ($id == NULL);
	}

	function getFolderStructureFailInfo() {

		$query = "SELECT `value` FROM `#__configbox_system_vars` WHERE `key` = 'folder_movings'";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$text = $db->loadResult();

		if ($text) {
			return $text;
		}
		else {
			return false;
		}

	}

	function userCanCreateConstraints() {

		$db = KenedoPlatform::getDb();
		$query = "SHOW GRANTS FOR CURRENT_USER()";
		$db->setQuery($query);
		$grants = $db->loadResultList();

		$hasRight = false;

		// This does not check if the grant is for the right table, host or anything - but we feel it's gonna be good enough
		foreach ($grants as $grant) {
			if (stristr($grant, 'REFERENCES') != '' || stristr($grant, 'ALL PRIVILEGES') != '') {
				$hasRight = true;
			}
		}

		return $hasRight;

	}

	function oldIonCubeVersionDetected() {

		$minVersion = '10.2';

		if (function_exists('ioncube_loader_version')) {
			$version = ioncube_loader_version();
			if (version_compare($version, $minVersion) <= -1) {
				return true;
			}
		}

		return false;

	}

	function upgradeFailureDetected() {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `value` FROM `#__configbox_system_vars` WHERE `key` = 'failed_update_detected'";
		$db->setQuery($query);
		$value = $db->loadResult();
		if ($value == 1) {
			return true;
		}
		else {
			return false;
		}
	}

	function allDataItemsWritable() {

		$allWritable = true;

		$dirs = array(
			KenedoPlatform::p()->getDirDataCustomer(),
			KenedoPlatform::p()->getDirDataStore(),
			KenedoPlatform::p()->getDirCache(),
		);

		$ignoredSubDirs = array('.svn','.git');

		foreach ($dirs as $dir) {

			$subDirs = KenedoFileHelper::getFolders($dir, '', true, true, $ignoredSubDirs);

			foreach ($subDirs as $item) {
				$writable = is_writable($item);
				if ($writable == false)	{
					$allWritable = false;
				}
			}

			$files = KenedoFileHelper::getFiles($dir, '', true, true, $ignoredSubDirs);
			foreach ($files as $item) {
				$writable = is_writable($item);
				if ($writable == false)	{
					$allWritable = false;
				}
			}

		}

		return $allWritable;

	}

	function parseLanguageOverrides() {

		$result = true;

		if ($result) {
			return true;
		}
		else {
			return false;
		}
	}

	protected function getInMb($bytes, $precision = 0) {
		return round( $bytes / 1048576 ,$precision);
	}

	protected function getInBytes($text) {

		if (stripos($text,'m')) {
			$text = intval($text) * 1048576;
		}
		elseif (stripos($text,'k')) {
			$text = intval($text) * 1024;
		}
		elseif (stripos($text,'g')) {
			$text = intval($text) * 1048576 * 1024;
		}

		return $text;

	}

	/**
	 * Tells if there's a license key entered in the CB settings
	 * @return bool
	 * @throws Exception
	 */
	public function isLicenseKeyEntered() {
		$licenseKey = CbSettings::getInstance()->get('product_key');
		return !empty($licenseKey);
	}


	/**
	 * Tells if license exired. Caches check result for 10 minutes.
	 * @return bool true if license has expired
	 * @throws Exception
	 */
	public function isLicenseExpired()
    {

    	$lastCheckDate = ConfigboxSystemVars::getVar('last_license_check_date');
    	$lastCheckResult = ConfigboxSystemVars::getVar('last_license_check_result');
		$lastCheckKey = ConfigboxSystemVars::getVar('last_license_check_key');

    	$timeNow = KenedoTimeHelper::getNormalizedTime('NOW', 'datetime');
		$licenseKey = CbSettings::getInstance()->get('product_key');

		if (!$licenseKey) {
			throw new Exception('No license key found in CB settings');
		}

    	if ($lastCheckDate != null) {
    		$last = new DateTime($lastCheckDate);
			$now = new DateTime('now');
			$diff = $now->diff($last, true);
			if ($diff->i < 10 && $lastCheckKey == $licenseKey) {
				return boolval($lastCheckResult);
			}
		}


		$serverList = explode(',', CbSettings::getInstance()->get('license_manager_satellites'));
		shuffle($serverList);

		$url = 'https://'.trim($serverList[0]).'/v4/getLicenseData.php?license_key='.$licenseKey;
		$timeout = ini_get('default_socket_timeout');
		ini_set('default_socket_timeout', 5);
		$json = file_get_contents($url);
		ini_set('default_socket_timeout', $timeout);
		$info = json_decode($json, true);

		if ($info['data']['days_left'] === NULL || $info['data']['days_left'] >= 0) {
			$result = false;
		}
		else {
			$result = true;
		}

		ConfigboxSystemVars::setVar('last_license_check_key', $licenseKey);
		ConfigboxSystemVars::setVar('last_license_check_date', $timeNow);
		ConfigboxSystemVars::setVar('last_license_check_result', intval($result));

		return $result;

    }

}