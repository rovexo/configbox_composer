<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmincalcformula */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<div id="calc-editor">

		<div id="buttons">

			<div class="floatright">
				<a class="button-limit-term-width backend-button-small"><?php echo KText::_('Limit term width');?></a>
				<a class="button-put-in-brackets backend-button-small"><?php echo KText::_('FORMULA_EDITOR_MAKE_PARENTHESES');?></a>
				<a class="button-remove-selected-items backend-button-small"><?php echo KText::_('Remove selected');?></a>
			</div>

		</div>

		<input type="hidden" id="calc" name="calc" value="<?php echo hsc($this->calcJson);?>" />

		<div id="compability-rule">
			<h2><?php echo KText::_('Calculation');?></h2>

			<div id="terms">

				<?php if (empty($this->calcJson)) { ?>
					<span class="drop-area initial"><?php echo KText::_('Drag a term from below into this area');?></span>
				<?php } else { ?>
					<?php echo ConfigboxCalculation::getCalculationHtml($this->calcJson, true);?>
				<?php } ?>

			</div>

		</div>

		<div id="item-picker">
			<h2><?php echo KText::_('Calculation Terms');?></h2>

			<div id="operator-blueprints">
				<?php
				foreach ($this->operatorData as $operatorData) {
					echo ConfigboxCalcTerm::getTerm('Operator')->getTermHtml($operatorData);
				}
				?>

			</div>

			<div id="custom-term-blueprints">
				<?php echo ConfigboxCalcTerm::getTerm('Number')->getTermHtml(array('type'=>'Number', 'value'=>'')); ?>
			</div>

			<div class="picker-tabs">
				<ul>
					<?php foreach ($this->termTabs as $typeName=>$tabTitle) { ?>
						<li class="picker-tab panel-<?php echo strtolower($typeName);?><?php echo ($typeName == $this->selectedTypeName) ? ' selected-tab':'';?>" id="panel-<?php echo strtolower($typeName);?>">
							<?php echo hsc($tabTitle);?>
						</li>
					<?php } ?>
				</ul>
			</div>

			<div class="picker-panels">
				<?php foreach ($this->termPanels as $typeName=>$content) { ?>
					<div class="panel panel-<?php echo strtolower($typeName);?><?php echo ($typeName == $this->selectedTypeName) ? ' selected-panel':'';?>">
						<?php echo $content;?>
					</div>
				<?php } ?>
				<div class="clear"></div>
			</div>


		</div>

		<div class="hidden-helpers">
			<div class="textfield-autosize"><span id="width-tester"></span></div>
		</div>

	</div>
</div>
</div>