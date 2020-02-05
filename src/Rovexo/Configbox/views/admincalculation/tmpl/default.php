<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmincalculation */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

<form class="kenedo-details-form" method="post" enctype="multipart/form-data" action="<?php echo hsc($this->formAction);?>" data-view="<?php echo hsc($this->view);?>" data-record="<?php echo hsc(json_encode($this->record));?>" data-properties="<?php echo hsc(json_encode($this->properties));?>">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>

	<?php if (!empty($this->pageTitle)) { ?><h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1><?php } ?>
	
	<div class="kenedo-messages">
		<div class="kenedo-messages-error"></div>
		<div class="kenedo-messages-notice"></div>
	</div>
	
	<div class="kenedo-properties name-type">

		<?php
		// Name prop
		$this->properties['name']->setData($this->record);
		if ($this->properties['name']->usesWrapper()) {
			?>
			<div id="<?php echo $this->properties['name']->getCssId();?>" class="<?php echo $this->properties['name']->renderCssClasses();?>">
				<?php if ($this->properties['name']->doesShowAdminLabel()) { ?>
					<div class="property-label"><?php echo $this->properties['name']->getLabelAdmin();?></div>
				<?php } ?>
				<div class="property-body"><?php echo $this->properties['name']->getBodyAdmin();?></div>
			</div>
			<?php
		}
		else {
			echo $this->properties['product_id']->getBodyAdmin();
		}
		?>

		<?php
		// Product_id prop
		$this->properties['product_id']->setData($this->record);
		if ($this->properties['product_id']->usesWrapper()) {
			?>
			<div id="<?php echo $this->properties['product_id']->getCssId();?>" class="<?php echo $this->properties['product_id']->renderCssClasses();?>">
				<?php if ($this->properties['product_id']->doesShowAdminLabel()) { ?>
					<div class="property-label"><?php echo $this->properties['product_id']->getLabelAdmin();?></div>
				<?php } ?>
				<div class="property-body"><?php echo $this->properties['product_id']->getBodyAdmin();?></div>
			</div>
			<?php
		}
		else {
			echo $this->properties['product_id']->getBodyAdmin();
		}
		?>

		<?php
		// Type prop
		$this->properties['type']->setData($this->record);
		if ($this->properties['type']->usesWrapper()) {
			?>
			<div style="display:<?php echo ($this->record->product_id) ? 'block':'none';?>" id="<?php echo $this->properties['type']->getCssId();?>" class="<?php echo $this->properties['type']->renderCssClasses();?>">
				<?php if ($this->properties['type']->doesShowAdminLabel()) { ?>
					<div class="property-label"><?php echo $this->properties['type']->getLabelAdmin();?></div>
				<?php } ?>
				<div class="property-body"><?php echo $this->properties['type']->getBodyAdmin();?></div>
			</div>
			<?php
		}
		else {
			echo $this->properties['product_id']->getBodyAdmin();
		}
		?>

	</div>

	<div class="calc-type-subview"
		 data-url-matrix="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalcmatrices&task=edit&ajax_sub_view=1&tmpl=component&format=raw&id='.intval($this->record->id));?>"
		 data-url-formula="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalcformulas&task=edit&ajax_sub_view=1&tmpl=component&format=raw&id='.intval($this->record->id));?>"
		 data-url-code="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalccodes&task=edit&ajax_sub_view=1&tmpl=component&format=raw&id='.intval($this->record->id));?>"
		>
		<?php
		if ($this->record->product_id && $this->record->type) {
			switch ($this->record->type) {
				case 'matrix':
					KenedoView::getView('ConfigboxViewAdmincalcmatrix')->setProductId($this->record->product_id)->display();
					break;

				case 'formula':
					KenedoView::getView('ConfigboxViewAdmincalcformula')->setProductId($this->record->product_id)->display();
					break;

				case 'code':
					KenedoView::getView('ConfigboxViewAdmincalccode')->setProductId($this->record->product_id)->display();
					break;
			}
		}
		?>
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
		<input type="hidden" id="option" 		name="option" 				value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 			value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 				value="" />
		<input type="hidden" id="ajax_sub_view" name="ajax_sub_view" 		value="<?php echo ($this->isAjaxSubview()) ? '1':'0';?>" />		
		<input type="hidden" id="in_modal"		name="in_modal" 			value="<?php echo ($this->isInModal()) ? '1':'0';?>" />
		<input type="hidden" id="tmpl"			name="tmpl" 				value="component" />
		<input type="hidden" id="format"		name="format" 				value="raw" />
		<input type="hidden" id="id"			name="id" 					value="<?php echo !empty($this->record->id) ? intval($this->record->id) : 0; ?>" />
		<input type="hidden" id="lang"			name="lang" 				value="<?php echo hsc(KenedoPlatform::p()->getLanguageUrlCode());?>" />
		<input type="hidden" id="return" 		name="return" 				value="<?php echo KLink::base64UrlEncode($this->returnUrl);?>" />
		<input type="hidden" id="form_custom_1" name="form_custom_1" 		value="<?php echo hsc(KRequest::getString('form_custom_1'));?>" />
		<input type="hidden" id="form_custom_2" name="form_custom_2" 		value="<?php echo hsc(KRequest::getString('form_custom_2'));?>" />
		<input type="hidden" id="form_custom_3" name="form_custom_3" 		value="<?php echo hsc(KRequest::getString('form_custom_3'));?>" />
		<input type="hidden" id="form_custom_4" name="form_custom_4" 		value="<?php echo hsc(KRequest::getString('form_custom_4'));?>" />

		<input type="hidden"
		       id="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
		       name="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
		       value="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenValue());?>" />
	</div>
	
</form>
</div>
</div>
