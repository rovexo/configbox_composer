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
				<label for="input-<?php echo hsc($this->questionCssId);?>"><?php echo hsc($this->question->title);?></label>
			<?php } ?>

			<textarea id="input-<?php echo hsc($this->questionCssId);?>" class="form-control" aria-label="<?php echo hsc($this->question->title);?>" <?php echo ($this->question->disableControl) ? 'disabled="disabled"' : '';?>><?php echo hsc($this->selection);?></textarea>

			<div class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</div>

		</div>

	</div>

</div>