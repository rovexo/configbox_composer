<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincurrencies extends KenedoModel {

	function getTableName() {
		return '#__configbox_currencies';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'positionList'=>10,
			'canSortBy'=>true,
			'positionForm'=>100,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'langType'=>6,
			'required'=>1,
			'positionList'=>20,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'admincurrencies',
			'canSortBy'=>true,
			'positionForm'=>200,
		);

		$propDefs['multiplicator'] = array(
			'name'=>'multiplicator',
			'label'=>KText::_('Multiplicator'),
			'type'=>'string',
			'stringType'=>'number',
			'required'=>1,
			'positionList'=>30,
			'listCellWidth'=>'70px',
			'positionForm'=>300,
		);

		$propDefs['symbol'] = array(
			'name'=>'symbol',
			'label'=>KText::_('Currency Symbol'),
			'type'=>'string',
			'stringType'=>'string',
			'required'=>1,
			'positionList'=>40,
			'listCellWidth'=>'70px',
			'positionForm'=>400,
		);

		$propDefs['code'] = array(
			'name'=>'code',
			'label'=>KText::_('Currency Code'),
			'type'=>'string',
			'stringType'=>'string',
			'required'=>1,
			'positionList'=>50,
			'listCellWidth'=>'90px',
			'positionForm'=>500,
		);

		$propDefs['default'] = array(
			'name'=>'default',
			'label'=>KText::_('Default'),
			'type'=>'boolean',
			'positionList'=>60,
			'listCellWidth'=>'90px',
			'default'=>0,
			'invisible'=>true,
			'positionForm'=>600,
		);

		$propDefs['base'] = array(
			'name'=>'base',
			'label'=>KText::_('Base Currency'),
			'type'=>'boolean',
			'positionList'=>70,
			'listCellWidth'=>'90px',
			'default'=>0,
			'invisible'=>true,
			'positionForm'=>700,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'positionList'=>80,
			'listCellWidth'=>'50px',
			'positionForm'=>800,
		);

		return $propDefs;
	}

	function getListingTasks() {
		$tasks = array(
				array('title'=>KText::_('Make Default'), 	'task'=>'makeDefault'),
				array('title'=>KText::_('Make Base'), 		'task'=>'makeBase'),
				array('title'=>KText::_('Add'), 			'task'=>'add',          'primary' => true),
				array('title'=>KText::_('Remove'), 			'task'=>'remove'),
		);
		return $tasks;
	}

	/**
	 * Makes the currency the base currency
	 * @param int $id Currency ID
	 * @return bool
	 */
	function makeBase($id) {

		$db = KenedoPlatform::getDb();
		$query = "UPDATE `#__configbox_currencies` SET `base` = '0'";
		$db->setQuery($query);
		$success = $db->query();
	
		if ($success) {
			$query = "UPDATE `#__configbox_currencies` SET `base` = '1' WHERE `id` = ".intval($id);
			$db->setQuery($query);
			$success = $db->query();
		}
	
		return (boolean) $success;
	
	}

	/**
	 * Makes the currency the default currency
	 * @param int $id Currency ID
	 * @return bool
	 */
	function makeDefault($id) {

		$db = KenedoPlatform::getDb();
		$query = "UPDATE `#__configbox_currencies` SET `default` = '0'";
		$db->setQuery($query);
		$success = $db->query();
	
		if ($success) {
				
			$query = "UPDATE `#__configbox_currencies` SET `default` = '1', `published` = '1' WHERE `id` = ".intval($id). ' LIMIT 1';
			$db->setQuery($query);
			$success = $db->query();
		}
	
		return (boolean) $success;
	
	}

	/**
	 * @param int|int[] $ids
	 * @return bool
	 */
	function delete($ids) {

		if (is_numeric($ids)) {
			$ids = array($ids);
		}

		$currencies = $this->getRecords();

		if (count($currencies) <= count($ids)) {
			$this->setError(KText::_('You cannot delete all currencies.'));
			return false;
		}
		
		foreach ($ids as $id) {

			$currency = $this->getRecord($id);
				
			if ($currency->base == 1) {
				$this->setError(KText::_('You cannot delete the base currency.'));
				return false;
			}
			if ($currency->default == 1) {
				$this->setError(KText::_('You cannot delete the default currency.'));
				return false;
			}
		}
	
		return parent::delete($ids);
	
	}
}