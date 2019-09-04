<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewProduct */
?>
<div <?php echo $this->getViewAttributes();?>>

	<?php if (ConfigboxPermissionHelper::canQuickEdit()) echo ConfigboxQuickeditHelper::renderProductPageButtons($this->product);?>

	<h1 class="page-title"><?php echo hsc($this->product->title);?></h1>

	<div class="wrapper-product-image">
		<img src="<?php echo $this->product->prod_image_href;?>" alt="<?php echo hsc($this->product->title);?>" />
	</div>

	<?php if ($this->showPricing) { ?>

		<div class="wrapper-pricing">

			<?php if ($this->product->wasPrice != 0) { ?>
				<span class="listing-was-price"> <?php echo cbprice($this->product->wasPrice);?></span>
			<?php } ?>

			<?php if ($this->product->custom_price_text) { ?>
				<span class="listing-product-price"><?php echo hsc($this->product->custom_price_text);?></span>
			<?php } ?>

			<?php if ($this->product->custom_price_text == '' && $this->product->price != 0) { ?>
				<span class="listing-product-price"><?php echo cbprice($this->product->price);?> <?php echo hsc($this->product->priceLabel);?></span>
			<?php } ?>

			<?php if ($this->product->wasPriceRecurring != 0) { ?>
				<span class="listing-was-price-recurring"> <?php echo cbprice($this->product->wasPriceRecurring);?></span>
			<?php } ?>

			<?php if ($this->product->custom_price_text_recurring) { ?>
				<span class="listing-product-price-recurring"><?php echo hsc($this->product->custom_price_text_recurring);?></span>
			<?php } ?>

			<?php if ($this->product->custom_price_text_recurring == '' && $this->product->priceRecurring != 0) { ?>
				<span class="listing-product-price-recurring"><?php echo cbprice($this->product->priceRecurring);?> <?php echo hsc($this->product->priceLabelRecurring);?></span>
			<?php } ?>

		</div>

	<?php } ?>

	<?php if ($this->product->longdescription) { ?>
		<div class="product-description"><?php echo $this->product->longdescription;?></div>
	<?php } ?>

	<div class="wrapper-buttons">
		<?php if ($this->product->isConfigurable) { ?>
			<a class="btn btn-primary link-configure" href="<?php echo $this->urlConfiguratorPage;?>"><?php echo KText::sprintf('Configure %s',hsc($this->product->title));?></a>
		<?php } ?>

		<?php if ($this->product->show_buy_button) { ?>
			<a rel="nofollow" class="btn btn-primary link-buy trigger-ga-track-add-to-cart" href="<?php echo $this->urlAddToCart;?>"><?php echo KText::_('Buy');?></a>
		<?php } ?>
	</div>

	<?php if ($this->showProductDetailPanes) { ?>
		<div class="product-detail-panes-wrapper"><?php echo $this->productDetailPanes; ?></div>
	<?php } ?>


	<?php if ($this->structuredData) { ?>
		<script type="application/ld+json">
			<?php echo $this->structuredData;?>
		</script>
	<?php } ?>

	<?php if ($this->useGaEnhancedTracking) $this->renderView('metadata'); ?>

</div>