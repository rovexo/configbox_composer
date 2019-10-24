<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-installation_id" class="property-name-installation_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Installation ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="installation_id" value="<?php echo hsc($this->settings->get('installation_id'));?>" />
		</div>
	</div>
</div>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Test Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('CBYES');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('CBNO');?></label>
	</div>
</div>

<p><b><?php echo KText::_('Configuration in the merchant interface');?>:</b></p>
<ul>
	<li>Log in to the Merchant Interface.</li>
	<li>Select <b>Installations</b> from the left hand navigation.</li>
	<li>Choose an installation and select the <b>Integration Setup</b> button for either the TEST or PRODUCTION environment.</li>
	<li>Check the Enable Payment Response checkbox.</li>
	<li>Enter the <b>Payment Response URL</b> as stated below and update it when your domain or language settings change.</li>
	<li>Select the Save Changes button.</li>
</ul>
<?php
$requestUrl = KPATH_URL_BASE.'/index.php?option=com_configbox&controller=ajaxapi&format=raw&task=getNotificationUrl&payment_class=worldpay_bg';
$feedbackUrl = file_get_contents($requestUrl);
if ($feedbackUrl == false) {
	?>
	<script type="text/javascript">
	cbj(document).ready(function(){
		cbj.get("<?php echo $requestUrl;?>")
		.done(function(data) {
		  cbj('.notification_url_textfield').val(data);
		});
	});
	</script>
	<?php
}
?>
<div>
	<input class="form-control" type="text" class="notification_url_textfield" name="notification_url" value="<?php echo $feedbackUrl;?>" />
</div>