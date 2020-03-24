<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyCalculation
 */

// Determine the product and page ID (currently we have calculations in questions and answers)
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

// Prepare the calculations for conditions
$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');

// If we got a product ID, we load only that product's calculations. Here we prepare the filter instruction
$filter = ($productId) ? array('admincalculations.product_id' => $productId) : array();

// Get the calculations for the dropdown
$calculations = $calcModel->getRecords($filter, array(), array('propertyName' => 'name', 'direction' => 'ASC'));

// Prepare the dropdown filters
$options = array($this->getPropertyDefinition('defaultlabel', KText::_('No calculation')));
foreach ($calculations as $calculation) {
	$options[$calculation->id] = $calculation->name;
}

// Convenience var for the currently selected calculation
$selectedId = $this->data->{$this->propertyName};

$dropdownClasses = 'calculation-select make-me-chosen';
$showLinks = $this->getPropertyDefinition('showLinks');
if ($showLinks) {
    $dropdownClasses .= ' join-select';
}

?>
<div class="select-and-links">

	<?php echo KenedoHtml::getSelectField($this->propertyName, $options, $selectedId, 0, false, $dropdownClasses);?>

    <?php if ($showLinks) { ?>

		<span class="join-link">
			<a class="trigger-open-join-link-modal btn btn-default"
			   data-controller="admincalculations"
			   data-task="edit"
			   data-selected-id="<?php echo intval($selectedId);?>"
			   data-name-form-control="<?php echo hsc($this->propertyName);?>"
			   data-link-text-new="<?php echo KText::_('New');?>"
			   data-link-text-open="<?php echo KText::_('Open');?>"
			   data-request-data="<?php echo hsc(json_encode(['prefill_product_id'=>intval($productId)]));?>">
					<?php echo ($selectedId == 0) ? KText::_('New') : KText::_('Open');?>
				</a>
		</span>

		<div class="modal join-link-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content"></div>
			</div>
		</div>

    <?php } ?>

</div>

