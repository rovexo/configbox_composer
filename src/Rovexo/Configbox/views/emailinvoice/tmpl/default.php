<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewEmailinvoice */
?>

<p><?php echo KText::sprintf('EMAIL_INVOICE_SALUTATION', $this->orderRecord->orderAddress->billingfirstname, $this->orderRecord->orderAddress->billinglastname, $this->orderRecord->orderAddress->salutation);?></p>
<p><?php echo KText::_('EMAIL_INVOICE_TEXT');?></p>

