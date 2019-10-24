<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'show_in_overview') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `show_in_overview` tinyint(1) NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'text_calcmodel') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `text_calcmodel` tinyint(1) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'dependencies') == true) {
	$query = "ALTER TABLE `#__configbox_elements` CHANGE  `dependencies`  `dependencies` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'dependencies') == true) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` CHANGE  `dependencies`  `dependencies` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_emails', 'send_customer') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_emails` ADD  `send_customer` BOOLEAN NOT NULL DEFAULT  '1', ADD  `send_manager` BOOLEAN NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'samedelivery') == true) {
	$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `samedelivery`  `samedelivery` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'samedelivery') == true) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `samedelivery`  `samedelivery` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

