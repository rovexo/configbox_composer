<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdminRuleeditor_elementattribute
 */
?>

<div <?php echo $this->getViewAttributes();?>>
	<div class="row">

		<div class="question-picker col-4 col-lg-3">

			<div class="note-pick-question"><?php echo KText::_('Pick a question to see related conditions');?></div>

			<?php if ($this->showQuestionFilters) { ?>
				<div class="page-filter"><?php echo $this->pageFilterHtml;?></div>
				<input type="text" id="question-filter" autocomplete="off" placeholder="<?php echo (CbSettings::getInstance()->get('use_internal_question_names')) ? KText::_('Internal name') : KText::_('Filter by title');?>" />
			<?php } ?>



			<ul class="question-list">
				<?php
				foreach ($this->questions as $questionId => $question) {
					$displayCssClass = ($this->showQuestionFilters == false || empty($this->selectedPageId) || $question->page_id == $this->selectedPageId) ? 'shown':'';
					?>
					<li class="page-<?php echo $question->page_id;?> <?php echo $displayCssClass;?>" data-question-id="<?php echo intval($question->id);?>"><span><?php echo hsc($question->title);?></span></li>
					<?php
				}
				?>
			</ul>

		</div>

		<div id="question-attributes" class="col-8 col-lg-9">

			<div class="note-drag-conditions"><?php echo KText::_('RULE_EDITOR_HELP_CONDITIONS');?></div>

			<div class="conditions-list">
				<?php foreach ($this->questions as $questionId => $question) { ?>

					<?php
					$questionAttributes = $this->questionAttributes;
					?>

					<div class="answer-group" id="answer-group-<?php echo intval($questionId);?>">
						<div class="row">

							<div class="predefined-answers col-md-6">
								<?php if (isset($this->questionAnswers[$questionId])) { ?>

									<h3 class="conditions-group-heading"><?php echo KText::_('Related to answers');?></h3>
									<ul class="conditions-list">
										<?php
										// Loop through all answers of the question
										foreach ($this->questionAnswers[$questionId] as $answer) {

											$conditionData = array(
												'type'=>'ElementAttribute',
												'elementId' => $questionId,
												'field' => 'selectedOption.id',
												'operator' => '==',
												'value' => $answer->id,
											);

											echo '<li>'.ConfigboxCondition::getCondition('ElementAttribute')->getConditionHtml($conditionData).'</li>';

										}

										// Do the 'no answer' bit now
										$conditionData = array(
											'type'=>'ElementAttribute',
											'elementId' => $questionId,
											'field' => 'selectedOption.id',
											'operator' => '==',
											'value' => '',
										);

										echo '<li>'.ConfigboxCondition::getCondition('ElementAttribute')->getConditionHtml($conditionData).'</li>';

										?>
									</ul>
									<?php
								}
								else {
									?>
									<h3 class="conditions-group-heading"><?php echo KText::_('Related to answers');?></h3>
									<ul class="conditions-list text-field-answers">
										<?php
										// Do the text field answer condition
										$conditionData = array(
											'type'=>'ElementAttribute',
											'elementId' => $questionId,
											'field' => 'selected',
											'operator' => '==',
											'value' => '',
										);

										echo '<li>'.ConfigboxCondition::getCondition('ElementAttribute')->getConditionHtml($conditionData).'</li>';

										?>
									</ul>
									<?php
								}

								?>

								<?php
								// Remove selectedOption.id and selected, we'll be dealing with price attributes and custom fields from now on
								unset($questionAttributes['selectedOption.id'], $questionAttributes['selected']);
								?>

								<h3 class="conditions-group-heading"><?php echo KText::_('Related to pricing');?></h3>
								<ul class="conditions-list element-prices">
									<?php
									// Do just price and price Recurring
									foreach ($questionAttributes as $fieldPath => $questionAttribute) {
										// Do only price and priceRecurring, skip anything else
										if ($fieldPath == 'price' || $fieldPath == 'priceRecurring') {
											$conditionData = array(
												'type'=>'ElementAttribute',
												'elementId' => $questionId,
												'field' => $fieldPath,
												'operator' => '==',
												'value' => '',
											);
											echo '<li>'.ConfigboxCondition::getCondition('ElementAttribute')->getConditionHtml($conditionData).'</li>';
										}
									}
									// Unset both, these are done
									unset($questionAttributes['price'], $questionAttributes['priceRecurring']);
									?>
								</ul>
							</div>

							<div class="other-conditions col-md-6">
								<h3 class="conditions-group-heading"><?php echo KText::_('Related to the custom fields of the answer');?></h3>
								<ul class="conditions-list answer-list">
									<?php
									// Loop through the remaining attributes
									foreach ($questionAttributes as $fieldPath => $questionAttribute) {

										$conditionData = array(
											'type'=>'ElementAttribute',
											'elementId' => $questionId,
											'field' => $fieldPath,
											'operator' => '==',
											'value' => '',
										);

										echo '<li>'.ConfigboxCondition::getCondition('ElementAttribute')->getConditionHtml($conditionData).'</li>';

									}
									?>
								</ul>
							</div>

						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

