<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Colorpicker */
?>
<div id="<?php echo hsc($this->questionCssId);?>" class="<?php echo hsc($this->questionCssClasses);?>" <?php echo $this->questionDataAttributes;?>>

	<?php echo $this->getViewOutput('question_edit_buttons');?>

	<?php echo $this->getViewOutput('question_heading');?>

	<div class="answers">

		<?php echo $this->getViewOutput('question_decoration');?>

		<div class="form-group">

			<?php if ($this->showLabel) { ?>
				<label for="input-<?php echo hsc($this->question->id);?>"><?php echo hsc($this->question->title);?></label>
			<?php } ?>

			<div class="input-group">

				<div class="form-control-static pseudo-text-field color-picker-output" style="background-color: <?php echo hsc($this->selection);?>"></div>

				<span class="input-group-addon trigger-show-colorpicker">
					<span class="fa fa-eyedropper" title="<?php echo KText::_('Change Color');?>"></span>
				</span>

			</div>

			<div class="wrapper-flat-spectrum">
				<input class="spectrum-input" value="<?php echo hsc($this->selection);?>" />
			</div>

			<span class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</span>

		</div>

	</div>

</div>