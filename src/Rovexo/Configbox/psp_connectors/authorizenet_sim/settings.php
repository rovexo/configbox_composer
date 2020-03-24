<?php
defined('CB_VALID_ENTRY') or die();
?>
	
<div id="property-name-api_login_id" class="property-name-api_login_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('API login ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="api_login_id" value="<?php echo hsc($this->settings->get('api_login_id'));?>" />
		</div>
	</div>
</div>

<div id="property-name-transactionkey" class="property-name-transactionkey kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Transaction Key');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="transactionkey" value="<?php echo hsc($this->settings->get('transactionkey'));?>" />
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
<div><?php echo KText::_('At Account -> Transaction Format Settings -> Relay Response, enter this exact URL and update it when your domain or language settings change.');?></div>
<?php
$requestUrl = KPATH_URL_BASE.'/index.php?option=com_configbox&controller=ajaxapi&output_mode=view_only&task=getNotificationUrl&payment_class=authorizenet_sim';
$feedbackUrl = file_get_contents($requestUrl);
?>
<div>
	
	<input type="text" class="form-control notification_url_textfield" name="notification_url" value="<?php echo hsc($feedbackUrl);?>" />
</div>
