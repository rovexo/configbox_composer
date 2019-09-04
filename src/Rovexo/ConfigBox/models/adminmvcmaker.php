<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminmvcmaker extends KenedoModel {

	/**
	 * @param string $componentName for example com_configbox
	 * @param string $nameSingular lowercase name for the form view
	 * @param string $namePlural lowercase name for the controller, model and list view
	 */
	function createControllerFile($componentName, $nameSingular, $namePlural) {

		$componentTitle = ucfirst(strtolower(substr($componentName, 4)));
		$nameSingular = ucfirst(strtolower($nameSingular));
		$namePlural = ucfirst(strtolower($namePlural));
		$controllerFilename = strtolower(strtolower($namePlural));

		$ctrlTmplPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/controller.txt';
		$ctrlPath =  KenedoPlatform::p()->getComponentDir($componentName) . "/data/customization/controllers/$controllerFilename.php";

		$ctrlFile = fopen($ctrlPath, 'w');

		fwrite($ctrlFile, str_replace(
			array('{componentTitle}', '{nameSingular}', '{namePlural}'),
			array($componentTitle, $nameSingular, $namePlural),
			file_get_contents($ctrlTmplPath)
		));

		fclose($ctrlFile);

		KLog::log("Controller $controllerFilename was created successfully.", 'custom_code_creator');

	}

	/**
	 * @param string $componentName for example com_configbox
	 * @param string $namePlural lowercase name for the controller, model and list view
	 * @param string $tableName database table name
 	 */
	function createModelFile($componentName, $namePlural, $tableName) {

		$componentTitle = ucfirst(strtolower(substr($componentName, 4)));
		$namePlural = ucfirst(strtolower($namePlural));
		$modelFilename = strtolower($namePlural);
		$tableName = strtolower($tableName);

		$modelTmplPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/model.txt';
     	$modelPath = KenedoPlatform::p()->getComponentDir($componentName) . "/data/customization/models/$modelFilename.php";

     	$modelFile = fopen($modelPath, 'w');

		fwrite($modelFile, str_replace(
			array('{componentTitle}', '{namePlural}', '{tableName}'),
			array($componentTitle, $namePlural, $tableName),
			file_get_contents($modelTmplPath)
		));

		fclose($modelFile);

		KLog::log("Model $modelFilename was created successfully.", 'custom_code_creator');
	}

	/**
	 * @param string $componentName for example com_configbox
	 * @param string $nameSingular lowercase name for the form view
	 * @param string $namePlural lowercase name for the controller, model and list view
	 * @param string $type form or list
	 */
	function createViewFiles($componentName, $nameSingular, $namePlural, $type) {

		$controllerName = strtolower($namePlural);
		$componentTitle = ucfirst(strtolower(substr($componentName, 4)));
		$nameSingular = ucfirst(strtolower($nameSingular));
		$namePlural = ucfirst(strtolower($namePlural));

		if ($type == 'form') {
			$viewFilename = strtolower($nameSingular);
			$viewTmplPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/view_form_tmpl.txt';
			$viewHtmlPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/view_form_class.txt';
		} else {
			$viewFilename = strtolower($namePlural);
			$viewTmplPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/view_list_tmpl.txt';
			$viewHtmlPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/view_list_class.txt';
		}

		$viewMetadataPath = KenedoPlatform::p()->getComponentDir('com_configbox') . '/helpers/mvc_creator/view_metadata.txt';

		$viewTmplDir = KenedoPlatform::p()->getComponentDir($componentName) . "/data/customization/views/$viewFilename";

		mkdir($viewTmplDir);

		$viewTemplateDir = $viewTmplDir. '/tmpl';
		mkdir($viewTemplateDir);

		$viewPath = "$viewTemplateDir/default.php";
		$viewPath = fopen($viewPath, 'w');

		$viewMetadata = "$viewTmplDir/metadata.xml";
		$viewMetadata = fopen($viewMetadata, 'w');

		$viewHtml = "$viewTmplDir/view.html.php";
		$viewHtml = fopen($viewHtml, 'w');

		fwrite($viewPath, file_get_contents($viewTmplPath));
		fwrite($viewMetadata, file_get_contents($viewMetadataPath));
		fwrite($viewHtml, str_replace(
			array('{componentName}', '{componentTitle}','{controllerName}', '{namePlural}', '{nameSingular}'),
			array($componentName, $componentTitle, $controllerName, $namePlural, $nameSingular),
			file_get_contents($viewHtmlPath)
		));

		fclose($viewPath);
		fclose($viewMetadata);
		fclose($viewHtml);

		KLog::log("View $viewFilename was created successfully.", 'custom_code_creator');

	}

}


