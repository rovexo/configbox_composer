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
?>

<?php if ($this->getPropertyDefinition('lockedAfterStore') && !empty($this->data->{$this->propertyName})) { ?>

	<input type="hidden" name="<?php echo $this->propertyName;?>" id="<?php echo $this->propertyName;?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">
	<div class="output-value"><?php echo hsc($outputValue);?></div>

<?php } else { ?>

	<select name="<?php echo $this->propertyName;?>" id="<?php echo $this->propertyName;?>" class="join-select">
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