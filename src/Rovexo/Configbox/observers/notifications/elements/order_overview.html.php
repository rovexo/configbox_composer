<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var ConfigboxOrderData $orderRecord
 * @see ConfigboxModelOrderrecord::getOrderRecord
 */
?>
<style>

table {
	border-collapse:collapse;
}

.sans-border td {
	border:none;
}

.sans-border table td {
	border:1px solid #ddd;
}

td {
	vertical-align:top;
	padding:3px;
	border:1px solid #ddd;
}

th {
	text-align:left;
	padding:3px;
	border:1px solid #ddd;
}

.item-price, 
.price-field {
	text-align:right;
	white-space:nowrap;
}

ul {
	margin:0px;
	padding:0px;
}

li {
	list-style:none;
	margin:0px;
	padding:0px;
}

.products-overview {
	margin-top:30px;
}

.position-details {
	border-bottom:1px solid #ccc;
	padding-bottom:30px;
	margin-bottom:30px;
}

.position-product-title {
	font-weight:bold;
	margin-bottom:0px;
}

.position-product-sku {
	color:#ccc;
	margin-top:5px;
}

.position-configuration {
	margin-top:5px;
}

.position-details .position-image {
	margin-left: 20px;
	margin-top: 10px;
}

.order-overview  .sub-total-merchandise td {
	background:#efefef;
}

.order-overview .grand-total td {
	background:#efefef;
	font-weight: bold;
}

.order-overview .delivery-title,
.order-overview .payment-title {
	font-weight: bold;
}

.clear {
	clear:both;
}

</style>

<div class="order-overview">
	<h3><?php echo KText::_('Order Overview');?></h3>
	
	<?php 
	$recordView  = KenedoView::getView('ConfigboxViewRecord');
	$recordView->orderRecord = $orderRecord;
	$recordView->showIn = 'emailNotification';
	$recordView->showChangeLinks = false;
	$recordView->showProductDetails = false;
	$recordView->renderView('default');
	?>

</div>

<div class="products-overview">
	<h3><?php echo KText::_('Configured Products');?></h3>
	<div class="positions">
		<?php foreach ($orderRecord->positions as $position) {
			echo ConfigboxPositionHelper::getPositionHtml($orderRecord, $position, 'emailNotification');
		}
		?>
		<div class="clear"></div>
	</div>
</div>