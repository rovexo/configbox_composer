<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Textbox */
?>
<div id="<?php echo hsc($this->questionCssId);?>" class="<?php echo hsc($this->questionCssClasses);?>" <?php echo $this->questionDataAttributes;?>>

	<?php echo $this->getViewOutput('question_edit_buttons');?>

	<?php echo $this->getViewOutput('question_heading');?>

	<div class="answers">

		<?php echo $this->getViewOutput('question_decoration');?>

		<div class="form-group">

			<?php if ($this->showLabel) { ?>

				<label for="input-<?php echo hsc($this->questionCssId);?>">

					<?php echo hsc($this->question->title);?>

					<?php if (!empty($this->question->description) && $this->question->desc_display_method == 1) { ?>
						<div class="question-description"><?php echo $this->question->description;?></div>
					<?php } ?>

					<?php if (!empty($this->question->description) && $this->question->desc_display_method == 2) { ?>
						<span class="fa fa-info-circle cb-popover question-popover" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="popover" data-placement="left" data-content="<?php echo hsc($this->question->description);?>"></span>
					<?php } ?>

					<?php if (!empty($this->question->description) && $this->question->desc_display_method == 3) { ?>
						<span class="fa fa-info-circle question-modal-icon" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="modal" data-target="#question-description-<?php echo intval($this->question->id);?>"></span>
						<?php echo $this->getViewOutput('question_desc_modal');?>
					<?php } ?>

				</label>

			<?php } ?>

			<div class="wrapper-input">

				<?php if ($this->question->unit) { ?><div class="input-group"><?php } ?>

					<input value="<?php echo hsc($this->selection);?>" type="text" id="input-<?php echo hsc($this->questionCssId);?>" class="form-control" aria-label="<?php echo hsc($this->question->title);?>" <?php echo ($this->question->disableControl) ? 'disabled="disabled"' : '';?>>

					<?php if ($this->question->unit) { ?>
						<span class="input-group-append"><span class="input-group-text"><?php echo hsc($this->question->unit);?></span></span>
					<?php } ?>

					<?php if ($this->question->unit) { ?></div><?php } ?>

			</div>

			<div class="wrapper-slider">
				<div id="cb-slider-<?php echo intval($this->question->id);?>" class="configbox-slider"></div>
			</div>

			<span class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</span>

		</div>

	</div>

</div>