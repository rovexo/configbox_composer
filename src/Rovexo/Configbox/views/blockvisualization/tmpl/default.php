<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewBlockVisualization */
?>

<?php if (count($this->visualizationSlots) > 0 || $this->urlBaseImage) { ?>

	<div <?php echo $this->getViewAttributes();?>>
		
		<?php if ($this->showBlockTitle) { ?>
			<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
		<?php } ?>
		
		<div class="visualization-frame">

			<?php if ($this->urlBaseImage) { ?>
				<div class="base-image">
					<img src="<?php echo $this->urlBaseImage;?>" alt="" />
				</div>
			<?php } ?>

			<?php foreach ($this->visualizationSlots as $image) { ?>
				<div class="<?php echo hsc($image->css_classes);?>" id="<?php echo hsc($image->css_id);?>" style="<?php echo ($image->selected) ? 'display:block;':'display:none';?>">
					<?php if ($image->visualization_image) { ?>
						<img src="<?php echo ($image->selected) ? $image->visualization_image : $this->urlBlankImage;?>"
						     alt=""
						     data-src="<?php echo $image->visualization_image;?>"<?php echo (!$image->selected) ? ' class="preload-image"':'';?> />
					<?php } ?>
				</div>
			<?php } ?>

		</div>
	</div>
<?php } ?>