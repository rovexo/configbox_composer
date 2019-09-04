<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewTerms */
?>

<div <?php echo $this->getViewAttributes();?>>
	<h1 class="page-heading"><?php echo hsc(KText::_('Terms and Conditions'));?></h1>
	<?php echo $this->terms;?>
</div>
