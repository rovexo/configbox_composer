<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyShapediverparameter
 */

$pageId = !empty($this->data->page_id) ? $this->data->page_id : NULL;

if ($pageId) {
	$assignments = ConfigboxCacheHelper::getAssignments();
	$productId = $assignments['page_to_product'][$pageId];

	$model = KenedoModel::getModel('ConfigboxModelAdminproducts');
	$product = $model->getRecord($productId);
}

if (!$pageId || $product->visualization_type != 'shapediver') {
	?>
	<div class="not-using-shapediver"></div>
	<?php
}
elseif (ConfigboxAddonHelper::hasAddon('shapediver') == false) {
	?>
	<div class="shapediver-addon-info"><?php echo KText::_('SHAPEDIVER_ADDON_EXPIRATION_INFO');?></div>
	<input type="hidden" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">
	<?php
}
else {

	$modelData = json_decode($product->shapediver_model_data, true);

	$options = array();

	foreach ($modelData['parameterData'] as $parameterId => $parameter) {
		if ($parameter['hidden'] == true) {
			continue;
		}
		$options[$parameterId] = $parameter['title'];
	}

	// Add some feedback in case some non-existing geometry is set currently
	if ($this->data->{$this->propertyName} != '' && !isset($options[$this->data->{$this->propertyName}])) {
		?>
		<div class="sd-note-bad-parameter bg-danger">
			<?php echo KText::sprintf('SHAPEDIVER_WARNING_BAD_PARAMETER_ID', $this->data->{$this->propertyName});?>
		</div>
		<?php
	}


	echo KenedoHtml::getSelectField($this->propertyName, $options, $this->data->{$this->propertyName}, null, false, 'make-me-chosen');

	?>
	<div class="sd-parameter-infos">

		<h4 class="parameter-selection-help-heading"><?php echo KText::_('SHAPEDIVER_PARAMETER_SELECTION_HELP_HEADING');?></h4>
		<div class="parameter-selection-help"><?php echo KText::_('SHAPEDIVER_PARAMETER_SELECTION_HELP');?></div>
		<h4 class="parameter-infos-heading"><?php echo KText::_('SHAPEDIVER_PARAMETER_INFOS');?></h4>

		<table class="table">
			<tr>
				<th><?php echo KText::_('Title');?></th>
				<th><?php echo KText::_('Type');?></th>
				<th><?php echo KText::_('Minimum');?></th>
				<th><?php echo KText::_('Maximum');?></th>
				<th><?php echo KText::_('Choices');?></th>
			</tr>
			<?php foreach ($modelData['parameterData'] as $parameterId => $parameter) { ?>

				<?php if ($parameter['hidden'] == true) { continue; };?>

				<tr>
					<td><?php echo hsc($parameter['title']);?></td>
					<td><?php echo hsc($parameter['type']);?></td>
					<td><?php echo hsc(isset($parameter['min']) ? $parameter['min'] : KText::_('NA') ) ;?></td>
					<td><?php echo hsc(isset($parameter['max']) ? $parameter['max'] : KText::_('NA') ) ;?></td>
					<td><?php echo hsc(isset($parameter['choices']) ? implode(', ', $parameter['choices']) : KText::_('NA') ) ;?></td>
				</tr>

			<?php } ?>

		</table>

	</div>



	<?php

}