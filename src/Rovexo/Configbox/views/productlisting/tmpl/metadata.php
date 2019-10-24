<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewProductlisting */
?>
<script type="application/json" id="product-list-data"><?php
	$res = [];
	foreach ($this->products as $product) {
		$prod = array(
			'id' => hsc($product->sku ? $product->sku : $product->id),
			'product_id' => $product->id,
			'name' => hsc($product->title),
			'list' => hsc($this->listing->title),
		);
		$res[] = $prod;
	}
	echo json_encode($res);?></script>