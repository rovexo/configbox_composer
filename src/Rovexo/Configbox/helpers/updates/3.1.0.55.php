<?php
defined('CB_VALID_ENTRY') or die();

$logPath = KenedoPlatform::p()->getTmpPath();

if (!is_dir($logPath) && is_writable(dirname($logPath))) {
	mkdir($logPath, 0777, true);
}