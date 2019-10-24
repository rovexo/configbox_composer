<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'element_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `element_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'element_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `element_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'element_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `element_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'element_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `element_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'rules') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `rules` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'internal_name') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `internal_name` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

// This appends internal element names from element titles, ignored when language table is gone (becomes obsolete in later versions)
if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == true && ConfigboxUpdateHelper::tableExists('#__configbox_languages') == true) {
	$query = "SELECT `id` FROM `#__configbox_elements` WHERE `internal_name` = '' LIMIT 1";
	$db->setQuery($query);
	$hasEmptyInternal = $db->loadResult();
	
	if ($hasEmptyInternal) {
		// Insert element titles in internal name initially
		$tag = KenedoPlatform::p()->getLanguageTag();
		$query = "SELECT `id` FROM `#__configbox_languages` WHERE tag = '".$tag."' LIMIT 1";
		$db->setQuery($query);
		$langId = $db->loadResult();
		
		$query = "
		SELECT `key`, `text`
		FROM `#__configbox_strings`
		WHERE `lang_id` = ".(int)$langId." AND `type` = 4";
		$db->setQuery($query);
		$elementTitles = $db->loadAssocList();
		
		if (is_array($elementTitles)) {
			foreach ($elementTitles as $elementTitle) {
				$query = "UPDATE `#__configbox_elements` SET `internal_name` = '".$db->getEscaped($elementTitle['text'])."' WHERE `internal_name` = '' AND `id` = ".(int) $elementTitle['key']." LIMIT 1";
				$db->setQuery($query);
				$db->query();
			}
		}
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'assignment_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `assignment_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'assignment_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `assignment_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'assignment_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `assignment_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'assignment_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `assignment_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'rules') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `rules` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'option_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD `option_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'option_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD `option_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'option_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD `option_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'option_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD `option_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_assignment_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_assignment_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_assignment_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_assignment_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_assignment_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_assignment_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_assignment_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_assignment_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_option_custom_1') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_option_custom_1` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_option_custom_2') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_option_custom_2` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_option_custom_3') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_option_custom_3` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_option_custom_4') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_option_custom_4` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_internal_element_names') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `use_internal_element_names` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {
	$query = "UPDATE `#__cbcheckout_userfields` SET `validation_browser` = '' WHERE `field_name` = 'email' OR `field_name` = 'billingemail'";
	$db->setQuery($query);
	$db->query();
}

if (KenedoPlatform::getName() == 'joomla') {
	
	if (KenedoPlatform::p()->getVersionShort() == '1.5') {
		
		$query = "SELECT * FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'jquery' LIMIT 1";
		$db->setQuery($query);
		$jQueryPlugin = $db->loadObject();
		
		if ($jQueryPlugin) {
		
			$params = new KStorage($jQueryPlugin->params);
			$version = $params->get('version','1');
		
			if (version_compare($version,'1.7.1','l')) {
				$params->set('version','1.7.1');
				$newString = $params->toString();
				$query = "UPDATE `#__plugins` SET `params` = '".$db->getEscaped($newString)."' WHERE `id` = ".(int)$jQueryPlugin->id;
				$db->setQuery($query);
				$db->query();
					
			}
		
		}
		
	}
	else {
		
		if (ConfigboxUpdateHelper::tableExists('#__extensions')) {
			$query = "SELECT * FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'jquery' LIMIT 1";
			$db->setQuery($query);
			$jQueryPlugin = $db->loadObject();
			
			if ($jQueryPlugin) {
			
				$params = new KStorage($jQueryPlugin->params);
				$version = $params->get('version','1');
			
				if (version_compare($version,'1.7.1','l')) {
					$params->set('version','1.7.1');
					$newString = $params->toString();
					$query = "UPDATE `#__extensions` SET `params` = '".$db->getEscaped($newString)."' WHERE `extension_id` = ".(int)$jQueryPlugin->extension_id;
					$db->setQuery($query);
					$db->query();
				}
			}
		}
		
	}
	
}

