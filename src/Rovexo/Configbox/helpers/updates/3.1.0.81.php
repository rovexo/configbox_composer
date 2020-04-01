<?php
defined('CB_VALID_ENTRY') or die();

$dirs = array(
	KenedoPlatform::p()->getLogPath(),
	KenedoPlatform::p()->getTmpPath(),
);

foreach ($dirs as $dir) {
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
}
