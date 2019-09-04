<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-customer_id_production" class="property-name-customer_id_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Customer ID - Production');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="customer_id_production" value="<?php echo hsc($this->settings->get('customer_id_production'));?>" />
		</div>
	</div>
</div>

<div id="property-name-secret_production" class="property-name-secret_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Secret - Production');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="secret_production" value="<?php echo hsc($this->settings->get('secret_production'));?>" />
		</div>
	</div>
</div>

<div id="property-name-customer_id_test" class="property-name-customer_id_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Customer ID - Test');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="customer_id_test" value="<?php echo hsc($this->settings->get('customer_id_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-secret_test" class="property-name-secret_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Secret - Test');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="secret_test" value="<?php echo hsc($this->settings->get('secret_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Test Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('CBYES');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('CBNO');?></label>
	</div>
</div>


<div id="property-name-service_url" class="property-name-service_url kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('URL to imprint page');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="service_url" value="<?php echo hsc($this->settings->get('service_url'));?>" />
		</div>
	</div>
</div>

<div id="property-name-paymenttype" class="property-name-paymenttype kenedo-property property-type-dropdown">
	<div class="property-label">
		<?php echo KText::_('Payment method');?>
	</div>
	<div class="property-body">
		<select name="paymenttype">
			<option <?php echo ($this->settings->get('paymenttype','') == 'SELECT') ? 'selected="selected"':''; ?> value="SELECT">Auswahl in QPAY</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'CCARD') ? 'selected="selected"':''; ?> value="CCARD">Kreditkarte</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'MAESTRO') ? 'selected="selected"':''; ?> value="MAESTRO">Maestro SecureCode</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'PBX') ? 'selected="selected"':''; ?> value="PBX">paybox</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'PSC') ? 'selected="selected"':''; ?> value="PSC">paysafecard</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'ELV') ? 'selected="selected"':''; ?> value="ELV">Lastschriftverfahren</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'QUICK') ? 'selected="selected"':''; ?> value="QUICK">@Quick</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'EPS') ? 'selected="selected"':''; ?> value="EPS">eps Online-Überweisung</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'MIA') ? 'selected="selected"':''; ?> value="MIA">Handyrechnung</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'IDL') ? 'selected="selected"':''; ?> value="IDL">iDEAL</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'GIROPAY') ? 'selected="selected"':''; ?> value="GIROPAY">Giropay</option>
			<option <?php echo ($this->settings->get('paymenttype','') == 'CCARD-MOTO') ? 'selected="selected"':''; ?> value="CCARD-MOTO">Kreditkarte-Backoffice</option>
		</select>
	</div>
</div>
