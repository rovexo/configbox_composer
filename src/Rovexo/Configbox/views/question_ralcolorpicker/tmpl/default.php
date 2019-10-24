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
					class="form-control-static pseudo-text-field ral-color-picker-output <?php if(in_array($this->selectedColorId, $this->ralColorsDark)) echo 'is-dark '; ?>"
					style="background-color: <?php echo isset($this->ralColors[$this->selectedColorId]) ? $this->ralColors[$this->selectedColorId]['hex'] : '#ffffff';?>"><?php
						if(isset($this->ralColors[$this->selectedColorId])){
							echo KText::_('RAL').' '.$this->selectedColorId . ' ' . KText::_('RAL_'.$this->selectedColorId) ;
						}
					?></div>

				<span class="input-group-addon trigger-show-ralcolorpicker">
					<span class="fa fa-eyedropper" title="<?php echo KText::_('Change RAL color');?>"></span>
				</span>

			</div>

			<input class="ral-color-input" value="<?php echo hsc($this->selection);?>" />

			<span class="help-block validation-message-target">
				<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
			</span>

		</div>

	</div>

	<div id="ralcolor-modal-<?php echo hsc($this->question->id);?>" data-id="<?php echo hsc($this->question->id);?>" class="modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<a class="close-modal"><?php echo KText::_('CLOSE'); ?></a>
				<h3><?php echo KText::_('Change RAL color');?></h3>
				<div class="ral-color-groups">
					<?php echo KenedoHtml::getSelectField(
							'ral-color-group',
							$this->ralColorGroups,
							0,
							0,
							false,
							'ral-color-group');?>
					<span class="ral-color-group-label"><?php echo KText::_('Color Group');?></span>
				</div>
				<div class="ral-color-group-colors">
					<?php
					foreach($this->ralColors as $key => $color){ ?>
							<div
									class="ral-color"
									data-group-id="<?php echo substr($color['code'], 0,1);?>"
									data-color-id="<?php echo $color['code'];?>"
									data-hex="<?php echo $color['hex'];?>"
									style="background-color: <?php echo $color['hex'];?>;">
										<a class="<?php if(in_array($color['code'], $this->ralColorsDark)) echo 'is-dark '; ?> ral-color-item"><?php echo KText::_('RAL').' '.$color['code'].' '.KText::_('RAL_' . $color['code']);?></a>
							</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

</div>