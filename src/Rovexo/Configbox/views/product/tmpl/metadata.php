<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewProduct */
?>
<script type="application/json" id="product-data"><?php
	$prod = array(
		'id' => hsc($this->product->sku ? $this->product->sku : $this->product->id),
		'name' => hsc($this->product->title)
	);
	echo json_encode($prod);?></script>
