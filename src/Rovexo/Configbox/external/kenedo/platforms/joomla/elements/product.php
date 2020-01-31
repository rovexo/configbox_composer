<?php
if (!defined('CB_VALID_ENTRY')) {
	define('CB_VALID_ENTRY',true);
}

jimport('joomla.form.formfield');

class JFormFieldProduct extends JFormField {
	
	public $_name = 'Product';

	protected function getInput() {
		
		// Init Kenedo framework
		require_once( dirname(__FILE__).'/../../../init.php');

		$products = KenedoModel::getModel('ConfigboxModelAdminproducts')->getRecords();

		ob_start();
		?>
		<select class="cb-product-dropdown" name="<?php echo hsc($this->name);?>" id="<?php echo hsc($this->id);?>">
			<?php foreach ($products as $product) { ?>
				<option value="<?php echo intval($product->id);?>"<?php echo ($this->value == $product->id) ? ' selected':'';?>><?php echo hsc($product->title);?> (<?php echo intval($product->id);?>)</option>
			<?php } ?>
		</select>
		<?php
		return ob_get_clean();

	}
}
