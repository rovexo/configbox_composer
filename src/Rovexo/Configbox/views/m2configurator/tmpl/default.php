<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewM2configurator */
?>
<div <?php echo $this->getViewAttributes();?>
		data-magento-option-id="<?php echo intval($this->magentoOptionId);?>"
		data-tax-rate="<?php echo hsc($this->taxRate);?>"
		data-config-info="<?php echo hsc(json_encode($this->configInfo));?>">

</div>