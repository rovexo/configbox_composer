<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'weight')) {
	$query = "
	ALTER TABLE `#__configbox_options`
	    MODIFY `weight` decimal(20, 4) default 0.0000 not null,
	    MODIFY `option_image` varchar(200) default ''  not null after `availibility_date`
	";
	$db->setQuery($query);
	$db->query();
}
