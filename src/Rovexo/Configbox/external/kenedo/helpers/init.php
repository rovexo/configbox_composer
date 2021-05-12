<?php
function initKenedo($component = 'com_configbox') {

	// Make sure we init only once per runtime
	if (defined('KENEDO_INIT_DONE')) {
		return;
	}
	else {
		define('KENEDO_INIT_DONE', true);
	}

	// This is our direct file access prevention
	if (!defined('CB_VALID_ENTRY')) {
		define('CB_VALID_ENTRY', true);
	}

	// Define shorthand constant for DIRECTORY_SEPARATOR
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

	$hasOpCache = extension_loaded('opcache') && ini_get('opcache.enabled');
	$hasApcu = extension_loaded('apcu') && ini_get('apc.enabled');

	if ($hasOpCache && !$hasApcu) {
		ini_set('opcache.revalidate_freq', 0);
	}

	// Load the autoload class file
	require_once (__DIR__.'/../classes/KenedoAutoload.php');

	// Register the autoload method
	spl_autoload_register('KenedoAutoload::loadClass');

	// Somehow this makes it play nicely with other software using autoload
	if (function_exists('__autoload') && in_array('__autoload', spl_autoload_functions())) {
		spl_autoload_register('__autoload');
	}

	// Register Kenedo classes and helpers
	KenedoAutoload::registerClass( 'InterfaceKenedoPlatform', __DIR__.'/../interfaces/KenedoPlatform.php' );
	KenedoAutoload::registerClass( 'KenedoProfiler', 		__DIR__.'/../classes/KenedoProfiler.php' );
	KenedoAutoload::registerClass( 'KenedoPlatform', 		__DIR__.'/../classes/KenedoPlatform.php' );
	KenedoAutoload::registerClass( 'KenedoController', 		__DIR__.'/../classes/KenedoController.php' );
	KenedoAutoload::registerClass( 'KenedoObserver', 		__DIR__.'/../classes/KenedoObserver.php' );
	KenedoAutoload::registerClass( 'KenedoModelLight',		__DIR__.'/../classes/KenedoModelLight.php' );
	KenedoAutoload::registerClass( 'KenedoModel', 			__DIR__.'/../classes/KenedoModel.php' );
	KenedoAutoload::registerClass( 'KenedoView', 			__DIR__.'/../classes/KenedoView.php' );
	KenedoAutoload::registerClass( 'KenedoHtml', 			__DIR__.'/../classes/KenedoHtml.php' );
	KenedoAutoload::registerClass( 'KenedoProperty',		__DIR__.'/../classes/KenedoProperty.php' );
	KenedoAutoload::registerClass( 'KenedoDatabase', 		__DIR__.'/../classes/KenedoDatabase.php' );
	KenedoAutoload::registerClass( 'KLog', 					__DIR__.'/../classes/KLog.php' );
	KenedoAutoload::registerClass( 'KLink', 				__DIR__.'/../classes/KLink.php' );
	KenedoAutoload::registerClass( 'KRequest', 				__DIR__.'/../classes/KRequest.php' );
	KenedoAutoload::registerClass( 'KSession', 				__DIR__.'/../classes/KSession.php' );
	KenedoAutoload::registerClass( 'KStorage', 				__DIR__.'/../classes/KStorage.php' );
	KenedoAutoload::registerClass( 'KText', 				__DIR__.'/../classes/KText.php' );
	KenedoAutoload::registerClass( 'KenedoObject', 			__DIR__.'/../classes/KenedoObject.php' );
	KenedoAutoload::registerClass( 'KenedoLanguageHelper', 	__DIR__.'/../helpers/language.php' );
	KenedoAutoload::registerClass( 'KenedoRouterHelper', 	__DIR__.'/../helpers/router.php' );
	KenedoAutoload::registerClass( 'KenedoViewHelper', 		__DIR__.'/../helpers/view.php' );
	KenedoAutoload::registerClass( 'KenedoTimeHelper', 		__DIR__.'/../helpers/time.php' );
	KenedoAutoload::registerClass( 'KenedoFileHelper', 		__DIR__.'/../helpers/file.php' );

	// Try to overcome the class name change on updates, class name reference is in cache files
	// We used to have KObject, but another popular extension uses that name, renaming it and changing references
	// only got us so far. KObject was used in CB cache files so we had to keep it at least as class alias.
	// REMOVE IN CB 4.0
	if (class_exists('KObject') == false) {
		class_alias('KenedoObject', 'KObject');
	}

	// Legacy class name (Remove in CB 4.0)
	class_alias('KLog', 'ConfigboxDebugger');

	// Run any platform specific init stuff
	KenedoPlatform::p()->initialize();

	// Define paths
	/**
	 * URL scheme (without colons or backslashes)
	 * E.g. https
	 * @const  KPATH_ROOT
	 */
	define('KPATH_SCHEME', 	KenedoPlatform::p()->requestUsesHttps() ? 'https' : 'http');

	/**
	 * HTTP Hostname
	 * E.g. configbox.dev
	 * @const  KPATH_HOST
	 */
	define('KPATH_HOST', 	(substr(PHP_SAPI, 0, 3) == 'cli') ? '' : $_SERVER['HTTP_HOST']);

	/**
	 * Platform base URL (scheme://host/path) - without a trailing slash
	 * @const  KPATH_URL_BASE
	 */
	define('KPATH_URL_BASE', KenedoPlatform::p()->getUrlBase());

	/**
	 * Full path to the application's root directory (not the web server's root)
	 * @const  KPATH_ROOT
	 */
	define('KPATH_ROOT', KenedoPlatform::p()->getRootDirectory());

	// Let the platform start the session
	KenedoPlatform::p()->startSession();

	// KenedoView template paths
	define('KPATH_TABLE_TMPL', 	 __DIR__.'/../tmpl/default-table.php');
	define('KPATH_LISTING_TMPL', __DIR__.'/../tmpl/default-listing.php');
	define('KPATH_DETAILS_TMPL', __DIR__.'/../tmpl/default-editform.php');

	// Set error handlers to log all errors. Shutdown function is there for the same reason
	KenedoPlatform::p()->setErrorHandler(array('KLog', 'handleError'));
	KenedoPlatform::p()->registerShutdownFunction(array('KLog', 'handleShutdown'));

	// Do application autoloads (does some initialization too now)
	$componentAutoLoadFile = KenedoPlatform::p()->getComponentDir($component).'/helpers/init.php';

	if (is_file($componentAutoLoadFile)) {
		require_once($componentAutoLoadFile);
	}

}