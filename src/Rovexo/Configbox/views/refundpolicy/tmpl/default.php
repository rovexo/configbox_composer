<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewRefundpolicy */
?>
<div <?php echo $this->getViewAttributes();?>>
	<h1 class="page-heading"><?php echo hsc(KText::_('Refund Policy'));?></h1>
	<?php echo $this->refundPolicy;?>
</div>