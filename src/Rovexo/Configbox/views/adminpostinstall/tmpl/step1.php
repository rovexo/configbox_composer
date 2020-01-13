<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>
<div class="wrapper-welcome cube-step" data-step="1">

	<h1 class="kenedo-page-title"><?php echo KText::_('POST_INSTALL_HEADING');?></h1>

	<p><?php echo KText::_('POST_INSTALL_INTRO');?></p>

	<div class="form-group">
		<label for="licenseKey"><?php echo KText::_('POST_INSTALL_LICENSE_KEY_LABEL');?></label>
		<input class="form-control" type="text" id="licenseKey" aria-describedby="help-licenseKey" value="<?php echo hsc($this->licenseKey);?>">
		<div class="help-block validation-placeholder"></div>
		<div id="help-licenseKey" class="help-block"><?php echo KText::_('POST_INSTALL_LICENSE_KEY_HELP');?></div>
	</div>

	<a class="btn btn-primary pull-right trigger-store-license-key" data-next-step="<?php echo ($this->platformName == 'wordpress') ? '3':'2';?>"><?php echo KText::_('Next');?></a>

</div>
