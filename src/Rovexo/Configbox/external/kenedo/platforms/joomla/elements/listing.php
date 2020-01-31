<?php
if (!defined('CB_VALID_ENTRY')) {
	define('CB_VALID_ENTRY',true);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.form.formfield');

class JFormFieldListing extends JFormField {
	
	public $_name = 'Listing';

	protected function getInput() {
		
		// Init Kenedo framework
		require_once( dirname(__FILE__).'/../../../init.php');

        $listings = KenedoModel::getModel('ConfigboxModelAdminlistings')->getRecords();

        ob_start();
        ?>
		<select class="cb-listing-dropdown" name="<?php echo hsc($this->name);?>" id="<?php echo hsc($this->id);?>">
            <?php foreach ($listings as $listing) { ?>
				<option value="<?php echo intval($listing->id);?>"<?php echo ($this->value == $listing->id) ? ' selected':'';?>><?php echo hsc($listing->title);?></option>
            <?php } ?>
		</select>
        <?php
        return ob_get_clean();
	}
}
