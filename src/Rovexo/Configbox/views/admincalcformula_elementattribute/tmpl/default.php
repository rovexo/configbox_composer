<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdmincalcformula_elementattribute
 */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<div id="element-picker">

		<h2><?php echo KText::_('Pick a question');?></h2>

		<div class="product-filter">
			<?php echo $this->productFilterHtml;?>
		</div>

		<div class="page-filters">
			<?php foreach ($this->pageFilterDropdowns as $productId => $pageFilter) { ?>
				<div class="page-filter page-filter-<?php echo intval($productId);?>" style="display: <?php echo ($productId == $this->selectedProductId) ? 'block':'none';?>">
					<?php echo $pageFilter;?>
				</div>
			<?php } ?>
		</div>

		<input type="text" name="element-filter" id="element-filter" autocomplete="off" placeholder="<?php echo ($this->useInternalQuestionNames) ? KText::_('Question internal name') : KText::_('Filter by question title');?>" />

		<ul class="element-list">
			<?php
			foreach ($this->questions as $question) {
				if ($question->product_id == $this->selectedProductId && ($this->selectedPageId == 0 || $question->page_id == $this->selectedPageId)) $displayCssClass = 'shown';
				else $displayCssClass = '';
				?>
				<li class="product-<?php echo $question->product_id;?> page-<?php echo $question->page_id;?> <?php echo $displayCssClass;?>" id="element-<?php echo $question->id;?>"><span><?php echo hsc($question->title);?></span></li>
				<?php
			}
			?>
		</ul>

		<ul class="element-list">
			<li class="shown" id="element-regarding">
				<span><?php echo KText::_('Regarding Question');?><?php echo KenedoHtml::getTooltip('<span class="fa fa-question-circle"></span>', KText::_('HELP_TEXT_REGARDING_QUESTION'));?></span>
			</li>
		</ul>

	</div>

	<div id="element-attributes">

		<h2><?php echo KText::_('Drag conditions into the rule area');?></h2>

		<ul class="conditions-list">
			<?php foreach ($this->questions as $questionId => $question) { ?>

				<li class="xref-group" id="xref-group-<?php echo intval($questionId);?>">
					<ul class="conditions-list xref-list">

						<?php

						// Loop through the element's attributes that are usable for conditions
						foreach ($this->elementAttributes as $fieldPath => $elementAttribute) {

							// 'selected' is the text entry for free entry elements
							if ($question->answer_count > 0  && $fieldPath == 'selected') continue;
							// selectedOption.* is for elements with options
							if ($question->answer_count == 0 && strstr($fieldPath, 'selectedOption')) continue;

							$termData = array(
								'type'=>'ElementAttribute',
								'elementId' => $questionId,
								'fieldPath' => $fieldPath,
								'fallbackValue' => '',
							);

							echo '<li>';
							echo ConfigboxCalcTerm::getTerm('ElementAttribute')->getTermHtml($termData);
							echo '</li>';

						}

						?>
					</ul>
				</li>
			<?php } ?>

			<li class="xref-group" id="xref-group-regarding">
				<ul class="conditions-list xref-list">

					<?php
					foreach ($this->elementAttributes as $fieldPath => $elementAttribute) {
						$termData = array(
						'type'=>'ElementAttribute',
						'elementId' => 'regarding',
						'fieldPath' => $fieldPath,
						'fallbackValue' => '',
						);

						echo '<li>';
						echo ConfigboxCalcTerm::getTerm('ElementAttribute')->getTermHtml($termData);
						echo '</li>';
					}
					?>
				</ul>
			</li>

		</ul>
	</div>
</div>

