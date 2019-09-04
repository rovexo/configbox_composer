<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUser */
?>
<div id="com_configbox" class="cb-content">
<div id="view-user">
<div id="layout-register">

<h1 class="componentheading"><?php echo KText::_('Register');?></h1>	

<form method="post" action="<?php echo KLink::getRoute('index.php', true, CbSettings::getInstance()->get('securecheckout'));?>">
	
	<table>
		<tr>
			<td><label for="configbox_firstname"><?php echo KText::_('First Name');?></label></td>
			<td><input id="configbox_firstname" type="text" name="firstname" value="" /></td>
		</tr>
		<tr>
			<td><label for="configbox_lastname"><?php echo KText::_('Last Name');?></label></td>
			<td><input id="configbox_lastname" type="text" name="lastname" value="" /></td>
		</tr>
		<tr>
			<td><label for="configbox_email"><?php echo KText::_('Email Address');?></label></td>
			<td><input id="configbox_email" type="text" name="email" value="" /></td>
		</tr>
		<tr>
			<td><label for="configbox_password"><?php echo KText::_('Password');?></label></td>
			<td><input id="configbox_password" type="password" name="password" value="" /></td>
		</tr>
		<tr>
			<td><label for="configbox_password2"><?php echo KText::_('Repeat Password');?></label></td>
			<td><input id="configbox_password2" type="password" name="passwordconf" value="" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submitform" value="<?php echo KText::_('Register');?>" /></td>
		</tr>
	</table>


	
	<div>
		<input type="hidden" name="option" 		value="com_configbox" />
		<input type="hidden" name="task" 		value="registerUser" />
		<input type="hidden" name="controller" 	value="user" />
	</div>

</form>
		
</div>
</div>
</div>