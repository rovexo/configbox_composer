<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewBlockVisualization */
?>

<?php if (count($this->visualizationSlots) > 0 || $this->urlBaseImage) { ?>

	<div class="<?php echo hsc($this->wrapperClasses);?>">
		
		<?php if ($this->showBlockTitle) { ?>
			<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
		<?php } ?>
		
		<div class="visualization-frame">

			<?php if ($this->urlBaseImage) { ?>
				<div class="base-image">
					<img src="<?php echo $this->urlBaseImage;?>" alt="" />
				</div>
			<?php } ?>

			<?php

			foreach ($this->visualizationSlots as $image) {

				// Skip the pre-loading if the image is on another page and not used yet - except if it can get
				// selected by auto-select. Page ID may not be defined, in that case pre-load any image
				if ($image->selected == false && $this->pageId && $image->page_id != $this->pageId ) {
					if ($image->answer_default == 0 or $image->behavior_on_activation == 'none') {
						continue;
					}
				}

				?>
				<div class="<?php echo hsc($image->css_classes);?>" id="<?php echo hsc($image->css_id);?>" style="<?php echo ($image->selected) ? 'display:block;':'display:none';?>">
					<?php if ($image->visualization_image) { ?>
						<img src="<?php echo ($image->selected) ? $image->visualization_image : $this->urlBlankImage;?>" alt="" data-src="<?php echo $image->visualization_image;?>"<?php echo (!$image->selected) ? ' class="preload-image"':'';?> />
					<?php } ?>
				</div>
				<?php
			}
			?>

			<div class="visualization-image blank-image">
				<img src="<?php echo $this->urlBlankImage;?>" alt="" />
			</div>

		</div>
	</div>
<?php } ?>