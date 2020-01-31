<?php
class ConfigboxViewHelper {

	protected static $gotAdded = false;
	protected static $memoCacheBusterValue = NULL;

	/**
	 * Gives you a string to be used as cache buster value for static assets.
	 * @return string
	 */
	static function getCacheBusterValue() {

		if (self::$memoCacheBusterValue === NULL) {

			// Prepare a value that we later use as GET parameter value for cache busting
			self::$memoCacheBusterValue = KenedoPlatform::p()->getApplicationVersion();

			// If you define a function called 'getReleaseNumber' you can make your own custom files 'cache-safe'
			if (function_exists('getReleaseNumber')) {
				self::$memoCacheBusterValue .= '-'. getReleaseNumber();
			}

		}

		return self::$memoCacheBusterValue;

	}

	/**
	 * This adds an inline script tag to the HTML's body that adds the requireJS loading script tag to the HTML's head.
	 * It puts some configuration in JSON form to the requireJS script tag which will later be available in module
	 * configbox/server through object var config.
	 *
	 * By design that method gets called in the KenedoView's renderView method (Through KenedoView::addAssets).
	 *
	 * Roadmap: We want to make it possible to add requireJS configuration settings and appConfig data through PHP
	 * methods to make more things possible.
	 *
	 * SUPER IMPORTANT: mind that we create our own requireJS context (CB), so use cbrequire (and not require)
	 * everywhere, otherwise you won't get the defined paths etc.
	 */
	static function addAmdLoader() {

		self::$gotAdded = true;

		$js = self::getAmdLoaderJs();

		// Now let the platform put that JS in a new JS tag in the HTML doc's body
		KenedoPlatform::p()->addScriptDeclaration($js, true, true);

	}

	static function amdLoaderWasAdded() {
		return self::$gotAdded;
	}

	/**
	 * Returns the JS code that creates the requireJS script tag
	 * @return string
	 */
	static function getAmdLoaderJs() {

		// Prepare a value that we later use as GET parameter value for cache busting
		$cacheVar = self::getCacheBusterValue();

		$useCacheBuster = (bool) CbSettings::getInstance()->get('use_assets_cache_buster');
		$useMinifiedCss = (bool) CbSettings::getInstance()->get('use_minified_css');
		$useMinifiedJs  = (bool) CbSettings::getInstance()->get('use_minified_js');

		$queryStringPart = ($useCacheBuster) ? '?version=' . $cacheVar : '';

		// Prepare the URLs to requirejs and our main.js file
		if ($useMinifiedJs) {
			$urlRequireJs = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/require-2.3.2/require.min.js';
			$urlMainJs = KenedoPlatform::p()->getUrlAssets().'/main.min.js'.$queryStringPart;
		}
		else {
			$urlRequireJs = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/require-2.3.2/require.js';
			$urlMainJs = KenedoPlatform::p()->getUrlAssets().'/main.js'.$queryStringPart;
		}

		$requireCustomJs = file_exists(KenedoPlatform::p()->getDirCustomizationAssets().'/javascript/custom.js');
		$requireCustomQuestionJs = file_exists(KenedoPlatform::p()->getDirCustomizationAssets().'/javascript/custom_questions.js');

		// We put a whole lot of settings as JSON string in a data attribute of the requireJS script tag
		// It will be read and stored in the main.js file and ready to use via the configbox JS module (configbox.config)
		$appConfig = array(
			'platformName'      => KenedoPlatform::getName(),
			'urlSystemAssets'   => KenedoPlatform::p()->getUrlAssets(),
			'urlCustomAssets'   => KenedoPlatform::p()->getUrlCustomizationAssets(),
			'urlBase'           => KenedoPlatform::p()->getUrlBase(),
			'urlTinyMceBase'    => KenedoPlatform::p()->getUrlAssets().'/kenedo/external/tinymce/',
			'languageCode'      => KText::getLanguageCode(),
			'languageTag'      	=> KText::getLanguageTag(),
			'decimalSymbol'     => KText::_('DECIMAL_MARK', '.'),
			'thousandsSeparator'=> KText::_('DIGIT_GROUPING_SYMBOL', ','),
			'cacheVar'			=> $cacheVar,
			'urlXhr'            => KLink::getRoute('index.php?option=com_configbox&format=raw', false),
			'useMinifiedJs'		=> $useMinifiedJs,
			'useMinifiedCss'	=> $useMinifiedCss,
			'useAssetsCacheBuster' 		=> $useCacheBuster,
			'requireCustomJs'			=> $requireCustomJs,
			'requireCustomQuestionJs'	=> $requireCustomQuestionJs,
		);

		// You can create a function called 'cbGetCustomRequirePaths' to add paths to the requireJS configuration
		if (function_exists('cbGetCustomRequirePaths')) {
			$appConfig['customPaths'] = cbGetCustomRequirePaths();
		}

		// You can create a function called 'cbGetCustomRequireShims' to add shims to the requireJS configuration
		if (function_exists('cbGetCustomRequireShims')) {
			$appConfig['customShims'] = cbGetCustomRequireShims();
		}

		// Prepare JS that will add our requireJS script tag to the HTML docs head (including our settings JS)
		$js = "
		(function (urlRequireJs, urlMainJs, appConfig) {
			
			// Let any other JS load first to avoid non-AMD-style loaded JS that uses define (avoiding anon mod mismatches)
			window.addEventListener('load', function() {
							
				// In case requireJS is already loaded by another, then just add our own main.js file
				if (typeof(require) !== 'undefined') {
					var mainScript = document.createElement('script');
					mainScript.id = 'cb-main-file-tag';
					mainScript.async = 1;
					mainScript.src = urlMainJs;
					mainScript.dataset.appConfig = appConfig;
					document.getElementsByTagName('head')[0].appendChild(mainScript);
				}
				else {
					var requireJsScript = document.createElement('script');
					requireJsScript.id = 'cb-require-tag';
					requireJsScript.async = 1;
					requireJsScript.src = urlRequireJs;
					requireJsScript.dataset.main = urlMainJs;
					requireJsScript.dataset.appConfig = appConfig;
					document.getElementsByTagName('head')[0].appendChild(requireJsScript);
				}
				
			});
							
		})('".$urlRequireJs."', '".$urlMainJs."', '".json_encode($appConfig)."');
		";

		return $js;

	}

	/**
	 * @param string $html
	 */
	static function processRelativeUrls(&$html) {

		// Rewrite paths to images if necessary
		preg_match_all("/src=\"(.*)\"/Ui", $html, $images);
		preg_match_all("/url(.*)/Ui", $html, $backgrounds);

		// Init replacement array
		$replacements = array();

		// Check sources for images
		if (isset($images[1])) {
			foreach ($images[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}
		// Check sources for CSS backgrounds and similar
		if (isset($backgrounds[1])) {
			foreach ($backgrounds[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}

		// Replace relative URLs with absolute ones
		foreach ($replacements as $replacement) {
			$html = str_replace($replacement, KPATH_URL_BASE .'/'. $replacement, $html);
		}

	}

	/**
	 * @deprecated No longer in use (see KenedoView::loadAsssets)
	 */
	static function loadGeneralAssets() {

	}
}