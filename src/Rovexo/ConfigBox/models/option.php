<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelOption {
		
	function getOption($optionId = NULL, $answerId = NULL) {
		
		$query = "	SELECT  o.*,
							o.price AS basePriceStatic,
							o.price_recurring AS basePriceRecurringStatic,
							answer.*,
							answer.id AS id,
							answer.option_id AS option_id
								
							FROM `#__configbox_xref_element_option` AS answer
							LEFT JOIN `#__configbox_options` AS o ON o.id = answer.option_id
							";
		
		if ($optionId) {
			$query .= " WHERE answer.option_id = ".(int)$optionId;
		}
		if ($answerId) {
			$query .= " WHERE answer.id = ".(int)$answerId;
		}
		
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$option = $db->loadObject();
		
		if ($option) {
			$option->title 			= ConfigboxCacheHelper::getTranslation('#__configbox_strings',  5, $option->option_id);
			$option->description 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 15, $option->option_id);
		}
		
		return $option;
	}
	
}
