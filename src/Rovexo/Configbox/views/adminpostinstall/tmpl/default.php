<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>

<div <?php echo $this->getViewAttributes();?> data-url-dashboard="<?php echo hsc($this->urlDashboard);?>">

	<div class="cube" id="step-<?php echo intval($this->currentStep);?>">
		<?php $this->renderView('step1');?>
		<?php $this->renderView('step2');?>
		<?php $this->renderView('step3');?>
		<?php $this->renderView('step4');?>
		<?php $this->renderView('step5');?>
		<?php $this->renderView('step6');?>
	</div>
</div>
