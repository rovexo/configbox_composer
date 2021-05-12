<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$platformName = KenedoPlatform::getName();

if ($platformName == 'wordpress') {

	$oldDir = KenedoPlatform::p()->getOldDirCustomization();
	$newDir = KenedoPlatform::p()->getDirCustomization();

	if ($oldDir != $newDir) {

		if (is_dir($oldDir) && !is_dir($newDir)) {
			try {
				KenedoFileHelper::copyDir($oldDir, $newDir);
			}
			catch (Exception $e) {
				KLog::log('Could not move customization directory. Exception log follows.', 'error');
				KLog::logException($e);
				ConfigboxSystemVars::setVar('customization_dir_move_failed', '1');
				throw $e;
			}
			KenedoFileHelper::deleteFolder($oldDir);

			$files = KenedoFileHelper::getFiles(dirname($oldDir));
			if (count($files) == 0) {
				KenedoFileHelper::deleteFolder(dirname($oldDir));
			}

		}

	}

}

