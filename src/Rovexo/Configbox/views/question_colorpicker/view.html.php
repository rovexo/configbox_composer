<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewQuestion_Colorpicker extends ConfigboxViewQuestion {

	function prepareTemplateVars() {
		KenedoPlatform::p()->addStylesheet(KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.spectrum-1.8.0/spectrum.css');
		parent::prepareTemplateVars();
	}

}