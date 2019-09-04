<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminnotification */
?>

<div id="notifications-keys">
	<h3><?php echo KText::_('Notification snippets');?></h3>
	<p><?php echo KText::_('With notification snippets, you can insert precompiled data in your notification text.');?></p>
	<ul>
		<li><b><?php echo KText::_('Order overview');?>:</b>{element_order_overview}</li>
		<li><b><?php echo KText::_('Shop information');?>:</b>{element_store_information}</li>
	</ul>
	
	<h3><?php echo KText::_('Placeholders for customer data');?></h3>
	<p><?php echo KText::_('You can use these placeholders to use the customer data in your email templates.');?></p>
	<p><?php echo KText::_('HELP_TEXT_NOTIFICATIONS_GENDERED_WORDING');?></p>
	<h3><?php echo KText::_('Order address data');?></h3>
	<ul>
		<?php foreach ($this->userKeys as $key) { ?>
			<li>{<?php echo $key;?>}</li>
		<?php } ?>
	</ul>
	<h3><?php echo KText::_('Order data');?></h3>
	<ul>
		<?php foreach ($this->orderKeys as $key) { ?>
			<li>{<?php echo $key;?>}</li>
		<?php } ?>
	</ul>
	<h3><?php echo KText::_('Store Information');?></h3>
	<ul>
		<?php foreach ($this->storeKeys as $key) { ?>
			<li>{<?php echo $key;?>}</li>
		<?php } ?>
	</ul>
</div>