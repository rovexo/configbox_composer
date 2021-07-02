<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__configbox_examples') == false) {
	$db = KenedoPlatform::getDb();
	$query = "
	CREATE TABLE `#__configbox_examples`
	(
		`id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
		`product_id` int(10) unsigned NOT NULL,
		`published`  int(10) unsigned DEFAULT NULL,
		`ordering`   int(11)          NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE = InnoDB DEFAULT CHARSET = utf8;
	";
	$db->setQuery($query);
	$db->query();
}
