<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmintemplates extends KenedoModel {

	function getConfiguratorPageTemplates() {
		
		$folder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'configuratorpage';
		$files = KenedoFileHelper::getFiles($folder, '.php$', false, false);
		
		$fileoption = new stdClass;
		$fileoption->value = 'default';
		$fileoption->title = KText::_('Default');
		
		$fileoptions[] = $fileoption;
		
		foreach ($files as $file) {
			if (!strstr($file,'_')) {
				$fileoption = new stdClass;
				$fileoption->value = preg_replace('#\.[^.]*$#', '', $file);
				$fileoption->title = ucwords( preg_replace('#\.[^.]*$#', '', $file) );
				
				$fileoptions[] = $fileoption;
			}
		}
		
		return $fileoptions;
		
	}

	
	function getProductTemplates() {
		
		$folder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'product';
		$filter = ".php$";
		
		$files = KenedoFileHelper::getFiles($folder,$filter,false,false);
		
		$fileoption = new stdClass;
		$fileoption->value = 'default';
		$fileoption->title = KText::_('Default');
		
		$fileoptions[] = $fileoption;
		
		foreach ($files as $file) {
		
			$fileoption = new stdClass;
			$fileoption->value = preg_replace('#\.[^.]*$#', '', $file);
			$fileoption->title = ucwords( preg_replace('#\.[^.]*$#', '', $file) );
			
			$fileoptions[] = $fileoption;
		
		}
		
		return $fileoptions;
		
	}
	
	function getProductsTemplates() {
		
		$folder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'productlisting';
		$filter = ".php$";
		
		$files = KenedoFileHelper::getFiles($folder,$filter,false,false);
		
		$fileoption = new stdClass();
		$fileoption->value = 'default';
		$fileoption->title = KText::_('Default');
		
		$fileoptions[] = $fileoption;
		
		foreach ($files as $file) {

			$fileoption = new stdClass();
			$fileoption->value = preg_replace('#\.[^.]*$#', '', $file);
			$fileoption->title = ucwords( preg_replace('#\.[^.]*$#', '', $file) );
			
			$fileoptions[] = $fileoption;
		
		}
		
		return $fileoptions;
	}
	
}
