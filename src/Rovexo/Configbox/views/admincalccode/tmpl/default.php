<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmincalccode */
?>
<div <?php echo $this->getViewAttributes();?>>
	<div class="kenedo-properties">

		<?php
		foreach($this->properties as $property) {
			$property->setData($this->record);
			if ($property->usesWrapper()) {
				?>
				<div id="<?php echo $property->getCssId();?>" class="<?php echo $property->renderCssClasses();?>">
					<?php if ($property->doesShowAdminLabel()) { ?>
						<div class="property-label"><?php echo $property->getLabelAdmin(); ?> <?php if ($property->isRequired()) { echo '<span class="required-flag">*</span>'; }?></div>
						<div class="property-body"><?php echo $property->getBodyAdmin();?></div>
					<?php } else { ?>
						<div class="property-body"><?php echo $property->getBodyAdmin();?></div>
					<?php } ?>
				</div>
				<?php
			} else {
				echo $property->getBodyAdmin();
			}
		}
		?>

		<div class="calculation-code-notes">
			<h2><?php echo KText::_('Notes')?></h2>
			<p class="syntax-notes"><?php echo KText::_('Always use a dot as decimal symbol and add a space before and after a placeholder.')?></p>
			<p><?php echo KText::_('You can select up to 4 elements - text-field elements with numbers-only validation are optimal - and use their values with the placeholders A,B,C and D in your formula. Always keep a space character before and after a placeholder. They get replaced with the value the customer entered in the respective textfield.');?></p>
			<h3><?php echo KText::_('Keywords');?></h3>
			<p><?php echo KText::_('You can use keywords to use data from the current configuration for price calculation.');?></p>
			<ul>
				<li><strong>Total</strong>: <?php echo KText::_('The total of the order. It can only be used once for a product.');?></li>
				<li><strong>TotalRecurring</strong>: <?php echo KText::_('The recurring total of the order. It can only be used once for a product.');?></li>
				<li><strong>ElementPrice(id)</strong>: <?php echo KText::_('The current element price. Enter the ID of the element in the brackets.');?></li>
				<li><strong>ElementPriceRecurring(id)</strong>: <?php echo KText::_('The current recurring element price. Enter the ID of the element in the brackets.');?></li>
				<li><strong>ElementAttribute(id.path)</strong>: <?php echo KText::_('HELP_TEXT_FORMULA_EDITOR_ELEMENT_ATTRIBUTE');?></li>
				<li><strong>ElementEntry(id)</strong>: <?php echo KText::_('The text entry the customer has made in this field. This keyword does exactly what the element placeholders do. Only numbers are accepted, so we recommend to set validation to numbers only. If empty, a value of 0 is assumed.');?></li>
				<li><strong>Calculation(id)</strong>: <?php echo KText::_('Use this keyword to use the result of another calculation in your formula. You can see the IDs of the calculations in the listing of calculations.');?></li>
				</ul>
			<h3>ElementAttribute</h3>
			<p><?php echo KText::_('There are many attributes in an element and its assigned options. This is a list of supported attributes which are not subject to change in future versions of ConfigBox. As example we use element ID 22.');?></p>
			<ul>
				<li><strong>ElementAttribute(22.selectedOption.weight)</strong>: <?php echo KText::_('The weight of the currently selected option.');?></li>
				<li><strong>ElementAttribute(22.element_custom_1) - ElementAttribute(22.element_custom_4)</strong>: <?php echo KText::_('The custom value found in the element screen under Custom Fields.');?></li>
				<li><strong>ElementAttribute(22.selectedOption.assignment_custom_1) - ElementAttribute(22.selectedOption.assignment_custom_4)</strong>: <?php echo KText::_('The custom value found in the option assignment screen.');?></li>
				<li><strong>ElementAttribute(22.selectedOption.option_custom_1) - ElementAttribute(22.selectedOption.option_custom_4)</strong>: <?php echo KText::_('The custom value found in the global option screen.');?></li>

			</ul>
			<h3>RegardingElement, RegardingElement(regardingOption.xxx)</h3>
			<p><?php echo KText::_('To quickly access the element or option assignment you attached the formula to, you can use the keyword RegardingElement and regardingOption.xxx. Examples:');?></p>
			<ul>
				<li><strong>RegardingElement(regardingOption.assignment_custom_1)</strong>: <?php echo KText::_('The custom field of the assignment option that uses this formula.');?></li>
				<li><strong>RegardingElement(regardingOption.weight)</strong>: <?php echo KText::_('The weight of the assignment option that uses this formula.');?></li>
				<li><strong>RegardingElement(element_custom_1)</strong>: <?php echo KText::_('The custom field value 1 of the element that uses this formula.');?></li>
			</ul>
			<p><?php echo KText::_('Please note that you need to assign any calculation model to an option or element to make it calculate. You can reuse an calculation model for multiple elements/options.');?></p>
			<p><strong><?php echo KText::_('Important: Please write a dot for a decimal mark and use no thousands separators or similar.');?></strong></p>
		</div>

		<div class="clear"></div>
	</div> <!-- .kenedo-properties -->
</div> <!-- view wrapper -->