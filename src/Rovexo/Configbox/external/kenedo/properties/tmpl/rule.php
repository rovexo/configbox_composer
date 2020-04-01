<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyRule
 */

// Determine the product and page ID
// If used in an answer, we got a question ID to start with
if (isset($this->data->element_id)) {
	$ass = ConfigboxCacheHelper::getAssignments();
	$pageId = $ass['element_to_page'][$this->data->element_id];
	$productId = $ass['element_to_product'][$this->data->element_id];
	$type = 'answer';
}
// For questions we got a page id
elseif (isset($this->data->page_id)) {
	$ass = ConfigboxCacheHelper::getAssignments();
	$pageId = $this->data->page_id;
	$productId = $ass['page_to_product'][$pageId];
	$type = 'question';
}
else {
	$productId = 0;
	$pageId = 0;
	$type = '';
}

// Tells if there is a rule already
$ruleIsSet = !empty($this->data->{$this->propertyName}) && $this->data->{$this->propertyName} != '[]';

// Prepare the URL for the editor
$editUrl = KLink::getRoute('index.php?option=com_configbox&controller=adminruleeditor&output_mode=view_only', false);

// There is a piece of JS in the admin.js that handles showing the editor

?>

<div class="rule-wrapper <?php echo ($ruleIsSet) ? 'has-rule' : 'has-no-rule';?>">

	<input class="data-field"
	       data-editor-url="<?php echo $editUrl;?>"
	       data-product-id="<?php echo intval($productId);?>"
	       data-page-id="<?php echo intval($pageId);?>"
	       data-usage-in="<?php echo hsc($type);?>"
	       type="hidden"
	       name="<?php echo $this->propertyName;?>"
	       id="<?php echo $this->propertyName;?>"
	       value="<?php echo hsc($this->data->{$this->propertyName}); ?>" />

	<span class="trigger-edit-rule">
		<span class="rule-html" id="rule-text-<?php echo $this->propertyName;?>"><?php echo ($ruleIsSet) ? ConfigboxRulesHelper::getRuleHtml($this->data->{$this->propertyName}, false) : '';?></span>
		<span class="pseudo-rule-no-conds"><?php echo hsc($this->getPropertyDefinition('textWhenNoRule', ''));?></span>
	</span>

	<a class="backend-button-small trigger-edit-rule"><?php echo KText::_('BTN_RULE_CHANGE')?></a>
	<a class="backend-button-small trigger-delete-rule"><?php echo KText::_('BTN_RULE_DELETE');?></a>
	<a class="backend-button-small trigger-copy-rule"><?php echo KText::_('BTN_RULE_COPY');?></a>
	<a class="backend-button-small trigger-paste-rule"><?php echo KText::_('BTN_RULE_PASTE');?></a>

	<div class="modal rule-editor-modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">

			</div>
		</div>
	</div>

</div>