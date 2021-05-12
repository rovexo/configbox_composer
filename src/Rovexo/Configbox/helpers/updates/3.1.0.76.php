<?php
defined('CB_VALID_ENTRY') or die();

$assetsDir = KenedoPlatform::p()->getOldDirCustomizationAssets();
$customDir = KenedoPlatform::p()->getOldDirCustomization();

$customCss = $assetsDir.'/css/custom.css';
$customMinCss = $assetsDir.'/css/custom.min.css';

if (is_file($customCss)) {
	$contents = file_get_contents($customCss);
	$originalText = "/* Write your custom CSS here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customCss);
	}
}

if (is_file($customMinCss)) {
	$contents = file_get_contents($customMinCss);
	$originalText = "/* Write your custom CSS here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customMinCss);
	}
}

$customJs = $assetsDir.'/javascript/custom.js';
$customJsMin = $assetsDir.'/css/custom.min.js';

if (is_file($customJs)) {
	$contents = file_get_contents($customJs);
	$originalText = "/* Write your custom JavaScript here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customJs);
	}
}

if (is_file($customJsMin)) {
	$contents = file_get_contents($customJsMin);
	$originalText = "/* Write your custom JavaScript here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customJsMin);
	}
}

$customQuestionsJs = $assetsDir.'/javascript/custom_questions.js';
$customQuestionsJsMin = $assetsDir.'/css/custom_questions.min.js';

if (is_file($customQuestionsJs)) {
	$contents = file_get_contents($customQuestionsJs);
	$originalText = "/* Write your custom JavaScript here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customQuestionsJs);
	}
}

if (is_file($customQuestionsJsMin)) {
	$contents = file_get_contents($customQuestionsJsMin);
	$originalText = "/* Write your custom JavaScript here */\n\n";
	if (trim($contents) == '' || $contents == $originalText) {
		unlink($customQuestionsJsMin);
	}
}

$templatesDir = $customDir.'/templates';

$dirs = KenedoFileHelper::getFolders($templatesDir, '', false, true);
foreach ($dirs as $dir) {
	$templateFiles = KenedoFileHelper::getFiles($dir, '.', true, true);
	if (count($templateFiles) == 0) {
		KenedoFileHelper::deleteFolder($dir);
	}
}

$languagesDir = $customDir.'/language_overrides';
$languageFiles = KenedoFileHelper::getFiles($languagesDir, '.', true, true);
foreach ($languageFiles as $languageFile) {
	$contents = file_get_contents($languageFile);
	if (trim($contents) == '') {
		unlink($languageFile);
	}
}

$calcFile = $customDir.'/calculation_functions/calculation_functions.php';
if (is_file($calcFile)) {
	$contents = file_get_contents($calcFile);
	if (trim($contents) == '') {
		unlink($calcFile);
	}
}