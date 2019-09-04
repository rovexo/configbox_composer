<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewInvoice */

if ($this->orderRecord->transaction_data) {
	$data = json_decode($this->orderRecord->transaction_data,true);
	
	?>
	<div class="billsafe-info">
		
		<div class="billsafe-info-legal"><?php echo hsc($data['legalNote'])?></div>
		<div class="billsafe-info-timing"><?php echo hsc($data['note'])?></div>
		
		<div class="billsafe-receipient">
			
			<table cellpadding="4">
				<tr>
					<td><?php echo KText::_('BANK ACCOUNT HOLDER');?></td>
					<td><?php echo hsc($data['recipient']);?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('BANK NAME');?></td>
					<td><?php echo hsc($data['bankName']);?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('IBAN');?></td>
					<td><?php echo hsc($data['iban']);?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('BIC');?></td>
					<td><?php echo hsc($data['bic']);?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('Payable amount');?></td>
					<td><?php echo hsc($data['currencyCode']) . ' '.hsc(number_format($data['amount'],2));?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('REFERENCE FIELD 1');?></td>
					<td><?php echo hsc($data['reference']);?></td>
				</tr>
				<tr>
					<td><?php echo KText::_('REFERENCE FIELD 2');?></td>
					<td><?php echo hsc($data['shopUrl']);?></td>
				</tr>
			</table>
			
		</div>
		
	</div>
	<?php
	
}