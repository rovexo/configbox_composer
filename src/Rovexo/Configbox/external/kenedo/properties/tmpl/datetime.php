<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyDatetime
 */

?>

<div class="input-group">

	<input type="text" class="form-control datepicker" name="<?php echo hsc($this->propertyName);?>" id="<?php echo hsc($this->propertyName);?>" value="<?php echo hsc($this->data->{$this->propertyName});?>" />

	<span class="input-group-addon trigger-show-calendar">
		<span class="glyphicon glyphicon-calendar" title="<?php echo KText::_('Change Date');?>"></span>
	</span>

</div>
