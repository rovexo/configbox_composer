<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmintemplates */
?>
<div <?php echo $this->getViewAttributes();?>>

<div class="kenedo-listing-form">
	
	<h1 class="kenedo-page-title"><?php echo KText::_('Templates');?></h1>
	
	<div class="tasks-and-filters">
		
		<div class="kenedo-tasks">
			<ul class="kenedo-task-list">
				<li class="task task-add"><a class="backend-button-small"><?php echo KText::_('Add');?></a></li>
			</ul>
		</div>
		
	</div>
	
	<div class="clear"></div>
	
	<div class="property-group-notes">
		<p><?php echo KText::_('Custom templates enable you to modify the design of the configurator.');?></p>
		<p><?php echo KText::_('You can override the default template by naming the template default or create templates for individual product listings, products, pages or elements. You can pick the template to use in the field Template for each of these items.');?></p>
	</div>
	
	<h3><?php echo KText::_('Custom Templates')?></h3>
	
	<?php if (count($this->customTemplates) == 0) { ?>
	<p><?php echo KText::_('No custom templates.');?></p>
	<?php } ?>

	<?php 
	$templateType = 'template_page';
	if (isset($this->customTemplates[$templateType])) {
		?>
		<b><?php echo KText::_('Custom configurator page templates')?></b>
		<ul>
		<?php
		foreach ($this->customTemplates[$templateType] as $templateName=>$path) {
			$linkOpen = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=edit&id='.hsc($templateType).'.'.hsc($templateName).'&return='.KLink::base64UrlEncode($this->returnUrl));
			$linkRemove = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=delete&id='.hsc($templateType).'.'.hsc($templateName));
			?>
			<li>
				<a class="open ajax-target-link" href="<?php echo $linkOpen;?>"><?php echo $templateName;?> - <?php echo KText::_($templateType); ?></a>
				-
				<a class="remove" href="<?php echo $linkRemove;?>"><?php echo KText::_('Remove'); ?></a>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php
	}
	?>
	
	<?php 
	$templateType = 'template_product';
	if (isset($this->customTemplates[$templateType])) {
		?>
		<b><?php echo KText::_('Custom product details templates')?></b>
		<ul>
		<?php
		foreach ($this->customTemplates[$templateType] as $templateName=>$path) {
			$linkOpen = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=edit&id='.hsc($templateType).'.'.hsc($templateName).'&return='.KLink::base64UrlEncode($this->returnUrl));
			$linkRemove = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=delete&id='.hsc($templateType).'.'.hsc($templateName).'&return='.KLink::base64UrlEncode($this->returnUrl));
			?>
			<li>
				<a class="open ajax-target-link" href="<?php echo $linkOpen;?>"><?php echo $templateName;?> - <?php echo KText::_($templateType); ?></a>
				-
				<a class="remove" href="<?php echo $linkRemove;?>"><?php echo KText::_('Remove'); ?></a>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php
	}
	?>
	
	<?php 
	$templateType = 'template_listing';
	if (isset($this->customTemplates[$templateType])) {
		?>
		<b><?php echo KText::_('Custom product listing templates')?></b>
		<ul>
		<?php
		foreach ($this->customTemplates[$templateType] as $templateName=>$path) {
			$linkOpen = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=edit&id='.hsc($templateType).'.'.hsc($templateName).'&return='.KLink::base64UrlEncode($this->returnUrl));
			$linkRemove = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=delete&id='.hsc($templateType).'.'.hsc($templateName).'&return='.KLink::base64UrlEncode($this->returnUrl));
			?>
			<li>
				<a class="open ajax-target-link" href="<?php echo $linkOpen;?>"><?php echo $templateName;?> - <?php echo KText::_($templateType); ?></a>
				-
				<a class="remove" href="<?php echo $linkRemove;?>"><?php echo KText::_('Remove'); ?></a>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php
	}
	?>
	
	<h3><?php echo KText::_('Default Templates')?></h3>
	<ul>
		<?php 
		foreach ($this->originalTemplates as $templateType=>$template) {
			$link = KLink::getRoute( 'index.php?option=com_configbox&controller=admintemplates&task=edit&is_original=1&id='.$templateType.'.default&return='.KLink::base64UrlEncode($this->returnUrl));
			?>
			<li><a class="open ajax-target-link" href="<?php echo $link;?>"><?php echo KText::_($templateType);?></a></li>
			<?php 
		}
		?>
		
	</ul>


	<div class="kenedo-hidden-fields">

		<div class="listing-data-add-link" data-value="<?php echo KLink::base64UrlEncode(KLink::getRoute('index.php?option=com_configbox&controller=admintemplates&task=edit', false));?>"></div>

		<input type="hidden" id="add-link"		name="add-link" 			value="<?php echo KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit');?>"/>
		<input type="hidden" id="option" 		name="option" 				value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 			value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 				value="display" />
		<input type="hidden" id="tmpl"			name="tmpl" 				value="<?php echo hsc(KRequest::getKeyword('tmpl','index'));?>" />
		<input type="hidden" id="lang"			name="lang" 				value="<?php echo hsc(KText::getLanguageCode());?>" />
		<input type="hidden" id="parampicker"	name="parampicker" 			value="<?php echo hsc(KRequest::getInt('parampicker',0));?>" />
		<input type="hidden" id="pickerobject"	name="pickerobject" 		value="<?php echo hsc(KRequest::getKeyword('pickerobject',''));?>" />
		<input type="hidden" id="pickermethod"	name="pickermethod" 		value="<?php echo hsc(KRequest::getKeyword('pickermethod',''));?>" />
		<!-- Return URL unencoded is "<?php echo hsc($this->returnUrl);?>" -->
		<input type="hidden" id="return" 		name="return" 				value="<?php echo KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode()), false));?>" />
	</div>
	
</div>
</div>