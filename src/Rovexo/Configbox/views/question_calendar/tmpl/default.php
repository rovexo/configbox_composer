<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Calendar */
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

				<div class="form-control-static pseudo-text-field"><?php echo hsc($this->outputValue);?></div>

				<span class="input-group-addon trigger-show-calendar">
					<span class="glyphicon glyphicon-calendar" title="<?php echo KText::_('Change Date');?>"></span>
				</span>

			</div>

			<input style="display:block;width:0;height:0;border:none;margin:0;padding:0;" value="<?php echo hsc($this->outputValue);?>" type="text" id="output-helper-<?php echo hsc($this->question->id);?>" />
			<input style="display:block;width:0;height:0;border:none;margin:0;padding:0;" value="<?php echo hsc($this->selection);?>" type="text" id="input-<?php echo hsc($this->question->id);?>" />

			<span class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</span>

		</div>

	</div>

</div>