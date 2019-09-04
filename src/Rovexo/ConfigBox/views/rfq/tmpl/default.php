<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewRfq */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewAttributes;?>>

	<h1 class="page-title page-title-rfq"><?php echo KText::_('Get a quotation');?></h1>

	<div class="wrapper-customer-form">
		<h2 class="step-title"><?php echo KText::_('Your address');?></h2>
		<?php echo $this->customerFormHtml; ?>
	</div>

	<div class="wrapper-comment-field row">
		<div class="col-sm-6">
			<div class="form-group">
				<textarea id="comment" class="form-control" name="comment" placeholder="<?php echo KText::_('Comment');?>" cols="20" rows="5"></textarea>
			</div>
		</div>
	</div>

	<div class="wrapper-buttons row">
		<div class="col-sm-6">
			<a class="btn btn-default button-back" href="<?php echo $this->urlCart;?>"><?php echo KText::_('Back to cart');?></a>
			<a class="btn btn-primary button-get-quote trigger-request-quotation"><?php echo KText::_('Get Quote');?></a>
		</div>
	</div>

</div>