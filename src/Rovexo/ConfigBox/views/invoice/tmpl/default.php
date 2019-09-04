<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewInvoice */
?>
<!DOCTYPE html>
<html lang="<?php echo KText::getLanguageCode();?>">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php echo $this->hrefCssSystem;?>" />
	<link type="text/css" rel="stylesheet" href="<?php echo $this->hrefCssCustom;?>" />
	<title><?php echo KText::_('Invoice');?></title>
</head>
<body class="body-pdf body-pdf-invoice">

<?php
// This includes the template that has the header/footer/fold lines instructions.
// Note that this code needs to be right after the opening body tag (the PDF generator somehow ignores the header/footer instructions otherwise).
// Note that if you make a template override file, you don't need to copy the header_footer.php file as well (unless you want to override it).
// The template engine will look for the header_footer template in the overrides location and if not found will use the one in the original location.
$this->renderView('header_footer');
?>

<div id="com_configbox" class="cb-content"><div id="view-invoice">

<table class="layout-table customer-information">
	<tr>
		<td class="left-column">
			<div class="wrapper">
				<div class="company-name"><?php echo hsc($this->orderRecord->orderAddress->billingcompanyname);?></div>
				<div class="name"><span class="salutation"><?php echo hsc($this->orderRecord->orderAddress->billingsalutation);?></span> <span class="firstname"><?php echo hsc($this->orderRecord->orderAddress->billingfirstname) . ' '. hsc($this->orderRecord->orderAddress->billinglastname);?></span></div>
				<div class="address1"><?php echo hsc($this->orderRecord->orderAddress->billingaddress1);?></div>
				<?php if ($this->orderRecord->orderAddress->billingaddress2) { ?>
					<div class="address2"><?php echo hsc($this->orderRecord->orderAddress->billingaddress2);?></div>
				<?php } ?>
				<div class="city"><span class="zipcode"><?php echo hsc($this->orderRecord->orderAddress->billingzipcode);?></span> <span class="city"><?php echo hsc($this->orderRecord->orderAddress->billingcity);?></span></div>
				<div class="country"><?php echo hsc($this->orderRecord->orderAddress->billingcountryname);?></div>
			</div>
		</td>
		<td class="right-column">
			<div class="wrapper">

				<table class="order-data">
					<tr class="invoice-id">
						<td class="key"><?php echo KText::_('Invoice no.')?>:</td>
						<td class="value"><?php echo hsc($this->invoiceNumber);?></td>
					</tr>
					<tr class="order-id">
						<td class="key"><?php echo KText::_('Order no.')?>:</td>
						<td class="value"><?php echo intval($this->orderRecord->id);?></td>
					</tr>
					<tr class="invoice-date">
						<td class="key"><?php echo KText::_('Date')?>:</td>
						<td class="value"><?php echo hsc( KenedoTimeHelper::getFormatted('NOW', 'date') );?></td>
					</tr>
					<tr class="customer-email">
						<td class="key"><?php echo KText::_('Email')?>:</td>
						<td class="value"><?php echo hsc($this->orderRecord->orderAddress->billingemail);?></td>
					</tr>
					<?php if ($this->orderRecord->orderAddress->billingphone) { ?>
						<tr class="customer-phone">
							<td class="key"><?php echo KText::_('Phone')?>:</td>
							<td class="value"><?php echo hsc($this->orderRecord->orderAddress->billingphone);?></td>
						</tr>
					<?php } ?>
					<?php if ($this->orderRecord->orderAddress->vatin) { ?>
						<tr class="customer-vatin">
							<td class="key"><?php echo KText::_('VAT IN')?>:</td>
							<td class="value"><?php echo hsc($this->orderRecord->orderAddress->vatin);?></td>
						</tr>
					<?php } ?>
				</table>
				
			</div>
		</td>
	</tr>
</table>


<h1 class="document-title"><?php echo KText::_( 'Invoice' );?></h1>


<div class="wrapper-order-overview">
	<h2><?php echo KText::_('Overview');?></h2>
	<?php 
	// The order record HTML comes from the view 'record'
	echo $this->orderRecordHtml;
	?>
</div>

<?php 
if ($this->paymentMethodHtml) {
	echo $this->paymentMethodHtml;
}
?>
</div></div>
</body>
</html>