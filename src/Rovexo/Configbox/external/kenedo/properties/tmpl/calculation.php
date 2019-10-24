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
$selected = $this->data->{$this->propertyName};

// If we show links to the calculations, we need to add a few CSS classes (works like join's joinLinks)
$showLinks = $this->getPropertyDefinition('showLinks');
$dropdownClasses = ($showLinks) ? 'make-me-chosen with-join-links join-select' : 'make-me-chosen';

// Render the dropdown
echo KenedoHtml::getSelectField($this->propertyName, $options, $selected, 0, false, $dropdownClasses);

// If instructed, render the links
if ($showLinks) {
	?>
	<span class="join-links">
		<span class="join-link join-link-0" <?php echo ($selected == 0) ? 'style="display:inline"':'style="display:none"'; ?>>
			<a class="trigger-open-modal btn btn-default" data-modal-width="1000" data-modal-height="700" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalculations&in_modal=1&tmpl=component&task=edit&id=0&prefill_product_id='.intval($productId).'&form_custom_4='.$this->propertyName);?>"><?php echo KText::_('New');?></a>
		</span>

		<?php foreach ($calculations as $calculation) { ?>
			<span class="join-link join-link-<?php echo intval($calculation->id)?>" <?php echo ($selected == $calculation->id) ? 'style="display:inline"':'style="display:none"'; ?>>
				<a class="trigger-open-modal btn btn-default" data-modal-width="1000" data-modal-height="700" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalculations&in_modal=1&tmpl=component&task=edit&id='.$calculation->id.'&form_custom_4='.$this->propertyName);?>"><?php echo KText::_('Open');?></a>
			</span>
		<?php } ?>
	</span>
	<?php
}
