<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-psp_id_test" class="property-name-psp_id_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('PSPID Test System');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="psp_id_test" value="<?php echo hsc($this->settings->get('psp_id_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-psp_id_production" class="property-name-psp_id_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('PSPID Production System');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="psp_id_production" value="<?php echo hsc($this->settings->get('psp_id_production'));?>" />
		</div>
	</div>
</div>

<div id="property-name-sha_in_passphrase" class="property-name-sha_in_passphrase kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('SHA-IN pass phrase');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sha_in_passphrase" value="<?php echo hsc($this->settings->get('sha_in_passphrase'));?>" />
		</div>
	</div>
</div>

<div id="property-name-sha_out_passphrase" class="property-name-sha_out_passphrase kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('SHA-OUT pass phrase');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sha_out_passphrase" value="<?php echo hsc($this->settings->get('sha_out_passphrase'));?>" />
		</div>
	</div>
</div>

<p><?php echo KText::_('You find the pass phrases in the PostFinance Backoffice tool at Configuration - Technical Information in Global security parameters and Transaction Feedback.');?></p>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('Test system');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('Production system');?></label>
	</div>
</div>

<div>
	
	<p><b><?php echo KText::_('Configuration of the PostFinance account');?>:</b></p>
	<p><?php echo KText::_('You need a PostFinance e-Commerce account. The connector supports PostFinance Basic, Startup and Professional.');?> <a href="https://www.postfinance.ch/en/biz/prod/eserv/epay/providing/offer.html" target="_blank"><?php echo KText::_('Link to product page');?></a></p>
	<p><?php echo KText::_('Here you find the settings you need to setup your account to work with ConfigBox. Settings not mentioned can be set as needed. For questions regarding these settings, please refer to the PostFinance customer support.');?></p>
	<h3><?php echo KText::_('At Configuration - Technical Information - Global transaction parameters');?></h3>
	<ul>
		<li><?php echo KText::_('Default operation code - Sale');?></li>
		<li><?php echo KText::_('Payment retry - 10 or more');?></li>
		<li><?php echo KText::_('Processing for individual transactions - Online but switch to offline when the online acquiring system is unavailable.');?></li>
	</ul>
	
	<h3><?php echo KText::_('At Configuration - Technical Information - Global security parameters');?></h3>
	<ul>
		<li><?php echo KText::_('Compose the string to be hashed by concatenating the value of - Each parameter followed by the passphrase.');?></li>
		<li><?php echo KText::_('Hash algorithm - SHA-256.');?></li>
		<li><?php echo KText::_('Character encoding - Use the character encoding expected...');?></li>
		<li><?php echo KText::_('Enable JavaScript check on template - No');?></li>
		<li><?php echo KText::_('Allow usage of static template - No');?></li>
		<li><?php echo KText::_('Allow usage of dynamic template - No');?></li>
	</ul>
	
	<h3><?php echo KText::_('At Configuration - Technical Information - Data and origin verification');?></h3>
	<ul>
		<li><?php echo KText::_('URL of the merchant page.. - Leave the field empty');?></li>
		<li><?php echo KText::_('SHA-IN pass phrase - Choose a password here and enter the same password at SHA-IN pass phrase in the ConfigBox PSP settings on this page.');?></li>
	</ul>
	
	<h3><?php echo KText::_('At Configuration - Technical Information - Transaction Feedback');?></h3>
	<ul>
		<li><?php echo KText::_('HTTP redirection in the browser - Leave the fields empty');?></li>
		<li><?php echo KText::_('I would like to receive transaction feedback parameters on the redirection URLs. - No');?></li>
		<li><?php echo KText::_('I would like PostFinance to display a short text to the customer... - No');?></li>
	</ul>
	<div><i><?php echo KText::_('Direct HTTP server-to-server request');?></i></div>
	<ul>
		<li><?php echo KText::_('Timing of the request - Online but switch to a deferred request when the online requests fail.');?></li>
		<?php
		$requestUrl = KPATH_URL_BASE.'/index.php?option=com_configbox&controller=ajaxapi&format=raw&task=getNotificationUrl&payment_class=postfinance';
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
		<li>
			<?php echo KText::_('URL of the merchants post-payment page - enter this excact URL in both fields and update the entry when the SEF settings of your platform change.');?>
			<br />
			<input class="form-control" type="text" class="notification_url_textfield" name="notification_url" value="<?php echo $feedbackUrl;?>" />
		</li>
		
		<li><?php echo KText::_('Request method - POST');?></li>
	</ul>
	<div><i><?php echo KText::_('All transaction submission modes');?></i></div>
	<ul>
		<li><?php echo KText::_('SHA-OUT pass phrase - Choose a password here and enter the same password at SHA-OUT pass phrase in the ConfigBox PSP settings on this page.');?></li>
		<li><?php echo KText::_('Timing of the request - Only at the time of the order authorisation request.');?></li>
		<li><?php echo KText::_('URL on which the merchant wishes to receive... - leave empty');?></li>
	</ul>
	<h3><?php echo KText::_('At Configuration - Technical Information - Test Info');?></h3>
	<ul>
		<li><?php echo KText::_('Test info - I would like to simulate transaction results based on the card number.');?></li>
	</ul>
	<p><?php echo KText::_('When in test mode you can use the test credit card numbers mentioned on this page to simulate transactions.');?></p>
	<p><?php echo KText::_('The wording in the PostFinance Backoffice is from May 2013. If texts or locations of settings have changed substantially, please let us know through support channels.');?>
</div>

