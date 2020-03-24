<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyOrdering extends KenedoProperty {

	/**
	 * @var string $group 	If records are grouped, tell the name of the property that groups them (e.g. pages are
	 *						grouped by product_id)
	 */
	protected $group;

	function getCellContentInListingTable($record) {
		ob_start();
		?>
		<span class="sort-handle fa fa-bars"></span>
		<input type="text" class="ordering-text-field" name="ordering-item[<?php echo hsc($record->{$this->model->getTableKey()});?>]" value="<?php echo intval($record->{$this->propertyName});?>" autocomplete="off" />
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
		if ($data->$key == 0) {

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