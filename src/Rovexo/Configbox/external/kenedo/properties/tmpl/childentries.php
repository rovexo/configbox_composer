<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyChildentries
 */
// If the model is a parent, and the record isn't stored yet, don't show any child records.
if ($this->getPropertyDefinition('parentKeyField') && empty($this->data->{$this->getPropertyDefinition('parentKeyField')})) {
	echo KText::sprintf('Save the record to add %s.', $this->getPropertyDefinition('label', ''));
}
else {

	// Get the view
	$class = $this->getPropertyDefinition('viewClass');
	$path = $this->getPropertyDefinition('viewPath', NULL);
	$view = KenedoView::getView($class, $path);

	// Hint the view that it's a listing
	$view->listing = true;

	// Add any view filters to listing URL and view - so that we show the right set of records
	foreach ($this->getPropertyDefinition('viewFilters', array()) as $viewFilter ) {

		// This is the name of the filter
		$filterName = $viewFilter['filterName'];

		// Get the value to filter for from the record's data
		$filterValue = $this->data->{$viewFilter['filterValueKey']};

		// Set the filter in the child entry view
		$view->filters[$filterName] = $filterValue;

	}

	// Set these infos for filtering - they will be used for pre-filling the parent id field in add-forms
	if ($this->getPropertyDefinition('foreignKeyField')) {
		$view->foreignKeyField = $this->getPropertyDefinition('foreignKeyField');
		$view->foreignKeyPresetValue = $this->data->{$this->getPropertyDefinition('parentKeyField')};
	}

	$view->prepareTemplateVars();
	?>
	<div class="intra-listing">
		<?php $view->renderView(); ?>
	</div>
	<?php 
}
?>