<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewSdvisualization */
?>
<aside <?php echo $this->getViewAttributes();?>
		data-ticket="<?php echo hsc($this->ticket);?>"
		data-model-view-url="<?php echo hsc($this->modelViewUrl);?>"
		data-parameters="<?php echo hsc($this->parameterJson);?>"
		data-used-images="<?php echo hsc(json_encode($this->currentImageUploads));?>">

	<div id="sdv-container"></div>

	<div class="current-images">
		<?php foreach ($this->currentImageUploads as $imageData) { ?>
			<img src="<?php echo $imageData['url'];?>"
			     data-question-id="<?php echo intval($imageData['question_id']);?>"
			     alt="" />
		<?php } ?>
	</div>

</aside>