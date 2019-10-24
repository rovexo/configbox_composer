<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminitempicker */
?>
<div class="view-item-picker">
	<ul class="product-list">
		<?php foreach($this->tree as $product) { ?>
			<li class="product-item product-<?php echo intval($product['id']);?>" data-type="product" data-id="<?php echo intval($product['id']);?>">
				<span class="sub-list-trigger product-title<?php echo (count($product['pages'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($product['id'],$this->openIds['products'])) ? ' trigger-opened':'';?>"></span>
				<a class="picker-link"><?php echo hsc($product['title']);?></a>
				<ul class="sub-list page-list<?php echo (in_array($product['id'],$this->openIds['products'])) ? ' list-opened':'';?>">
					<?php foreach ($product['pages'] as $page) { ?>
						<li class="page-item page-<?php echo intval($page['id']);?>"  data-type="page" data-id="<?php echo intval($page['id']);?>">
							<span class="sub-list-trigger configurator-page-title<?php echo (count($page['questions'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($page['id'],$this->openIds['pages'])) ? ' trigger-opened':'';?>"></span>
							<a class="picker-link"><?php echo hsc($page['title'])?></a>
							<ul class="sub-list question-list<?php echo (in_array($page['id'],$this->openIds['pages'])) ? ' list-opened':'';?>">
								<?php foreach ($page['questions'] as $question) { ?>
									<li class="question-item question-<?php echo intval($question['id']);?>" data-type="question" data-id="<?php echo intval($question['id']);?>">
										<span class="sub-list-trigger question-title<?php echo (count($question['answers'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($question['id'],$this->openIds['questions'])) ? ' trigger-opened':'';?>"></span>
										<a class="picker-link"><?php echo ($this->useInternalNames) ? hsc($question['internal_name']) : hsc($question['title']);?></a>
										<ul class="sub-list answer-list<?php echo (in_array($question['id'], $this->openIds['questions'])) ? ' list-opened':'';?>">
											<?php foreach ($question['answers'] as $answer) { ?>
												<li class="answer-item answer-<?php echo intval($answer['id']);?>" data-type="xref" data-id="<?php echo intval($answer['id']);?>">
													<a class="picker-link"><?php echo hsc($answer['title']);?></a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>
							</ul>
						</li>
					<?php } ?>
				</ul>
			</li>
		<?php } ?>
	</ul>
</div>