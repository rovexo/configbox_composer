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

		$db = KenedoPlatform::getDb();
		$query = "SELECT DISTINCT `".$this->propertyName."` AS `id`, `".$this->propertyName."` AS `title` FROM `".$this->model->getTableName()."`";
		$db->setQuery($query);
		$values = $db->loadObjectList('id');
		if ($values) {
			if (in_array($this->getType(), array('boolean', 'published', 'checkbox') ) ) {
				foreach ($values as $value) {
					$value->title = ($value->id) ? KText::_('CBYES') : KText::_('CBNO');
				}
			}
		}
		return $values;

	}
		
}