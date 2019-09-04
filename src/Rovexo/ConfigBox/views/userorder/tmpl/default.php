<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUserorder */
?>
<div <?php echo $this->getViewAttributes();?>>
	
	<h1 class="page-title page-title-userorder-page"><?php echo KText::sprintf('Order ID %s', intval($this->orderRecord->id))?></h1>

	<div class="subsection-wrapper wrapper-order-address">
		<h2 class="subsection-title"><?php echo KText::_('Address');?></h2>
		<?php $this->renderView('orderaddress');?>
	</div>

	<div class="subsection-wrapper wrapper-order-details">
		<h2 class="subsection-title"><?php echo KText::_('Details');?></h2>
		<?php echo $this->orderRecordHtml; ?>
	</div>

	<div class="subsection-wrapper wrapper-order-status">
		<h2 class="subsection-title"><?php echo KText::_('Order Status');?></h2>
		<div class="order-status">
			<p><?php echo KText::sprintf('Status of your order is %s.','<b>'.$this->orderStatusString.'</b>');?></p>
		</div>
	</div>

	<div class="clear"></div>

	<?php $this->renderView('buttons');?>

</div>
