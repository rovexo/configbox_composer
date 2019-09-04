<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewAttributes;?>>

	<?php if ($this->showPageHeading) { ?>
		<h1 class="page-title"><?php echo hsc($this->pageHeading);?></h1>
	<?php } ?>

	<div class="wrapper-cart-summary">
		<?php $this->renderView('summary'); ?>
	</div>

	<?php $this->renderView('cartbuttons'); ?>

	<div class="wrapper-checkout-view"></div>

</div>

