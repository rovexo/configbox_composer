<?php
defined('CB_VALID_ENTRY') or die();

/*
About this template:
This template uses a trick: Instead of being a regular template that will be parsed and converted into a PDF, it
triggers the creation of a quotation and puts the quotation file's path in the email's attachment array in order
to be included in the email. Since there is no actual output in this template, no PDF is generated.
*/
/**
 * @var ConfigboxOrderData $orderRecord
 * @see ConfigboxModelOrderrecord::getOrderRecord
 */

$quotationModel = KenedoModel::getModel('ConfigboxModelQuotation');
$quotation = $quotationModel->getQuotation($orderRecord->id);

if (!$quotation) {
	$quotation = $quotationModel->createQuotation($orderRecord->id);
}

if ($quotation) {
	$email->attachments[] = CONFIGBOX_DIR_QUOTATIONS.DS.$quotation->file;
}
else {
	KLog::log('Could not create quotation for order ID "'.$orderRecord->id.'".','error',KText::_('Could not create quotation.'));
	return false;
}
