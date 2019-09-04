<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewConfiguratorpage */
?>
<div <?php echo $this->getViewAttributes();?>>

	<?php if ($this->canQuickEdit) { ?>
		<?php echo $this->pageEditButtonsHtml;?>
	<?php } ?>

	<?php if ($this->structuredData) { ?>
		<script type="application/ld+json">
			<?php echo $this->structuredData;?>
		</script>
	<?php } ?>

	<div class="row">
		<div class="col-md-6 top-part">
			<?php if ($this->showPageHeading) { ?>
				<h1 class="page-title page-title-configurator-page"><?php echo hsc($this->pageHeading);?></h1>
			<?php } ?>

			<?php if ($this->showTabNavigation) { ?>
				<?php echo $this->tabNavigationHtml;?>
			<?php } ?>

			<?php if (!empty($this->page->description)) { ?>
				<div class="configurator-page-description"><?php echo $this->page->description; ?></div>
			<?php } ?>

			<?php if (empty($this->questionsHtml)) { ?>
				<div class="configurator-page-no-elements-note">
					<p><?php echo KText::_('There are no elements on this page.');?></p>
				</div>
			<?php } else { ?>
				<div class="configurator-page-questions">
					<?php echo implode('', $this->questionsHtml); ?>
				</div>
			<?php } ?>

			<?php if ($this->showButtonNavigation) { ?>
				<?php echo $this->getViewOutput('navigation');?>
			<?php } ?>

			<?php if ($this->showButtonNavigation == false && $this->showFinishButton) { ?>
				<a rel="nofollow" class="btn btn-primary add-to-cart-button <?php echo $this->finishButtonClasses;?>" href="<?php echo $this->urlFinishButton;?>"><?php echo KText::_('Add to cart');?></a>
			<?php } ?>
		</div>

		<div class="col-md-6 bottom-part">
			<div class="overviews sticky-block show-visualization">
				<div class="wrapper-visualization">
					<div class="visualization">
						<?php if ($this->showVisualization) { ?>
							<?php echo $this->visualizationHtml; ?>
						<?php } else { ?>
							<img src="<?php echo $this->product->prod_image_href;?>" alt="<?php echo hsc($this->product->title);?>" />
						<?php } ?>
					</div>
				</div>

				<div class="wrapper-selections">
					<h2 class="selections-title"><?php echo KText::_('Selections');?></h2>
					<?php echo $this->selectionsHtml;?>
				</div>

				<div class="view-picker">
					<a class="trigger-show-visualization"><?php echo KText::_('See product');?></a>
					<a class="trigger-show-selections"><?php echo KText::_('See selections');?></a>
				</div>
			</div>
		</div>
	</div>

	<?php if ($this->showProductDetailPanes) { ?>
		<div class="product-detail-panes-wrapper"><?php echo $this->productDetailPanes; ?></div>
	<?php } ?>

	<div id="configurator-data" data-json="<?php echo hsc($this->configuratorDataJson);?>"></div>

	<?php if ($this->useGaEnhancedTracking) $this->renderView('metadata'); ?>

</div>


