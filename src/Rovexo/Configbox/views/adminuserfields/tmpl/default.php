<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminuserfields */
?>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
<form data-view="<?php echo hsc($this->view);?>" class="kenedo-details-form" method="post" enctype="multipart/form-data" action="<?php echo hsc($this->formAction);?>" data-record="<?php echo hsc(json_encode($this->record));?>" data-properties="<?php echo hsc(json_encode($this->properties));?>">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>
	<?php if (!empty($this->pageTitle)) { ?><h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1><?php } ?>

	<div class="kenedo-messages">
		<div class="kenedo-messages-error"></div>
		<div class="kenedo-messages-notice"></div>
	</div>

	<div class="clear"></div>

	<br />
	<p><?php echo KText::_('You can define which customer information has to be entered at checkout address entry and quotation requests. Certain fields are required by the system and cannot be changed.');?></p>
	<p><?php echo KText::_('If you need to validate customer information you can enter regular expressions to check for valid input. Non-required fields are still being validated if the field is filled by the customer.');?></p>
	<p><?php echo KText::_("Server Validation takes PCRE compatible regular expressions to check customer input.");?></p>
	<p><?php echo KText::_("Browser validation takes JavaScript compatible expressions.");?></p>
	<p><?php echo KText::_('Billing related information is only required if the billing address and delivery address differs.');?>
	<br /><br />

	<table class="user-fields-table">

		<tr>

			<th rowspan="2" class="field-name"><?php echo KText::_('Field Name');?></th>
			<th colspan="2"><?php echo KText::_('Checkout');?></th>
			<th colspan="2"><?php echo KText::_('Quotation');?></th>
			<th colspan="2"><?php echo KText::_('Save');?></th>
			<th colspan="2"><?php echo KText::_('Account');?></th>

			<th rowspan="2" class="browser-validation"><?php echo KText::_('Browser Validation');?></th>
			<th rowspan="2" class="server-validation"><?php echo KText::_('Server Validation');?></th>
			<th style="display:none">&nbsp;</th>
		</tr>

		<tr>

			<th><a title="<?php echo KText::_('Displayed');?>"><span class="fa fa-eye"></span></a></th>
			<th><a title="<?php echo KText::_('Required');?>"><span class="fa fa-star"></span></a></th>

			<th><a title="<?php echo KText::_('Displayed');?>"><span class="fa fa-eye"></span></a></th>
			<th><a title="<?php echo KText::_('Required');?>"><span class="fa fa-star"></span></a></th>

			<th><a title="<?php echo KText::_('Displayed');?>"><span class="fa fa-eye"></span></a></th>
			<th><a title="<?php echo KText::_('Required');?>"><span class="fa fa-star"></span></a></th>

			<th><a title="<?php echo KText::_('Displayed');?>"><span class="fa fa-eye"></span></a></th>
			<th><a title="<?php echo KText::_('Required');?>"><span class="fa fa-star"></span></a></th>

		</tr>

		<?php foreach ($this->userFields as $userField) { ?>
			<?php if ($userField->field_name == 'billingcity_id') continue;?>
			<?php if ($userField->field_name == 'city_id') continue;?>
			<tr class="userfield-<?php echo hsc($userField->field_name);?>">
				<td class="field-name"><?php echo hsc($this->userFieldTranslations[$userField->field_name]);?></td>

				<td class="show-checkout"><input type="checkbox" name="data[<?php echo $userField->id;?>][show_checkout]" value="1" <?php echo ($userField->show_checkout) ? 'checked="checked"':'';?> /></td>
				<td class="require-checkout"><input type="checkbox" name="data[<?php echo $userField->id;?>][require_checkout]" value="1" <?php echo ($userField->require_checkout) ? 'checked="checked"':'';?> /></td>

				<td class="show-quotation"><input type="checkbox" name="data[<?php echo $userField->id;?>][show_quotation]" value="1" <?php echo ($userField->show_quotation) ? 'checked="checked"':'';?> /></td>
				<td class="require-quotation"><input type="checkbox" name="data[<?php echo $userField->id;?>][require_quotation]" value="1" <?php echo ($userField->require_quotation) ? 'checked="checked"':'';?> /></td>

				<td class="show-saveorder"><input type="checkbox" name="data[<?php echo $userField->id;?>][show_saveorder]" value="1" <?php echo ($userField->show_saveorder) ? 'checked="checked"':'';?> /></td>
				<td class="require-saveorder"><input type="checkbox" name="data[<?php echo $userField->id;?>][require_saveorder]" value="1" <?php echo ($userField->require_saveorder) ? 'checked="checked"':'';?> /></td>

				<td class="show-profile"><input type="checkbox" name="data[<?php echo $userField->id;?>][show_profile]" value="1" <?php echo ($userField->show_profile) ? 'checked="checked"':'';?> /></td>
				<td class="require-profile"><input type="checkbox" name="data[<?php echo $userField->id;?>][require_profile]" value="1" <?php echo ($userField->require_profile) ? 'checked="checked"':'';?> /></td>

				<td class="browser-validation"><input type="text" name="data[<?php echo $userField->id;?>][validation_browser]" value="<?php echo hsc($userField->validation_browser);?>" /></td>
				<td class="server-validation">
					<input type="text" name="data[<?php echo $userField->id;?>][validation_server]" value="<?php echo hsc($userField->validation_server);?>" />
					<span class="loading-symbol"></span>
				</td>
				<td style="display:none">
					<input type="hidden" name="data[<?php echo $userField->id;?>][field_name]" value="<?php echo hsc($userField->field_name);?>" />
				</td>
			</tr>
		<?php } ?>

	</table>

	<div class="kenedo-hidden-fields">

		<input type="hidden" id="option" 		name="option" 			value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 		value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 			value="" />
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
        <?php if (KenedoPlatform::getName() == 'magento2') { ?>
            <?php $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); ?>
            <?php $formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey'); ?>
            <input type="hidden" id="form_key" 		name="form_key" 		value="<?php echo $formKey->getFormKey();?>" />
        <?php } ?>
	</div>
	
</form>
</div>