<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminproducttree */
?>
<div <?php echo $this->getViewAttributes();?>>

<div class="wrapper-product-list-filter">
	<?php echo $this->productListsDropdown;?>
</div>

<div class="wrapper-title-filter">
	<input type="text" id="product-tree-title" class="form-control" placeholder="Product Title" />
</div>

<a class="toggle-tree-edit"></a>
<ul class="product-list" data-update-url="<?php echo $this->treeUpdateUrl;?>">
	<?php foreach($this->tree as $product) { ?>
		<li class="product-item<?php echo ($product['published']) ? '':' inactive';?><?php echo ($product['active']) ? ' active':'';?>" id="product-<?php echo intval($product['id']);?>">
			<div>
				<span id="product-trigger-<?php echo intval($product['id']);?>" class="sub-list-trigger product-title<?php echo (count($product['pages'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($product['id'],$this->openIds['products'])) ? ' trigger-opened':'';?>"></span>
				<a class="edit-link product-edit-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id='.$product['id']);?>" title="<?php echo hsc($product['title']);?>"><?php echo hsc($product['title']);?></a>
				<a class="trigger-remove" data-url="<?php echo $product['url_delete'];?>"></a>

				<?php if ($this->showCopy) { ?>
					<a title="<?php echo KText::_('PRODUCT_TREE_COPY');?>" class="trigger-copy" data-short-name="product" data-controller="adminproducts" data-id="<?php echo intval($product['id']);?>"></a>
				<?php } ?>

				<ul class="sub-list page-list<?php echo (in_array($product['id'],$this->openIds['products'])) ? ' list-opened':'';?>">
					<?php foreach ($product['pages'] as $page) { ?>
						<li class="page-item<?php echo ($page['published']) ? '':' inactive';?><?php echo ($page['active']) ? ' active':'';?>" id="page-<?php echo intval($page['id']);?>">
							<div>
								<span id="page-trigger-<?php echo intval($page['id']);?>" class="sub-list-trigger configurator-page-title<?php echo (count($page['questions'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($page['id'],$this->openIds['pages'])) ? ' trigger-opened':'';?>"></span>
								<a class="edit-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=edit&id='.$page['id']);?>" title="<?php echo hsc($page['title'])?>"><?php echo hsc($page['title'])?></a>
								<a class="trigger-remove" data-url="<?php echo $page['url_delete'];?>"></a>
								<?php if ($this->showCopy) { ?>
									<a title="<?php echo KText::_('PRODUCT_TREE_COPY');?>" class="trigger-copy" data-short-name="page" data-controller="adminpages" data-id="<?php echo intval($page['id']);?>"></a>
								<?php } ?>
								<ul class="sub-list question-list<?php echo (in_array($page['id'],$this->openIds['pages'])) ? ' list-opened':'';?>">
									<?php foreach ($page['questions'] as $question) { ?>
										<li class="question-item<?php echo ($question['published']) ? '':' inactive';?><?php echo ($question['active']) ? ' active':'';?>" id="question-<?php echo intval($question['id']);?>">
											<div>
												<span id="question-trigger-<?php echo intval($question['id']);?>" class="sub-list-trigger question-title<?php echo (count($question['answers'])) ? ' has-sub-list':' no-sub-list';?><?php echo (in_array($question['id'],$this->openIds['questions'])) ? ' trigger-opened':'';?>"></span>
												<a class="edit-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminelements&task=edit&id=' . $question['id']);?>" title="<?php echo ($this->useInternalNames) ? hsc($question['internal_name']) : hsc($question['title']);?>"><?php echo ($this->useInternalNames) ? hsc($question['internal_name']) : hsc($question['title']);?></a>
												<a class="trigger-remove" data-url="<?php echo $question['url_delete'];?>"></a>
												<?php if ($this->showCopy) { ?>
													<a title="<?php echo KText::_('PRODUCT_TREE_COPY');?>" class="trigger-copy" data-short-name="question" data-controller="adminelements" data-id="<?php echo intval($question['id']);?>"></a>
												<?php } ?>
												<ul class="sub-list answer-list<?php echo (in_array($question['id'],$this->openIds['questions'])) ? ' list-opened':'';?>">
													<?php foreach ($question['answers'] as $answer) { ?>
														<li class="answer-item<?php echo ($answer['published']) ? '':' inactive';?>" id="answer-<?php echo intval($answer['id']);?>">
															<div>
																<a class="edit-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminoptionassignments&task=edit&id='.$answer['id']);?>" title="<?php echo hsc($answer['title']);?>"><?php echo hsc($answer['title']);?></a>
																<a class="trigger-remove"></a>
															</div>
														</li>
													<?php } ?>
												</ul>
											</div>
										</li>
									<?php } ?>
									<li class="question-item add-item">
										<a class="add-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminelements&task=edit&id=0&prefill_page_id='.$page['id']);?>"><?php echo KText::_('Add question');?></a>
									</li>
								</ul>
							</div>
						</li>
					<?php } ?>
					<li class="page-item add-item">
						<a class="add-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=edit&id=0&prefill_product_id='.$product['id']);?>"><?php echo KText::_('Add page');?></a>
					</li>
				</ul>
			</div>
		</li>
	<?php } ?>
	<li class="product-item add-item">
		<a class="add-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id=0');?>"><?php echo KText::_('Add product');?></a>
	</li>
</ul>
</div>