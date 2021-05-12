<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckout */
?>
<div class="modal" tabindex="-1" id="modal-terms" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<?php echo $this->termsHtml;?>
		</div>
	</div>
</div>

<div class="modal" tabindex="-1" id="modal-refund-policy" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<?php echo $this->refundPolicyHtml;?>
		</div>
	</div>
</div>