<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>
<div id="question-description-<?php echo intval($this->question->id);?>" class="modal question-description-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo KText::_('Close');?>"><span aria-hidden="true">&times;</span></button>
			<div class="modal-body"><?php echo $this->question->description;?></div>
		</div>
	</div>
</div>