<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorderslip */
?>
<script type="text/php">

if (isset($pdf)) {

$footer = $pdf->open_object();

$h = $pdf->get_height();
$w = $pdf->get_width();

$fontFace = $fontMetrics->getFont("Helvetica", "normal");
$fontColor = array(0,0,0);
$fontSize = 8;
$footerBackgroundColor = array(238/255,238/255,238/255);

// Fold lines
$pdf->line(0, $h / 2, 10, $h /2 , array(238/255,238/255,238/255), 1);
$pdf->line(0, $h / 3 * 1, 10, $h / 3 * 1 , array(238/255,238/255,238/255), 1);
$pdf->line(0, $h / 3 * 2, 10, $h / 3 * 2 , array(238/255,238/255,238/255), 1);

<?php if ($this->useShopLogo) { ?>

	// Header bottom line
	$pdf->line(43, 100, $w - 43, 100, array(238/255, 238/255, 238/255), 1);

	// Vertical position of the lofo (top edge). Use a position that vertically centers the image within the 100px high header area
	$logoVerticalOffset = (100 - <?php echo intval($this->shopLogoHeight);?>) / 2;

	// Horizontal offset of the logo (left edge). Width of the pdf minus the width minus 43 pixel right margin.
	$logoHorizontalOffset = $w - <?php echo $this->shopLogoWidth;?> - 43;

	// Finally the image
	$pdf->image("<?php echo $this->shopLogoUrl;?>", $logoHorizontalOffset, $logoVerticalOffset, <?php echo $this->shopLogoWidth;?>, <?php echo $this->shopLogoHeight;?>);

<?php } ?>

$offsetFooter = $h - 80;

// Pagination
$textWidth = $fontMetrics->getTextWidth('<?php echo KText::_('PAGINATION_PAGE');?> 4 <?php echo KText::_('PAGINATION_OF');?> 4', $fontFace, $fontSize);
$pdf->page_text(($w/2) - ($textWidth / 2) , $offsetFooter - 16, '<?php echo KText::_('PAGINATION_PAGE');?> {PAGE_NUM} <?php echo KText::_('PAGINATION_OF');?> {PAGE_COUNT}', $fontFace, $fontSize, $fontColor);


// Footer background
$pdf->filled_rectangle(0, $h-80, $w, 80, $footerBackgroundColor);

$offsetLineVertical = $offsetFooter + 15;
$offsetLineHorizontal = 43;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo $this->shopData->shopname;?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo $this->shopData->shopaddress1;?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$var = "<?php echo hsc($this->shopData->shopaddress2);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo hsc($this->shopData->shopaddress2);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo hsc($this->shopData->shopzipcode . ' '. $this->shopData->shopcity);?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo hsc($this->shopCountryName);?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;


$offsetLineVertical = $offsetFooter + 15;
$offsetLineHorizontal = 200;
$valueOffset = 50;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Email');?>:", $fontFace, $fontSize, $fontColor);
$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopemailsales);?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Website');?>:", $fontFace, $fontSize, $fontColor);
$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopwebsite);?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Phone');?>:", $fontFace, $fontSize, $fontColor);
$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopphonesales);?>", $fontFace, $fontSize, $fontColor);
$offsetLineVertical += 10;

$var = "<?php echo hsc($this->shopData->shopfax);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Fax');?>:", $fontFace, $fontSize, $fontColor);
	$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopfax);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}


$offsetLineVertical = $offsetFooter + 15;
$offsetLineHorizontal = 380;
$valueOffset = 115;

$var = "<?php echo hsc($this->shopData->shopowner);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Company owner');?>:", $fontFace, $fontSize, $fontColor);
	$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopowner);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}

$var = "<?php echo hsc($this->shopData->shopuid);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('VAT IN');?>:", $fontFace, $fontSize, $fontColor);
	$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopuid);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}

$var = "<?php echo hsc($this->shopData->shoplegalvenue);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Legal Venue');?>:", $fontFace, $fontSize, $fontColor);
	$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shoplegalvenue);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}

$var = "<?php echo hsc($this->shopData->shopcomreg);?>";
if ($var) {
	$pdf->text($offsetLineHorizontal, $offsetLineVertical, "<?php echo KText::_('Company reg. no.');?>:", $fontFace, $fontSize, $fontColor);
	$pdf->text($offsetLineHorizontal + $valueOffset, $offsetLineVertical, "<?php echo hsc($this->shopData->shopcomreg);?>", $fontFace, $fontSize, $fontColor);
	$offsetLineVertical += 10;
}

$pdf->close_object();
$pdf->add_object($footer, "all");
}

</script>