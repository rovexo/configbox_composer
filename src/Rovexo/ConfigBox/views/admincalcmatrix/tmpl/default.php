<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmincalcmatrix */
?>

<?php 
ob_start();
?>
<div class="axis-parameter-picker">
	<div class="tabs">
		<div class="tab tab-open" id="tab-questions"><?php echo KText::_('Questions');?></div>
		<div class="tab tab-closed" id="tab-calculations"><?php echo KText::_('Calculations');?></div>
		<a class="trigger-disable-axis"><?php echo KText::_('Ignore this input');?></a>
	</div>
	<div class="panes">
		<div class="pane pane-open" id="pane-questions">
			<?php echo KenedoHtml::getSelectField('question_id', $this->questionTitles, NULL, 0, false, 'question-picker','');?>
		</div>
		<div class="pane" id="pane-calculations">
			<?php echo KenedoHtml::getSelectField('calculation_id', $this->calculations, NULL, 0, false, 'calculation-picker','');?>
		</div>
	</div>
</div>
<?php 
$pickerContent = ob_get_clean();
?>

<div <?php echo $this->getViewAttributes();?>>

	<table class="matrix-wrapper-table">

		<tr>
			<td></td>
			<td class="cell-axis-parameter cell-column-parameter">

				<span class="axis-label label-textfield" style="<?php echo ($this->columnType == 'question' && $this->columnUsesAnswers == false) ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Entry in %s', '<span class="parameter-title">'.(($this->columnQuestionId) ? hsc($this->questionTitles[$this->columnQuestionId]) : '').'</span>');?>
				</span>
				<span class="axis-label label-answers" style="<?php echo ($this->columnType == 'question' && $this->columnUsesAnswers == true) ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Selection for %s','<span class="parameter-title">'.(($this->columnQuestionId) ? hsc($this->questionTitles[$this->columnQuestionId]) : '').'</span>');?>
				</span>
				<span class="axis-label label-calculation" style="<?php echo ($this->columnType == 'calculation') ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Result of calculation %s','<span class="parameter-title">'.(($this->columnCalculationId) ? $this->calculations[$this->columnCalculationId] : '').'</span>');?>
				</span>
				<span class="axis-label label-none" style="<?php echo ($this->isNew == false && $this->columnType == 'none') ? 'display:inline;':'display:none;';?>">
					<?php echo KText::_('Ignored');?>
				</span>
				<span class="axis-label label-not-set" style="<?php echo ($this->isNew == true && $this->columnType == 'none') ? 'display:inline;':'display:none;';?>">
					<?php echo KText::_('No input parameter set');?>
				</span>

				<?php echo KenedoHtml::getTooltip(KText::_('change'), $pickerContent, 'top', 400, 400, NULL, NULL, 'column-parameter-picker');?>

			</td>
		</tr>

		<tr>
			<td class="cell-axis-parameter cell-row-parameter">

				<span class="axis-label label-textfield" style="<?php echo ($this->rowType == 'question' && $this->rowUsesAnswers == false) ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Entry in %s','<span class="parameter-title">'.(($this->rowQuestionId) ? hsc($this->questionTitles[$this->rowQuestionId]) : '').'</span>');?>
				</span>
				<span class="axis-label label-answers" style="<?php echo ($this->rowType == 'question' && $this->rowUsesAnswers) ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Selection for %s','<span class="parameter-title">'.(($this->rowQuestionId) ? hsc($this->questionTitles[$this->rowQuestionId]) : '').'</span>');?>
				</span>
				<span class="axis-label label-calculation" style="<?php echo ($this->rowType == 'calculation') ? 'display:inline;':'display:none;';?>">
					<?php echo KText::sprintf('Result of calculation %s','<span class="parameter-title">'.(($this->rowCalculationId) ? $this->calculations[$this->rowCalculationId] : '').'</span>');?>
				</span>
				<span class="axis-label label-none" style="<?php echo ($this->isNew == false && $this->rowType == 'none') ? 'display:inline;':'display:none;';?>">
					<?php echo KText::_('Ignored');?>
				</span>
				<span class="axis-label label-not-set" style="<?php echo ($this->isNew == true) ? 'display:inline;':'display:none;';?>">
					<?php echo KText::_('No input parameter set');?>
				</span>

				<br />

				<?php echo KenedoHtml::getTooltip(KText::_('change'), $pickerContent, 'top', 400, 400, NULL, NULL, 'row-parameter-picker');?>

			</td>
			<td width="10px">
				<table class="calc-matrix">
					<thead>
						<tr class="column-parameters">
							<th width="20px" class="dragtable-drag-boundary">
								<a class="toggle-matrix-tools" title="<?php echo KText::_('Show edit icons');?>"></a>
							</th>
							<?php
							foreach ($this->matrixValues as $y => $xValues) {

								foreach ($xValues as $x=>$value) {
									?>
									<th width="20px" class="column-parameter">
										<i class="dragtable-drag-handle column-sort-handle fa fa-reorder"></i>
										<?php if ($this->columnType == 'question' && $this->columnUsesAnswers) {?>
											<?php echo KenedoHtml::getSelectField('option-picker', $this->columnAnswers, $x, NULL, false, 'no-chosen');?>
										<?php } elseif($this->columnType == 'none') { ?>
											<input class="input-value" type="hidden" value="0" />
										<?php } else { ?>
											<input class="input-value" type="text" value="<?php echo floatval($x);?>" />
										<?php } ?>
										<span class="trigger-remove"></span>
									</th>
									<?php
								}
								break;
							}
							?>
						</tr>
					</thead>
					<?php foreach ($this->matrixValues as $y=>$xValues) { ?>
						<tr>
							<th width="20px"  class="row-parameter">
								<i class="row-sort-handle fa fa-reorder"></i>
								<?php if ($this->rowType == 'question' && $this->rowUsesAnswers) {?>
									<?php echo KenedoHtml::getSelectField('option-picker', $this->rowAnswers, $y, NULL, false, 'no-chosen');?>
								<?php } elseif($this->rowType == 'none') { ?>
									<input class="input-value" type="hidden" value="0" />
								<?php } else { ?>
									<input class="input-value" type="text" value="<?php echo floatval($y);?>" />
								<?php } ?>
								<span class="trigger-remove"></span>
							</th>
							<?php foreach ($xValues as $x=>$value) { ?>

								<?php
								$value = str_replace('.', KText::_('DECIMAL_MARK', '.'), floatval($value));
								?>
								<td width="20px" ><input class="price" type="text" value="<?php echo $value;?>" /></td>

							<?php } ?>
						</tr>
					<?php } ?>

				</table>
			</td>

			<td class="cell-trigger-add-column">
				<a class="trigger-add-column" style="display:<?php echo ($this->isNew == true || $this->columnType != 'none') ? 'block':'none';?>" title="<?php echo KText::_('Add a column');?>"></a>
			</td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td class="cell-trigger-add-row">
				<a class="trigger-add-row" style="display:<?php echo ($this->isNew == true || $this->rowType != 'none') ? 'block':'none';?>" title="<?php echo KText::_('Add a row');?>"></a>
			</td>
		</tr>

	</table>

	<input type="hidden" id="matrix" name="matrix" value="" />

	<div class="wrapper-spreadsheet-upload">
		<a class="trigger-show-file-browser"><?php echo KText::_('Import from a spreadsheet');?> <?php echo KenedoHtml::getTooltip('', KText::_('CALCMATRIX_SPREADSHEETINFO'));?></a>
		<input type="file" class="spreadsheet-upload-input" style="display:none;" />
	</div>

	<div class="kenedo-properties">

		<?php
		foreach($this->properties as $property) {
			echo $property->getPropertyFormOutput($this->record);
		}
		?>

	</div>
</div>