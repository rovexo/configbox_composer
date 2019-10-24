<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-partner_id" class="property-name-partner_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Partner ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="partner_id" value="<?php echo hsc($this->settings->get('partner_id'));?>" />
		</div>
	</div>
</div>

<div id="property-name-password" class="property-name-password kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Passwort');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="password" value="<?php echo hsc($this->settings->get('password'));?>" />
		</div>
	</div>
</div>

<div id="property-name-choose" class="property-name-choose kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Customer can choose payment type');?>
	</div>
	<div class="property-body">
		<input id="chooseyes" type="radio" name="choose" value="1" <?php echo ($this->settings->get('choose') == 1) ? 'checked = "checked"':''; ?> /><label for="chooseyes"><?php echo KText::_('CBYES');?></label>
		<input id="chooseno"  type="radio" name="choose" value="0" <?php echo ($this->settings->get('choose') == 0) ? 'checked = "checked"':''; ?> /><label for="chooseno"><?php echo KText::_('CBNO');?></label>
	</div>
</div>

<div id="property-name-brand" class="property-name-brand kenedo-property property-type-dropdown">
	<div class="property-label">
		<?php echo KText::_('Payment Type');?>
	</div>
	<div class="property-body">
		<select name="brand">
			<option <?php echo ($this->settings->get('brand','') == 'ELV') ? 'selected="selected"':''; ?> value="ELV">Elektronisches Lastschriftverfahren</option>
			<option <?php echo ($this->settings->get('brand','') == 'VISA') ? 'selected="selected"':''; ?> value="VISA">Kreditkarte Visa / Verified by Visa</option>
			<option <?php echo ($this->settings->get('brand','') == 'ECMC') ? 'selected="selected"':''; ?> value="ECMC">Kreditkarte Mastercard / Mastercard SecureCode</option>
			<option <?php echo ($this->settings->get('brand','') == 'DINERS') ? 'selected="selected"':''; ?> value="DINERS">Kreditkarte Diners</option>
			<option <?php echo ($this->settings->get('brand','') == 'AMEX') ? 'selected="selected"':''; ?> value="AMEX">Kreditkarte AMEX</option>
			<option <?php echo ($this->settings->get('brand','') == 'JCB') ? 'selected="selected"':''; ?> value="JCB">Kreditkarte JCB</option>
			<option <?php echo ($this->settings->get('brand','') == 'GIROPAY') ? 'selected="selected"':''; ?> value="GIROPAY">Onlineüberweisung giropay</option>
		</select>
	</div>
</div>

<div>
	<p><?php echo KText::_('Infos');?>: <a href="https://www.vr-epay.info/gad/index.php" target="_blank">www.vr-epay.info</a></p>
</div>
