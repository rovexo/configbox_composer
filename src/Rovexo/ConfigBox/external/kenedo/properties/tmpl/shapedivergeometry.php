<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyShapedivergeometry
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

	foreach ($modelData['texturedGeometries'] as $name) {
		$options[$name] = $name;
	}

	// Add some feedback in case some non-existing geometry is set currently
	if ($this->data->{$this->propertyName} != '' && !isset($options[$this->data->{$this->propertyName}])) {
		?>
		<div class="sd-note-bad-geometry bg-danger">
			<?php echo KText::sprintf('SHAPEDIVER_WARNING_BAD_GEOMETRY_NAME', $this->data->{$this->propertyName});?>
		</div>
		<?php
	}

	echo KenedoHtml::getSelectField($this->propertyName, $options, $this->data->{$this->propertyName}, null, false, 'make-me-chosen');

	?>
	<div class="sd-parameter-infos">

		<h4 class="parameter-selection-help-heading"><?php echo KText::_('SHAPEDIVER_GEOMETRY_SELECTION_HELP_HEADING');?></h4>
		<div class="parameter-selection-help"><?php echo KText::_('SHAPEDIVER_GEOMETRY_SELECTION_HELP');?></div>

	</div>

	<?php

}