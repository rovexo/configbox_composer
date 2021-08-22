<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyId extends KenedoProperty {

	function getHeaderCellContentInListingTable($orderingInstructions) {
		ob_start();
		?>
		<input type="checkbox" name="checkall" class="kenedo-check-all-items" />
		<?php
		echo parent::getHeaderCellContentInListingTable($orderingInstructions);
		return ob_get_clean();
	}

	function getCellContentInListingTable($record) {
		ob_start();
		?>
		<input type="checkbox" name="cid[]" class="kenedo-item-checkbox" value="<?php echo intval($record->{$this->propertyName});?>" />
		<span><?php echo intval($record->{$this->propertyName}); ?></span>
		<?php
		return ob_get_clean();
	}

	function isInListing() {
		return true;
	}

	function usesWrapper() {
		return false;
	}

	function getBodyAdmin() {
		return '';
	}

	function getDataFromRequest(&$data) {

		$input = KRequest::getString($this->propertyName, NULL);

		if ($input === '0') {
			$data->{$this->propertyName} = NULL;
		}
		else {
			$data->{$this->propertyName} = $input;
		}

	}

	function getDataKeysForBaseTable($data) {
		$key = $this->model->getTableKey();
		if (property_exists($data, $key)) {
			return array($this->propertyName);
		}
		else {
			return array();
		}
	}

}