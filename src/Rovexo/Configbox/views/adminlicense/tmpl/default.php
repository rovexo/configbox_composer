<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminlicense */
?>
<div <?php echo $this->getViewAttributes();?>>
		<p><?php echo KText::_('Please enter your license key.');?></p>
		<form action="<?php echo KLink::getRoute('index.php?option=com_configbox');?>" method="post">
			<input class="form-control" type="text" name="license_key" placeholder="<?php echo KText::_('License Key');?>"  value="<?php echo hsc($this->licenseKey);?>" />
			<a class="btn btn-primary trigger-store-license-key"><?php echo KText::_('Save');?></a>
			<input type="hidden" name="option" value="<?php echo hsc($this->component);?>" />
			<input type="hidden" name="controller" value="<?php echo hsc($this->controllerName);?>" />
			<input type="hidden" name="task" value="storeLicenseKey" />

			<input type="hidden"
			       id="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
			       name="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenName());?>"
			       value="<?php echo hsc(KenedoPlatform::p()->getCsrfTokenValue());?>" />

		</form>
</div>