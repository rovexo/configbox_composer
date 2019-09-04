<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>
<?php if ($this->question->el_image) { ?>
	<img class="<?php echo hsc($this->question->elementImageCssClasses);?>" src="<?php echo $this->question->elementImageSrc;?>" alt="<?php echo hsc($this->question->title);?>"<?php echo $this->question->elementImagePreloadAttributes;?> />
<?php } ?>