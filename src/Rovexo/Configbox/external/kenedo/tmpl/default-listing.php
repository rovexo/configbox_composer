<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoView
 */
?>
<div <?php echo $this->getViewAttributes();?>>
	<div class="kenedo-listing-form"
	     data-view="<?php echo hsc($this->view);?>">
		<?php include(__DIR__.'/default-table.php');?>
	</div>
</div>