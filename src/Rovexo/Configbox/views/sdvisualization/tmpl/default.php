<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewSdvisualization */
?>
<aside <?php echo $this->getViewAttributes();?>>

	<?php if ($this->iframeUrl) { ?>

		<iframe id="shapediver-vis" src="about:blank" data-src="<?php echo $this->iframeUrl;?>" data-relative-height="<?php echo hsc($this->relativeIframeHeight);?>" allowfullscreen></iframe>

	<?php } ?>

	<div class="current-images">
		<?php foreach ($this->currentImageUploads as $imageData) { ?>
			<img alt="" id="image-question-id-<?php echo intval($imageData['question_id']);?>" src="<?php echo $imageData['url'];?>" data-question-id="<?php echo intval($imageData['question_id']);?>" data-geometry-name="<?php echo hsc($imageData['geometry_name']);?>" />
		<?php } ?>
	</div>

</aside>