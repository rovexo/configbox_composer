<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div class="customer-form-sections row<?php echo ($this->customerData->samedelivery) ? '':' show-delivery-fields';?>">

	<div class="col-sm-6 customer-form-section customer-form-section-billing">

		<div class="customer-form-section-heading">
			<div class="text-address-heading"><?php echo KText::_('Billing Address');?></div>
		</div>

		<div class="customer-form-fields">

			<div class="<?php echo $this->fieldCssClasses['billingcompanyname'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingcompanyname" name="billingcompanyname" placeholder="<?php echo KText::_('Company');?>" value="<?php echo hsc($this->customerData->billingcompanyname);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingsalutation_id'];?>">
				<div class="form-field">
					<?php echo ConfigboxUserHelper::getSalutationDropdown('billingsalutation_id', $this->customerData->billingsalutation_id, 'chosen-dropdown');?>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingfirstname'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingfirstname" name="billingfirstname" placeholder="<?php echo KText::_('First Name');?>" value="<?php echo hsc($this->customerData->billingfirstname);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billinglastname'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billinglastname" name="billinglastname" placeholder="<?php echo KText::_('Last Name');?>" value="<?php echo hsc($this->customerData->billinglastname);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingaddress1'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingaddress1" name="billingaddress1" placeholder="<?php echo KText::_('Address');?>" value="<?php echo hsc($this->customerData->billingaddress1);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingaddress2'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingaddress2" name="billingaddress2" placeholder="<?php echo KText::_('Address 2');?>" value="<?php echo hsc($this->customerData->billingaddress2);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<?php if ($this->useCityLists == false) { ?>

				<div class="<?php echo $this->fieldCssClasses['billingzipcode'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="billingzipcode" name="billingzipcode" placeholder="<?php echo KText::_('ZIP code');?>" value="<?php echo hsc($this->customerData->billingzipcode);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['billingcity'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="billingcity" name="billingcity" placeholder="<?php echo KText::_('City');?>" value="<?php echo hsc($this->customerData->billingcity);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

			<?php } ?>

			<div class="<?php echo $this->fieldCssClasses['billingcountry'];?>">
				<div class="form-field">
					<?php echo ConfigboxCountryHelper::createCountrySelect('billingcountry', $this->customerData->billingcountry, KText::_('Select a country'), 'billingstate');?>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingstate'];?>">
				<div class="form-field">
					<?php echo ConfigboxCountryHelper::createStateSelect('billingstate', $this->customerData->billingstate, $this->customerData->billingcountry, NULL, 'billingcounty_id');?>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingcounty_id'];?>">
				<div class="form-field">
					<?php echo ConfigboxCountryHelper::createCountySelect('billingcounty_id', $this->customerData->billingcounty_id, $this->customerData->billingstate, KText::_('Select County'), 'billingcity_id');?>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<?php if ($this->useCityLists == true) { ?>

				<div class="<?php echo $this->fieldCssClasses['billingzipcode'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="billingzipcode" name="billingzipcode" placeholder="<?php echo KText::_('ZIP Code');?>" value="<?php echo hsc($this->customerData->billingzipcode);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['billingcity'];?>">
					<div class="form-field">
						<?php echo ConfigboxCountryHelper::getCityTextInput('billingcity', $this->customerData->billingcity);?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['billingcity_id'];?>">
					<div class="form-field">
						<?php echo ConfigboxCountryHelper::getCitySelect('billingcity_id', $this->customerData->billingcity_id, $this->customerData->billingcounty_id, KText::_('Select City'));?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

			<?php } ?>

			<div class="<?php echo $this->fieldCssClasses['billingemail'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingemail" name="billingemail" placeholder="<?php echo KText::_('Email');?>" value="<?php echo hsc($this->customerData->billingemail);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['billingphone'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="billingphone" name="billingphone" placeholder="<?php echo KText::_('Phone');?>" value="<?php echo hsc($this->customerData->billingphone);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['vatin'];?>">
				<div class="form-field">
					<input class="form-control" type="text" id="vatin" name="vatin" placeholder="<?php echo KText::_('VAT IN');?>" value="<?php echo hsc($this->customerData->vatin);?>" />
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['language_tag'];?>">
				<div class="form-field">
					<?php echo $this->languageDropDownHtml; ?>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<div class="<?php echo $this->fieldCssClasses['newsletter'];?>">
				<div class="form-field">
					<label><?php echo KText::_('Newsletter');?></label>
					<input type="radio" <?php echo ($this->customerData->newsletter == 1)? 'checked="checked"':'';?> id="newsletter-yes" name="newsletter" value="1" />
					<label class="radio-button-label" for="newsletter-yes"><?php echo KText::_('CBYES');?></label>
					<input type="radio" <?php echo ($this->customerData->newsletter != 1)? 'checked="checked"':'';?> id="newsletter-no" name="newsletter" value="0" />
					<label class="radio-button-label" for="newsletter-no"><?php echo KText::_('CBNO');?></label>
					<div class="validation-tooltip"></div>
				</div>
			</div>

			<?php if ($this->useOptionalRegistration) { ?>
				<div class="customer-field customer-field-register">
					<div class="form-field">
						<label>
							<?php echo KText::_('Set up an Account');?>
							<span class="fa fa-info-circle cb-popover" aria-label="<?php echo KText::_('Details');?>" role="button" data-toggle="popover" data-trigger="hover" data-placement="auto top" data-content="<?php echo KText::_('TOOLTIP_TEXT_CUSTOMER_FORM_REGISTER');?>"></span>
						</label>
						<input type="radio" checked="checked" id="register-yes" name="register" value="1" />
						<label class="radio-button-label" for="register-yes"><?php echo KText::_('CBYES');?></label>
						<input type="radio" id="register-no" name="register" value="0" />
						<label class="radio-button-label" for="register-no"><?php echo KText::_('CBNO');?></label>
						<div class="validation-tooltip"></div>
					</div>
				</div>
			<?php } ?>

		</div> <!-- .customer-form-fields -->

	</div> <!-- .customer-form-section-billing -->

	<?php if ($this->allowDeliveryAddress) { ?>
		<div class="customer-form-section customer-form-section-delivery col-sm-6 ">

			<div class="customer-form-section-heading">
				<div class="text-address-heading"><?php echo KText::_('Delivery Address');?></div>

				<div class="different-shipping-address-toggle">
					<input class="trigger-toggle-same-delivery" type="checkbox" value="1" name="samedelivery" id="samedelivery"<?php echo ($this->customerData->samedelivery) ? ' checked="checked"':'';?> />
					<label for="samedelivery"><?php echo KText::_('Same as billing');?></label>
				</div>

			</div>

			<div class="customer-form-fields">

				<div class="<?php echo $this->fieldCssClasses['companyname'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="companyname" name="companyname" placeholder="<?php echo KText::_('Company');?>" value="<?php echo hsc($this->customerData->companyname);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['salutation_id'];?>">
					<div class="form-field">
						<?php echo ConfigboxUserHelper::getSalutationDropdown('salutation_id',$this->customerData->salutation_id, 'chosen-dropdown');?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['firstname'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="firstname" name="firstname" placeholder="<?php echo KText::_('First Name');?>" value="<?php echo hsc($this->customerData->firstname);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['lastname'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="lastname" name="lastname" placeholder="<?php echo KText::_('Last Name');?>" value="<?php echo hsc($this->customerData->lastname);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['address1'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="address1" name="address1" placeholder="<?php echo KText::_('Address');?>" value="<?php echo hsc($this->customerData->address1);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['address2'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="address2" name="address2" placeholder="<?php echo KText::_('Address 2');?>" value="<?php echo hsc($this->customerData->address2);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<?php if ($this->useCityLists == false) { ?>

					<div class="<?php echo $this->fieldCssClasses['zipcode'];?>">
						<div class="form-field">
							<input class="form-control" type="text" id="zipcode" name="zipcode" placeholder="<?php echo KText::_('ZIP Code');?>" value="<?php echo hsc($this->customerData->zipcode);?>" />
							<div class="validation-tooltip"></div>
						</div>
					</div>

					<div class="<?php echo $this->fieldCssClasses['city'];?>">
						<div class="form-field">
							<input class="form-control" type="text" id="city" name="city" placeholder="<?php echo KText::_('City');?>" value="<?php echo hsc($this->customerData->city);?>" />
							<div class="validation-tooltip"></div>
						</div>
					</div>

				<?php } ?>

				<div class="<?php echo $this->fieldCssClasses['country'];?>">
					<div class="form-field">
						<?php echo ConfigboxCountryHelper::createCountrySelect('country', $this->customerData->country, KText::_('Select a country'), 'state');?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['state'];?>">
					<div class="form-field">
						<?php echo ConfigboxCountryHelper::createStateSelect('state', $this->customerData->state, $this->customerData->country, NULL, 'county_id');?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['county_id'];?>">
					<div class="form-field">
						<?php echo ConfigboxCountryHelper::createCountySelect('county_id', $this->customerData->county_id, $this->customerData->state, KText::_('Select County'), 'city_id');?>
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<?php if ($this->useCityLists == true) { ?>

					<div class="<?php echo $this->fieldCssClasses['zipcode'];?>">
						<div class="form-field">
							<input class="form-control" type="text" id="zipcode" name="zipcode" placeholder="<?php echo KText::_('ZIP Code');?>" value="<?php echo hsc($this->customerData->zipcode);?>" />
							<div class="validation-tooltip"></div>
						</div>
					</div>

					<div class="<?php echo $this->fieldCssClasses['city'];?>">
						<div class="form-field">
							<?php echo ConfigboxCountryHelper::getCityTextInput('city', $this->customerData->city);?>
							<div class="validation-tooltip"></div>
						</div>
					</div>

					<div class="<?php echo $this->fieldCssClasses['city_id'];?>">
						<div class="form-field">
							<?php echo ConfigboxCountryHelper::getCitySelect('city_id', $this->customerData->city_id, $this->customerData->county_id, KText::_('Select City'));?>
							<div class="validation-tooltip"></div>
						</div>
					</div>

				<?php } ?>

				<div class="<?php echo $this->fieldCssClasses['email'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="email" name="email" placeholder="<?php echo KText::_('Email');?>" value="<?php echo hsc($this->customerData->email);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

				<div class="<?php echo $this->fieldCssClasses['phone'];?>">
					<div class="form-field">
						<input class="form-control" type="text" id="phone" name="phone" placeholder="<?php echo KText::_('Phone');?>" value="<?php echo hsc($this->customerData->phone);?>" />
						<div class="validation-tooltip"></div>
					</div>
				</div>

			</div> <!-- .customer-form-fields -->

		</div> <!-- .customer-form-section-delivery -->
	<?php } ?>

</div> <!-- .customer-form-sections -->
