<?php 
defined('CB_VALID_ENTRY') or die(); 

class KenedoPropertyChildentries extends KenedoProperty {

	/**
	 * @var string The class name of the view you want to show (e.g. ConfigboxViewAdminproducts). Always use a typical
	 * records list view.
	 */
	protected $viewClass;
	/**
	 * Each filter has this structure: array('filterName'=>'[foreign key property's table alias].[foreign key property's select alias]', 'filterValueKey'=>'[name of your parent model primary key property]')
	 * @var array[] Filters to apply to that view. Filters are the classic filters you use for KenedoModel::getRecords
	 * @see KenedoModel::getRecords, KenedoModel::getModelName, KenedoProperty::getPropertyName, KenedoProperty::getSelectAlias
	 */
	protected $viewFilters;
	/**
	 * @var string Property name of the child's model foreign key field
	 */
	protected $foreignKeyField;
	/**
	 * @var string Property name of the parent's model primary key field
	 */
	protected $parentKeyField;

	/**
	 * @param object $data No need to add anything to the data object
	 */
	function getDataFromRequest( &$data ) {

	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		return array();
	}

	/**
	 * Tells which keys of our data object have values that go into the base table during storing
	 *
	 * @param $data
	 * @return array
	 */
	function getDataKeysForBaseTable($data) {
		return array();
	}

    function copy($data, $newId, $oldId) {

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';

		KLog::log($logPrefix.' Searching for child records. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_my');

        // Get the child view..
        $view = KenedoView::getView($this->getPropertyDefinition('viewClass'));
        // ..and it's model
        $model = $view->getDefaultModel();

        // Get the filter information (this is filtering the items to show in the form)
        $viewFilters = $this->getPropertyDefinition('viewFilters');

        // Prepare the filter array for loading the child records
        $filters = array();

        // filterName is the column you filter for
        // filterValueKey is the property in $data that has the value to search for (e.g. for gallery images it would be the product's ID)
        foreach ($viewFilters as $viewFilter) {
            // if filter contains id, set it to oldId
            if($viewFilter['filterValueKey'] == 'id') $filters[$viewFilter['filterName']] = $oldId;
            else $filters[$viewFilter['filterName']] = $data->{$viewFilter['filterValueKey']};
        }

        // This should give you all the child records to copy
        $recordsToCopy = $model->getRecords($filters);

		KLog::log($logPrefix.' Child model is '.get_class($model).'. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_my');
		KLog::log($logPrefix.' Got '.count($recordsToCopy).' child records to copy. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_my');

        // Loop through them..
        foreach ($recordsToCopy as $recordToCopy) {
            // .. take the child's property name for the fk
            $fkPropName = $this->getPropertyDefinition('foreignKeyField');
            // set it to the new parent id
            $recordToCopy->{$fkPropName} = $newId;
            // and finally copy it
            $model->copy($recordToCopy);
        }

    }
    
}