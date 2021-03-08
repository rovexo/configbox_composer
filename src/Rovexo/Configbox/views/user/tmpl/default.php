<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUser */
?>
<div <?php echo $this->getViewAttributes();?>>

	<h1 class="page-title"><?php echo KText::_('Your Account')?></h1>

	<?php if ($this->isTemporaryAccount) { ?>
		<div class="account-temporary-notice">
			<p><?php echo KText::_('Your account is temporary, when you provide your address, we set you up with a permanent customer account and send your password via email.');?></p>
			<p><?php echo KText::_('If you already have an account, please login to see your account information and order history.');?></p>
		</div>
	<?php } ?>

	<div class="row">

		<div class="col-md-6">
			<h2><?php echo KText::_('Billing Address');?></h2>

			<table>
				<tr>
					<td class="key"><?php echo KText::_('Company');?>:</td>
					<td><?php echo hsc($this->customer->billingcompanyname);?></td>
				</tr>

				<tr>
					<td class="key"><?php echo KText::_('First Name');?>:</td>
					<td><?php echo hsc($this->customer->billingfirstname);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Last Name');?>:</td>
					<td><?php echo hsc($this->customer->billinglastname);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Address 1');?>:</td>
					<td><?php echo hsc($this->customer->billingaddress1);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Address 2');?>:</td>
					<td><?php echo hsc($this->customer->billingaddress2);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('ZIP Code');?>:</td>
					<td><?php echo hsc($this->customer->billingzipcode);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('City');?>:</td>
					<td><?php echo hsc($this->customer->billingcity);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Country');?>:</td>
					<td><?php echo hsc($this->customer->billingcountryname);?></td>
				</tr>
				<?php if ($this->customer->billingstate) { ?>
				<tr>
					<td class="key"><?php echo KText::_('State');?>:</td>
					<td><?php echo hsc($this->customer->billingstatename);?></td>
				</tr>
				<?php } ?>
				<tr>
					<td class="key"><?php echo KText::_('Email');?>:</td>
					<td><?php echo hsc($this->customer->billingemail);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Phone');?>:</td>
					<td><?php echo hsc($this->customer->billingphone);?></td>
				</tr>
			</table>
		</div>

		<div class="col-md-6">
			<h2><?php echo KText::_('Shipping Address');?></h2>

			<table>
				<tr>
					<td class="key"><?php echo KText::_('Company');?>:</td>
					<td><?php echo hsc($this->customer->companyname);?></td>
				</tr>

				<tr>
					<td class="key"><?php echo KText::_('First Name');?>:</td>
					<td><?php echo hsc($this->customer->firstname);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Last Name');?>:</td>
					<td><?php echo hsc($this->customer->lastname);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Address 1');?>:</td>
					<td><?php echo hsc($this->customer->address1);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Address 2');?>:</td>
					<td><?php echo hsc($this->customer->address2);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('ZIP Code');?>:</td>
					<td><?php echo hsc($this->customer->zipcode);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('City');?>:</td>
					<td><?php echo hsc($this->customer->city);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Country');?>:</td>
					<td><?php echo hsc($this->customer->countryname);?></td>
				</tr>
				<?php if ($this->customer->state) { ?>
				<tr>
					<td class="key"><?php echo KText::_('State');?>:</td>
					<td><?php echo hsc($this->customer->statename);?></td>
				</tr>
				<?php } ?>
				<tr>
					<td class="key"><?php echo KText::_('Email');?>:</td>
					<td><?php echo hsc($this->customer->email);?></td>
				</tr>
				<tr>
					<td class="key"><?php echo KText::_('Phone');?>:</td>
					<td><?php echo hsc($this->customer->phone);?></td>
				</tr>
			</table>
		</div>

	</div>

	<div class="wrapper-buttons">
		<a rel="nofollow" href="<?php echo $this->urlEditForm;?>" class="btn btn-primary button-change-address trigger-change-address"><?php echo KText::_('Change');?></a>
	</div>

	<?php if (count($this->orderRecords)) { ?>

		<h2><?php echo KText::_('Order History');?></h2>

		<table class="table">
			<tr>
				<th class="orders-id"><?php echo KText::_('Order ID');?></th>
				<th class="orders-date"><?php echo KText::_('Date');?></th>
				<?php if (ConfigboxPermissionHelper::canSeePricing()) { ?>
					<th class="orders-total"><?php echo KText::_('Total');?></th>
				<?php } ?>
				<th class="orders-status"><?php echo KText::_('Status');?></th>
				<th class="orders-actions"><?php echo KText::_('Actions');?></th>
			</tr>

			<?php foreach ($this->orderRecords as $orderRecord) { ?>
				<tr class="order-status-<?php echo intval($orderRecord->status);?>">
					<td class="orders-id"><?php echo (int)$orderRecord->id;?></td>
					<td class="orders-date"><?php echo hsc( KenedoTimeHelper::getFormatted($orderRecord->created_on) );?></td>
					<?php if (ConfigboxPermissionHelper::canSeePricing()) { ?>
						<td class="orders-total"><?php echo cbprice($orderRecord->payableAmount);?></td>
					<?php } ?>
					<td class="orders-status"><?php echo hsc($orderRecord->statusString);?></td>
					<td class="orders-actions">

						<?php if ($orderRecord->toUserOrders) { ?>
							<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.(int)$orderRecord->id);?>"><?php echo KText::_('Display')?></a>
						<?php } else { ?>
							<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&view=cart&cart_id='.(int)$orderRecord->cart_id);?>"><?php echo KText::_('Display')?></a>
						<?php } ?>

						<?php if (ConfigboxPermissionHelper::isPermittedAction('removeOrderRecord', $orderRecord)) { ?>
							<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=user&view=user&task=removeOrder&cid[]='.(int)$orderRecord->id, true);?>"><?php echo KText::_('Remove')?></a>
						<?php } ?>

						<?php if ($orderRecord->invoice_released) { ?>
							<span class="download-invoice">
								<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=invoice&output_mode=view_only&order_id='.(int)$orderRecord->id, true);?>"><?php echo KText::_('Download Invoice')?></a>
							</span>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>

</div>