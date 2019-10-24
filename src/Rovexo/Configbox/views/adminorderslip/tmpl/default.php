<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorderslip */
?>
<!DOCTYPE html>
<html lang="<?php echo KText::getLanguageCode();?>">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php echo $this->hrefCssSystem;?>" />
	<link type="text/css" rel="stylesheet" href="<?php echo $this->hrefCssCustom;?>" />
	<title><?php echo KText::sprintf( 'Quotation for order %s', intval($this->orderRecord->id) );?></title>
</head>
<body class="body-pdf body-pdf-orderslip">

<?php
// This includes the template that has the header/footer/fold lines instructions.
// Note that this code needs to be right after the opening body tag (the PDF generator somehow ignores the header/footer instructions otherwise).
// Note that if you make a template override file, you don't need to copy the header_footer.php file as well (unless you want to override it).
// The template engine will look for the header_footer template in the overrides location and if not found will use the one in the original location.
$this->renderView('header_footer');
?>

<div id="com_configbox" class="cb-content"><div id="view-orderslip">

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
					<tr class="order-id">
						<td class="key"><?php echo KText::_('Order no.')?>:</td>
						<td class="value"><?php echo intval($this->orderRecord->id);?></td>
					</tr>
					<tr class="order-date">
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
					<?php if ($this->orderRecord->store_id) { ?>
						<tr class="store-id">
							<td class="key"><?php echo KText::_('Store ID')?>:</td>
							<td class="value"><?php echo hsc($this->orderRecord->store_id);?></td>
						</tr>
					<?php } ?>
				</table>
				
			</div>
		</td>
	</tr>
</table>


<h1 class="document-title"><?php echo KText::_( 'Manufacturing Slip' );?></h1>

<div class="products-overview">
	<h2><?php echo KText::_('Product Configuration');?></h2>
	<div class="positions">
		<?php
		foreach ($this->orderRecord->positions as $position) {
			echo ConfigboxPositionHelper::getPositionHtml($this->orderRecord, $position, 'quotation');
		}
		?>
		<div class="clear"></div>
	</div>
</div>



</div></div>
</body>
</html><?php //die();
