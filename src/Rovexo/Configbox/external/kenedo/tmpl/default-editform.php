<?php
defined('CB_VALID_ENTRY') or die();
/** @var KenedoView $this */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>">

<form data-view="<?php echo hsc($this->view);?>" class="kenedo-details-form" method="post" enctype="multipart/form-data" action="<?php echo hsc($this->formAction);?>" data-record="<?php echo hsc(json_encode($this->record));?>" data-properties="<?php echo hsc(json_encode($this->properties));?>">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>
	<?php if (!empty($this->pageTitle)) { ?><h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1><?php } ?>

	<?php if ($this->contentAfterTitle) { ?>
		<div class="kenedo-after-title"><?php echo $this->contentAfterTitle;?></div>
	<?php } ?>

	<div class="kenedo-messages">
		<div class="kenedo-messages-error"></div>
		<div class="kenedo-messages-notice"></div>
	</div>
	
	<div class="kenedo-properties">
		<?php 
		foreach($this->properties as $property) {
			$property->setData($this->record);
			if ($property->usesWrapper()) {
				?>
			 	<div id="<?php echo $property->getCssId();?>" class="<?php echo $property->renderCssClasses();?>" data-property-definition="<?php echo hsc(json_encode($property->getPropertyDefinition()));?>">
			 		<?php if ($property->doesShowAdminLabel()) { ?>
			 			<div class="property-label"><?php echo $property->getLabelAdmin();?></div>
			 		<?php } ?>
			 		<div class="property-body"><?php echo $property->getBodyAdmin();?></div>
			 	</div>
				<?php
			}
			else {
				echo $property->getBodyAdmin();
			}
		}
		?>
		<div class="clear"></div>
	</div>
	
	<?php if (!empty($this->recordUsage)) { ?>
		<div class="kenedo-item-usage">
			<div class="kenedo-usage-message"><?php echo KText::_('The item is in use in the following entries:');?></div>
			<?php foreach ($this->recordUsage as $fieldName=>$items) { ?>
				<?php foreach ($items as $item) { ?>
				<div>
					<span class="kenedo-candelete-usage-entry-name"><?php echo hsc($fieldName);?></span> - <a class="kenedo-new-tab kenedo-candelete-usage-entry-link" href="<?php echo $item->link;?>" class="new-tab"><?php echo hsc($item->title);?></a>
				</div>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>
	
	<div class="kenedo-hidden-fields">
		<input type="hidden" id="option" 		name="option" 			value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 		value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 			value="" />
		<input type="hidden" id="id"			name="id" 				value="<?php echo intval($this->record->id); ?>" />
		<input type="hidden" id="lang"			name="lang" 			value="<?php echo hsc(KenedoPlatform::p()->getLanguageUrlCode());?>" />
		<!-- unencoded return url "<?php echo hsc($this->returnUrl);?>" -->
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

	<div class="clear"></div>

</form>
</div>
</div>