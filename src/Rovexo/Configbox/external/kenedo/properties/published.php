<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyPublished extends KenedoProperty {

	function getCellContentInListingTable($record) {

		ob_start();

		if ($record->{$this->propertyName}) {
			?>
			<span class="trigger-toggle-record-activation" data-active="1" data-id="<?php echo intval($record->{$this->model->getTableKey()});?>">
				<span class="fa fa-check-circle"></span>
				<?php echo KText::_('CBYES');?>
			</span>
			<?php
		}
		else {
			?>
			<span class="trigger-toggle-record-activation" data-active="0" data-id="<?php echo intval($record->{$this->model->getTableKey()});?>">
				<span class="fa fa-ban"></span>
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

	protected function getPossibleFilterValues() {
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));
		$options['1'] = KText::_('CBYES');
		$options['0'] = KText::_('CBNO');
		return $options;
	}

}