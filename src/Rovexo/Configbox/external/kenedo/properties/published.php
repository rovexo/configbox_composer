<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyPublished extends KenedoProperty {
	
	function getCellContentInListingTable($record) {

		ob_start();

		if ($record->{$this->propertyName}) {
			?>
			<span class="trigger-toggle-record-activation" data-active="1" data-id="<?php echo intval($record->{$this->model->getTableKey()});?>">
				<span class="fa fa-check-circle fa-lg pull-left"></span>
				<?php echo KText::_('CBYES');?>
			</span>
			<?php
		}
		else {
			?>
			<span class="trigger-toggle-record-activation" data-active="0" data-id="<?php echo intval($record->{$this->model->getTableKey()});?>">
				<span class="fa fa-ban fa-lg pull-left"></span>
				<?php echo KText::_('CBNO');?>
			</span>
			<?php
		}

		return ob_get_clean();
		
	}

	function getOutputValueFromRecordData($record) {

		if ($record->{$this->propertyName} == 1) {
			return KText::_('CBYES');
		}
		else {
			return KText::_('CBNO');
		}

	}
	
}