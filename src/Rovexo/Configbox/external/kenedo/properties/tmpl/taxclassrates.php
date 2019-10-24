<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyTaxclassrates
 */
$taxClasses = ConfigboxPrices::getTaxClasses();

?>
<table class="form-fields">
	<?php
	foreach ($taxClasses AS $taxClass) {
		$dataFieldKey = 'tax_rate_tcr_'.$taxClass['id'];
		$dataFieldCodeKey = 'tax_code_tcr_'.$taxClass['id'];
		$dataFieldValue = !empty($this->data->$dataFieldKey) ? $this->data->$dataFieldKey : '';
		$dataFieldCodeValue = !empty($this->data->$dataFieldCodeKey) ? $this->data->$dataFieldCodeKey : '';

		?>
		<tr>
			<td class="property-label"><?php echo $taxClass['title'];?></td>
			<td class="property-body"><input style="width:35px;margin-left:5px;display:inline-block" class="tax-rate-input" type="text" name="<?php echo $dataFieldKey; ?>" id="<?php echo $dataFieldKey; ?>" value="<?php echo cbtaxrate($dataFieldValue, false); ?>" /> %<td>

			<td class="property-label" style="padding-left:10px;padding-right:5px;"><?php echo KText::_('Code');?></td>
			<td class="property-body"><input style="width:70px;padding-left:5px" class="tax-code-input" type="text" name="<?php echo $dataFieldCodeKey; ?>" id="<?php echo $dataFieldCodeKey; ?>" value="<?php echo hsc($dataFieldCodeValue);?>" /><td>
		</tr>
		<?php
	}
	?>
</table>
