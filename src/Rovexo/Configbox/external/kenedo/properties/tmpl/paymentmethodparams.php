<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyPaymentmethodparams
 */
$connectorName = $this->data->connector_name;

if (empty($connectorName)) {
	?>
	<p><?php echo KText::_('Please save your payment method to see payment service provider specific settings here.');?></p>
	<p><?php echo KText::_('Please note that your payment method is inactive by default until you activate it with the field called Active.');?></p>
	<?php
}
else {
	$tag = KenedoPlatform::p()->getLanguageTag();

	$connectorFolder = ConfigboxPspHelper::getPspConnectorFolder($connectorName);

	$file = $connectorFolder.'/language/'. $tag . '/' . $tag.'.ini';
	if (is_file($file)) {
		KText::load($file, KText::getLanguageTag());
	}
	$settingsFile = $connectorFolder.'/settings.php';

	$this->settings = new KStorage($this->data->{$this->propertyName});

	if (is_file($settingsFile) && strlen(trim(file_get_contents($settingsFile)))) {
		include($settingsFile);
	}
	else {
		echo KText::_('There are no specific settings for this payment service provider.');
	}
}