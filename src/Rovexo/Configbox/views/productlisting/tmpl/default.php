<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewProductlisting */
?>
<div <?php echo $this->getViewAttributes();?>>

	<?php if ($this->canQuickEdit) echo ConfigboxQuickeditHelper::renderProductListingButtons($this->listing);?>

	<?php if ($this->showPageHeading) { ?>
		<h1 class="page-title"><?php echo hsc($this->pageHeading);?></h1>
	<?php } ?>

	<?php if ($this->listing->description) { ?>
		<div class="product-listing-description"><?php echo $this->listing->description;?></div>
	<?php } ?>

	<ul class="listing-products row">

		<?php foreach ($this->products as $product) { ?>

			<li class="listing-product col-md-6 col-lg-4">

				<?php if ($this->canQuickEdit) echo ConfigboxQuickeditHelper::renderProductButtons($product);?>

				<div class="wrapper-image">
					<a class="trigger-ga-track-product-click" data-id="<?php echo $product->id; ?>" href="<?php echo $this->urlsProductLink[$product->id];?>">
						<img class="product-image" src="<?php echo $product->prod_image_href;?>" alt="<?php echo hsc($product->title);?>" />
					</a>
				</div>

				<h2 class="listing-product-title">
					<a href="<?php echo $this->urlsProductLink[$product->id];?>"><?php echo hsc($product->title);?></a>
				</h2>

				<?php if ($product->showReviews) { ?>
					<div class="product-rating-wrapper">
						<?php echo $this->productRatingStarHtml[$product->id];?>
						<?php echo $this->productRatingCountHtml[$product->id];?>
					</div>
				<?php } ?>

				<?php if ($this->showPricing) { ?>

					<div class="wrapper-pricing">

						<div class="regular-pricing">
							<?php if ($product->wasPrice != 0) { ?>
								<span class="listing-was-price"> <?php echo cbprice($product->wasPrice);?></span>
							<?php } ?>

							<?php if ($product->custom_price_text) { ?>
								<span class="listing-product-price"><?php echo hsc($product->custom_price_text);?></span>
							<?php } ?>

							<?php if ($product->custom_price_text == '' && $product->price != 0) { ?>
								<span class="listing-product-price"><?php echo cbprice($product->price);?> <?php echo hsc(($product->use_recurring_pricing) ? $product->priceLabel : '');?></span>
							<?php } ?>
						</div>

						<?php if ($product->use_recurring_pricing) { ?>

							<div class="recurring-pricing">
								<?php if ($product->wasPriceRecurring != 0) { ?>
									<span class="listing-was-price-recurring"> <?php echo cbprice($product->wasPriceRecurring);?></span>
								<?php } ?>

								<?php if ($product->custom_price_text_recurring) { ?>
									<span class="listing-product-price-recurring"><?php echo hsc($product->custom_price_text_recurring);?></span>
								<?php } ?>

								<?php if ($product->custom_price_text_recurring == '' && $product->priceRecurring != 0) { ?>
									<span class="listing-product-price-recurring"><?php echo cbprice($product->priceRecurring);?> <?php echo hsc($product->priceLabelRecurring);?></span>
								<?php } ?>

							</div>
						<?php } ?>

					</div>

				<?php } ?>

				<div class="wrapper-buttons">

					<?php if ($product->isConfigurable) { ?>
						<a class="btn btn-default link-configure trigger-ga-track-product-click" data-id="<?php echo $product->id; ?>" href="<?php echo $this->urlsConfiguratorPage[$product->id];?>"><?php echo KText::_('Configure');?></a>
					<?php } ?>

					<?php if ($product->show_buy_button) { ?>
						<a class="btn btn-default link-buy trigger-ga-track-add-to-cart" rel="nofollow" data-id="<?php echo $product->id; ?>" href="<?php echo $this->urlsAddToCart[$product->id];?>"><?php echo KText::_('Buy');?></a>
					<?php } ?>

					<?php if ($product->show_product_details_button) { ?>
						<a class="btn btn-default link-details trigger-ga-track-product-click" data-id="<?php echo $product->id; ?>" href="<?php echo $this->urlsProductPage[$product->id];?>"><?php echo KText::_('Details');?></a>
					<?php } ?>

				</div>

			</li>

		<?php } ?>

	</ul>

	<?php $this->renderView('reviews_modal');?>

	<?php if ($this->useGaEnhancedTracking)  $this->renderView('metadata'); ?>

</div>