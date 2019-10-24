<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyShapedivermodel
 */

$productId    = $this->data->{$this->model->getTableKey()};
$valueJson    = $this->data->{$this->propertyName};

// Make an array of the model JSON
$data = json_decode($valueJson, true);

// Set defaults if items are missing
if (empty($data['iframeUrl'])) {
	$data['iframeUrl'] = '';
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
		<label for="<?php echo hsc($this->propertyName);?>"><b><?php echo KText::_('Embed Iframe URL');?></b><?php echo KenedoHtml::getTooltip('', KText::_('SHAPEDIVER_ADMIN_IFRAME_URL_TEXT'));?></label>
		<input class="model-url form-control" type="text" value="<?php echo hsc($data['iframeUrl']);?>">
		<input class="shapediver-model-data" type="hidden" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">

		<input class="model-parameters" type="hidden" value="<?php echo hsc(json_encode($data['parameterData']));?>" />
		<input class="model-textured-geometries" type="hidden" value="<?php echo hsc(json_encode($data['texturedGeometries']));?>" />

		<p><?php echo KText::_('SHAPEDIVER_ADMIN_LOAD_MODEL_INFO');?></p>
		<a class="btn btn-primary trigger-load-model-data" data-processing-text="<?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL_WAIT_TEXT');?>" data-done-text="<?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL_DONE_TEXT');?>"><?php echo KText::_('SHAPEDIVER_ADMIN_BUTTON_LOAD_MODEL');?></a>
	</div>

	<div class="form-group">
		<label for="<?php echo hsc($this->propertyName);?>-ratio"><b><?php echo KText::_('Visualization image ratio');?></b><?php echo KenedoHtml::getTooltip('', KText::_('SHAPEDIVER_ADMIN_IMAGE_RATIO'));?></label>
		<input class="form-control model-ratio" type="text" value="<?php echo hsc($data['ratioDimensions']);?>">
	</div>

	<iframe style="display:none" data-api-version="<?php echo hsc(ConfigboxShapediverHelper::getApiVersion());?>" class="shapediver-iframe" id="shapediver-iframe" width="100%" height="200px" frameborder="0" src="about:blank"></iframe>

<?php } else { ?>

	<div class="shapediver-addon-info">
		<?php echo KText::_('SHAPEDIVER_ADDON_INFO');?>
	</div>

	<input class="shapediver-model-data" type="hidden" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>">

<?php } ?>

