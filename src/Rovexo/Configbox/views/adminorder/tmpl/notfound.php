<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorder */
?>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
	<?php echo KText::_('Order not found');?>
</div>
