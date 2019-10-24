<?php
defined('CB_VALID_ENTRY') or die();
/*
About this template:
The template looks into the group data of the regarding customer and checks if a quotation is supposed to be sent.
If so, it loads the shop manager's template to avoid code duplication
*/
/**
 * @var ConfigboxOrderData $orderRecord
 * @see ConfigboxModelOrderrecord::getOrderRecord
 */
if ($orderRecord->groupData->quotation_email == false) {
	return;
}
else {
	include(dirname(__FILE__).DS.'manager_11.pdf.php');
}
