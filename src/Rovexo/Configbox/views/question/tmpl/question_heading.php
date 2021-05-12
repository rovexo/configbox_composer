<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>

<?php if ($this->showHeading) { ?>

	<div class="question-heading">

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 2) { ?>
			<a class="pull-right fa fa-info-circle cb-popover"
			   aria-label="<?php echo KText::_('Details');?>"
			   role="button"
			   tabindex="0"
			   data-toggle="popover"
			   data-trigger="hover"
			   data-placement="top"
			   data-html="true"
			   data-content="<?php echo hsc($this->question->description);?>"></a>
		<?php } ?>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 3) { ?>
			<span class="pull-right fa fa-info-circle question-modal-icon" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="modal" data-target="#question-description-<?php echo intval($this->question->id);?>"></span>
			<?php echo $this->getViewOutput('question_desc_modal');?>
		<?php } ?>

		<h2 class="question-title"><?php echo hsc($this->question->title);?></h2>

	</div>

<?php } ?>

<?php if (!empty($this->question->description) && $this->question->desc_display_method == 1) { ?>
	<div class="question-description"><?php echo $this->question->description;?></div>
<?php } ?>