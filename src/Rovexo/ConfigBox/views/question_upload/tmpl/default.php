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

			<div class="upload-drop-zone">
				<div class="drop-zone-note-pre"><?php echo KText::_('Drop your file here');?></div>
				<div class="help-block validation-message-target">
					<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
				</div>
				<button class="trigger-show-file-browser btn btn-default" <?php echo ($this->question->disableControl) ? ' disabled="disabled"':'';?>><?php echo KText::_('Browse');?></button>
				<input class="fallback-input" style="display: none" type="file" />
			</div>

			<div class="upload-current-file<?php echo ($this->selection) ? ' has-file' : '';?>">
				<div class="no-file-text"><?php echo KText::_('No file uploaded yet.');?></div>
				<div class="file-list">
					<a class="fa fa-times trigger-remove-file"></a><span class="file-name"><?php echo $this->outputValue;?></span>
				</div>
			</div>

		</div>

	</div>

</div>