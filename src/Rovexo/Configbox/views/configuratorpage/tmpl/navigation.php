<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewConfiguratorpage
 */
?>
<div class="page-navigation row">

	<div class="page-navigation-cart col-sm-6">
		<?php if ($this->showNextButton == true && $this->showFinishButton == true) { ?>
			<a rel="nofollow" class="btn btn-primary <?php echo $this->finishButtonClasses;?>"><?php echo KText::_('Add to cart');?></a>
		<?php } ?>
	</div>

	<div class="page-navigation-pages col-sm-6">

		<?php if ($this->showPrevButton == true) { ?>
			<a rel="prev" class="btn btn-default <?php echo $this->prevButtonClasses;?>" href="<?php echo $this->prevPage->url;?>"><?php echo KText::_('Back')?></a>
		<?php } ?>

		<?php if ($this->showNextButton == true) { ?>
			<a rel="next" class="btn btn-default <?php echo $this->nextButtonClasses;?>" href="<?php echo $this->nextPage->url;?>"><?php echo KText::_('Next')?></a>
		<?php } ?>

		<?php if ($this->showFinishButton == true && $this->showNextButton == false) { ?>
			<a rel="nofollow" class="btn btn-primary <?php echo $this->finishButtonClasses;?>"><?php echo KText::_('Add to cart');?></a>
		<?php } ?>

	</div>

</div>