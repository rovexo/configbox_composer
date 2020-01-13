<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>
<div class="wrapper-languages cube-step" data-step="2">

	<h2><?php echo KText::_('POST_INSTALL_LANGUAGES_HEADING');?></h2>
	<div class="bs-callout"><?php echo KText::_('POST_INSTALL_LANGUAGES_INTRO');?></div>

	<div class="form-group">
		<label for="languageTags"><?php echo KText::_('POST_INSTALL_ACTIVE_LANGUAGES');?></label>

		<select data-placeholder="<?php echo KText::_('POST_INSTALL_LANGUAGES_HINT_MULTISELECT');?>" multiple="" class="chosen-select" id="languageTags" aria-describedby="help-languageTags-help">
			<option value=""></option>
			<?php foreach ($this->languages as $language) { ?>
				<option <?php echo (in_array($language->tag, $this->selectedLanguageTags)) ? 'selected' : '';?> value="<?php echo hsc($language->tag);?>"><?php echo hsc($language->label);?></option>
			<?php } ?>
		</select>
		<div class="help-block validation-placeholder"></div>

		<span id="help-languageTags-help" class="help-block"><?php echo KText::_('POST_INSTALL_ACTIVE_LANGUAGES_HELP');?></span>

	</div>

	<a class="btn btn-primary pull-right trigger-store-languages" data-next-step="<?php echo ($this->platformName == 'magento' || $this->platformName == 'magento2') ? '6':'3';?>"><?php echo KText::_('Next');?></a>

</div>
