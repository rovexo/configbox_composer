<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-sofortueberweisung_user_id" class="property-name-sofortueberweisung_user_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Customer number');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sofortueberweisung_user_id" value="<?php echo hsc($this->settings->get('sofortueberweisung_user_id'));?>" />
		</div>
	</div>
</div>

<div id="sofortueberweisung_project_id" class="property-name-sofortueberweisung_project_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Project number');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sofortueberweisung_project_id" value="<?php echo hsc($this->settings->get('sofortueberweisung_project_id'));?>" />
		</div>
	</div>
</div>

<div id="sofortueberweisung_project_password" class="property-name-sofortueberweisung_project_password kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Project Password');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sofortueberweisung_project_password" value="<?php echo hsc($this->settings->get('sofortueberweisung_project_password'));?>" />
		</div>
	</div>
</div>

<div id="sofortueberweisung_notification_password" class="property-name-sofortueberweisung_notification_password kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Notification Password');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="sofortueberweisung_notification_password" value="<?php echo hsc($this->settings->get('sofortueberweisung_notification_password'));?>" />
		</div>
	</div>
</div>

<div>
	
	<p><b><?php echo KText::_('Configuration in Sofort merchant panel');?>:</b></p>
	<p><?php echo KText::_('You need a SOFORT Classic project.');?> <a href="http://www.sofort.com" target="_blank">www.sofort.com</a></p>
	<p><?php echo KText::_('You find the settings mentioned below in the merchant panel at My projects.')?>
	<ul>
		<li><?php echo KText::_("Set a notification password in the payment service provider's admin panel at Extended Settings -> Passwords and hash algorithm");?></li>
		<li><?php echo KText::_("Set Hash algorithm to SHA256 at Extended settings -> Passwords and hash algorithm");?></li>
		<li><?php echo KText::_("Add a HTTP notification and enter -USER_VARIABLE_4- in Notification URL and POST in Method at Extended settings -> Notifications");?></li>
		<li><?php echo KText::_('Enter -USER_VARIABLE_2- in Success link and -USER_VARIABLE_3- in Abort link and Timeout link at Extended settings -> Shop interface settings.');?></li>
	</ul>
</div>