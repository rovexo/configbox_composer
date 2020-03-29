<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyRule extends KenedoProperty {

	protected $textWhenNoRule;
	
	function getCellContentInListingTable($record) {
		if ($record->{$this->propertyName}) {
			return ConfigboxRulesHelper::getRuleHtml($record->{$this->propertyName}, false);
		}
		else {
			if ($this->getPropertyDefinition('textWhenNoRule')) {
				return $this->getPropertyDefinition('textWhenNoRule');
			}
			else {
				return '';
			}
		}

	}

	function copyRule($record, $copyIds) {

		$rule = $record->{$this->propertyName};

		if ($rule == '' || $rule == []) {
			return;
		}

		$ruleCopy = ConfigboxRulesHelper::getRuleCopy($rule, $copyIds);

		if ($rule == $ruleCopy) {
			return;
		}

		$db = KenedoPlatform::getDb();

		if ($this->getPropertyDefinition('storeExternally')) {
			$tableName = $this->getPropertyDefinition('foreignTableName');
			$tableKeyCol = $this->getPropertyDefinition('foreignTableKey');
		}
		else {
			$tableName = $this->model->getTableName();
			$tableKeyCol = $this->model->getTableKey();
		}

		$columnName = $this->getTableColumnName();

		$query = "
		UPDATE `".$tableName."`
		SET `".$columnName."` = '".$db->getEscaped($ruleCopy)."' 
		WHERE `".$tableKeyCol."` = ".intval($record->{$this->model->getTableKey()})."
		";
		$db->setQuery($query);
		$db->query();

	}
	
}