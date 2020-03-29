<?php
defined('CB_VALID_ENTRY') or die();

define('KPATH_DIR_CB', KenedoPlatform::p()->getComponentDir('com_configbox') );

KenedoAutoload::registerClass('ConfigboxCacheHelper',		KPATH_DIR_CB.'/helpers/cache.php');
KenedoAutoload::registerClass('ConfigboxConfiguratorHelper',KPATH_DIR_CB.'/helpers/configurator.php');
KenedoAutoload::registerClass('ConfigboxUpdateHelper',		KPATH_DIR_CB.'/helpers/update.php');

if (is_file('/Users/martin/PhpstormProjects/configbox_non_encoded/encoded/helpers/rules.php')) {
	KenedoAutoload::registerClass('ConfigboxRulesHelper',		'/Users/martin/PhpstormProjects/configbox_non_encoded/encoded/helpers/rules.php');
}
else {
	KenedoAutoload::registerClass('ConfigboxRulesHelper',		KPATH_DIR_CB.'/helpers/rules.php');
}

if (is_file('/Users/martin/PhpstormProjects/configbox_non_encoded/encoded/helpers/calculation.php')) {
	KenedoAutoload::registerClass('ConfigboxCalculation',		'/Users/martin/PhpstormProjects/configbox_non_encoded/encoded/helpers/calculation.php');
}
else {
	KenedoAutoload::registerClass('ConfigboxCalculation',		KPATH_DIR_CB.'/helpers/calculation.php');
}

KenedoAutoload::registerClass('ConfigboxProductImageHelper', KPATH_DIR_CB.'/helpers/productimage.php');
KenedoAutoload::registerClass('ConfigboxPrices',			KPATH_DIR_CB.'/helpers/prices.php');
KenedoAutoload::registerClass('ConfigboxPositionHelper',	KPATH_DIR_CB.'/helpers/position.php');
KenedoAutoload::registerClass('ConfigboxLocationHelper',	KPATH_DIR_CB.'/helpers/location.php');
KenedoAutoload::registerClass('ConfigboxQuickeditHelper',	KPATH_DIR_CB.'/helpers/quickedit.php');
KenedoAutoload::registerClass('ConfigboxRatingsHelper',		KPATH_DIR_CB.'/helpers/ratings.php');
KenedoAutoload::registerClass('ConfigboxCacheHelper',		KPATH_DIR_CB.'/helpers/cache.php');
KenedoAutoload::registerClass('ConfigboxUserHelper',		KPATH_DIR_CB.'/helpers/user.php');
KenedoAutoload::registerClass('ConfigboxShapediverHelper',	KPATH_DIR_CB.'/helpers/shapediver.php');
KenedoAutoload::registerClass('ConfigboxDeviceHelper',		KPATH_DIR_CB.'/helpers/device.php');
KenedoAutoload::registerClass('ConfigboxStoreHelper',		KPATH_DIR_CB.'/helpers/store.php');
KenedoAutoload::registerClass('ConfigboxSystemVars',		KPATH_DIR_CB.'/helpers/systemvars.php');
KenedoAutoload::registerClass('ConfigboxDataHelper',		KPATH_DIR_CB.'/helpers/data.php');
KenedoAutoload::registerClass('ConfigboxCurrencyHelper',	KPATH_DIR_CB.'/helpers/currency.php');
KenedoAutoload::registerClass('ConfigboxViewHelper',		KPATH_DIR_CB.'/helpers/view.php');
KenedoAutoload::registerClass('ConfigboxVersionHelper',		KPATH_DIR_CB.'/helpers/version.php');
KenedoAutoload::registerClass('ConfigboxAddonHelper',		KPATH_DIR_CB.'/helpers/addon.php');

KenedoAutoload::registerClass('ConfigboxCountryHelper',	    KPATH_DIR_CB.'/helpers/country.php');
KenedoAutoload::registerClass('ConfigboxPermissionHelper',	KPATH_DIR_CB.'/helpers/permission.php');
KenedoAutoload::registerClass('ConfigboxImageResizer',		KPATH_DIR_CB.'/helpers/imageresizer.php');
KenedoAutoload::registerClass('ConfigboxDomPdfHelper',		KPATH_DIR_CB.'/helpers/dompdf.php');
KenedoAutoload::registerClass('ConfigboxPspHelper',			KPATH_DIR_CB.'/helpers/psp.php');
KenedoAutoload::registerClass('ConfigboxCustomerGroupHelper',	KPATH_DIR_CB.'/helpers/customergroup.php');
KenedoAutoload::registerClass('ConfigboxOverridesHelper', 	KPATH_DIR_CB.'/helpers/overrides.php');

KenedoAutoload::registerClass('ConfigboxCalcTerm',			KPATH_DIR_CB.'/classes/ConfigboxCalcTerm.php');
KenedoAutoload::registerClass('ConfigboxCondition',			KPATH_DIR_CB.'/classes/ConfigboxCondition.php');
KenedoAutoload::registerClass('ConfigboxElement',			KPATH_DIR_CB.'/classes/ConfigboxElement.php');
KenedoAutoload::registerClass('ConfigboxOption',			KPATH_DIR_CB.'/classes/ConfigboxOption.php');
KenedoAutoload::registerClass('ConfigboxConfiguration',		KPATH_DIR_CB.'/classes/ConfigboxConfiguration.php');
KenedoAutoload::registerClass('ConfigboxQuestion',		    KPATH_DIR_CB.'/classes/ConfigboxQuestion.php');
KenedoAutoload::registerClass('ConfigboxAnswer',		    KPATH_DIR_CB.'/classes/ConfigboxAnswer.php');
KenedoAutoload::registerClass('ConfigboxJsonResponse',		KPATH_DIR_CB.'/classes/ConfigboxJsonResponse.php');
KenedoAutoload::registerClass('ConfigboxLocation',		    KPATH_DIR_CB.'/classes/ConfigboxLocation.php');
KenedoAutoload::registerClass('CbSettings',		    		KPATH_DIR_CB.'/classes/CbSettings.php');

if (function_exists('hsc') == false) {
	function hsc($string) {
		return htmlspecialchars($string,ENT_QUOTES);
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

// In case JDump isn't installed create a dummy dump function (dump calls may be left in code, leading to a fatal error on other installations)
if (!function_exists('dump')) {
	function dump() {
		return true;
	}
}

//require_once(__DIR__.'/../external/vendor/autoload.php');

// Legacy class names (remove with CB 4.0)
class_alias('ConfigboxCountryHelper', 'CbcheckoutCountryHelper');
class_alias('ConfigboxPositionHelper', 'CbcheckoutPositionHelper');
class_alias('ConfigboxUserHelper', 'CbcheckoutUserHelper');
class_alias('ConfigboxPermissionHelper', 'ConfigboxOrderHelper');

// SYSTEM DATA (these are supposed to be different on DEV/TEST/LIVE)
define('CONFIGBOX_DIR_CACHE',						KenedoPlatform::p()->getDirCache().'/configbox');
define('CONFIGBOX_DIR_SETTINGS',					KenedoPlatform::p()->getDirCustomizationSettings());

// CUSTOMER DATA
define('CONFIGBOX_DIR_QUOTATIONS',					KenedoPlatform::p()->getDirDataCustomer().'/private/quotations' );
define('CONFIGBOX_DIR_INVOICES',					KenedoPlatform::p()->getDirDataCustomer().'/private/invoices' );
define('CONFIGBOX_DIR_CONFIGURATOR_FILEUPLOADS',	KenedoPlatform::p()->getDirDataCustomer().'/public/file_uploads' );
define('CONFIGBOX_DIR_POSITION_IMAGES',				KenedoPlatform::p()->getDirDataCustomer().'/public/position_images' );

define('CONFIGBOX_URL_CONFIGURATOR_FILEUPLOADS',	KenedoPlatform::p()->getUrlDataCustomer().'/public/file_uploads' );
define('CONFIGBOX_URL_POSITION_IMAGES',				KenedoPlatform::p()->getUrlDataCustomer().'/public/position_images' );


// Files that DO get deployed on a live site

// STORE DATA
define('CONFIGBOX_DIR_PRODUCT_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/product_images');
define('CONFIGBOX_DIR_PRODUCT_GALLERY_IMAGES',		KenedoPlatform::p()->getDirDataStore().'/public/product_gallery_images');
define('CONFIGBOX_DIR_PRODUCT_DETAIL_PANE_ICONS',	KenedoPlatform::p()->getDirDataStore().'/public/product_detail_pane_icons');
define('CONFIGBOX_DIR_VIS_PRODUCT_BASE_IMAGES',		KenedoPlatform::p()->getDirDataStore().'/public/vis_product_images');
define('CONFIGBOX_DIR_VIS_ANSWER_IMAGES', 			KenedoPlatform::p()->getDirDataStore().'/public/vis_answer_images');
define('CONFIGBOX_DIR_DEFAULT_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/default_images');
define('CONFIGBOX_DIR_QUESTION_DECORATIONS',		KenedoPlatform::p()->getDirDataStore().'/public/question_decorations');
define('CONFIGBOX_DIR_ANSWER_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/answer_images');
define('CONFIGBOX_DIR_ANSWER_PICKER_IMAGES',		KenedoPlatform::p()->getDirDataStore().'/public/answer_picker_images');
define('CONFIGBOX_DIR_SHOP_LOGOS',					KenedoPlatform::p()->getDirDataStore().'/public/shoplogos');
define('CONFIGBOX_DIR_MAXMIND_DBS',					KenedoPlatform::p()->getDirDataStore().'/private/maxmind');

define('CONFIGBOX_URL_PRODUCT_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/product_images');
define('CONFIGBOX_URL_PRODUCT_GALLERY_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/product_gallery_images');
define('CONFIGBOX_URL_PRODUCT_DETAIL_PANE_ICONS',	KenedoPlatform::p()->getUrlDataStore().'/public/product_detail_pane_icons');
define('CONFIGBOX_URL_VIS_PRODUCT_BASE_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/vis_product_images');
define('CONFIGBOX_URL_VIS_ANSWER_IMAGES', 			KenedoPlatform::p()->getUrlDataStore().'/public/vis_answer_images');
define('CONFIGBOX_URL_DEFAULT_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/default_images');
define('CONFIGBOX_URL_QUESTION_DECORATIONS',		KenedoPlatform::p()->getUrlDataStore().'/public/question_decorations');
define('CONFIGBOX_URL_ANSWER_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/answer_images');
define('CONFIGBOX_URL_ANSWER_PICKER_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/answer_picker_images');
define('CONFIGBOX_URL_SHOP_LOGOS',					KenedoPlatform::p()->getUrlDataStore().'/public/shoplogos');
define('CONFIGBOX_URL_MAXMIND_DBS',					KenedoPlatform::p()->getUrlDataStore().'/private/maxmind');

// System data
define('CONFIGBOX_DIR_PSPS_DEFAULT',				KenedoPlatform::p()->getComponentDir('com_configbox').'/psp_connectors' );
define('CONFIGBOX_DIR_PROPERTIES_DEFAULT',			KenedoPlatform::p()->getComponentDir('com_configbox').'/external/kenedo/properties' );

// Customization data
define('CONFIGBOX_DIR_PSPS_CUSTOM',					KenedoPlatform::p()->getDirCustomization().'/psp_connectors');
define('CONFIGBOX_DIR_PROPERTIES_CUSTOM',			KenedoPlatform::p()->getDirCustomization().'/properties');
define('CONFIGBOX_DIR_MODEL_PROPERTY_CUSTOMIZATION',KenedoPlatform::p()->getDirCustomization().'/model_property_customization');


KenedoObserver::triggerEvent('onConfigboxInitialized');
