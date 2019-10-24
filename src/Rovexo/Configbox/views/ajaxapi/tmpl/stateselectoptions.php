<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewAjaxApi */

foreach ($this->data as $key=>$value) {
	?>
	<option value="<?php echo hsc($key);?>"<?php echo ($key == $this->selectedId) ? ' selected="selected"':''?>><?php echo hsc($value);?></option>
	<?php
}