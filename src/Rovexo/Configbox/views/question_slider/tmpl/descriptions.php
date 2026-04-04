<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Slider */
?>
<?php if ($this->showLabel) { ?>

	<label for="input-<?php echo hsc($this->questionCssId);?>">

		<?php echo hsc($this->question->title);?>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 1) { ?>
			<div class="question-description"><?php echo $this->question->description;?></div>
		<?php } ?>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 2) { ?>
			<button class="pull-right cb-popover"
					aria-label="<?php echo KText::_('Details');?>"
					tabindex="0"
					data-bs-toggle="popover"
					data-bs-trigger="focus"
					data-bs-placement="top"
					data-bs-html="true"
					data-bs-content="<?php echo hsc($this->question->description);?>">
				<i class='fa fa-info-circle'></i>
			</button>
		<?php } ?>

		<?php if (!empty($this->question->description) && $this->question->desc_display_method == 3) { ?>
			<button class="question-modal-icon"
					aria-label="<?php echo KText::_('Details');?>"
					data-bs-toggle="modal"
					data-bs-target="#question-description-<?php echo intval($this->question->id);?>">
				<i class="fa fa-info-circle"></i>
			</button>
			<?php echo $this->getViewOutput('question_desc_modal');?>
		<?php } ?>

	</label>

<?php } ?>