<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewBlockNavigation */
?>

<?php if (count($this->pages) > 1) { ?>

	<div class="<?php echo hsc($this->wrapperClasses);?>">

		<?php if ($this->showBlockTitle) { ?>
			<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
		<?php } ?>

		<ul class="nav nav-tabs" role="tablist">
			<?php foreach ($this->pages as $page) { ?>
				<li role="presentation" class="<?php echo hsc($this->listItemCssClasses[$page->id]);?>">
					<a role="tab" class="<?php echo hsc($this->tabLinkClasses[$page->id]);?>" href="<?php echo $page->url;?>">
						<span><?php echo hsc($page->title);?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>

<?php } ?>