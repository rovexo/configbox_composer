<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'inputx') == true) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `inputx`  `column_element_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'inputy') == true) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `inputy`  `row_element_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'calcmodel_id_x') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'column_calc_id') == false) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `calcmodel_id_x`  `column_calc_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'calcmodel_id_y') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'row_calc_id') == false) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `calcmodel_id_y`  `row_calc_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

// Add the row type field to the calc table, populate the existing field
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'row_type') == false) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` ADD  `row_type` VARCHAR( 32 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();	
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_tables') == true) {
	$query = "SELECT * FROM `#__configbox_calculation_tables` WHERE `row_type` = ''";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	
	if ($items) {
		foreach ($items as $item) {
			$query = "UPDATE `#__configbox_calculation_tables` SET `row_type` = '".( ($item->row_calc_id) ? 'calculation':'element' )."' WHERE `id` = ".intval($item->id);
			$db->setQuery($query);
			$db->query();
		}
	}
}

// Add the column type field to the calc table, populate the existing field
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'column_type') == false) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` ADD  `column_type` VARCHAR( 32 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_tables') == true) {
	$query = "SELECT * FROM `#__configbox_calculation_tables` WHERE `column_type` = ''";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	if ($items) {
		foreach ($items as $item) {
			$query = "UPDATE `#__configbox_calculation_tables` SET `column_type` = '".( ($item->column_calc_id) ? 'calculation':'element' )."' WHERE `id` = ".intval($item->id);
			$db->setQuery($query);
			$db->query();
		}
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculationmodels') == true) {
	$query = "UPDATE `#__configbox_calculationmodels` SET `type` = 'matrix' WHERE `type` = 'table'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_config') == true) {
	$query = "SELECT * FROM `#__cbcheckout_config` WHERE `id` = 1";
	$db->setQuery($query);
	$config = $db->loadObject();


	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'default_user_group_id') == false) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'default_customer_group_id') == false) {


			$query = "ALTER TABLE  `#__configbox_config` ADD  `default_user_group_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 1";
			$db->setQuery($query);
			$db->query();

			if (!empty($config->default_user_group_id)) {
				$query = "UPDATE `#__configbox_config` SET `default_user_group_id` = " . intval($config->default_user_group_id);
				$db->setQuery($query);
				$db->query();
			}

		}
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'default_country_id') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `default_country_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `default_country_id` = " . intval($config->default_country_id);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_one_page_checkout') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `use_one_page_checkout` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `use_one_page_checkout` = " . intval($config->use_one_page_checkout);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'disable_delivery') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `disable_delivery` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `disable_delivery` = " . intval($config->disable_delivery);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'sku_in_order_record') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `sku_in_order_record` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `sku_in_order_record` = " . intval($config->sku_in_order_record);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'newsletter_preset') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `newsletter_preset` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `newsletter_preset` = " . intval($config->newsletter_preset);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'alternate_shipping_preset') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `alternate_shipping_preset` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `alternate_shipping_preset` = " . intval($config->alternate_shipping_preset);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'show_recurring_login_cart') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `show_recurring_login_cart` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `show_recurring_login_cart` = " . intval($config->show_recurring_login_cart);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'explicit_agreement_terms') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `explicit_agreement_terms` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `explicit_agreement_terms` = " . intval($config->explicit_agreement_terms);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'explicit_agreement_rp') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `explicit_agreement_rp` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `explicit_agreement_rp` = " . intval($config->explicit_agreement_rp);
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_invoicing') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `enable_invoicing` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `enable_invoicing` = '" . intval($config->enable_invoicing) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'send_invoice') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `send_invoice` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `send_invoice` = '" . intval($config->send_invoice) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'invoice_generation') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `invoice_generation` ENUM('0', '1', '2') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `invoice_generation` = '" . intval($config->invoice_generation) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'invoice_number_prefix') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `invoice_number_prefix` VARCHAR(32) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `invoice_number_prefix` = '" . $db->getEscaped($config->invoice_number_prefix) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'invoice_number_start') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `invoice_number_start` INT(10) UNSIGNED NOT NULL DEFAULT 1";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `invoice_number_start` = '" . $db->getEscaped($config->invoice_number_start) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_file_uploads') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `enable_file_uploads` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `enable_file_uploads` = '" . intval($config->enable_file_uploads) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_extensions') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `allowed_file_extensions` VARCHAR(64) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `allowed_file_extensions` = '" . $db->getEscaped($config->allowed_file_extensions) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_mimetypes') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `allowed_file_mimetypes` VARCHAR(64) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `allowed_file_mimetypes` = '" . $db->getEscaped($config->allowed_file_mimetypes) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_size') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `allowed_file_size` INT(10) UNSIGNED NOT NULL DEFAULT 0";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `allowed_file_size` = '" . intval($config->allowed_file_size) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'showrefundpolicy') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `showrefundpolicy` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `showrefundpolicy` = '" . intval($config->showrefundpolicy) . "'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'showrefundpolicyinline') == false) {
		$query = "ALTER TABLE  `#__configbox_config` ADD  `showrefundpolicyinline` ENUM('0', '1') NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_config` SET `showrefundpolicyinline` = '" . intval($config->showrefundpolicyinline) . "'";
		$db->setQuery($query);
		$db->query();
	}
	
}