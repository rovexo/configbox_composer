<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyJoin
 */

$groupField = $this->getPropertyDefinition('groupby');

// Get the joined items
$ungroupedItems = $this->getParentModelRecords();

if (!is_array($ungroupedItems)) {
	$ungroupedItems = array();
}

// Group if necessary
if ($groupField) {
	$joinedRecords = $this->groupItems($ungroupedItems, $groupField);
}
else {
	$joinedRecords = $ungroupedItems;
}

// Get the selected value
$value = !empty($this->data->{$this->propertyName}) ? $this->data->{$this->propertyName} : 0;
if (!empty($value) || $value === 0) {
	$selected = $value;
}
elseif ($this->getPropertyDefinition('default')) {
	$selected = $this->getPropertyDefinition('default');
}
else {
	$selected = 0;
}

$outputValue = '';
foreach ($ungroupedItems as $item) {
	if ($item->{$this->getPropertyDefinition('propNameKey')} == $selected) {
		$outputValue = $item->{$this->getPropertyDefinition('propNameDisplay')};
	}
}

// See if we got join links
$joinLink = $this->getPropertyDefinition('joinLink')

?>

<?php if ($this->getPropertyDefinition('lockedAfterStore') && !empty($this->data->{$this->propertyName})) { ?>

	<input type="hidden" name="<?php echo $this->propertyName;?>" id="<?php echo $this->propertyName;?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">
	<div class="output-value"><?php echo hsc($outputValue);?></div>

<?php } else { ?>

	<select name="<?php echo $this->propertyName;?>" id="<?php echo $this->propertyName;?>" class="join-select<?php echo ($joinLink) ? ' with-join-links':'';?>">
		<?php if ($this->getPropertyDefinition('defaultlabel')) { ?>
			<option value="0"><?php echo hsc($this->getPropertyDefinition('defaultlabel'));?></option>
		<?php } ?>

		<?php
		foreach ($joinedRecords as $key=>$joinedRecord) {

			if (is_array($joinedRecord)) {
				?>
				<optgroup label="<?php echo hsc($key);?>">
					<?php foreach ($joinedRecord as $record) { ?>
						<option <?php echo ($selected == $record->{$this->getPropertyDefinition('propNameKey')}) ? 'selected="selected"':''; ?> value="<?php echo hsc($record->{$this->getPropertyDefinition('propNameKey')});?>">
							<?php echo hsc($record->{$this->getPropertyDefinition('propNameDisplay')});?>
						</option>
					<?php } ?>
				</optgroup>
				<?php
			}
			else {
				?>
				<option <?php echo ($selected == $joinedRecord->{$this->getPropertyDefinition('propNameKey')}) ? 'selected="selected"':''; ?> value="<?php echo hsc($joinedRecord->{$this->getPropertyDefinition('propNameKey')});?>">
					<?php echo hsc($joinedRecord->{$this->getPropertyDefinition('propNameDisplay')});?>
				</option>
				<?php
			}

		}
		?>
	</select>

<?php } ?>

<?php
if ($joinLink) {
	?>
	<span class="join-links">
		<?php if (!empty($joinLink['allowNew'])) { ?>
			<span class="join-link join-link-0" <?php echo ($selected == 0) ? 'style="display:inline"':'style="display:none"'; ?>>
				<a class="trigger-open-modal btn btn-primary" data-modal-width="1000" data-modal-height="700" href="<?php echo KLink::getRoute($joinLink['linkNew'].'&form_custom_4='.$this->propertyName);?>"><?php echo KText::_('New');?></a>
			</span>
		<?php } ?>
		
		<?php foreach ($ungroupedItems as $joinedRecord) { ?>
			<span class="join-link join-link-<?php echo hsc($joinedRecord->{$this->getPropertyDefinition('propNameKey')})?>" <?php echo ($selected == $joinedRecord->{$this->getPropertyDefinition('propNameKey')}) ? 'style="display:inline"':'style="display:none"'; ?>>
				<a class="trigger-open-modal btn btn-default" data-modal-width="1000" data-modal-height="700" href="<?php echo KLink::getRoute($joinLink['linkEdit'] . $joinedRecord->{$joinLink['idField']});?>"><?php echo KText::_('Open');?></a>
			</span>
		<?php } ?>
	</span>
	<?php
}
?>
