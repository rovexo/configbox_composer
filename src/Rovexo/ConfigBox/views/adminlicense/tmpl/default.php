<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminlicense */
?>
<div <?php echo $this->getViewAttributes();?>>
	<div class="kenedo-general-page">
		<p><?php echo KText::_('Please enter your license key.');?></p>
		<div>
			<form action="<?php echo KLink::getRoute('index.php?option=com_configbox');?>" method="post">
				<input class="form-control" type="text" name="license_key" placeholder="<?php echo KText::_('License Key');?>"  value="<?php echo hsc($this->licenseKey);?>" />
				<a class="btn btn-primary trigger-store-license-key"><?php echo KText::_('Save');?></a>
				<input type="hidden" name="option" value="<?php echo hsc($this->component);?>" />
				<input type="hidden" name="controller" value="<?php echo hsc($this->controllerName);?>" />
				<input type="hidden" name="task" value="storeLicenseKey" />
				<?php if (KenedoPlatform::getName() == 'magento') { ?>
					<input type="hidden" name="form_key" value="<?php echo Mage::getSingleton('core/session')->getFormKey();?>" />
				<?php } ?>
                <?php if (KenedoPlatform::getName() == 'magento2') { ?>
                    <?php $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); ?>
                    <?php $formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey'); ?>
                    <input type="hidden" id="form_key" 		name="form_key" 		value="<?php echo $formKey->getFormKey();?>" />
                <?php } ?>
			</form>
		</div>
	</div>
</div>