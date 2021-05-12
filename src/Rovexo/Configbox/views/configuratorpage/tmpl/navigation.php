<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewConfiguratorpage
 */
?>
<div class="page-navigation row">
	<div class="page-navigation-cart col-md-6">
		<?php if ($this->showNextButton == true && $this->showFinishButton == true) { ?>
			<a rel="nofollow" class="btn btn-primary <?php echo $this->finishButtonClasses;?>"><?php echo KText::_('Add to cart');?></a>
		<?php } ?>
	</div>

	<div class="page-navigation-pages col-md-6">

		<?php if ($this->showPrevButton == true) { ?>
			<a rel="prev"
			   class="btn btn-default <?php echo $this->prevButtonClasses;?>"
			   data-page-id="<?php echo intval($this->prevPage->id);?>"
			   href="<?php echo $this->prevPage->url;?>"><?php echo KText::_('Back')?></a>
		<?php } ?>

		<?php if ($this->showNextButton == true) { ?>
			<a rel="next"
			   class="btn btn-default <?php echo $this->nextButtonClasses;?>"
			   data-page-id="<?php echo intval($this->nextPage->id);?>"
			   href="<?php echo $this->nextPage->url;?>"><?php echo KText::_('Next')?></a>
		<?php } ?>

		<?php if ($this->showFinishButton == true && $this->showNextButton == false) { ?>
			<a rel="nofollow" class="btn btn-primary <?php echo $this->finishButtonClasses;?>"><?php echo KText::_('Add to cart');?></a>
		<?php } ?>

	</div>

</div>