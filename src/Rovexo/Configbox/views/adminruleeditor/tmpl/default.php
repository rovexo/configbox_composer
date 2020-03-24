<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdminRuleeditor
 */
?>

<div <?php echo $this->getViewAttributes();?>>

	<div id="buttons">

		<div class="floatright">
			<a class="btn btn-default button-limit-condition-width"><?php echo KText::_('Limit condition width');?></a>
			<a class="btn btn-default button-put-in-brackets"><?php echo KText::_('RULE_EDITOR_MAKE_PARENTHESES');?></a>
			<a class="btn btn-default button-remove-selected-items"><?php echo KText::_('Remove selected');?></a>
			<a class="btn btn-default button-cancel"><?php echo KText::_('Cancel');?></a>
			<a class="btn btn-primary button-store"><?php echo KText::_('Save');?></a>
		</div>

	</div>

	<div class="editor-heading">
		<select class="rule-is-negated">
			<option value="0" data-prefix-rule-text="<?php echo hsc($this->ruleTextPrefixNormal);?>" <?php echo ($this->isNegatedRule == false) ? ' selected':'';?>><?php echo hsc($this->editorHeadingNormal);?></option>
			<option value="1" data-prefix-rule-text="<?php echo hsc($this->ruleTextPrefixNegated);?>"<?php echo ($this->isNegatedRule == true) ? ' selected':'';?>><?php echo hsc($this->editorHeadingNegated);?></option>
		</select>
	</div>

	<div class="rule-area">
		<?php if ($this->ruleIsSet) { ?>
			<?php echo $this->ruleHtml;?>
		<?php } else { ?>
			<span class="drop-area initial"><?php echo KText::_('Drag conditions from below into this area.');?></span>
		<?php } ?>
	</div>

		<div class="hint-combinators"><?php echo KText::_('Drag in combinators to combine conditions');?></div>

		<div id="combinator-blueprints">
			<span class="item combinator" data-type="combinator" data-kind="AND"><?php echo KText::_('AND');?></span>
			<span class="item combinator" data-type="combinator" data-kind="OR"><?php echo KText::_('OR');?></span>
		</div>


	<div id="condition-picker">

		<h3 class="heading-conditions"><?php echo KText::_('Condition Types');?></h3>

		<div class="picker-tabs">
			<ul>
				<?php foreach ($this->conditionTabs as $typeName=>$tabTitle) { ?>
					<li class="picker-tab panel-<?php echo strtolower($typeName);?><?php echo ($typeName == $this->selectedTypeName) ? ' selected-tab':'';?>" id="panel-<?php echo strtolower($typeName);?>">
						<?php echo hsc($tabTitle);?>
					</li>
				<?php } ?>
			</ul>
		</div>

		<div class="picker-panels">
			<?php foreach ($this->conditionPanels as $typeName=>$content) { ?>
				<div class="panel panel-<?php echo strtolower($typeName);?><?php echo ($typeName == $this->selectedTypeName) ? ' selected-panel':'';?>">
					<?php echo $content;?>
				</div>
			<?php } ?>
		</div>

	</div>

	<div id="operator-picker-blueprint">

		<div class="operator-picker operator-picker-full">
			<div data-operator="<?php echo htmlentities('<');?>" class="operator operator-less-than"><?php echo KText::_('is below');?></div>
			<div data-operator="<?php echo htmlentities('<=');?>" class="operator operator-less-than-equals"><?php echo KText::_('is or below');?></div>
			<div data-operator="<?php echo htmlentities('==');?>" class="operator operator-is"><?php echo KText::_('is');?></div>
			<div data-operator="<?php echo htmlentities('!=');?>" class="operator operator-is-not"><?php echo KText::_('is not');?></div>
			<div data-operator="<?php echo htmlentities('>=');?>" class="operator operator-greater-than-equals"><?php echo KText::_('is or above');?></div>
			<div data-operator="<?php echo htmlentities('>');?>" class="operator operator-greater-than"><?php echo KText::_('is above');?></div>
		</div>

		<div class="operator-picker operator-picker-short">
			<div data-operator="<?php echo htmlentities('==');?>" class="operator operator-is"><?php echo KText::_('is');?></div>
			<div data-operator="<?php echo htmlentities('!=');?>" class="operator operator-is-not"><?php echo KText::_('is not');?></div>
		</div>

	</div>

	<div class="hidden-helpers">
		<div class="textfield-autosize"><span id="width-tester"></span></div>
	</div>

</div>