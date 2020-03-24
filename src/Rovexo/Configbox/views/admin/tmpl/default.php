<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmin */
?>
<div <?php echo $this->getViewAttributes();?>>

	<div class="configbox-heading clearfix">
		<a class="visible-xs fa fa-bars pull-left offcanvas-toggle trigger-toggle-offcanvas" aria-label="<?php echo KText::_('Toggle Menu');?>"></a>
		<div class="configbox-logo"></div>
		<div class="right-part"></div>
		<div class="messages-wrapper"></div>
	</div>

	<div class="row row-offcanvas row-offcanvas-left">

		<div class="col-xs-6 col-sm-9 col-sm-4 col-md-3 sidebar-offcanvas">
			<div class="configbox-mainmenu">
				<?php KenedoView::getView('ConfigboxViewAdminmainmenu')->display();?>
			</div>
		</div>

		<div class="col-xs-12 col-sm-8 col-md-9">
			<div class="configbox-content configbox-ajax-target">
				<?php echo $this->contentHtml;?>
			</div>
		</div>

	</div>

	<div class="configbox-modals">

	</div>

</div>