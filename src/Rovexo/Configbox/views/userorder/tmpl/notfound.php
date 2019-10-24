<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUserorder */
?>
<div <?php echo $this->getViewAttributes();?>>

	<div class="order-not-found-notice">
		<p><?php echo KText::_('This order does not exist.'); ?></p>
	</div>

</div>