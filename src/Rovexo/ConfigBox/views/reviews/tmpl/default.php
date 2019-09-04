<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewReviews */
?>
<div <?php echo $this->getViewAttributes();?>>

	<a class="trigger-close-modal"><?php echo KText::_('Close');?></a>

	<h1 class="page-title"><?php echo KText::_('Reviews');?></h1>

	<?php if ($this->canAddReview) { ?>
		<div class="wrapper-show-review-form">
			<a class="btn btn-primary trigger-show-review-form"><?php echo KText::_('Add your review');?></a>
		</div>
		<div class="wrapper-review-form">
			<?php echo $this->formHtml;?>
		</div>
	<?php } ?>

	<ul class="review-list">
		<?php foreach ($this->reviews as $review) { ?>
			<li class="<?php echo hsc($review->wrapperClass);?>">
				<div class="by-line"><?php echo hsc($review->name);?></div>
				<div class="rating"><?php echo ConfigboxRatingsHelper::getRatingStarHtml($review->rating);?></div>
				<div class="comment">&quot;<?php echo nl2br(hsc($review->comment));?>&quot;</div>
			</li>
		<?php } ?>
	</ul>

	<?php if (count($this->reviews) > $this->countVisibleReviewsInitial) { ?>
		<a class="trigger-toggle-all-reviews" data-text-less="<?php echo KText::_('Less');?>"><?php echo KText::_('More');?></a>
	<?php } ?>

</div>