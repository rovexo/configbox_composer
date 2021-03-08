<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyGroupPrice
 */
$overrides = $this->getOverrides();
$groups = $this->getCustomerGroups();

$usedGroups = array();
foreach ($overrides as $override) {
	$usedGroups[$override['group_id']] = $override['group_id'];
}

$label = $this->getOverrideLabel();

?>

<input type="hidden"
	   name="<?php echo hsc($this->propertyName);?>"
	   id="<?php echo hsc($this->propertyName);?>"
	   class="overrides-json-data"
	   value="<?php echo hsc(json_encode($overrides));?>"
	   />

<div class="price-overrides">
	<?php foreach ($overrides as $override) { ?>
		<div class="price-override" data-group-id="<?php echo intval($override['group_id']);?>">

			<div class="property-label">
				<?php echo hsc($label);?> <?php echo KText::_('PROPERTY_GROUP_PRICE_FOR');?> <span class="group-title-field"><?php echo hsc($groups[$override['group_id']]->title);?></span>
				(<a class="trigger-remove-price-override"><?php echo KText::_('Remove');?></a>)
			</div>

			<div class="input-group">

				<input class="form-control chosen-price" type="text" value="<?php echo hsc($override['price']);?>">

				<?php if ($this->getPropertyDefinition('unit')) { ?>
					<div class="input-group-append">
						<span class="input-group-text"><?php echo hsc($this->getPropertyDefinition('unit'));?></span>
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

		<div class="input-group">

			<input class="form-control chosen-price" type="text" value="">

			<?php if ($this->getPropertyDefinition('unit')) { ?>
				<div class="input-group-append">
					<span class="input-group-text"><?php echo hsc($this->getPropertyDefinition('unit'));?></span>
				</div>
			<?php } ?>

		</div>

	</div>
</div>
