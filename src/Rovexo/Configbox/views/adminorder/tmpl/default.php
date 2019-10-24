<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorder */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

<div class="page-content kenedo-details-form">

<div class="floatright"><a class="backend-button-small ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminorders');?>"><?php echo KText::_('Back');?></a></div>

<h1 class="kenedo-page-title"><?php echo KText::sprintf('Order ID %s',$this->orderRecord->id);?> (<?php echo KText::sprintf('Ordered on %s', KenedoTimeHelper::getFormatted($this->orderRecord->created_on) );?>)</h1>

<div class="order-meta-info">ConfigBox Grand Order ID: <?php echo $this->orderRecord->cart_id; ?>, <?php echo KText::_('Store ID');?>: <?php echo $this->orderRecord->store_id;?></div>

<fieldset class="products">
	<legend><?php echo KText::_('Ordered Products')?></legend>
	
	<div class="order-overview">
		<?php echo $this->orderRecordHtml; ?>
	</div>
	
	<div class="clear"></div>
	
</fieldset>


<div class="order-controls">
	
	<fieldset class="slip">
		<legend><?php echo KText::_('Manufacturing Slip');?></legend>
		<div>
			<a class="backend-button-small" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminorderslip&order_id='.$this->orderRecord->id);?>"><?php echo KText::_('Download');?></a>
		</div>
	</fieldset>
	
	<fieldset class="status">
		<legend><?php echo KText::_('Status');?></legend>
		<form method="post" action="<?php KLink::getRoute('index.php');?>">
			<?php echo $this->statusSelect;?>
			<input type="submit" name="updatestatus" value="<?php echo KText::_('Update');?>" class="backend-button-small" />
			
			<input type="hidden" name="order_id" value="<?php echo (int)$this->orderRecord->id;?>" />
			<input type="hidden" id="option" name="option" value="<?php echo hsc($this->component);?>" />
			<input type="hidden" id="controller" name="controller" value="<?php echo hsc($this->controllerName);?>" />
			<input type="hidden" name="task" value="update_status" />
		</form>
	</fieldset>
	
	<?php if ($this->showInvoicingBox) { ?>
	
		<fieldset class="invoice">
			<legend><?php echo KText::_('Invoice');?></legend>
			
			<?php if ($this->orderRecord->invoice_released) { ?>
				<div>
					<?php echo KText::sprintf('Invoice %s is released.', $this->invoiceData->invoice_number_prefix.$this->invoiceData->invoice_number_serial);?>
					<?php echo '<a href="'.KLink::getRoute('index.php?option=com_configbox&controller=invoice&task=display&order_id='.$this->orderRecord->id).'">'.KText::_('Download').'</a>';?>
				</div>
			<?php } elseif($this->invoiceGenerationMode == 0) { ?>
				<div><?php echo KText::_('Invoice is not yet released.');?></div>
			<?php } elseif($this->invoiceGenerationMode == 1) { ?>
				<div><?php echo KText::_('Invoice is not yet released.');?> <?php echo '<a href="'.KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=release_invoice&order_id='.$this->orderRecord->id).'">'.KText::_('Release').'</a>';?></div>
			<?php } elseif($this->invoiceGenerationMode == 2) { ?>
				
				<form enctype="multipart/form-data" method="post" action="<?php KLink::getRoute('index.php');?>">
					
					<div><?php echo KText::_('Invoice is not yet released.');?></div>
					
					<div class="form-item invoice-number-prefix">
						<label for="invoice_number_prefix"><?php echo KText::_('Invoice Number Prefix');?></label>
						<input id="invoice_number_prefix" type="text" name="invoice_number_prefix" value="<?php echo hsc($this->invoicePrefix);?>" />
					</div>
					
					<div class="form-item invoice-number-serial">
						<label for="invoice_number_serial"><?php echo KText::_('Invoice Number Serial');?></label>
						<input id="invoice_number_serial" type="text" name="invoice_number_serial" value="<?php echo hsc($this->nextInvoiceSerial);?>" />
					</div>
					
					<div class="form-item invoice-file">
						<label for="invoice_file"><?php echo KText::_('Invoice File');?></label>
						<input id="invoice_file" type="file" name="invoice_file" />
					</div>
					
					<div class="buttons">
						<input type="submit" name="updatestatus" value="<?php echo KText::_('Release Invoice');?>" class="backend-button-small" />
					</div>
					
					<div class="hidden-fields">
						<input type="hidden" name="order_id" value="<?php echo (int)$this->orderRecord->id;?>" />
						<input type="hidden" name="controller" value="adminorders" />
						<input type="hidden" name="option" value="com_configbox" />
						<input type="hidden" name="task" value="insert_invoice" />
					</div>
					
				</form>
				
			<?php } ?>
		</fieldset>
		
	<?php } ?>

</div>

<?php if ($this->orderRecord->custom_1 or $this->orderRecord->custom_2 or $this->orderRecord->custom_3 or $this->orderRecord->custom_4) { ?>
	<fieldset class="custom-order-values">
		<legend><?php echo KText::_('Custom Order Values');?></legend>
		<?php for ($i = 1; $i <= 4; $i++) { 
			if (!empty($this->orderRecord->{'custom_'.$i})) {
				?>
				<div class="custom-1">
					<div class="key"><?php echo KText::sprintf('Custom Field %s',$i);?></div>
					<div class="value">
						<?php echo hsc($this->orderRecord->{'custom_'.$i})?>
					</div>
				</div>
				<?php
			} 
		} ?>
		<div class="clear"></div>
	</fieldset>

<?php } ?>
	
	
<div class="clear"></div>


<?php if ($this->orderRecord->payment) { ?>
	<div class="order-record-payment-info">
		<?php
		$pspFolder = ConfigboxPspHelper::getPspConnectorFolder($this->orderRecord->payment->connector_name);
		
		$file = $pspFolder.DS.'order_details.php';
		
		if (!empty($this->orderRecord->payment->id) && is_file($file)) {
			include($file);
		}
		?>
		<div class="clear"></div>
	</div>
<?php } ?>


<fieldset class="admin-order-address">
	<legend><?php echo KText::_('Order billing and shipping information')?></legend>
	
	<div id="addressinfo">
		
		<div style="float:left;margin-right:40px" id="billinginfo">
				<h4><?php echo KText::_('Billing');?></h4>
				<table class="user-info-table user-info-table-billing">
					<tr>
						<td class="key"><?php echo KText::_('Company');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingcompanyname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Gender');?>:</td>
						<td><?php if ($this->orderRecord->orderAddress->billinggender !== '') echo ($this->orderRecord->orderAddress->billinggender == 1)? KText::_('Male'):KText::_('Female');?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Salutation');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingsalutation);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('First Name');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingfirstname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Last Name');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billinglastname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Address 1');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingaddress1);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Address 2');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingaddress2);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('ZIP Code');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingzipcode);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('City');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingcity);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('County');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingcounty);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Country');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingcountryname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('State');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->billingstatename);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Email');?>:</td>
						<td><a href="mailto:<?php echo hsc($this->orderRecord->orderAddress->billingemail);?>"><?php echo hsc($this->orderRecord->orderAddress->billingemail);?></a></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Phone');?>:</td>
						<td><?php echo $this->orderRecord->orderAddress->billingphone;?></td>
					</tr>
				</table>
			</div>
			
			<?php if ($this->orderRecord->orderAddress->samedelivery == 0) { ?>
			
			<div style="float:left;margin-right:40px" id="deliveryinfo">
				<h4><?php echo KText::_('Delivery');?></h4>
				<table class="user-info-table user-info-table-delivery">
					<tr>
						<td class="key"><?php echo KText::_('Company');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->companyname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Gender');?>:</td>
						<td><?php if ($this->orderRecord->orderAddress->gender != '') echo ($this->orderRecord->orderAddress->gender == 1)? KText::_('Male'):KText::_('Female');?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Salutation');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->salutation);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('First Name');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->firstname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Last Name');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->lastname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Address 1');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->address1);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Address 2');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->address2);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('ZIP Code');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->zipcode);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('City');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->city);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('County');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->county);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Country');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->countryname);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('State');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->statename);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Email');?>:</td>
						<td><a href="mailto:<?php echo hsc($this->orderRecord->orderAddress->email);?>"><?php echo hsc($this->orderRecord->orderAddress->email);?></a></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Phone');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->phone);?></td>
					</tr>
				</table>
			</div>
			<?php } ?>
			<div style="float:left; max-width:400px;">
				<h4><?php echo KText::_('Other info');?></h4>
				<table class="user-info-table user-info-table-others">
					<tr>
						<td><?php echo KText::_('Billing address same as delivery');?>:</td>
						<td><?php echo ($this->orderRecord->orderAddress->samedelivery == 1)? KText::_('CBYES'):KText::_('CBNO');?></td>
					</tr>
					<tr>
						<td><?php echo KText::_('Newsletter');?>:</td>
						<td><?php echo ($this->orderRecord->orderAddress->newsletter == 1)? KText::_('CBYES'):KText::_('CBNO');?></td>
					</tr>
					
					<tr>
						<td><?php echo KText::_('Preferred Language');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->language_name);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('VAT IN');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->vatin);?></td>
					</tr>
					<tr>
						<td class="key"><?php echo KText::_('Platform User ID');?>:</td>
						<td><?php echo hsc($this->orderRecord->orderAddress->platform_user_id);?></td>
					</tr>
					<?php if ($this->orderRecord->transaction_id) { ?>
					<tr>
						<td class="key"><?php echo KText::_('Transaction ID');?>:</td>
						<td><?php echo hsc($this->orderRecord->transaction_id);?></td>
					</tr>
					<?php } ?>
					<tr>
						<td><?php echo KText::_('Comment');?>:<br /><br /><?php echo hsc($this->orderRecord->comment);?></td>
					</tr>
					
				</table>
			</div>
			
		</div>
		
		<div class="clear"></div>
		
		<?php if (!empty($this->orderRecord->user_id)) { ?>
			<div class="link-to-customer-edit-form">
				<p><a class="backend-button-small" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincustomers&task=edit&id='.$this->orderRecord->user_id);?>"><?php echo KText::_('Go to customer account data');?></a></p>
				<p><?php echo KText::_('Please note that customer account data and the order billing and shipping information is stored separately. You cannot change an order\'s billing and shipping information.');?></p>
				
			</div>
		<?php } ?>
		
		<?php if ($this->showPlatformUserEditLink) { ?>
			<p><a class="kenedo-new-tab backend-button-small" href="<?php echo $this->urlPlatformUserEditForm;?>"><?php echo KText::_('Go to platform user edit form');?></a></p>
		<?php } ?>
		
		</fieldset>
		
		<div class="clear"></div>
		
		<form id="adminForm" name="adminForm">
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="controller" value="adminorders" />
				<input type="hidden" name="view" value="adminorders" />
				<input type="hidden" name="option" value="com_configbox" />
				<input type="hidden" name="cid" value="<?php echo (int)$this->orderRecord->id;?>" />
			</div>
		</form>
</div> <!-- .page-content -->
</div>
</div>