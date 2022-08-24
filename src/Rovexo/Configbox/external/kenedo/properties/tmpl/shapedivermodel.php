<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyShapedivermodel
 */

$productId    = $this->data->{$this->model->getTableKey()};
$valueJson    = $this->data->{$this->propertyName};

// Make an array of the model JSON
$data = json_decode((string)$valueJson, true);

// Set defaults if items are missing
if (empty($data['iframeUrl'])) {
	$data['iframeUrl'] = '';
}

if (empty($data['ticket'])) {
	$data['ticket'] = '';
}

if (empty($data['modelViewUrlOverride'])) {
	$data['modelViewUrlOverride'] = '';
}

if (empty($data['ratioDimensions'])) {
	$data['ratioDimensions'] = '4:3';
}

if (empty($data['parameterData'])) {
	$data['parameterData'] = array();
}

if (empty($data['texturedGeometries'])) {
	$data['texturedGeometries'] = array();
}

?>

<?php if (ConfigboxAddonHelper::hasAddon('shapediver')) { ?>

	<div class="badge-enterprise-feature"><?php echo KText::_('ENTERPRISE_FEATURE');?></div>

	<div class="form-group">
		<label for="<?php echo hsc($this->propertyName);?>"><b><?php echo KText::_('TICKET FOR DIRECT EMBEDDING');?></b><?php echo KenedoHtml::getTooltip('', KText::_('SHAPEDIVER_ADMIN_IFRAME_URL_TEXT'));?></label>
		<input class="model-ticket form-control" type="text" value="<?php echo hsc($data['ticket']);?>">

		<label for="input-module-view-url-override"><b><?php echo KText::_('SHAPEDIVER_ADMIN_LABEL_MODEL_VIEW_URL_OVERRIDE');?></b><?php echo KenedoHtml::getTooltip('', KText::_('SHAPEDIVER_ADMIN_TOOLTIP_MODEL_VIEW_URL_OVERRIDE'));?></label>

		<input class="model-view-url-override form-control" id="input-module-view-url-override" type="text" value="<?php echo hsc($data['modelViewUrlOverride']);?>">

		<input class="shapediver-model-data" type="hidden" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">

		<input class="model-parameters" type="hidden" value="<?php echo hsc(json_encode($data['parameterData']));?>" />
		<input class="model-textured-geometries" type="hidden" value="<?php echo hsc(json_encode($data['texturedGeometries']));?>" />

		<p><?php echo KText::_('SHAPEDIVER_ADMIN_LOAD_MODEL_INFO');?></p>
		<a class="btn btn-primary trigger-load-model-data" data-processing-text="<?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL_WAIT_TEXT');?>" data-done-text="<?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL_DONE_TEXT');?>"><?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL');?></a>

		<span class="feedback-box"></span>
	</div>

	<div class="shapediver-container"></div>

<?php } else { ?>

	<div class="shapediver-addon-info">
		<?php echo KText::_('SHAPEDIVER_ADDON_INFO');?>
	</div>

	<input class="shapediver-model-data" type="hidden" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">

<?php } ?>

