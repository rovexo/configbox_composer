<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminproducttour */
?>
<div <?php echo $this->getViewAttributes();?>>

	<div class="tour-stops">

		<div id="tour-stop-1" class="tour-stop" data-step="1" data-selector="#view-adminmainmenu .item-adminproducttree">
			<?php echo KText::_('TOUR_SEE_PRODUCT_TREE');?>
		</div>

	</div>

	<div class="popover-blueprint">
		<div class="product-tour-popover fade popover in top" role="tooltip">
			<div class="arrow"></div>
			<h3 class="popover-title"></h3>
			<div class="popover-content"></div>
		</div>
	</div>
	<div class="popover-staging">

	</div>

</div>