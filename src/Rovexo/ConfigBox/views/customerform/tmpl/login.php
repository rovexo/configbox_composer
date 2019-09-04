<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div class="recurring-customer-login row">

	<div class="wrapper-show-login-box col-xs-12">
		<input type="checkbox" value="1" id="show-login"><label for="show-login"><?php echo KText::_('I have an account');?></label>
	</div>

	<div class="login-wrapper col-sm-6">

		<div class="login-box">

			<input type="text" class="form-control input-username" name="username" placeholder="<?php echo KText::_('Email');?>" />
			<input type="password" class="form-control input-password" name="password" placeholder="<?php echo KText::_('Password');?>" />

			<div class="feedback"></div>

			<a class="btn btn-default button-recover-password trigger-recover-password"><?php echo KText::_('Recover Password');?></a>
			<a class="btn btn-primary button-login trigger-login"><?php echo KText::_('Login');?></a>

		</div>

		<div class="recover-box">

			<div class="info-text"><?php echo KText::_('Please enter your email address.');?></div>

			<input type="text" class="form-control input-username" name="username" placeholder="<?php echo KText::_('Email');?>" />

			<div class="feedback"></div>

			<a class="btn btn-default button-cancel trigger-cancel-recovery"><?php echo KText::_('Cancel');?></a>
			<a class="btn btn-primary button-recover-password trigger-request-verification-code"><?php echo KText::_('Recover Password');?></a>

		</div>

		<div class="change-password-box">

			<div class="info-text"><?php echo KText::_('We have sent a verification code to your email address. Please enter the code and pick a new password.');?></div>

			<input class="form-control input-verification" type="text" id="verification_code" name="verification_code" value="" placeholder="<?php echo KText::_('Verification Code');?>" />
			<input class="form-control input-new-password" type="password" id="new_password" name="new_password" value="" placeholder="<?php echo KText::_('New Password');?>" />

			<div class="feedback"></div>

			<a class="btn btn-default button-cancel trigger-cancel-recovery"><?php echo KText::_('Cancel');?></a>
			<a class="btn btn-primary button-cancel trigger-change-password"><?php echo KText::_('Change Password');?></a>

		</div>

	</div>

</div>