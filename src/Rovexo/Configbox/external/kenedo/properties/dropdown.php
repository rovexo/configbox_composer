<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyDropdown extends KenedoProperty {

	protected $items;

	function getOutputValueFromRecordData($record) {
		$value = $record->{$this->propertyName};
		$choices = $this->getPropertyDefinition('choices', array());
		// Legacy - old name was items
		if (count($choices) == 0) {
			$choices = $this->getPropertyDefinition('items', array());
		}
		return (isset($choices[$value])) ? $choices[$value] : '';
	}

	public function getFilterInput(KenedoView $view, $filters) {

		if (!$this->getPropertyDefinition('search') && !$this->getPropertyDefinition('filter')) {
			return '';
		}

		$filterName = $this->getFilterName();
		$filterNameRequest = $this->getFilterNameRequest();
		$filterHtmlName = str_replace('.', '_', $filterName);

		$chosenValue = !empty($filters[$filterName]) ? $filters[$filterName] : NULL;

		$options = $this->getPossibleFilterValues();

		$html = KenedoHtml::getSelectField($filterNameRequest, $options, $chosenValue, '', false, 'listing-filter', $filterHtmlName);

		return $html;

	}

	protected function getPossibleFilterValues() {

		if ($this->getPropertyDefinition('storeExternally')) {
			$tableName = $this->getPropertyDefinition('foreignTableName'); // Foreign as in the table we store the property's data in
		}
		else {
			$tableName = $this->model->getTableName();
		}
		$db = KenedoPlatform::getDb();
		$query = "SELECT DISTINCT `".$this->propertyName."` AS `value` FROM `".$tableName."`";
		$db->setQuery($query);
		$values = $db->loadResultList('value');

		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		$choices = $this->getPropertyDefinition('choices', array());

		foreach ($values as $value) {

			if ($value) {

				if (isset($choices[$value])) {
					$options[$value] = $choices[$value];
				}
				else {
					$options[$value] = $value;
				}

			}

		}

		return $options;

	}
		
}