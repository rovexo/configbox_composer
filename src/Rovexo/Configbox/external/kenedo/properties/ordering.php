<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyOrdering extends KenedoProperty {

	function getCellContentInListingTable($record) {
		ob_start();
		?>
		<span class="sort-handle fa fa-bars" data-unset-pagination-text="<?php echo KText::_('ADMIN_FEEDBACK_UNSET_PAGINATION_BEFORE_SORTING');?>"></span>
		<?php
		return ob_get_clean();
	}
	
	function usesWrapper() {
		return false;
	}
	
	function getBodyAdmin() {
		?>
		<div><input type="hidden" id="<?php echo hsc($this->propertyName);?>" name="<?php echo hsc($this->propertyName);?>" value="<?php echo intval($this->data->{$this->propertyName});?>" /></div>
		<?php
	}

	function prepareForStorage(&$data) {

		$key = $this->model->getTableKey();

		// In case of an insert, we bump up the ordering number
		if (empty($data->{$key})) {

			$db = KenedoPlatform::getDb();

			$tableName = $this->model->getTableName();
			$groupKey = $this->getPropertyDefinition('group');
			$where = (!$groupKey) ? '' : "WHERE `".$groupKey."` = '".$db->getEscaped($data->{$groupKey})."'";

			$query = "SELECT `".$this->propertyName."` FROM `".$tableName."` ".$where." ORDER BY `".$this->propertyName."` DESC LIMIT 1";
			$db->setQuery($query);

			$data->{$this->propertyName} = intval($db->loadResult()) + 10;

		}

		return parent::prepareForStorage($data);

	}

}