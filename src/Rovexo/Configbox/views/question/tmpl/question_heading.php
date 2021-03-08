<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>

<?php if ($this->showHeading) { ?>

	<div class="question-heading">

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 2) { ?>
			<span class="pull-right fa fa-info-circle cb-popover question-popover" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="popover" data-placement="left" data-content="<?php echo hsc($this->question->description);?>"></span>
		<?php } ?>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 3) { ?>
			<span class="pull-right fa fa-info-circle question-modal-icon" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="modal" data-target="#question-description-<?php echo intval($this->question->id);?>"></span>
			<?php echo $this->getViewOutput('question_desc_modal');?>
		<?php } ?>

		<h2 class="question-title"><?php echo hsc($this->question->title);?></h2>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 1) { ?>
			<div class="question-description"><?php echo $this->question->description;?></div>
		<?php } ?>

	</div>

<?php } ?>

