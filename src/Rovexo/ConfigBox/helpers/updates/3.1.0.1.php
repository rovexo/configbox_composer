<?php
defined('CB_VALID_ENTRY') or die();

// Why 3.1.0.1? Chances are there will be plenty of DB changes over an extended period of time the 3.1.0 release.
// So let's make a new file update script file each time we do a feature.

$db = KenedoPlatform::getDb();

// Prev. 3.0.7

// Rename the formula field to 'code' in calculation_codes
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_codes') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_codes', 'formula') == true) {
	$query = "ALTER TABLE  `#__configbox_calculation_codes` CHANGE  `formula`  `code` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$db->setQuery($query);
	$db->query();
}

// Prev. 3.0.8


// Rename the formula field to 'code' in calculation_codes
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculations') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculations', 'product_id') == false) {

	function __asa_el_to_prod($elementId) {

		$db = KenedoPlatform::getDb();

		$query = "
			SELECT p.product_id
			FROM `#__configbox_elements` AS e
			LEFT JOIN `#__configbox_pages` AS p ON p.id = e.page_id
			WHERE e.id = ".intval($elementId);

		$db->setQuery($query);
		return $db->loadResult();

	}

	$query = "ALTER TABLE `#__configbox_calculations` ADD `product_id` INT UNSIGNED NULL DEFAULT '0', ADD INDEX (  `product_id` )";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_calculations`";
	$db->setQuery($query);
	$calcs = $db->loadAssocList();
	foreach ($calcs as $calc) {
		if ($calc['type'] == 'formula') {

			$query = "SELECT * FROM `#__configbox_calculation_formulas` WHERE `id` = " . intval($calc['id']);
			$db->setQuery($query);
			$formula = $db->loadAssoc();

			preg_match('/"elementId":[0-9]+/', $formula['calc'], $matches);

			if (count($matches)) {
				$elementId = str_replace('"elementId":', '', $matches[0]);

				if ($elementId) {

					$productId = __asa_el_to_prod($elementId);

					$query = "UPDATE `#__configbox_calculations` SET `product_id` = " . intval($productId) . " WHERE `id` = " . intval($calc['id']);
					$db->setQuery($query);
					$db->query();
				}


			}

		}

		if ($calc['type'] == 'matrix') {
			$query = "SELECT * FROM `#__configbox_calculation_matrices` WHERE `id` = " . intval($calc['id']);
			$db->setQuery($query);
			$matrix = $db->loadAssoc();

			$elementId = 0;
			if ($matrix['row_type'] == 'element' && !empty($matrix['row_element_id'])) {
				$elementId = $matrix['row_element_id'];
			} elseif ($matrix['column_type'] == 'element' && !empty($matrix['column_element_id'])) {
				$elementId = $matrix['column_element_id'];
			}

			if ($elementId) {
				$productId = __asa_el_to_prod($elementId);

				$query = "UPDATE `#__configbox_calculations` SET `product_id` = " . intval($productId) . " WHERE `id` = " . intval($calc['id']);
				$db->setQuery($query);
				$db->query();
			}

		}

		if ($calc['type'] == 'code') {

			$query = "SELECT * FROM `#__configbox_calculation_codes` WHERE `id` = " . intval($calc['id']);
			$db->setQuery($query);
			$code = $db->loadAssoc();

			$elementId = 0;

			if (!empty($code['element_id_a'])) {
				$elementId = $code['element_id_a'];
			} elseif (!empty($code['element_id_b'])) {
				$elementId = $code['element_id_b'];
			} elseif (!empty($code['element_id_c'])) {
				$elementId = $code['element_id_c'];
			} elseif (!empty($code['element_id_d'])) {
				$elementId = $code['element_id_d'];
			} else {
				preg_match('/ElementAttribute\([0-9]+/x', $code['code'], $matches);

				if (count($matches)) {
					$elementId = str_replace('ElementAttribute(', '', $matches[0]);
				}

			}

			if ($elementId) {
				$productId = __asa_el_to_prod($elementId);

				$query = "UPDATE `#__configbox_calculations` SET `product_id` = " . intval($productId) . " WHERE `id` = " . intval($calc['id']);
				$db->setQuery($query);
				$db->query();
			}

		}
	}

	$query = "SELECT * FROM `#__configbox_calculations` WHERE `product_id` = 0 OR `product_id` IS NULL";
	$db->setQuery($query);
	$calcs = $db->loadAssocList();
	foreach ($calcs as $calc) {
		$calcId = $calc['id'];

		$query = "
		SELECT `element_id` 
		FROM `#__configbox_xref_element_option` 
		WHERE `calcmodel` = " . intval($calcId) . " OR `calcmodel_recurring` = " . intval($calcId) . " OR `calcmodel_weight` = " . intval($calcId) . "
		LIMIT 1";
		$db->setQuery($query);
		$elementId = $db->loadResult();

		if ($elementId) {
			$productId = __asa_el_to_prod($elementId);
			$query = "UPDATE `#__configbox_calculations` SET `product_id` = " . intval($productId) . " WHERE `id` = " . intval($calc['id']);
			$db->setQuery($query);
			$db->query();
			continue;
		}

		$query = "
		SELECT `id` 
		FROM `#__configbox_elements` 
		WHERE `calcmodel_id_min_val` = " . intval($calcId) . " OR `calcmodel_id_max_val` = " . intval($calcId) . " OR `calcmodel` = " . intval($calcId) . " OR `calcmodel_recurring` = " . intval($calcId) . " OR `calcmodel_weight` = " . intval($calcId) . "
		LIMIT 1";
		$db->setQuery($query);
		$elementId = $db->loadResult();

		if ($elementId) {
			$productId = __asa_el_to_prod($elementId);
			$query = "UPDATE `#__configbox_calculations` SET `product_id` = " . intval($productId) . " WHERE `id` = " . intval($calc['id']);
			$db->setQuery($query);
			$db->query();
			continue;
		}

	}

}

// 3.0.9

if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'behavior_on_activation') == false) {

		// Add the behavior_activation column
		$query = "ALTER TABLE `#__configbox_elements` ADD  `behavior_on_activation` ENUM('none', 'select_default', 'select_any') NOT NULL DEFAULT 'none'";
		$db->setQuery($query);
		$db->query();

		// Populate the column for select_default rows
		$query = "UPDATE `#__configbox_elements` SET `behavior_on_activation` = 'select_default' WHERE `autoselect_default` = '1'";
		$db->setQuery($query);
		$db->query();

		// Populate the column for autoselect_any rows
		$query = "UPDATE `#__configbox_elements` SET `behavior_on_activation` = 'select_any' WHERE `autoselect_any` = '1'";
		$db->setQuery($query);
		$db->query();

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'behavior_on_changes') == false) {

		// Add the behavior_on_changes column
		$query = "ALTER TABLE `#__configbox_elements` ADD  `behavior_on_changes` ENUM('silent', 'confirm') NOT NULL DEFAULT 'silent'";
		$db->setQuery($query);
		$db->query();

		// Populate the column for confirm_deselect rows
		$query = "UPDATE `#__configbox_elements` SET `behavior_on_changes` = 'confirm' WHERE `confirm_deselect` = '1'";
		$db->setQuery($query);
		$db->query();

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'question_type') == false) {

		// Add the behavior_on_changes column
		$query = "ALTER TABLE `#__configbox_elements` ADD  `question_type` VARCHAR(64) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$db    = KenedoPlatform::getDb();
		$query = "
		SELECT xref.element_id, COUNT(*) xref_count, GROUP_CONCAT(xref.option_picker_image SEPARATOR '') as pickers
		FROM `#__configbox_xref_element_option` AS xref
		GROUP BY xref.element_id
		";
		$db->setQuery($query);
		$xrefInfo = $db->loadAssocList('element_id');

		$query = "SELECT * FROM `#__configbox_elements`";
		$db->setQuery($query);
		$elements = $db->loadObjectList();

		foreach ($elements as $element) {

			if (!empty($xrefInfo[ $element->id ])) {
				// images, checkbox, radiobuttons, dropdown
				if ($xrefInfo[ $element->id ]['pickers']) {
					$type = 'images';
				} elseif ($element->as_dropdown) {
					$type = 'dropdown';
				} elseif ($xrefInfo[ $element->id ]['xref_count'] == 1) {
					$type = 'checkbox';
				} else {
					$type = 'radiobuttons';
				}
			} else {
				// textbox, textarea, upload, slider, calendar
				if ($element->widget == 'calendar') {
					$type = 'calendar';
				} elseif ($element->widget == 'slider') {
					$type = 'slider';
				} elseif ($element->as_textarea == '1') {
					$type = 'textarea';
				} elseif ($element->widget == 'fileupload') {
					$type = 'upload';
				} elseif ($element->widget == 'choices') {
					$type = 'choices';
				} else {
					$type = 'textbox';
				}

			}

			$query = "UPDATE `#__configbox_elements` SET `question_type` = '" . $db->getEscaped($type) . "' WHERE `id` = " . intval($element->id);
			$db->setQuery($query);
			$db->query();

		}

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'as_dropdown') == true) {
		$query = "ALTER TABLE `#__configbox_elements` DROP `as_dropdown`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'class') == true) {
		$query = "ALTER TABLE `#__configbox_elements` DROP `class`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'prefill_on_init') == false) {
		$query = "ALTER TABLE `#__configbox_elements` ADD `prefill_on_init` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a default_value want prefill
		$query = "SELECT `id` FROM `#__configbox_elements` WHERE `default_value` != ''";
		$db->setQuery($query);
		$ids = $db->loadResultList();
		if ($ids) {
			$query = "UPDATE `#__configbox_elements` SET `prefill_on_init` = '1' WHERE `id` IN (".implode(',', $ids).")";
			$db->setQuery($query);
			$db->query();
		}

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'input_restriction') == false) {
		$query = "ALTER TABLE `#__configbox_elements` ADD `input_restriction` ENUM('plaintext', 'integer', 'decimal') NOT NULL DEFAULT 'plaintext'";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a validation use decimal
		$query = "SELECT `id` FROM `#__configbox_elements` WHERE `minval` != '' OR `maxval` != '' OR `calcmodel_id_min_val` != 0 OR `calcmodel_id_max_val` != 0 OR `calcmodel_id_min_val` IS NOT NULL OR `calcmodel_id_max_val` IS NOT NULL";
		$db->setQuery($query);
		$ids = $db->loadResultList();
		if ($ids) {
			$query = "UPDATE `#__configbox_elements` SET `input_restriction` = 'decimal' WHERE `id` IN (".implode(',', $ids).")";
			$db->setQuery($query);
			$db->query();
		}

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'set_min_value') == false) {

		$query = "ALTER TABLE `#__configbox_elements` ADD `set_min_value` ENUM('none', 'static', 'calculated') NOT NULL DEFAULT 'none'";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a min val have static
		$query = "UPDATE `#__configbox_elements` SET `set_min_value` = 'static' WHERE `minval` != ''";
		$db->setQuery($query);
		$db->query();

		// Normalise the calc model field values
		$query = "UPDATE `#__configbox_elements` SET `calcmodel_id_min_val` = NULL WHERE `calcmodel_id_min_val` = 0";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a calcmodel_id_min_val have calculated
		$query = "UPDATE `#__configbox_elements` SET `set_min_value` = 'calculated' WHERE `calcmodel_id_min_val` IS NOT NULL";
		$db->setQuery($query);
		$db->query();

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'set_max_value') == false) {

		$query = "ALTER TABLE `#__configbox_elements` ADD `set_max_value` ENUM('none', 'static', 'calculated') NOT NULL DEFAULT 'none'";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a max val have static
		$query = "UPDATE `#__configbox_elements` SET `set_max_value` = 'static' WHERE `maxval` != ''";
		$db->setQuery($query);
		$db->query();

		// Normalise the calc model field values
		$query = "UPDATE `#__configbox_elements` SET `calcmodel_id_max_val` = NULL WHERE `calcmodel_id_max_val` = 0";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a calcmodel_id_max_val have calculated
		$query = "UPDATE `#__configbox_elements` SET `set_max_value` = 'calculated' WHERE `calcmodel_id_max_val` IS NOT NULL";
		$db->setQuery($query);
		$db->query();

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'show_unit') == false) {
		$query = "ALTER TABLE `#__configbox_elements` ADD `show_unit` ENUM('1', '0') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		// Update the settings - assume all questions with a calcmodel_id_max_val have calculated
		$query = "SELECT `id` FROM `#__configbox_elements` WHERE `unit` != ''";
		$db->setQuery($query);
		$ids = $db->loadResultList();
		if ($ids) {
			$query = "UPDATE `#__configbox_elements` SET `show_unit` = '1' WHERE `id` IN (".implode(',', $ids).")";
			$db->setQuery($query);
			$db->query();
		}

	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'as_textarea') == true) {
		$query = "ALTER TABLE `#__configbox_elements` DROP `as_textarea`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'widget') == true) {
		$query = "ALTER TABLE `#__configbox_elements` DROP `widget`";
		$db->setQuery($query);
		$db->query();
	}

}

// Create the customization dir if not there already
if (!is_dir(CONFIGBOX_DIR_CUSTOMIZATION)) {
	mkdir(CONFIGBOX_DIR_CUSTOMIZATION, 0755, true);
}

// Rename or create the customization assets dir
$oldAssetsPath = CONFIGBOX_DIR_CUSTOMIZATION.'/style_overrides';
$newAssetsPath = CONFIGBOX_DIR_CUSTOMIZATION.'/assets';

if (is_dir($oldAssetsPath)) {
	$success = rename($oldAssetsPath, $newAssetsPath);
	if ($success == false) {
		KLog::log('Could not rename customization assets folder during upgrade', 'upgrade_errors');
	}
}
else {
	$success = mkdir($newAssetsPath, 0755);
	if ($success == false) {
		KLog::log('Could not create customization assets folder during upgrade', 'upgrade_errors');
	}
}

// Rename or create the custom JS and CSS files
$renamings = array(
	$newAssetsPath.'/css/style_overrides.css'				=> $newAssetsPath.'/css/custom.css',
	$newAssetsPath.'/css/style_overrides.min.css'			=> $newAssetsPath.'/css/custom.min.css',
	$newAssetsPath.'/javascript/extra_functionality.js'		=> $newAssetsPath.'/javascript/custom.js',
	$newAssetsPath.'/javascript/extra_functionality.min.js'	=> $newAssetsPath.'/javascript/custom.min.js',
);

foreach ($renamings as $old=>$new) {

	// If the file is there already, all good
	if (file_exists($new) == true) {
		continue;
	}

	// Create whatever folder the new file location is in if not there yet
	if (!is_dir(dirname($new))) {
		mkdir(dirname($new), 0777, true);
	}

	if (file_exists($old) == true && file_exists($new) == false) {

		$success = rename($old, $new);

		if ($success == false) {

			$query = "REPLACE INTO `#__configbox_system_vars` SET `key` = 'failed_update_detected', `value` = '1'";
			$db->setQuery($query);
			$db->query();

			$msg = 'Tried to move "'.$old.'" to "'.$new.'", but it failed (probably due to file permission issues). Make these moves yourself please:'."\n";
			foreach ($renamings as $oldFile=>$newFile) {
				$msg .= 'Old: '.$oldFile."\n";
				$msg .= 'New: '.$newFile."\n\n";
			}

			KLog::log($msg, 'upgrade_errors');

		}
	}

	// Create the files if there is none yet
	if (file_exists($old) == false && file_exists($new) == false) {

		// leave out minified files (just in case the starting situation was 'regular file there' but no min file
		if (strpos(basename($new), '.min.') !== false) {
			file_put_contents($new, '');
		}

	}

}

/* New files in customization folder: custom_questions.js */
$newFiles = array(
	CONFIGBOX_DIR_CUSTOMIZATION_ASSETS_JAVASCRIPT.'/custom_questions.js',
);

foreach ($newFiles as $newFile) {

	if (!is_dir(dirname($newFile))) {
		mkdir(dirname($newFile), 0777, true);
	}

	if (!is_file($newFile)) {
		file_put_contents($newFile, '');
	}

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_cart_position_configurations') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_cart_position_configurations', 'selection') == false) {

		$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD `selection` VARCHAR( 2000 ) NOT NULL";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_cart_position_configurations` SET `selection` = CASE WHEN `text` != '' THEN `text` ELSE `element_option_xref_id` END";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP `element_option_xref_id`";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP `text`";
		$db->setQuery($query);
		$db->query();

	}

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_bundle_items') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_bundle_items', 'selection') == false) {

		$query = "ALTER TABLE `#__configbox_bundle_items` ADD `selection` VARCHAR( 2000 ) NOT NULL";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_bundle_items` SET `selection` = CASE WHEN `text` != '' THEN `text` ELSE `element_option_xref_id` END";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_bundle_items` DROP `element_option_xref_id`";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_bundle_items` DROP `text`";
		$db->setQuery($query);
		$db->query();

	}

}

// 3.0.10

$fields = ConfigboxUpdateHelper::getColumnNames('#__configbox_products');

if (isset($fields['quantity_element_id'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `quantity_element_id`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['alt_quantity_element_id'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `alt_quantity_element_id`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['quantity_multiplies'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `quantity_multiplies`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['quantity_in_cart'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `quantity_in_cart`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['longdesctemplate'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `longdesctemplate`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['checked_out'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `checked_out`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['checked_out_time'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `checked_out_time`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['is_tangible'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `is_tangible`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['discontinued'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `discontinued`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['desc'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `desc`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['class'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `class`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['prod_download'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `prod_download`";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getColumnNames('#__configbox_elements');

if (isset($fields['checked_out'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `checked_out`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['checked_out_time'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `checked_out_time`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['dependencies'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `dependencies`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['autoselect_any'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `autoselect_any`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['upload_visualize'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `upload_visualize`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['upload_visualization_view'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `upload_visualization_view`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['upload_visualization_stacking'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `upload_visualization_stacking`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['autoselect_default'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `autoselect_default`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['confirm_deselect'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `confirm_deselect`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['class'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `class`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['classparams'])) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `classparams`";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getColumnNames('#__configbox_options');

if (isset($fields['checked_out'])) {
	$query = "ALTER TABLE `#__configbox_options` DROP `checked_out`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['checked_out_time'])) {
	$query = "ALTER TABLE `#__configbox_options` DROP `checked_out_time`";
	$db->setQuery($query);
	$db->query();
}

// Make 0000-00-00 datetimes NULL (some SQL strict mode is particularly picky about those)
if (isset($fields['availibility_date'])) {
	$query = "UPDATE `#__configbox_options` SET `availibility_date` = NULL WHERE `availibility_date` = '0000-00-00'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getColumnNames('#__configbox_xref_element_option');

if (isset($fields['dependencies'])) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` DROP `dependencies`";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getColumnNames('#__configbox_listings');

if (isset($fields['checked_out'])) {
	$query = "ALTER TABLE `#__configbox_listings` DROP `checked_out`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['checked_out_time'])) {
	$query = "ALTER TABLE `#__configbox_listings` DROP `checked_out_time`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_listings')) {

	$query = "ALTER TABLE  `#__configbox_listings` CHANGE  `id`  `id` INT( 8 ) UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_listing_product')) {

	$query = "ALTER TABLE  `#__configbox_xref_listing_product` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_xref_listing_product` CHANGE  `listing_id`  `listing_id` INT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_xref_listing_product` CHANGE  `product_id`  `product_id` INT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_products')) {

	$query = "ALTER TABLE `#__configbox_products` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_pages')) {

	$query = "ALTER TABLE  `#__configbox_pages` CHANGE  `product_id`  `product_id` INT UNSIGNED NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_pages` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_elements')) {

	$query = "ALTER TABLE  `#__configbox_elements` CHANGE  `page_id`  `page_id` INT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_elements` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option')) {

	$query = "ALTER TABLE  `#__configbox_xref_element_option` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_xref_element_option` CHANGE  `element_id`  `element_id` INT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_xref_element_option` CHANGE  `option_id`  `option_id` INT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_options')) {

	$query = "ALTER TABLE  `#__configbox_options` CHANGE  `id`  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_formulas')) {

	$query = "ALTER TABLE  `#__configbox_calculation_formulas` CHANGE  `id`  `id` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

}


// 3.0.11

if (ConfigboxUpdateHelper::tableExists('#__configbox_countries')) {

	$query = "ALTER TABLE  `#__configbox_countries` CHANGE  `id`  `id` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

}

// 3.0.12.1

if (ConfigboxUpdateHelper::tableExists('#__configbox_strings') && ConfigboxUpdateHelper::tableFieldExists('#__configbox_strings', 'lang_id')) {

	$query = "ALTER TABLE `#__configbox_strings` DROP `lang_id`";
	$db->setQuery($query);
	$db->query();

}

// 3.0.12.2

if (ConfigboxUpdateHelper::tableExists('#__configbox_states') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_states', 'fips_number') == false) {

		$query = 'DROP TABLE IF EXISTS `#__configbox_states`';
		$db->setQuery($query);
		$db->query();

		$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_states` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` mediumint(8) unsigned NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `iso_code` varchar(50) NOT NULL DEFAULT '',
  `fips_number` varchar(5) NOT NULL DEFAULT '',
  `custom_1` text NOT NULL,
  `custom_2` text NOT NULL,
  `custom_3` text NOT NULL,
  `custom_4` text NOT NULL,
  `ordering` mediumint(9) NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  KEY `ordering` (`ordering`,`published`),
  KEY `iso_fips` (`iso_code`,`fips_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";

		$db->setQuery($query);
		$db->query();


		$query = "SELECT `id` FROM `#__configbox_states` LIMIT 1";
		$db->setQuery($query);
		$hasStates = $db->loadResult();

		if (!$hasStates) {
			$file = __DIR__ . '/complete/import_states_3.sql';

			if (file_exists($file)) {
				$queries = $db->splitSql(file_get_contents($file));
				foreach ($queries as $query) {
					if (trim($query)) {
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}

	}

}

// States used to have a default value 0 and NULL was not allowed. Changed that to NULL and DEFAULT NULL for doing CONSTRAINTS in the future
if (ConfigboxUpdateHelper::tableExists('#__configbox_states') == true) {
	$query = "ALTER TABLE  `#__configbox_states` CHANGE  `country_id`  `country_id` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();
}


// 3.0.12.3

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shopdata') == true && ConfigboxUpdateHelper::tableExists('#__configbox_shopdata') == false) {

	// Rename the table
	$query = "ALTER TABLE `#__cbcheckout_shopdata` RENAME `#__configbox_shopdata`";
	$db->setQuery($query);
	$db->query();

	// Delete custom invoice template texts (feature is discontinued)
	$query = "DELETE FROM `#__configbox_strings` WHERE `type` = 34";
	$db->setQuery($query);
	$db->query();

	// Get rid of the custom_invoice thing (feature is discontinued)
	$query = "ALTER TABLE `#__configbox_shopdata` DROP `use_custom_invoice`";
	$db->setQuery($query);
	$db->query();

}

// Rename country/zone xref table and put foreign key constraints in place
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_xref_country_zone') == true && ConfigboxUpdateHelper::tableExists('#__configbox_xref_country_zone') == false) {

	// Rename the table
	$query = "ALTER TABLE `#__cbcheckout_xref_country_zone` RENAME `#__configbox_xref_country_zone`";
	$db->setQuery($query);
	$db->query();

	// Remove possible Orphans (country_id)
	$query = "DELETE FROM `#__configbox_xref_country_zone` WHERE `country_id` NOT IN (SELECT `id` FROM `#__configbox_countries`)";
	$db->setQuery($query);
	$db->query();

	// Remove possible Orphans (zone_id)
	$query = "DELETE FROM `#__configbox_xref_country_zone` WHERE `zone_id` NOT IN (SELECT `id` FROM `#__configbox_zones`)";
	$db->setQuery($query);
	$db->query();

	// Add foreign key (zone_id)
	$query = "ALTER TABLE `#__configbox_xref_country_zone` ADD CONSTRAINT FOREIGN KEY (`zone_id`) REFERENCES `#__configbox_zones`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

	// Add foreign key (country_id)
	$query = "ALTER TABLE `#__configbox_xref_country_zone` ADD CONSTRAINT FOREIGN KEY (`country_id`) REFERENCES `#__configbox_countries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

}

// Rename user groups table and clean up a few columns (enum(0,1) instead of tinyints)
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_groups') == true && ConfigboxUpdateHelper::tableExists('#__configbox_groups') == false) {

	// Rename the table
	$query = "ALTER TABLE `#__cbcheckout_user_groups` RENAME `#__configbox_groups`";
	$db->setQuery($query);
	$db->query();

	// Clean up some yes/no columns - make them ENUMS
	$query = "
	ALTER TABLE `#__configbox_groups` 
	CHANGE `enable_checkout_order` `enable_checkout_order` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `enable_see_pricing` `enable_see_pricing` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `enable_save_order` `enable_save_order` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `enable_request_quotation` `enable_request_quotation` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `enable_request_assistance` `enable_request_assistance` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `enable_recommendation` `enable_recommendation` ENUM('0','1') NOT NULL DEFAULT '1',
	CHANGE `b2b_mode` `b2b_mode` ENUM('0','1') NOT NULL DEFAULT '1'
	";
	$db->setQuery($query);
	$db->query();

}

// Rename country/payment method xref table and put foreign key constraints in place
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_xref_country_payment_option') == true && ConfigboxUpdateHelper::tableExists('#__configbox_xref_country_payment_method') == false) {

	// Rename the table
	$query = "ALTER TABLE `#__cbcheckout_xref_country_payment_option` RENAME `#__configbox_xref_country_payment_method`";
	$db->setQuery($query);
	$db->query();

	// Remove possible Orphans (country_id)
	$query = "DELETE FROM `#__configbox_xref_country_payment_method` WHERE `country_id` NOT IN (SELECT `id` FROM `#__configbox_countries`)";
	$db->setQuery($query);
	$db->query();

	// Remove possible Orphans (payment_id)
	$query = "DELETE FROM `#__configbox_xref_country_payment_method` WHERE `payment_id` NOT IN (SELECT `id` FROM `#__configbox_payment_methods`)";
	$db->setQuery($query);
	$db->query();

	// Add foreign key (country_id)
	$query = "ALTER TABLE `#__configbox_xref_country_payment_method` ADD CONSTRAINT FOREIGN KEY (`country_id`) REFERENCES `#__configbox_countries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

	// Add foreign key (zone_id)
	$query = "ALTER TABLE `#__configbox_xref_country_payment_method` ADD CONSTRAINT FOREIGN KEY (`payment_id`) REFERENCES `#__configbox_payment_methods`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

}

// Rename user fields table and remove group_id column (cancelled the possible future feature behind it)
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_field_definitions') == true && ConfigboxUpdateHelper::tableExists('#__configbox_user_field_definitions') == false) {
	$query = "ALTER TABLE `#__cbcheckout_user_field_definitions` RENAME `#__configbox_user_field_definitions`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_user_field_definitions` DROP `group_id`";
	$db->setQuery($query);
	$db->query();

}


// Countries to cities: Make primary keys and foreign keys all MEDIUMINT(8) UNSIGNED NOT NULL (plus some tweaks)

if (ConfigboxUpdateHelper::tableExists('#__configbox_countries') == true) {

	$query = "
	ALTER TABLE `#__configbox_countries` 
		CHANGE `vat_free` `vat_free` ENUM('0','1') NOT NULL DEFAULT '1',
		CHANGE `vat_free_with_vatin` `vat_free_with_vatin` ENUM('0','1') NOT NULL DEFAULT '1',
		CHANGE `published` `published` ENUM('0','1') NOT NULL DEFAULT '1',
		CHANGE `ordering` `ordering` MEDIUMINT(9) NOT NULL DEFAULT '0'
	";
	$db->setQuery($query);
	$db->query();

	// Add an index on the `ordering` column
	$query = "SHOW INDEX FROM `#__configbox_states` WHERE `Column_name` = 'ordering'";
	$db->setQuery($query);
	$index = $db->loadAssoc();

	if (!$index) {
		$query = "ALTER TABLE `#__configbox_countries` ADD INDEX (`ordering`)";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_states') == true) {

	$query = "
	ALTER TABLE `#__configbox_states` 
	CHANGE `id` `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	CHANGE `country_id` `country_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `published` `published` ENUM('0','1') NOT NULL DEFAULT '1'
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_counties') == true) {

	$query = "
	ALTER TABLE `#__configbox_counties` 
	CHANGE `id` `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	CHANGE `state_id` `state_id` MEDIUMINT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_cities') == true) {

	$query = "
	ALTER TABLE `#__configbox_cities` 
	CHANGE `id` `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	CHANGE `county_id` `county_id` MEDIUMINT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

// FK for configbox_states
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_states', 'country_id') == '') {

	// Orphans
	$query = "DELETE FROM `#__configbox_states` WHERE `country_id` NOT IN (SELECT `id` FROM `#__configbox_countries`)";
	$db->setQuery($query);
	$db->query();

	// FK
	$query = "ALTER TABLE `#__configbox_states` ADD FOREIGN KEY (`country_id`) REFERENCES `#__configbox_countries`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

// FK for configbox_counties
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_counties', 'state_id') == '') {

	// Orphans
	$query = "DELETE FROM `#__configbox_counties` WHERE `state_id` NOT IN (SELECT `id` FROM `#__configbox_states`)";
	$db->setQuery($query);
	$db->query();

	// FK
	$query = "ALTER TABLE `#__configbox_counties` ADD FOREIGN KEY (`state_id`) REFERENCES `#__configbox_states`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

// FK for configbox_cities
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cities', 'county_id') == '') {

	// Orphans
	$query = "DELETE FROM `#__configbox_cities` WHERE `county_id` NOT IN (SELECT `id` FROM `#__configbox_counties`)";
	$db->setQuery($query);
	$db->query();

	// FK
	$query = "ALTER TABLE `#__configbox_cities` ADD FOREIGN KEY (`county_id`) REFERENCES `#__configbox_counties`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}


// Rename the tax class table to configbox_tax_class_rates
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_tax_class_rates') == true && ConfigboxUpdateHelper::tableExists('#__configbox_tax_class_rates') == false) {
	$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` RENAME `#__configbox_tax_class_rates`";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_tax_class_rates') == true) {

	// Make the types of all columns with future foreign keys like their parent's primary key (and tweak some)
	$query = "
	ALTER TABLE `#__configbox_tax_class_rates` 
	CHANGE `tax_class_id` `tax_class_id` MEDIUMINT(8) UNSIGNED NOT NULL,
	CHANGE `city_id` `city_id` MEDIUMINT(8) UNSIGNED DEFAULT NULL,
	CHANGE `county_id` `county_id` MEDIUMINT(8) UNSIGNED DEFAULT NULL,
	CHANGE `state_id` `state_id` MEDIUMINT(8) UNSIGNED DEFAULT NULL,
	CHANGE `country_id` `country_id` MEDIUMINT(8) UNSIGNED DEFAULT NULL,
	CHANGE `tax_rate` `tax_rate` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '0.00',
	CHANGE `tax_code` `tax_code` VARCHAR(100) NOT NULL DEFAULT ''
	";
	$db->setQuery($query);
	$db->query();


	// Change all 0 values to NULL
	$query = "UPDATE `#__configbox_tax_class_rates` SET `city_id` = NULL WHERE `city_id` = 0";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_tax_class_rates` SET `county_id` = NULL WHERE `county_id` = 0";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_tax_class_rates` SET `state_id` = NULL WHERE `state_id` = 0";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_tax_class_rates` SET `country_id` = NULL WHERE `country_id` = 0";
	$db->setQuery($query);
	$db->query();


	// Delete any possible orphans
	$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `tax_class_id` IS NOT NULL AND `tax_class_id` NOT IN (SELECT `id` FROM `#__configbox_tax_classes`)";
	$db->setQuery($query);
	$db->query();

	$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `city_id` IS NOT NULL AND `city_id` NOT IN (SELECT `id` FROM `#__configbox_cities`)";
	$db->setQuery($query);
	$db->query();

	$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `county_id` IS NOT NULL AND `county_id` NOT IN (SELECT `id` FROM `#__configbox_counties`)";
	$db->setQuery($query);
	$db->query();

	$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `state_id` IS NOT NULL AND `state_id` NOT IN (SELECT `id` FROM `#__configbox_states`)";
	$db->setQuery($query);
	$db->query();

	$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `country_id` IS NOT NULL AND `country_id` NOT IN (SELECT `id` FROM `#__configbox_countries`)";
	$db->setQuery($query);
	$db->query();


	// Set foreign keys (Using CASCADE, so when a referenced country/state/county/city gets removed, rows here get removed automatically)
	if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_tax_class_rates', 'tax_class_id') == '') {

		$query = "ALTER TABLE `#__configbox_tax_class_rates` ADD FOREIGN KEY (`tax_class_id`) REFERENCES `#__configbox_tax_classes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_tax_class_rates` ADD FOREIGN KEY (`city_id`) REFERENCES `#__configbox_cities`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_tax_class_rates` ADD FOREIGN KEY (`county_id`) REFERENCES `#__configbox_counties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_tax_class_rates` ADD FOREIGN KEY (`state_id`) REFERENCES `#__configbox_states`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_tax_class_rates` ADD FOREIGN KEY (`country_id`) REFERENCES `#__configbox_countries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->setQuery($query);
		$db->query();

	}

}

// Put a foreign key on `group_id` in configbox_users
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_users', 'group_id') == '') {

	// Make the group_id like configbox_user.id (and make it NULLABLE)
	$query = "ALTER TABLE `#__configbox_users` CHANGE `group_id` `group_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL;";
	$db->setQuery($query);
	$db->query();

	// Get current group ids
	$query = "SELECT `id` FROM `#__configbox_groups`";
	$db->setQuery($query);
	$groupIds = $db->loadResultList();

	// Create a group if there are none and load the group ids again
	if (empty($groupIds)) {

		$query = "INSERT INTO `#__configbox_groups` (`id`, `title`) VALUES (NULL, 'Default Group')";
		$db->setQuery($query);
		$db->query();

		$query = "SELECT `id` FROM `#__configbox_groups`";
		$db->setQuery($query);
		$groupIds = $db->loadResultList();

	}

	// If we got groups, assign the first one to all users that have an invalid group_id
	$query = "UPDATE `#__configbox_users` SET `group_id` = ".intval($groupIds[0])." WHERE `group_id` NOT IN (".implode(',', $groupIds).")";
	$db->setQuery($query);
	$db->query();

	// Set the foreign key constraint (ON DELETE we do SET NULL because there will always be some temporary users and we'll never be able to delete a group then)
	$query = "ALTER TABLE `#__configbox_users` ADD FOREIGN KEY (`group_id`) REFERENCES `#__configbox_groups`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

}

// Update table is no longer in use, drop it
if (ConfigboxUpdateHelper::tableExists('#__configbox_updates') == true) {
	$query = "DROP TABLE `#__configbox_updates`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_shipping_methods', 'shipper_id') == '') {
	$query = "ALTER TABLE `#__configbox_shipping_methods` ADD FOREIGN KEY (`shipper_id`) REFERENCES `#__configbox_shippers`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_shipping_methods', 'taxclass_id') == '') {
	$query = "ALTER TABLE `#__configbox_shipping_methods` ADD FOREIGN KEY (`taxclass_id`) REFERENCES `#__configbox_tax_classes`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'zone_id') == false) {
	$query = "ALTER TABLE `#__configbox_shipping_methods` CHANGE `zone` `zone_id` MEDIUMINT(8) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_shipping_methods', 'zone_id') == '') {
	$query = "ALTER TABLE `#__configbox_shipping_methods` ADD FOREIGN KEY (`zone_id`) REFERENCES `#__configbox_zones`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_product_images', 'product_id') == '') {
	$query = "ALTER TABLE `#__configbox_product_images` ADD FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_product_detail_panes', 'product_id') == '') {
	$query = "ALTER TABLE `#__configbox_product_detail_panes` ADD FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'taxclass_id') == true) {

	$query = "ALTER TABLE `#__configbox_products` MODIFY `taxclass_id` MEDIUMINT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_products` SET `taxclass_id` = NULL WHERE `taxclass_id` NOT IN (SELECT `id` FROM `#__configbox_tax_classes`)";
	$db->setQuery($query);
	$db->query();

	if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_products', 'taxclass_id') == '') {
		$query = "ALTER TABLE `#__configbox_products` ADD FOREIGN KEY (`taxclass_id`) REFERENCES `#__configbox_tax_classes`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'taxclass_recurring_id') == true) {

	$query = "ALTER TABLE `#__configbox_products` MODIFY `taxclass_recurring_id` MEDIUMINT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_products` SET `taxclass_recurring_id` = NULL WHERE `taxclass_recurring_id` NOT IN (SELECT `id` FROM `#__configbox_tax_classes`)";
	$db->setQuery($query);
	$db->query();

	if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_products', 'taxclass_recurring_id') == '') {
		$query = "ALTER TABLE `#__configbox_products` ADD FOREIGN KEY (`taxclass_recurring_id`) REFERENCES `#__configbox_tax_classes`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'taxclass_id') == true) {

	$query = "ALTER TABLE `#__configbox_payment_methods` MODIFY `taxclass_id` MEDIUMINT UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_payment_methods` SET `taxclass_id` = NULL WHERE `taxclass_id` NOT IN (SELECT `id` FROM `#__configbox_tax_classes`)";
	$db->setQuery($query);
	$db->query();

	if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_payment_methods', 'taxclass_id') == '') {
		$query = "ALTER TABLE `#__configbox_payment_methods` ADD FOREIGN KEY (`taxclass_id`) REFERENCES `#__configbox_tax_classes`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
		$db->setQuery($query);
		$db->query();
	}

}


// Delete possible Orphans between products and answer xrefs

$query = "DELETE FROM `#__configbox_pages` WHERE `product_id` NOT IN (SELECT `id` FROM `#__configbox_products`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_elements` WHERE `page_id` NOT IN (SELECT `id` FROM `#__configbox_pages`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_xref_element_option` WHERE `element_id` NOT IN (SELECT `id` FROM `#__configbox_elements`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_xref_element_option` WHERE `option_id` NOT IN (SELECT `id` FROM `#__configbox_options`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_xref_listing_product` WHERE `product_id` NOT IN (SELECT `id` FROM `#__configbox_products`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_xref_listing_product` WHERE `listing_id` NOT IN (SELECT `id` FROM `#__configbox_listings`)";
$db->setQuery($query);
$db->query();

// Do foreign keys all they ways from xref answers to pages

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_xref_element_option', 'element_id') == '') {

	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD FOREIGN KEY (`element_id`) REFERENCES `#__configbox_elements`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD FOREIGN KEY (`option_id`) REFERENCES `#__configbox_options`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_elements', 'page_id') == '') {
	$query = "ALTER TABLE `#__configbox_elements` ADD FOREIGN KEY (`page_id`) REFERENCES `#__configbox_pages`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_pages', 'product_id') == '') {
	$query = "ALTER TABLE `#__configbox_pages` ADD FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_xref_listing_product', 'listing_id') == '') {

	// Make sure the primary key on the parent matches
	$query = "ALTER TABLE `#__configbox_listings` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_listing_product` ADD FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_listing_product` ADD FOREIGN KEY (`listing_id`) REFERENCES `#__configbox_listings`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_config', 'default_customer_group_id') == '') {

	// Make the continue_listing_id like the referenced listing.id
	$query = "ALTER TABLE `#__configbox_config` CHANGE `default_customer_group_id` `default_customer_group_id` MEDIUMINT UNSIGNED NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();

	// Weed out invalid values
	$query = "UPDATE `#__configbox_config` SET `default_customer_group_id` = NULL WHERE `default_customer_group_id` NOT IN (SELECT `id` FROM `#__configbox_groups`)";
	$db->setQuery($query);
	$db->query();

	// Set the FK (with RESTRICT)
	$query = "ALTER TABLE `#__configbox_config` ADD FOREIGN KEY (`default_customer_group_id`) REFERENCES `#__configbox_groups`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT";
	$db->setQuery($query);
	$db->query();
}

// FK on configbox_config.default_country_id (using SET NULL)
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_config', 'default_country_id') == '') {

	// Make the continue_listing_id like the referenced listing.id
	$query = "ALTER TABLE `#__configbox_config` CHANGE `default_country_id` `default_country_id` MEDIUMINT UNSIGNED NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();

	// Weed out invalid values
	$query = "UPDATE `#__configbox_config` SET `default_country_id` = NULL WHERE `default_country_id` NOT IN (SELECT `id` FROM `#__configbox_countries`)";
	$db->setQuery($query);
	$db->query();

	// Set the FK (with SET NULL)
	$query = "ALTER TABLE `#__configbox_config` ADD FOREIGN KEY (`default_country_id`) REFERENCES `#__configbox_countries`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

// FK on configbox_config.continue_listing_id (using SET NULL)
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_config', 'continue_listing_id') == '') {

	// Make the continue_listing_id like the referenced listing.id
	$query = "ALTER TABLE `#__configbox_config` CHANGE `continue_listing_id` `continue_listing_id` INT UNSIGNED NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();

	// Weed out invalid values
	$query = "UPDATE `#__configbox_config` SET `continue_listing_id` = NULL WHERE `continue_listing_id` NOT IN (SELECT `id` FROM `#__configbox_listings`)";
	$db->setQuery($query);
	$db->query();

	// Set the FK (with SET NULL)
	$query = "ALTER TABLE `#__configbox_config` ADD FOREIGN KEY (`continue_listing_id`) REFERENCES `#__configbox_listings`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

// Drop any existing FKs on configbox_carts/user_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_carts', 'user_id');
if ($name) {
	$query = "ALTER TABLE `#__configbox_carts` DROP FOREIGN KEY `$name`";
	$db->setQuery($query);
	$db->query();
}

// Drop any existing FKs on configbox_cart_positions/cart_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_positions', 'cart_id');
if ($name) {
	$query = "ALTER TABLE `#__configbox_cart_positions` DROP FOREIGN KEY `$name`";
	$db->setQuery($query);
	$db->query();
}

// Drop any existing FKs on configbox_cart_position_configurations/cart_position_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_position_configurations', 'cart_position_id');
if ($name) {
	$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP FOREIGN KEY `$name`";
	$db->setQuery($query);
	$db->query();
}

// Make configbox_users.id INT UNSIGNED (for FKs later)
if (ConfigboxUpdateHelper::tableExists('#__configbox_users')) {
	$query = "ALTER TABLE `#__configbox_users` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_carts')) {

	// Make FK column same as primary
	$query = "ALTER TABLE `#__configbox_carts` CHANGE `user_id` `user_id` INT UNSIGNED NOT NULL;";
	$db->setQuery($query);
	$db->query();

	// Make cart id type better
	$query = "ALTER TABLE `#__configbox_carts` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;";
	$db->setQuery($query);
	$db->query();

	// There was no index on created_time yet
	if (ConfigboxUpdateHelper::keyExists('#__configbox_carts', 'created_time') === false) {
		$query = "ALTER TABLE `#__configbox_carts` ADD KEY `created_time` (`created_time`);";
		$db->setQuery($query);
		$db->query();
	}

	// Cannot hurt I guess
	if (ConfigboxUpdateHelper::keyExists('#__configbox_carts', 'user_id') === false) {
		$query = "ALTER TABLE `#__configbox_carts` ADD KEY `user_id` (`user_id`);";
		$db->setQuery($query);
		$db->query();
	}

}

// Make cart position table column types match referenced columns
if (ConfigboxUpdateHelper::tableExists('#__configbox_cart_positions')) {

	$query = "
	ALTER TABLE `#__configbox_cart_positions` 
	CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	CHANGE `cart_id` `cart_id` INT UNSIGNED NOT NULL,
	CHANGE `quantity` `quantity` SMALLINT UNSIGNED NOT NULL DEFAULT '1',
	CHANGE `prod_id` `prod_id` INT UNSIGNED NULL
	";
	$db->setQuery($query);
	$db->query();

}

// Make cart position configurations table column types match referenced columns
if (ConfigboxUpdateHelper::tableExists('#__configbox_cart_position_configurations')) {

	$query = "
	ALTER TABLE `#__configbox_cart_position_configurations` 
	CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	CHANGE `cart_position_id` `cart_position_id` INT UNSIGNED NOT NULL,
	CHANGE `prod_id` `prod_id` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `element_id` `element_id` INT UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	// Remove an index we no longer need
	if (ConfigboxUpdateHelper::keyExists('#__configbox_cart_position_configurations', 'cart_position_id-element_id') === true) {
		$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP INDEX `cart_position_id-element_id`";
		$db->setQuery($query);
		$db->query();
	}

}

// Weed out orpaned carts
$query = "DELETE FROM `#__configbox_carts` WHERE `user_id` NOT IN (SELECT `id` FROM `#__configbox_users`)";
$db->setQuery($query);
$db->query();

// Weed out orpaned cart positions
$query = "DELETE FROM `#__configbox_cart_positions` WHERE `cart_id` NOT IN (SELECT `id` FROM `#__configbox_carts`)";
$db->setQuery($query);
$db->query();

// Weed out cart positions with products that no longer exist
$query = "DELETE FROM `#__configbox_cart_positions` WHERE `prod_id` NOT IN (SELECT `id` FROM `#__configbox_products`)";
$db->setQuery($query);
$db->query();

// Weed out orphaned cart position configurations
$query = "DELETE FROM `#__configbox_cart_position_configurations` WHERE `cart_position_id` NOT IN (SELECT `id` FROM `#__configbox_cart_positions`)";
$db->setQuery($query);
$db->query();

// Make foreign key constraints all over carts/positions/configurations (all CASCADING)
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_carts', 'user_id') == '') {
	$query = "ALTER TABLE `#__configbox_carts` ADD FOREIGN KEY (`user_id`) REFERENCES `#__configbox_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_positions', 'cart_id') == '') {
	$query = "ALTER TABLE `#__configbox_cart_positions` ADD FOREIGN KEY (`cart_id`) REFERENCES `#__configbox_carts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_positions', 'prod_id') == '') {
	$query = "ALTER TABLE `#__configbox_cart_positions` ADD FOREIGN KEY (`prod_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_position_configurations', 'cart_position_id') == '') {
	$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD FOREIGN KEY (`cart_position_id`) REFERENCES `#__configbox_cart_positions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_position_configurations', 'prod_id') == '') {
	$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD FOREIGN KEY (`prod_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_cart_position_configurations', 'element_id') == '') {
	$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD FOREIGN KEY (`element_id`) REFERENCES `#__configbox_elements`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();
}

// NOW FOR CALCULATIONS

// Weed out Orphans al over calculation-related tables

$query = "DELETE FROM `#__configbox_calculation_matrices_data` WHERE `id` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_calculation_matrices` WHERE `id` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_calculation_formulas` WHERE `id` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM `#__configbox_calculation_codes` WHERE `id` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
$db->setQuery($query);
$db->query();

// Set foreign key constraints all over calculation related tables (CASCADING)

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_matrices_data', 'id') == '') {
	$query = "ALTER TABLE `#__configbox_calculation_matrices_data` ADD FOREIGN KEY (`id`) REFERENCES `#__configbox_calculations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_matrices', 'id') == '') {
	$query = "ALTER TABLE `#__configbox_calculation_matrices` ADD FOREIGN KEY (`id`) REFERENCES `#__configbox_calculations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_formulas', 'id') == '') {
	$query = "ALTER TABLE `#__configbox_calculation_formulas` ADD FOREIGN KEY (`id`) REFERENCES `#__configbox_calculations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_codes', 'id') == '') {
	$query = "ALTER TABLE `#__configbox_calculation_codes` ADD FOREIGN KEY (`id`) REFERENCES `#__configbox_calculations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

// Make configbox_calculations.product_id like primary key, make it NULLABLE, make 0s NULL and do a foreign key constraint with SET NULL
if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculations', 'product_id') == '') {
	$query = "ALTER TABLE `#__configbox_calculations` CHANGE `product_id` `product_id` INT UNSIGNED NULL DEFAULT NULL;";
	$db->setQuery($query);
	$db->query();

	// NULL any existing 0 entries in `product_id` (or non-existent product_ids)
	$query = "UPDATE `#__configbox_calculations` SET `product_id` = NULL WHERE `product_id` = 0 OR `product_id` NOT IN (SELECT `id` FROM `#__configbox_products`)";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_calculations` ADD FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();
}

// Set foreign keys on calculations in configbox_elements
if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == true) {

	$joins = array(
		'calcmodel_id_min_val',
		'calcmodel_id_max_val',
		'calcmodel',
		'calcmodel_recurring',
		'calcmodel_weight'
	);

	$query = "
	ALTER TABLE `#__configbox_elements` 
	CHANGE `calcmodel_id_min_val` `calcmodel_id_min_val` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_id_max_val` `calcmodel_id_max_val` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel` `calcmodel` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_recurring` `calcmodel_recurring` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_weight` `calcmodel_weight` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	foreach ($joins as $join) {
		$query = "UPDATE `#__configbox_elements` SET `".$join."` = NULL WHERE `".$join."` = 0 OR `".$join."` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_elements', $join) == '') {
			$query = "ALTER TABLE `#__configbox_elements` ADD FOREIGN KEY (`".$join."`) REFERENCES `#__configbox_calculations`(`id`)";
			$db->setQuery($query);
			$db->query();
		}

	}

}

// Set foreign keys on calculations in configbox_xref_element_option
if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option') == true) {

	$query = "
	ALTER TABLE `#__configbox_xref_element_option` 
	CHANGE `calcmodel` `calcmodel` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_recurring` `calcmodel_recurring` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_weight` `calcmodel_weight` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	$joins = array(
		'calcmodel',
		'calcmodel_recurring',
		'calcmodel_weight'
	);

	foreach ($joins as $join) {
		$query = "UPDATE `#__configbox_xref_element_option` SET `".$join."` = NULL WHERE `".$join."` = 0 OR `".$join."` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_xref_element_option', $join) == '') {
			$query = "ALTER TABLE `#__configbox_xref_element_option` ADD FOREIGN KEY (`".$join."`) REFERENCES `#__configbox_calculations`(`id`)";
			$db->setQuery($query);
			$db->query();
		}

	}

}

// Set foreign keys on placeholders in calculation_codes
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_codes') == true) {

	$query = "
	ALTER TABLE `#__configbox_calculation_codes` 
	CHANGE `element_id_a` `element_id_a` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `element_id_b` `element_id_b` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `element_id_c` `element_id_c` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `element_id_d` `element_id_d` INT UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	$joins = array(
		'element_id_a',
		'element_id_b',
		'element_id_c',
		'element_id_d',
	);

	foreach ($joins as $join) {
		$query = "UPDATE `#__configbox_calculation_codes` SET `".$join."` = NULL WHERE `".$join."` = 0 OR `".$join."` NOT IN (SELECT `id` FROM `#__configbox_elements`)";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_codes', $join) == '') {
			$query = "ALTER TABLE `#__configbox_calculation_codes` ADD FOREIGN KEY (`".$join."`) REFERENCES `#__configbox_elements`(`id`)";
			$db->setQuery($query);
			$db->query();
		}

	}

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_matrices') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices', 'calcmodel_id_x') == true) {
		$query = "ALTER TABLE `#__configbox_calculation_matrices` DROP `calcmodel_id_x`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices', 'calcmodel_id_y') == true) {
		$query = "ALTER TABLE `#__configbox_calculation_matrices` DROP `calcmodel_id_y`";
		$db->setQuery($query);
		$db->query();
	}


	$query = "
	ALTER TABLE `#__configbox_calculation_matrices` 
	CHANGE `column_element_id` `column_element_id` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `row_element_id` `row_element_id` INT UNSIGNED NULL DEFAULT NULL,
	CHANGE `multielementid` `multielementid` INT UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	$joins = array(
		'column_element_id',
		'row_element_id',
		'multielementid',
	);

	foreach ($joins as $join) {
		$query = "UPDATE `#__configbox_calculation_matrices` SET `".$join."` = NULL WHERE `".$join."` = 0 OR `".$join."` NOT IN (SELECT `id` FROM `#__configbox_elements`)";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_matrices', $join) == '') {
			$query = "ALTER TABLE `#__configbox_calculation_matrices` ADD FOREIGN KEY (`".$join."`) REFERENCES `#__configbox_elements`(`id`)";
			$db->setQuery($query);
			$db->query();
		}

	}

	$query = "
	ALTER TABLE `#__configbox_calculation_matrices` 
	CHANGE `column_calc_id` `column_calc_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `row_calc_id` `row_calc_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	CHANGE `calcmodel_id_multi` `calcmodel_id_multi` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

	$joins = array(
		'column_calc_id',
		'row_calc_id',
		'calcmodel_id_multi'
	);

	foreach ($joins as $join) {
		$query = "UPDATE `#__configbox_calculation_matrices` SET `".$join."` = NULL WHERE `".$join."` = 0 OR `".$join."` NOT IN (SELECT `id` FROM `#__configbox_calculations`)";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculation_matrices', $join) == '') {
			$query = "ALTER TABLE `#__configbox_calculation_matrices` ADD FOREIGN KEY (`".$join."`) REFERENCES `#__configbox_calculations`(`id`)";
			$db->setQuery($query);
			$db->query();
		}

	}

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_strings') == true) {

	// CB stored translations even if the translation was empty - time to weed out those empty ones.
	$query = "DELETE FROM `#__configbox_strings` WHERE `text` = ''";
	$db->setQuery($query);
	$db->query();

	// Drop the ID column, we got a multi-column unique key anyways
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_strings', 'id')) {
		$query = "ALTER TABLE `#__configbox_strings` DROP `id`";
		$db->setQuery($query);
		$db->query();
	}

	// Make type and key a bit smaller (SMALLINT and INT) and move `text` to the end for better reading
	$query = "
	ALTER TABLE `#__configbox_strings` 
	CHANGE `type` `type` SMALLINT UNSIGNED NOT NULL COMMENT 'See langType in the property definition .',
	CHANGE `key` `key` INT UNSIGNED NOT NULL COMMENT 'Primary key value for the regarding record.',
	CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `language_tag`
	";
	$db->setQuery($query);
	$db->query();

}

// 3.0.12.4

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_users') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_users` 
	MODIFY `id` INT(10) UNSIGNED NOT NULL,
	MODIFY `order_id` INT(10) UNSIGNED NOT NULL,
	MODIFY `county_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	MODIFY `billingcounty_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	MODIFY `city_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	MODIFY `billingcity_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_records') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_records` 
	MODIFY `delivery_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	MODIFY `payment_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
	MODIFY `cart_id` INT(10) UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_payment_methods') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_payment_methods` 
	MODIFY `order_id` INT(10) UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_cities') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_cities` 
	MODIFY `id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `county_id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_counties') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_counties` 
	MODIFY `id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `state_id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_states') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_states` 
	MODIFY `id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `country_id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_countries') == true) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_countries` 
	MODIFY `id` MEDIUMINT UNSIGNED NOT NULL,
	MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_xref_country_payment_option') == true) {
	$query = "DROP TABLE `#__cbcheckout_order_xref_country_payment_option`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_xref_country_zone') == true) {
	$query = "DROP TABLE `#__cbcheckout_order_xref_country_zone`";
	$db->setQuery($query);
	$db->query();
}


function switchToFourDecimals4($tableName, $colName, $colType = "DECIMAL(20,4)") {

	// Stop if table doesn't exist
	if (ConfigboxUpdateHelper::tableExists($tableName) == false) {
		return;
	}

	// Get column information
	$db = KenedoPlatform::getDb();
	$query = "SHOW COLUMNS FROM `".$tableName."` WHERE `Field` = '".$colName."'";
	$db->setQuery($query);
	$col = $db->loadAssoc();

	// Can only mean column doesn't exist, so stop
	if (empty($col['Type'])) {
		return;
	}

	// Have the type normalized (no spaces, all uppercase)
	$isType = strtoupper(str_replace(' ', '', $col['Type']));
	$shouldType = strtoupper(str_replace(' ', '', $colType));

	// If type isn't right, change it
	if (true || strstr($isType, $shouldType) == false) {
		$isUnsigned = strstr($isType, 'UNSIGNED');
		$canBeNull = ($col['Null'] == 'NO') == false;

		$attributes = array();
		$attributes[] = ($isUnsigned) ? 'UNSIGNED':'';
		$attributes[] = ($canBeNull) ? 'NULL':'NOT NULL';

		$query = "ALTER"." TABLE `".$tableName."` CHANGE `".$colName."` `".$colName."` ".$colType." ".implode(' ', $attributes)." DEFAULT '0.0000'";
		$db->setQuery($query);
		$db->query();

	}

}

// Forgot to change those two price fields
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'price_net') == true) {
	switchToFourDecimals4('#__cbcheckout_order_configurations', 'price_net');
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'price_recurring_net') == true) {
	switchToFourDecimals4('#__cbcheckout_order_configurations', 'price_recurring_net');
}
