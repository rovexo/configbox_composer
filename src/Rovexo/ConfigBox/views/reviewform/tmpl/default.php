<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewReviewform */
?>
<div <?php echo $this->getViewAttributes();?>>

	<a class="trigger-close-modal"><?php echo KText::_('Close');?></a>

	<div class="review-form">

		<div class="form-heading"><?php echo KText::_('Your Review');?></div>

		<div class="name">
			<input id="review-name" class="form-control" type="text" placeholder="<?php echo KText::_('Your Name');?>" />
		</div>

		<div class="comment">
			<textarea id="review-comment" class="form-control" placeholder="<?php echo KText::_('Review');?>"></textarea>
		</div>

		<input type="hidden" id="review-product-id" value="<?php echo intval($this->productId);?>" />

		<div class="wrapper-rating-stars">
			<?php echo ConfigboxRatingsHelper::getRatingStarHtml(0);?>
		</div>

		<div class="send-button">
			<a class="btn btn-primary trigger-send-review"><?php echo KText::_('Send');?></a>
			<a class="btn btn-default trigger-cancel-review"><?php echo KText::_('Cancel');?></a>
		</div>

	</div>

	<div class="feedback-message"></div>

</div>