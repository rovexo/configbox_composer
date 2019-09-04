<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckout */
?>
<div class="modal" tabindex="-1" role="dialog" id="modal-terms">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

			<?php echo $this->termsHtml;?>

		</div>
	</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="modal-refund-policy">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

			<?php echo $this->refundPolicyHtml;?>

		</div>
	</div>
</div>