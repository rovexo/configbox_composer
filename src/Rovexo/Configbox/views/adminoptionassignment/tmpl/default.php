<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminoptionassignment */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
<form class="kenedo-details-form"
      method="post"
      enctype="multipart/form-data"
      action="<?php echo hsc($this->formAction);?>"
      data-view="<?php echo hsc($this->view);?>"
      data-record="<?php echo hsc(json_encode($this->record));?>"
      data-properties="<?php echo hsc(json_encode($this->properties));?>">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>

	<?php if (!empty($this->pageTitle)) { ?><h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1><?php } ?>

	<div class="clear"></div>

	<div class="kenedo-messages">
		<div class="kenedo-messages-error"></div>
		<div class="kenedo-messages-notice"></div>
	</div>

	<div class="row">

		<div class="col-md-6 option-data">
			<h2><?php echo KText::_('Global Answer Settings');?></h2>
			<div class="kenedo-properties">
				<?php echo $this->properties['option_id']->getPropertyFormOutput($this->record);?>
			</div>
			<div class="option-fields-target">
				<div class="kenedo-properties">
					<?php
					foreach($this->optionProperties as $propertyName=>$property) {

						// Hide recurring price fields if product does not use recurring pricing
						if ($this->record->joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_joinedby_product_id_to_adminproducts_use_recurring_pricing = "0" == 0) {
							if (in_array($propertyName, array('price_recurring', 'price_recurring_overrides', 'was_price_recurring'))) {
								continue;
							}
						}

						echo $property->getPropertyFormOutput($this->option);


					}
					?>
					<div class="clear"></div>
				</div>
			</div>
		</div>

		<div class="col-md-6 xref-data">
			<h2><?php echo KText::_('Settings just for this question');?></h2>
			<div class="kenedo-properties">
				<?php
				foreach($this->properties as $property) {

					// Skip option_id, we show it in the other column (see above)
					if ($property->propertyName == 'option_id') {
						continue;
					}

					echo $property->getPropertyFormOutput($this->record);

				}
				?>
				<div class="clear"></div>
			</div>

		</div>

	</div>

	<?php if (!empty($this->recordUsage)) { ?>
		<div class="kenedo-item-usage">
			<div class="kenedo-usage-message"><?php echo KText::_('The item is in use in the following entries:');?></div>
			<?php foreach ($this->recordUsage as $propertyName=>$items) { ?>
				<?php foreach ($items as $item) { ?>
				<div>
					<span class="kenedo-candelete-usage-entry-name"><?php echo hsc($propertyName);?></span> - <a class="kenedo-new-tab kenedo-candelete-usage-entry-link" href="<?php echo $item->link;?>" class="new-tab"><?php echo hsc($item->title);?></a>
				</div>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>

	<div class="kenedo-hidden-fields">

		<input type="hidden" id="option_assignment_load_url" name="option_assignment_load_url" value="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminoptions&task=edit&output_mode=view_only&id=placeholder_option_id');?>" />

		<input type="hidden" id="option" 		name="option" 			value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 		value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 			value="" />
		<input type="hidden" id="id"			name="id" 				value="<?php echo intval($this->record->id); ?>" />
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