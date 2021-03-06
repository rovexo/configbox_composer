<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Checkbox */
$answer = reset($this->question->answers);
?>
<div id="<?php echo hsc($this->questionCssId);?>" class="<?php echo hsc($this->questionCssClasses);?>" <?php echo $this->questionDataAttributes;?>>

	<?php echo $this->getViewOutput('question_edit_buttons');?>

	<?php echo $this->getViewOutput('question_heading');?>

	<div class="answers">

		<div class="help-block validation-message-target">
			<?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : '';?>
		</div>

		<div class="form-group">

			<div id="<?php echo hsc($answer->cssId);?>" class="checkbox <?php echo hsc($answer->cssClasses);?>">

				<?php echo $this->getViewOutput('question_decoration');?>

				<label>

					<input
						name="question-<?php echo intval($this->question->id);?>"
						id="answer-input-<?php echo intval($answer->id);?>"
						value="<?php echo intval($answer->id);?>"
						type="checkbox"
						<?php echo ($answer->isSelected) ? 'checked="checked"' : '';?>
						<?php echo ($answer->disableControl) ? 'disabled="disabled"' : '';?>
					/>

					<span class="answer-title"><?php echo hsc($answer->title);?></span>

				</label>

				<?php if ($this->canQuickEdit) echo ConfigboxQuickeditHelper::getAnswerEditButtons($answer);?>

				<?php if ($this->showPricing) { ?>
					<span class="answer-price-display">
						<span class="answer-price-wrapper" <?php echo ($answer->price == 0) ? 'style="display:none"':'';?>>
							<?php if ($answer->was_price != 0) { ?>
								<span class="answer-was-price answer-was-price-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->was_price);?></span>
							<?php } ?>
							<span class="answer-price answer-price-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->price);?></span>
							<span class="answer-price-label answer-price-label-<?php echo intval($answer->id);?>"><?php echo hsc($this->priceLabel);?></span>
						</span>

						<span class="answer-price-recurring-wrapper" <?php echo ($answer->price_recurring == 0) ? 'style="display:none"':'';?>>
							<?php if ($answer->was_price_recurring != 0) { ?>
								<span class="answer-was-price-recurring answer-was-price-recurring-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->was_price_recurring);?></span>
							<?php } ?>
							<span class="answer-price-recurring answer-price-recurring-<?php echo intval($answer->id);?>"><?php echo cbprice($answer->price_recurring);?></span>
							<span class="answer-price-recurring-label answer-price-recurring-label-<?php echo intval($answer->id);?>"><?php echo hsc($this->priceLabelRecurring);?></span>
						</span>
					</span>
				<?php } ?>

				<?php if ($answer->description) { ?>

					<?php if ($answer->desc_display_method == 'tooltip') { ?>
						<a class="fa fa-info-circle cb-popover"
						   aria-label="<?php echo KText::_('Details');?>"
						   role="button"
						   tabindex="0"
						   data-toggle="popover"
						   data-trigger="hover"
						   data-placement="top"
						   data-html="true"
						   data-content="<?php echo hsc($answer->description);?>"></a>
					<?php } ?>

					<?php if ($answer->desc_display_method == 'modal') { ?>

						<a class="fa fa-info-circle"
						   aria-label="<?php echo KText::_('Details');?>"
						   role="button"
						   data-toggle="modal"
						   data-target="#answer-description-<?php echo intval($answer->id);?>"></a>

						<div id="answer-description-<?php echo intval($answer->id);?>" class="modal answer-description-modal" tabindex="-1" aria-hidden="true">
							<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
								<div class="modal-content">
									<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo KText::_('Close');?>"><span aria-hidden="true">&times;</span></button>
									<div class="modal-body"><?php echo $answer->description;?></div>
								</div>
							</div>
						</div>

					<?php } ?>

				<?php } ?>

				<?php if ($answer->showAvailibilityInfo) { ?>
					<span class="xref-available"><?php echo hsc($answer->availibility_date);?></span>
				<?php } ?>

			</div>
		</div>
	</div>
</div>