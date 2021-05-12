<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Radiobuttons */
?>

<?php
$choices = explode("\n", $this->question->choices);
foreach ($choices as &$choice) {
	$choice = trim($choice);
}
unset($choice);

$choiceMade = trim($this->selection);

$customValue = '';

if ($choiceMade != '') {
	// If the entered text isn't one of the choices, but there is a custom field, make sure it's filled in right
	if (array_search($choiceMade, $choices) === false && array_search('custom', $choices)) {
		$customValue = $choiceMade;
		$choiceMade  = 'custom';
	}
}
?>

<div id="<?php echo hsc($this->questionCssId);?>" class="<?php echo hsc($this->questionCssClasses);?>" <?php echo $this->questionDataAttributes;?>>

	<?php echo $this->getViewOutput('question_edit_buttons');?>

	<?php echo $this->getViewOutput('question_heading');?>

	<div class="answers">

		<?php echo $this->getViewOutput('question_decoration');?>

		<div class="form-group">

			<?php foreach ($choices as $key=>$choice) { ?>

				<div class="radio<?php echo ($choiceMade == $choice) ? ' selected' : '';?>">

					<label>

						<input
							class="configbox-choice-field"
							data-choice="<?php echo $choice;?>"
							<?php echo ($this->question->disableControl) ? 'disabled="disabled"' : '';?>
							<?php echo ($this->question->isValidValue($choice) !== true) ? 'disabled="disabled"' : '';?>
							<?php echo ($choiceMade == $choice) ? ' checked="checked" ':' ';?>
							type="radio"
							id="choice-<?php echo intval($this->question->id).'-'.$key;?>"
							name="choice-<?php echo intval($this->question->id);?>"
							value="<?php echo hsc($choice);?>"
						/>

						<?php if (trim($choice) == 'custom') { ?>
							<input class="configbox-choice-custom-field" type="text" value="<?php echo hsc($customValue);?>" <?php echo ($this->question->disableControl) ? 'disabled="disabled"' : '';?> />
						<?php } else { ?>
							<span class="answer-title"><?php echo hsc($choice);?></span>
						<?php } ?>

					</label>

				</div>

			<?php } ?>

			<div class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</div>

		</div>
	</div>
</div>