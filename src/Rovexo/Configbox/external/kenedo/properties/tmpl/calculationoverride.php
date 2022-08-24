<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyCalculationOverride
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

$overrides = $this->getOverrides();
$groups = $this->getCustomerGroups();

$usedGroups = array();
foreach ($overrides as $override) {
	$usedGroups[$override['group_id']] = $override['group_id'];
}

$label = $this->getOverrideLabel();
$calculations = $this->getCalculations($productId);
$dropDownOptions = $this->getCalculationsDropdownOptions($productId);
?>

<input type="hidden"
	   name="<?php echo hsc($this->propertyName);?>"
	   id="<?php echo hsc($this->propertyName);?>"
	   class="overrides-json-data"
	   value="<?php echo hsc(json_encode($overrides));?>" />

<div class="price-overrides">
	<?php foreach ($overrides as $override) { ?>
		<div class="price-override" data-group-id="<?php echo intval($override['group_id']);?>">

			<div class="property-label">
				<?php echo hsc($label);?> <?php echo KText::_('PROPERTY_GROUP_PRICE_FOR');?> <span class="group-title-field"><?php echo hsc($groups[$override['group_id']]->title);?></span>
				(<a class="trigger-remove-price-override"><?php echo KText::_('Remove');?></a>)
			</div>

			<div class="select-and-links">
				<?php $dropdownId = 'dummy-id-for-chosen-'.rand(0,10000);?>
				<?php echo KenedoHtml::getSelectField($dropdownId, $dropDownOptions, $override['calculation_id'], 0, false, 'calculation-select make-me-chosen');?>

				<span class="join-link">
					<a class="trigger-open-join-link-modal btn btn-default"
					   data-controller="admincalculations"
					   data-task="edit"
					   data-selected-id="<?php echo intval($override['calculation_id']);?>"
					   data-name-form-control="<?php echo hsc($dropdownId);?>"
					   data-link-text-new="<?php echo KText::_('New');?>"
					   data-link-text-open="<?php echo KText::_('Open');?>"
					   data-request-data="<?php echo hsc(json_encode(['prefill_product_id'=>intval($productId)]));?>">
							<?php echo (empty($override['calculation_id'])) ? KText::_('New') : KText::_('Open');?>
						</a>
				</span>

				<div class="modal join-link-modal" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content"></div>
					</div>
				</div>

				<?php if (false) { ?>
				<span class="join-links">

					<span class="join-link join-link-0" style="display: <?php echo (empty($override['calculation_id'])) ? 'inline':'none'; ?>">

						<a class="trigger-open-join-link-modal btn btn-default"
						   data-controller="admincalculations"
						   data-task="edit"
						   data-name-form-control="<?php echo hsc($id);?>"
						   data-request-data="<?php echo hsc(json_encode(['id'=>0, 'prefill_product_id'=>intval($productId)]));?>">
						<?php echo KText::_('New');?>
						</a>

					</span>

					<?php foreach ($calculations as $calc) { ?>
						<span class="join-link join-link-<?php echo intval($calc->id);?>" style="display: <?php echo ($calc->id == $override['calculation_id']) ? 'inline':'none'; ?>">

							<a class="trigger-open-join-link-modal btn btn-default"
							   data-controller="admincalculations"
							   data-task="edit"
							   data-request-data="<?php echo hsc(json_encode(['id'=>intval($calc->id), 'prefill_product_id'=>intval($productId)]));?>">
							<?php echo KText::_('Open');?>
							</a>
						</span>
					<?php } ?>
				</span>

				<div class="modal join-link-modal" tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content"></div>
					</div>
				</div>
				<?php } ?>
			</div>

		</div>
	<?php } ?>
</div>

<a class="trigger-show-group-picker<?php echo (count($overrides) == count($groups)) ? ' hidden':'';?>"><?php echo KText::_('PROPERTY_GROUP_PRICE_OVERRIDE_ADD_PRICE_OVERRIDE');?></a>

<div class="group-picker">
	<div class="call-pick-group"><?php echo KText::_('PROPERTY_GROUP_PRICE_OVERRIDE_PICK_GROUP');?></div>

	<?php foreach ($groups as $group) { ?>
		<a class="trigger-add-price-override group-id-<?php echo intval($group->id);?> <?php echo (!empty($usedGroups[$group->id])) ? 'used-already':'';?>" data-group-id="<?php echo intval($group->id);?>"><?php echo hsc($group->title);?></a>
	<?php } ?>

	<a class="trigger-cancel-group-picker"><?php echo KText::_('Cancel');?></a>

</div>

<div class="price-override-blueprint">
	<div class="price-override" data-group-id="0">

		<div class="property-label">
			<?php echo hsc($label);?> <?php echo KText::_('PROPERTY_GROUP_PRICE_FOR');?> <span class="group-title-field"></span>
			(<a class="trigger-remove-price-override"><?php echo KText::_('Remove');?></a>)
		</div>

		<div class="select-and-links">
			<?php
			$dropdownId = 'dummy-id-for-chosen-'.rand(0,10000);
			?>

			<?php echo KenedoHtml::getSelectField($dropdownId, $dropDownOptions, 0, 0, false, 'calculation-select');?>

			<div class="modal join-link-modal" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content"></div>
				</div>
			</div>

			<span class="join-link">
				<a class="trigger-open-join-link-modal btn btn-default"
				   data-controller="admincalculations"
				   data-task="edit"
				   data-selected-id="0"
				   data-name-form-control="PLACEHOLDER_CALC_SELECT"
				   data-link-text-new="<?php echo KText::_('New');?>"
				   data-link-text-open="<?php echo KText::_('Open');?>"
				   data-request-data="<?php echo hsc(json_encode(['prefill_product_id'=>intval($productId)]));?>">
						<?php echo KText::_('New');?>
					</a>
			</span>
			<?php if (false) { ?>
			<span class="join-links">
				<span class="join-link join-link-0" style="display:inline">

					<a class="trigger-open-join-link-modal btn btn-default"
					   data-controller="admincalculations"
					   data-task="edit"
					   data-name-form-control="PLACEHOLDER_CALC_SELECT"
					   data-request-data="<?php echo hsc(json_encode(['id'=>0, 'prefill_product_id'=>intval($productId)]));?>">
						<?php echo KText::_('New');?>
					</a>

				</span>

				<?php foreach ($calculations as $calc) { ?>
					<span class="join-link join-link-<?php echo intval($calc->id)?>" style="display:none">

						<a class="trigger-open-join-link-modal btn btn-default"
						   data-controller="admincalculations"
						   data-task="edit"
						   data-name-form-control="PLACEHOLDER_CALC_SELECT"
						   data-request-data="<?php echo hsc(json_encode(['id'=>intval($calc->id), 'prefill_product_id'=>intval($productId)]));?>">
							<?php echo KText::_('Open');?>
							</a>

					</span>
				<?php } ?>
			</span>
			<?php } ?>

		</div>

	</div>
</div>
