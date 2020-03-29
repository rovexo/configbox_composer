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
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		$choices = $this->getPropertyDefinition('choices', array());

		foreach ($choices as $key=>$value) {
			$options[$key] = $value;
		}

		return $options;
	}
		
}