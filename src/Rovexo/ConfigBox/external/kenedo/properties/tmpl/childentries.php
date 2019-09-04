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
	$view->isIntralisting = true;

	// Prepare the query string data for the listing URL
	$httpQuery = array();
	$httpQuery['option'] = $view->component;
	$httpQuery['controller'] = $view->controllerName;
	$httpQuery['ajax_sub_view'] = '1';
	$httpQuery['format'] = 'raw';
	$httpQuery['intralisting'] = 1;

	// Add any view filters to listing URL and view - so that we show the right set of records
	foreach ($this->getPropertyDefinition('viewFilters', array()) as $viewFilter ) {

		// This is the name of the filter
		$filterName = $viewFilter['filterName'];
		// This is how the filter name is formatted for HTTP requests
		$filterRequestName = 'filter_'.str_replace('.', '$', $viewFilter['filterName']);

		// Get the value to filter for from the record's data
		$filterValue = $this->data->{$viewFilter['filterValueKey']};

		// Set the filter in the child entry view
		$view->filters[$filterName] = $filterValue;

		// Set the filter to the listing URL for the child entry view
		$httpQuery[$filterRequestName] = $this->data->{$viewFilter['filterValueKey']};

	}

	// Set the addlink info (for the add button)
	if ($this->getPropertyDefinition('foreignKeyField')) {
		$httpQuery['foreignKeyField'] = $this->getPropertyDefinition('foreignKeyField');
		$httpQuery['foreignKeyPresetValue'] = $this->data->{$this->getPropertyDefinition('parentKeyField', '')};

		$view->foreignKeyField = $this->getPropertyDefinition('foreignKeyField');
		$view->foreignKeyPresetValue = $this->data->{$this->getPropertyDefinition('parentKeyField')};
	}

	$url = 'index.php?'.http_build_query($httpQuery);


	$view->prepareTemplateVars();
	?>
	<div class="intra-listing kenedo-listing-form" data-listing-url="<?php echo KLink::getRoute($url);?>">
		<?php $view->renderView(); ?>
	</div>
	<?php 
}
?>