<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoView
 */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
	
	<div class="kenedo-listing-form"><?php include(dirname(__FILE__).DS.'default-table.php');?></div>

	<div class="clear"></div>

</div>
</div>