<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUser */
?>
<div id="com_configbox" class="cb-content">
<div id="view-user">
<div id="layout-login">

<h1 class="componentheading"><?php echo KText::_('Login');?></h1>	

<form method="post" action="<?php echo KLink::getRoute('index.php', true, CbSettings::getInstance()->get('securecheckout'));?>">
	
	<table>
		<tr>
			<td><label for="configbox_email"><?php echo KText::_('Email Address');?></label></td>
			<td><input id="configbox_email" type="text" name="email" value="" /></td>
		</tr>
		<tr>
			<td><label for="configbox_email"><?php echo KText::_('Password');?></label></td>
			<td><input id="configbox_email" type="password" name="password" value="" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submitform" value="<?php echo KText::_('Login');?>" /></td>
		</tr>
	</table>
	
	<p><?php echo KText::_('No account yet?');?>&nbsp;<a href="<?php echo KLink::getRoute('index.php?view=user&layout=register');?>"><?php echo KText::_('Register');?></a></p>

	<div class="hidden-fields">
		<input type="hidden" name="option" 			value="com_configbox" />
		<input type="hidden" name="controller" 		value="user" />
		<input type="hidden" name="task" 			value="loginUser" />
		<input type="hidden" name="return_failure" 	value="<?php echo urlencode(KLink::getRoute('index.php?option=com_configbox&view=user&layout=login',false, CbSettings::getInstance()->get('securecheckout')));?>" />
		<input type="hidden" name="return_success" 	value="<?php echo urlencode(KLink::getRoute('index.php?option=com_configbox&view=user', false, CbSettings::getInstance()->get('securecheckout')));?>" />
	</div>
</form>

</div>
</div>
</div>