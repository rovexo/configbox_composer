<?php
defined('CB_VALID_ENTRY') or die();

$appDir = KenedoPlatform::p()->getComponentDir('com_configbox');

// Workaround for ioncube/xdebug incompatibility on PHP 8.1-8.4
if (strpos(__DIR__, '/Users/martin/PhpstormProjects') === 0) {
	$dir = '/Users/martin/PhpstormProjects/configbox_non_encoded/encoded';
	KenedoAutoload::registerClass('ConfigboxRulesHelper',		$dir.'/helpers/rules.php');
	KenedoAutoload::registerClass('ConfigboxCalculation',		$dir.'/helpers/calculation.php');
	KenedoAutoload::registerClass('ConfigboxShapediverHelper',	$dir.'/helpers/shapediver.php');
	KenedoAutoload::registerClass('ConfigboxAddonHelper',		$dir.'/helpers/addon.php');
	KenedoAutoload::registerClass('ConfigboxConfiguratorHelper',$dir.'/helpers/configurator.php');
}
else {

	// Pick the encoded files suitable for the found ionCube loader version, fall back to version 15
	if (function_exists('ioncube_loader_version')) {
		$loaderVersion = ioncube_loader_version();
		if (version_compare($loaderVersion, '15', '>=')) {
			$subDir = '15';
		}
		elseif (version_compare($loaderVersion, '14', '>=')) {
			$subDir = '14';
		}
		else {
			$subDir = '13';
		}
	}
	else {
		$subDir = '15';
	}

	KenedoAutoload::registerClass('ConfigboxRulesHelper',		$appDir.'/helpers/encoded/'.$subDir.'/rules.php');
	KenedoAutoload::registerClass('ConfigboxCalculation',		$appDir.'/helpers/encoded/'.$subDir.'/calculation.php');
	KenedoAutoload::registerClass('ConfigboxShapediverHelper',	$appDir.'/helpers/encoded/'.$subDir.'/shapediver.php');
	KenedoAutoload::registerClass('ConfigboxAddonHelper',		$appDir.'/helpers/encoded/'.$subDir.'/addon.php');
	KenedoAutoload::registerClass('ConfigboxConfiguratorHelper',$appDir.'/helpers/encoded/'.$subDir.'/configurator.php');

}

KenedoAutoload::registerClass('ConfigboxCacheHelper',		$appDir.'/helpers/cache.php');
KenedoAutoload::registerClass('ConfigboxUpdateHelper',		$appDir.'/helpers/update.php');
KenedoAutoload::registerClass('ConfigboxProductImageHelper',$appDir.'/helpers/productimage.php');
KenedoAutoload::registerClass('ConfigboxPrices',			$appDir.'/helpers/prices.php');
KenedoAutoload::registerClass('ConfigboxPositionHelper',	$appDir.'/helpers/position.php');
KenedoAutoload::registerClass('ConfigboxLocationHelper',	$appDir.'/helpers/location.php');
KenedoAutoload::registerClass('ConfigboxQuickeditHelper',	$appDir.'/helpers/quickedit.php');
KenedoAutoload::registerClass('ConfigboxRatingsHelper',		$appDir.'/helpers/ratings.php');
KenedoAutoload::registerClass('ConfigboxCacheHelper',		$appDir.'/helpers/cache.php');
KenedoAutoload::registerClass('ConfigboxUserHelper',		$appDir.'/helpers/user.php');
KenedoAutoload::registerClass('ConfigboxDeviceHelper',		$appDir.'/helpers/device.php');
KenedoAutoload::registerClass('ConfigboxStoreHelper',		$appDir.'/helpers/store.php');
KenedoAutoload::registerClass('ConfigboxSystemVars',		$appDir.'/helpers/systemvars.php');
KenedoAutoload::registerClass('ConfigboxDataHelper',		$appDir.'/helpers/data.php');
KenedoAutoload::registerClass('ConfigboxCurrencyHelper',	$appDir.'/helpers/currency.php');
KenedoAutoload::registerClass('ConfigboxViewHelper',		$appDir.'/helpers/view.php');
KenedoAutoload::registerClass('ConfigboxVersionHelper',		$appDir.'/helpers/version.php');
KenedoAutoload::registerClass('ConfigboxWordpressHelper',	$appDir.'/helpers/wordpress.php');

KenedoAutoload::registerClass('ConfigboxCountryHelper',	    $appDir.'/helpers/country.php');
KenedoAutoload::registerClass('ConfigboxPermissionHelper',	$appDir.'/helpers/permission.php');
KenedoAutoload::registerClass('ConfigboxImageResizer',		$appDir.'/helpers/imageresizer.php');
KenedoAutoload::registerClass('ConfigboxDomPdfHelper',		$appDir.'/helpers/dompdf.php');
KenedoAutoload::registerClass('ConfigboxPspHelper',			$appDir.'/helpers/psp.php');
KenedoAutoload::registerClass('ConfigboxCustomerGroupHelper',$appDir.'/helpers/customergroup.php');
KenedoAutoload::registerClass('ConfigboxOverridesHelper', 	$appDir.'/helpers/overrides.php');

KenedoAutoload::registerClass('ConfigboxCalcTerm',			$appDir.'/classes/ConfigboxCalcTerm.php');
KenedoAutoload::registerClass('ConfigboxCondition',			$appDir.'/classes/ConfigboxCondition.php');
KenedoAutoload::registerClass('ConfigboxElement',			$appDir.'/classes/ConfigboxElement.php');
KenedoAutoload::registerClass('ConfigboxOption',			$appDir.'/classes/ConfigboxOption.php');
KenedoAutoload::registerClass('ConfigboxConfiguration',		$appDir.'/classes/ConfigboxConfiguration.php');
KenedoAutoload::registerClass('ConfigboxQuestion',		    $appDir.'/classes/ConfigboxQuestion.php');
KenedoAutoload::registerClass('ConfigboxAnswer',		    $appDir.'/classes/ConfigboxAnswer.php');
KenedoAutoload::registerClass('ConfigboxJsonResponse',		$appDir.'/classes/ConfigboxJsonResponse.php');
KenedoAutoload::registerClass('ConfigboxLocation',		    $appDir.'/classes/ConfigboxLocation.php');
KenedoAutoload::registerClass('CbSettings',		    		$appDir.'/classes/CbSettings.php');

if (function_exists('hsc') == false) {
	function hsc($string) {
		return htmlspecialchars((string)$string,ENT_QUOTES);
	}
}

if (function_exists('cbprice') == false) {
	function cbprice($price, $symbol = true, $emptyOnZero = false, $decimals = 2) {
		return ConfigboxCurrencyHelper::getFormatted($price, $symbol, $emptyOnZero, $decimals);
	}
}

if (function_exists('cbtaxrate') == false) {
	function cbtaxrate($rate, $symbol = true) {
		return ConfigboxCurrencyHelper::getFormattedTaxRate($rate, $symbol);
	}
}

KenedoObserver::triggerEvent('onConfigboxInitialized');
