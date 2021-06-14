<?php
defined('CB_VALID_ENTRY') or die();

// Moving data dirs, cache, logs and tmp to WP uploads dir
if (KenedoPlatform::getName() == 'wordpress') {

	$moveInfos = array(
		array(
			'old' => KenedoPlatform::p()->getComponentDir('com_configbox').'/data/customer',
			'new' => KenedoPlatform::p()->getDirDataCustomer()
		),
		array(
			'old' => KenedoPlatform::p()->getComponentDir('com_configbox').'/data/store',
			'new' => KenedoPlatform::p()->getDirDataStore()
		),
		array(
			'old' => KenedoPlatform::p()->getRootDirectory().'/cache',
			'new' => KenedoPlatform::p()->getDirCache()
		),
		array(
			'old' => KenedoPlatform::p()->getRootDirectory().'/tmp',
			'new' => KenedoPlatform::p()->getTmpPath()
		),
		array(
			'old' => KenedoPlatform::p()->getRootDirectory().'/logs',
			'new' => KenedoPlatform::p()->getLogPath()
		),
	);

	foreach ($moveInfos as $moveInfo) {
		$oldDir = $moveInfo['old'];
		$newDir = $moveInfo['new'];

		if (!is_dir($newDir)) {
			if (is_dir($oldDir)) {
				rename($oldDir, $newDir);
			}
			else {
				mkdir($newDir,0777, true);
			}
		}

	}

}