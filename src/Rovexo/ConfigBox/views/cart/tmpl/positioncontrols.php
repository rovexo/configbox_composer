<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>
<div class="position-controls">
	<?php if ($this->canEditOrder) { ?>

		<a rel="nofollow" class="btn btn-default button-remove-product trigger-ga-track-remove-position" data-position-id="<?php echo intval($this->position->id);?>" href="<?php echo $this->positionUrls[$this->position->id]['urlRemove'];?>"><?php echo KText::_('Remove');?></a>

		<?php if ($this->position->isConfigurable) { ?>
			<a rel="nofollow" class="btn btn-default button-edit-product" href="<?php echo $this->positionUrls[$this->position->id]['urlEdit'];?>"><?php echo KText::_('Change');?></a>
			<a rel="nofollow" class="btn btn-default button-copy-product" href="<?php echo $this->positionUrls[$this->position->id]['urlCopy'];?>"><?php echo KText::_('Copy');?></a>
		<?php } ?>

		<a class="trigger-close-modal btn btn-primary"><?php echo KText::_('Close');?></a>

	<?php } ?>
</div>