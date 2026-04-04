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
				<label for="input-display-<?php echo hsc($this->question->id);?>"><?php echo hsc($this->question->title);?></label>
			<?php } ?>

			<div class="input-group">

				<input type="text"
                       readonly
                       class="form-control trigger-show-calendar pseudo-text-field"
                       id="input-display-<?php echo hsc($this->question->id);?>"
                       value="<?php echo hsc($this->outputValue);?>" />

                <button class="btn btn-outline-secondary trigger-show-calendar"
                        type="button"
                        title="<?php echo KText::_('Change Date');?>">
                    <span class="fa fa-calendar"></span>
                </button>

			</div>

			<input style="display:block;width:0;height:0;border:none;margin:0;padding:0;"
                   value="<?php echo hsc($this->outputValue);?>"
                   type="text"
                   id="output-helper-<?php echo hsc($this->question->id);?>" />
            <label for="output-helper-<?php echo hsc($this->question->id);?>" class="sr-only"></label>

			<div id="input-<?php echo hsc($this->question->id);?>" data-selection="<?php echo hsc($this->selection);?>"></div>

			<div class="invalid-feedback validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</div>

		</div>

	</div>

</div>