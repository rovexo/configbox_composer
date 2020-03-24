<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmintemplates extends KenedoModel {

	function getListingTitle() {
		return KText::_('Templates');
	}

	function getDetailsTitle() {
		return KText::_('Template');
	}

	function getListingTasks() {
		$tasks = array(
				array('title'=>KText::_('Add'),
				      'task'=>'add',
				      'primary'=>true,
					),
		);
		return $tasks;
	}
	
	function getDetailsTasks() {
	
		if (KRequest::getInt('is_original')) {
			$tasks = array(
					array('title'=>KText::_('Close'), 	'task'=>'cancel'),
			);
		}
		else {
			$tasks = array(
					array('title'=>KText::_('Save and close'), 	'task'=>'store'),
					array('title'=>KText::_('Save'), 			'task'=>'apply'),
					array('title'=>KText::_('Cancel'), 			'task'=>'cancel'),
			);
		}
	
		return $tasks;
	}
	
	function initData() {
		$template = new stdClass();
		$template->name = '';
		$template->type = '';
		$template->path = '';
		$template->content = '';
		return $template;
	}

	/**
	 * @param string $id template_type.template_name
	 * @param string $languageTag Just a dummy to keep function signatures right
	 * @return object
	 * @throws Exception
	 */
	function getRecord($id, $languageTag = '') {

		$templatesModel = KenedoModel::getModel('ConfigboxModelAdmintemplates');

		$exp = explode('.', $id);
		$templateType = $exp[0];
		$templateName = $exp[1];

		// Sanitize the name
		$templateName = str_replace(' ', '', $templateName);
		$templateName = str_replace('.', '', $templateName);
		$templateName = str_replace(DS, '', $templateName);

		if (KRequest::getInt('is_original') == '1') {

			$templates = $templatesModel->getOriginalTemplates();
			$path = $templates[$templateType];

			$template = new stdClass();
			$template->content = file_get_contents($path);
			$template->writable = is_writable($path);
			$template->name = 'default';
			$template->type = $templateType;
			$template->path = $path;

			return $template;

		}
		else {

			$templates = $templatesModel->getCustomTemplates();

			if (strpos($templateType, 'template_element') === 0) {
				$path = $templates['template_element'][$templateName][$templateType];
			}
			else {
				$path = $templates[$templateType][$templateName];
			}

			$template = new stdClass();
			$template->content = file_get_contents($path);
			$template->writable = is_writable($path);
			$template->name = $templateName;
			$template->type = $templateType;
			$template->path = $path;

			return $template;

		}

	}

	function getDataFromRequest() {

		$data = new stdClass();

		$data->templateName = KRequest::getString('templateName');
		$data->templateType = KRequest::getString('templateType');

		// Sanitize template name
		$data->templateName = str_replace(' ', '', $data->templateName);
		$data->templateName = str_replace('.', '', $data->templateName);
		$data->templateName = str_replace(DS, '', $data->templateName);

		// Sanitize template type
		$data->templateType = str_replace(' ', '', $data->templateType);
		$data->templateType = str_replace('.', '', $data->templateType);
		$data->templateType = str_replace(DS, '', $data->templateType);

		// Put together the ID
		$data->id = $data->templateType.'.'.$data->templateName;

		$data->content = KRequest::getVar('content', '');

		return $data;

	}

	function validateData($data, $context = '') {

		$file = $this->getFilename($data->templateType, $data->templateName);

		$valid = ($file && $data->templateName && $data->templateType);

		if ($valid === false) {
			$this->setError(KText::_('Please make sure you filled all required fields.'));
			return false;
		}

		return true;

	}

	function isInsert($data) {
		$file = $this->getFilename($data->templateType, $data->templateName);
		return (!is_file($file));
	}

	function store($data) {

		$file = $this->getFilename($data->templateType, $data->templateName);
	
		$success = KenedoFileHelper::writeFile($file, $data->content);
	
		if ($success === false) {
			$this->setError(KText::sprintf('Could not write file to %s. Please check write permissions on file and folder.', $file));
			return false;
		}
		else {
			return true;
		}
	
	}
	
	function delete($id = NULL) {

		$exp = explode('.', $id);
		$templateType = $exp[0];
		$templateName = $exp[1];

		$templateName = str_replace(' ', '', $templateName);
		$templateName = str_replace('.', '', $templateName);
		$templateName = str_replace(DS, '', $templateName);
	
		$file = $this->getFilename($templateType, $templateName);
	
		$valid = ($file && $templateName && $templateType);
	
		if ($valid === false) {
			$this->setError(KText::_('File not found.'));
			return false;
		}
	
		$success = unlink($file);
	
		$baseFolder = KenedoPlatform::p()->getDirCustomization() .DS. 'templates';
	
		if (is_dir($baseFolder . DS . 'element' .DS. $templateName)) {
				
			$files = KenedoFileHelper::getFiles($baseFolder . DS . 'element' .DS. $templateName, "\.php$");

			if (count($files) == 0) {
				KenedoFileHelper::deleteFolder($baseFolder . DS . 'element' .DS. $templateName);
			}
		}
	
		if ($success === false) {
			$this->setError(KText::sprintf('Could not delete file %s. Please check write permissions on file and folder.', $file));
			return false;
		}
		else {
			return true;
		}
	
	}
	
	function getFilename($templateType, $templateName) {
	
		$baseFolder = KenedoPlatform::p()->getDirCustomization() .DS. 'templates';
	
		switch ($templateType) {
	
			case 'template_listing':
				$file = $baseFolder . DS . 'productlisting' .DS. $templateName.'.php';
				break;
	
			case 'template_product':
				$file = $baseFolder . DS . 'product' .DS. $templateName.'.php';
				break;
	
			case 'template_page':
				$file = $baseFolder . DS . 'configuratorpage' .DS. $templateName.'.php';
				break;

			default:
				$file = false;
				break;
		}
	
		return $file;
	}
	
	function getOriginalTemplates() {
		
		$templates['template_listing'] 	= KPATH_DIR_CB.DS.'views'.DS.'productlisting'.DS.'tmpl'.DS.'default.php';
		$templates['template_product'] 	= KPATH_DIR_CB.DS.'views'.DS.'product'.DS.'tmpl'.DS.'default.php';
		$templates['template_page'] 	= KPATH_DIR_CB.DS.'views'.DS.'configuratorpage'.DS.'tmpl'.DS.'default.php';

		return $templates;
	}
	
	function getCustomTemplates() {
		
		$filter = "\.php$";
		
		$elementFolder = KenedoPlatform::p()->getDirCustomization() .DS. 'templates'.DS.'element';
		
		$folders = KenedoFileHelper::getFolders($elementFolder,'',false,false);
		
		$templates = array();
		
		foreach ($folders as $folder) {
			$files = KenedoFileHelper::getFiles($elementFolder.DS.$folder,$filter,false,false);
			foreach ($files as $file) {
				$templates['template_element'][$folder]['template_element_'.preg_replace('#\.[^.]*$#', '', $file)] = $elementFolder.DS.$folder.DS.$file;				
			}
		}
		
		$pageFolder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'configuratorpage';
		$files = KenedoFileHelper::getFiles($pageFolder,$filter,false,false);
		foreach ($files as $file) {
			$templates['template_page'][preg_replace('#\.[^.]*$#', '', $file)] = $pageFolder.DS.$file;
		}
		
		$productFolder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'product';
		$files = KenedoFileHelper::getFiles($productFolder,$filter,false,false);
		foreach ($files as $file) {
			$templates['template_product'][preg_replace('#\.[^.]*$#', '', $file)] = $productFolder.DS.$file;
		}
		
		$productListingFolder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'productlisting';
		$files = KenedoFileHelper::getFiles($productListingFolder,$filter,false,false);
		foreach ($files as $file) {
			$templates['template_listing'][preg_replace('#\.[^.]*$#', '', $file)] = $productListingFolder.DS.$file;
		}

		return $templates;
		
	}
	
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
	
	function getElementTemplates() {
				
		$folder = KenedoPlatform::p()->getDirCustomization() .DS.'templates'.DS.'element';
						
		$folders = KenedoFileHelper::getFolders($folder,'',false,false);
		$fileoption = new stdClass();
		$fileoption->value = 'default';
		$fileoption->title = KText::_('Default');
		
		$fileoptions[] = $fileoption;
		
		foreach ($folders as $folder) {
			
			unset($fileoption);
			$fileoption = new stdClass();
			$fileoption->value = $folder;
			$fileoption->title = ucwords( $folder );
			
			$fileoptions[] = $fileoption;
			
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
