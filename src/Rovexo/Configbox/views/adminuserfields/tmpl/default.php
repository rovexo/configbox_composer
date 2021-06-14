<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminuserfields */
?>
<div <?php echo $this->getViewAttributes();?>>
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
	<p><?php echo KText::_('Billing related information is only required if the billing address and delivery address differs.');?>
	<br /><br />

	<table class="user-fields-table">

		<tr>

			<th rowspan="2" class="field-name"><?php echo KText::_('Field Name');?></th>
			<th colspan="2"><?php echo KText::_('Checkout');?></th>
			<th colspan="2"><?php echo KText::_('Quotation');?></th>
			<th colspan="2"><?php echo KText::_('Save');?></th>
			<th colspan="2"><?php echo KText::_('Account');?></th>
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

				<td style="display:none">
					<input type="hidden" name="data[<?php echo intval($userField->id);?>][field_name]" value="<?php echo hsc($userField->field_name);?>" />
				</td>
			</tr>
		<?php } ?>

	</table>

	<div class="kenedo-hidden-fields">

		<input type="hidden" id="option" 		name="option" 			value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 		value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="output_mode"	name="output_mode" 		value="view_only" />
		<input type="hidden" id="task" 			name="task" 			value="" />
		<input type="hidden" id="lang"			name="lang" 			value="<?php echo hsc(KenedoPlatform::p()->getLanguageUrlCode());?>" />
		<!-- unencoded return url "<?php echo $this->returnUrl;?>" -->
		<input type="hidden" id="return" 		name="return" 			value="<?php echo KLink::base64UrlEncode($this->returnUrl);?>" />
		<input type="hidden" id="form_custom_1" name="form_custom_1" 	value="<?php echo hsc(KRequest::getString('form_custom_1'));?>" />
		<input type="hidden" id="form_custom_2" name="form_custom_2" 	value="<?php echo hsc(KRequest::getString('form_custom_2'));?>" />
		<input type="hidden" id="form_custom_3" name="form_custom_3" 	value="<?php echo hsc(KRequest::getString('form_custom_3'));?>" />
		<input type="hidden" id="form_custom_4" name="form_custom_4" 	value="<?php echo hsc(KRequest::getString('form_custom_4'));?>" />

		<input type="hidden"
		       id="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
		       name="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
		       value="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenValue());?>" />

	</div>
	
</form>
</div>
</div>