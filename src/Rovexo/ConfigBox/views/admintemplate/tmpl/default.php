<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmintemplate */
?>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<form data-view="<?php echo hsc($this->view);?>" class="kenedo-details-form" method="post" enctype="multipart/form-data" action="<?php echo hsc($this->formAction);?>" data-record="<?php echo hsc(json_encode($this->record));?>" data-properties="<?php echo hsc(json_encode($this->properties));?>">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>

	<h1 class="kenedo-page-title"><?php echo KText::_('Template');?></h1>

	<div class="kenedo-messages">
		<div class="kenedo-messages-error"></div>
		<div class="kenedo-messages-notice"></div>
	</div>

	<div class="type-and-name"<?php echo ($this->record->name == '') ? '':' style="display:none"' ?>>
		<div id="property-name-templateName" class="property-name-templateName kenedo-property property-type-string">
			<div class="property-label"><?php echo KText::_('Template Name');?></div>
			<div class="property-body"><div class="string-type-string">
				<input type="text" name="templateName" id="templateName" value="<?php echo hsc($this->record->name);?>">
			</div></div>
		</div>

		<div id="property-name-templateType" class="property-name-templateType kenedo-property property-type-join">
			<div class="property-label"><?php echo KText::_('Template Type');?></div>
			<div class="property-body">
				<select name="templateType">
					<option <?php echo ($this->record->type == 'template_listing') ? 'selected="selected" ':'';?>value="template_listing"><?php echo KText::_('template_listing');?></option>
					<option <?php echo ($this->record->type == 'template_product') ? 'selected="selected" ':'';?>value="template_product"><?php echo KText::_('template_product');?></option>
					<option <?php echo ($this->record->type == 'template_page') ? 'selected="selected" ':'';?>value="template_page"><?php echo KText::_('template_page');?></option>
				</select>
			</div>
		</div>
	</div>

	<?php if ($this->record->name) { ?>
		<p class="file-name"><?php echo $this->record->path;?></p>
	<?php } ?>
	
	<p style="color:red;font-size:1.2em"><?php if (isset($this->record->writable) && $this->record->writable == false) echo KText::_('The template is not writable');?></p>
	
	<div class="clear"></div>
	
	<div class="content-wrapper">
		<textarea id="template-code" name="content"><?php echo hsc($this->record->content);?></textarea>
 	</div>
	
	<div class="kenedo-hidden-fields">
		<input type="hidden" id="option" 		name="option" 			value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 		value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 			value="" />
		<input type="hidden" id="id" 			name="id" 				value="" />
		<input type="hidden" id="ajax_sub_view" name="ajax_sub_view" 	value="<?php echo ($this->isAjaxSubview()) ? '1':'0';?>" />
		<input type="hidden" id="in_modal"		name="in_modal" 		value="<?php echo ($this->isInModal()) ? '1':'0';?>" />
		<input type="hidden" id="tmpl"			name="tmpl" 			value="component" />
		<input type="hidden" id="format"		name="format" 			value="raw" />
		<input type="hidden" id="lang"			name="lang" 			value="<?php echo hsc(KenedoPlatform::p()->getLanguageUrlCode());?>" />
		<!-- unencoded return url "<?php echo $this->returnUrl;?>" -->
		<input type="hidden" id="return" 		name="return" 			value="<?php echo KLink::base64UrlEncode($this->returnUrl);?>" />
		<input type="hidden" id="form_custom_1" name="form_custom_1" 	value="<?php echo hsc(KRequest::getString('form_custom_1'));?>" />
		<input type="hidden" id="form_custom_2" name="form_custom_2" 	value="<?php echo hsc(KRequest::getString('form_custom_2'));?>" />
		<input type="hidden" id="form_custom_3" name="form_custom_3" 	value="<?php echo hsc(KRequest::getString('form_custom_3'));?>" />
		<input type="hidden" id="form_custom_4" name="form_custom_4" 	value="<?php echo hsc(KRequest::getString('form_custom_4'));?>" />
		<?php if (KenedoPlatform::getName() == 'magento') { ?>
			<input type="hidden" id="form_key" 		name="form_key" 		value="<?php echo Mage::getSingleton('core/session')->getFormKey();?>" />
		<?php } ?>
		
	</div>
</form>
</div>