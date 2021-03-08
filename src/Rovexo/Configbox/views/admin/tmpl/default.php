<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmin */
?>
<div <?php echo $this->getViewAttributes();?>>

	<div class="configbox-heading clearfix">
		<div class="configbox-logo"></div>
		<div class="right-part"></div>
		<div class="messages-wrapper"></div>
	</div>

	<div class="row">

		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="configbox-mainmenu">
				<?php KenedoView::getView('ConfigboxViewAdminmainmenu')->display();?>
			</div>
		</div>

		<div class="col-sm-6 col-md-8 col-lg-9">
			<div class="configbox-content configbox-ajax-target">
				<?php echo $this->contentHtml;?>
			</div>
		</div>

	</div>

	<div class="configbox-modals">

	</div>

</div>