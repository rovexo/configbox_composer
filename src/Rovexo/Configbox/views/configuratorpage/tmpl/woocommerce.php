<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewConfiguratorpage */
?>
<div <?php echo $this->getViewAttributes();?>>

	<?php if ($this->canQuickEdit) { ?>
		<?php echo $this->pageEditButtonsHtml;?>
	<?php } ?>

	<?php if ($this->showPageHeading) { ?>
		<h1 class="page-title page-title-configurator-page"><?php echo hsc($this->pageHeading);?></h1>
	<?php } ?>

	<?php if ($this->showTabNavigation) { ?>
		<?php echo $this->tabNavigationHtml;?>
	<?php } ?>

	<?php if (!empty($this->page->description)) { ?>
		<div class="configurator-page-description"><?php echo $this->page->description; ?></div>
	<?php } ?>

	<?php if (empty($this->questionsHtml)) { ?>
		<div class="configurator-page-no-elements-note">
			<p><?php echo KText::_('There are no elements on this page.');?></p>
		</div>
	<?php } else { ?>
		<div class="configurator-page-questions clearfix">
			<?php echo implode('', $this->questionsHtml); ?>
		</div>
	<?php } ?>

	<?php if ($this->showButtonNavigation) { ?>
		<?php echo $this->getViewOutput('navigation');?>
	<?php } ?>

	<div id="configurator-data" data-json="<?php echo hsc($this->configuratorDataJson);?>"></div>

	<?php if ($this->structuredData) { ?>
		<script type="application/ld+json"><?php echo $this->structuredData;?></script>
	<?php } ?>

</div>


