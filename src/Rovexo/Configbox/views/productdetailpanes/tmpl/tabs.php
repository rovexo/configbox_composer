<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewProductdetailpanes */
?>
<div <?php echo $this->getViewAttributes();?>>
	<ul class="nav nav-tabs" role="tablist">
		<?php foreach ($this->productDetailPanes as $i=>$pane) { ?>
			<li role="presentation" class="<?php echo ($i == 0) ? 'active ':'';?><?php echo ($pane->usesHeadingIcon) ? ' uses-icon':''; ?><?php echo (trim($pane->css_classes) != '')  ? ' '.hsc($pane->css_classes):'';?>">
				<a data-toggle="tab" role="tab" aria-controls="tab-<?php echo intval($i);?>" href="#tab-<?php echo intval($i);?>">
					<?php if ($pane->usesHeadingIcon) { ?><img alt="" class="product-detail-panes-heading-icon" src="<?php echo $pane->heading_icon_filename_href;?>" /><?php } ?>
					<?php echo hsc($pane->heading);?>
				</a>
			</li>
		<?php } ?>
	</ul>

	<div class="tab-content">
		<?php foreach ($this->productDetailPanes as $i=>$pane) { ?>
			<div role="tabpanel" class="tab-pane <?php echo ($i == 0) ? ' active':'';?>" id="tab-<?php echo intval($i);?>">
				<?php echo $pane->content;?>
			</div>
		<?php } ?>
	</div>

</div>