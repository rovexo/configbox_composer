<?php
defined('CB_VALID_ENTRY') or die();

// Create the MaxMind GeoIP database folder
$maxMindGeoIpFolder = KenedoPlatform::p()->getDirDataStore().'/private/maxmind/';
if (!is_dir($maxMindGeoIpFolder)) {
	mkdir($maxMindGeoIpFolder, 0777, true);
}

$maxMindGeoIpFolderInfoFile = $maxMindGeoIpFolder.'readme.txt';
if (!is_file($maxMindGeoIpFolderInfoFile) || is_writable($maxMindGeoIpFolderInfoFile)) {
	$text = 'Copy the uncompressed MaxMind binary database files in this folder. Filename for GeoIp City is "GeoLite2-City.mmdb".';
	file_put_contents($maxMindGeoIpFolderInfoFile, $text);
}