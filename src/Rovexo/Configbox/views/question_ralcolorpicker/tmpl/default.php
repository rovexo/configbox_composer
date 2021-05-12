<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Ralcolorpicker */
?>
<div id="<?php echo hsc($this->questionCssId);?>" class="<?php echo hsc($this->questionCssClasses);?>" data-selection-group-id="<?php echo $this->selectedColorGroupId;?>" <?php echo $this->questionDataAttributes;?>>

	<?php echo $this->getViewOutput('question_edit_buttons');?>

	<?php echo $this->getViewOutput('question_heading');?>

	<div class="answers">

		<?php echo $this->getViewOutput('question_decoration');?>
		<div class="form-group">

			<?php if ($this->showLabel) { ?>
				<label for="input-<?php echo hsc($this->question->id);?>"><?php echo hsc($this->question->title);?></label>
			<?php } ?>

			<div class="input-group">

				<div
					class="form-control pseudo-text-field ral-color-picker-output <?php if(in_array($this->selectedColorId, $this->ralColorsDark)) echo 'is-dark '; ?>"
					style="background-color: <?php echo isset($this->ralColors[$this->selectedColorId]) ? $this->ralColors[$this->selectedColorId]['hex'] : '#ffffff';?>"><?php
						if(isset($this->ralColors[$this->selectedColorId])){
							echo KText::_('RAL').' '.$this->selectedColorId . ' ' . KText::_('RAL_'.$this->selectedColorId) ;
						}
					?></div>

				<span class="input-group-append trigger-show-ralcolorpicker">
					<span class="input-group-text fas fa-eye-dropper" title="<?php echo KText::_('Change RAL color');?>"></span>
				</span>

			</div>

			<input class="ral-color-input" value="<?php echo hsc($this->selection);?>" />

			<div class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</div>

		</div>

	</div>

	<div id="ralcolor-modal-<?php echo hsc($this->question->id);?>" data-id="<?php echo hsc($this->question->id);?>" class="modal" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<a class="close-modal"><?php echo KText::_('CLOSE'); ?></a>
				<div class="modal-header">
					<h3><?php echo KText::_('Change RAL color');?></h3>
					<div class="ral-color-groups">
						<?php echo KenedoHtml::getSelectField(
							'ral-color-group',
							$this->ralColorGroups,
							0,
							0,
							false,
							'ral-color-group');?>
						<label for="ral-color-group" class="sr-only"><?php echo KText::_('Color Group');?></label>
					</div>
				</div>
				<div class="modal-body">
					<div class="ral-color-group-colors">
						<?php foreach($this->ralColors as $key => $color) { ?>
							<a class="trigger-pick-ral-color ral-color <?php echo (in_array($color['code'], $this->ralColorsDark)) ? 'is-dark':'is-light'; ?>"
							   data-group-id="<?php echo hsc(substr($color['code'], 0,1));?>"
							   data-color-id="<?php echo hsc($color['code']);?>"
							   data-hex="<?php echo hsc($color['hex']);?>"
							   style="background-color: <?php echo hsc($color['hex']);?>;">
								<?php echo hsc(KText::_('RAL').' '.$color['code'].' '.KText::_('RAL_' . $color['code']));?>
							</a>
						<?php } ?>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>