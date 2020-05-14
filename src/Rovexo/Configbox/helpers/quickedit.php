<?php
class ConfigboxQuickeditHelper {

	/**
	 * @param ConfigboxQuestion $element
	 *
	 * @return string
	 * @deprecated Use getQuestionButtons instead
	 */
	static function renderElementButtons($element) {
		return self::getQuestionEditButtons($element);
	}

	/**
	 * @param ConfigboxAnswer $option
	 * @deprecated Use ConfigboxQuickeditHelper::getAnswerEditButtons instead
	 * @return string HTML with edit buttons
	 */
	static function renderXrefButtons($option) {
		return self::getAnswerEditButtons($option);
	}

	/**
	 * @param ConfigboxQuestion $question
	 *
	 * @return string HTML with edit buttons
	 */
	static function getQuestionEditButtons(ConfigboxQuestion $question) {
		ob_start();
		?>
		<div class="quick-edit-buttons quick-edit-buttons-element">
			<a title="<?php echo KText::_('Delete Question');?>" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminelements&task=delete&quickedit=1&ids=' . $question->id);?>">
				<i class="fa fa-trash-o" aria-hidden="true"></i>
			</a>			
			<a title="<?php echo KText::_('Edit Question');?>" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminelements&task=edit&id=' . $question->id);?>">
				<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
			</a>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param ConfigboxAnswer $answer
	 *
	 * @return string HTML with edit buttons
	 */
	static function getAnswerEditButtons(ConfigboxAnswer $answer) {
		ob_start();
		?>
		<div class="quick-edit-buttons quick-edit-buttons-option">
			<a class="quick-edit-button-edit" title="<?php echo KText::_('Delete Answer');?>" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminoptionassignments&task=delete&quickedit=1&ids=' . $answer->id);?>">
				<i class="fa fa-trash-o" aria-hidden="true"></i>
			</a>			
			<a data-modal-width="1000" data-modal-height="700" title="<?php echo KText::_('Edit Answer');?>" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminoptionassignments&task=edit&id=' . $answer->id);?>">
				<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
			</a>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param object $page Configurator page data (as from ConfigboxModelConfiguratorpage::getPage)
	 * @param ConfigboxProductData $product Product data (as from ConfigboxModelProduct::getProduct)
	 * @return string
	 * @see ConfigboxModelConfiguratorpage::getPage, ConfigboxModelProduct::getProduct
	 */
	static function renderConfigurationPageButtons($page, $product = NULL) {
		ob_start();
		?>
		<div class="quick-edit-buttons quick-edit-buttons-page">

			<a class="trigger-show-page-edit-buttons" title="<?php echo KText::_('Edit Configurator Page');?>">
				<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
			</a>
			
			<div class="page-edit-buttons">
				
				<div class="toolbarbutton">
					<a class="trigger-hide-page-edit-buttons">
						<i class="fa fa-times-circle" aria-hidden="true"></i><?php echo KText::_('Close');?>
					</a>
				</div>
				<div class="toolbarbutton">
					<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminelements&prefill_page_id='.$page->id.'&task=edit&id=0');?>">
						<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Question');?>
					</a>
					
				</div>
				<div class="toolbarbutton">
					<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=edit&id=0&prefill_product_id='.(int)$page->product_id);?>">
						<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Page');?>
					</a>
				</div>
				<div class="toolbarbutton">
					<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=edit&id='.$page->id);?>">
						<i class="fa fa-pencil-square-o" aria-hidden="true"></i><?php echo KText::_('Edit Page');?>
					</a>
				</div>
				<div class="toolbarbutton">
					<a class="toolbarimage" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=delete&quickedit=1&ids='.$page->id);?>">
						<i class="fa fa-trash-o" aria-hidden="true"></i><?php echo KText::_('Delete Page');?>
					</a>
				</div>
				<?php if ($product) { ?>
					<div class="toolbarbutton">
						<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id='.$product->id);?>">
							<i class="fa fa-pencil-square-o" aria-hidden="true"></i><?php echo KText::_('Edit Product');?>
						</a>
					</div>
				<?php } ?>

			</div>
			
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param ConfigboxProductData $product Product data (as from ConfigboxModelProduct::getProduct)
	 * @return string
	 * @see ConfigboxModelProduct::getProduct
	 */
	static function renderProductPageButtons($product = NULL) {
		ob_start();
		?>
		<div class="quick-edit-buttons quick-edit-buttons-product-page">
			
			<div class="toolbarbutton">
				<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&prefill_prod_id='.$product->id.'&task=edit&id=0');?>">
					<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Page');?>
			 	</a>
			</div>
			
			<div class="toolbarbutton">
				<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id='.$product->id);?>">
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i><?php echo KText::_('Edit Product');?>
				</a>
			</div>
			
			<div class="clear"></div>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param ConfigboxListingData $listing Listing data (as from ConfigboxModelProductlisting::getProductListing)
	 * @return string
	 * @see ConfigboxModelProductlisting::getProductListing
	 */
	static function renderProductListingButtons($listing) {
		ob_start();
		?>

		<div class="quick-edit-buttons quick-edit-buttons-product-listing-page">
			<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id=0');?>">
				<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Product');?>
			</a>
			<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminlistings&task=edit&id=0');?>">
				<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Listing');?>
			</a>
			<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminlistings&id='.$listing->id.'&task=edit');?>">
				<i class="fa fa-pencil-square-o" aria-hidden="true"></i><?php echo KText::_('Edit Listing');?>
			</a>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * @param ConfigboxProductData $product Product data (as from ConfigboxModelProduct::getProduct)
	 * @return string
	 * @see ConfigboxModelProduct::getProduct
	 */
	static function renderProductButtons($product = NULL) {
		ob_start();
		?>
		<div class="quick-edit-buttons quick-edit-buttons-product">
			
			<a title="<?php echo KText::_('Delete Product');?>" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=delete&quickedit=1&ids='.$product->id);?>">
				<i class="fa fa-trash-o" aria-hidden="true"></i><?php echo KText::_('Delete Product');?>
			</a>
			
			<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminproducts&task=edit&id='.$product->id);?>">
				<i class="fa fa-pencil-square-o" aria-hidden="true"></i><?php echo KText::_('Edit Product');?>
			</a>
			<?php if (!$product->isConfigurable) { ?>
				<a href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpages&task=edit&id=0&prefill_product_id='.$product->id);?>">
					<i class="fa fa-plus-square" aria-hidden="true"></i><?php echo KText::_('Add Page');?>
				</a>
			<?php } ?>
			
			<div class="clear"></div>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
	
}