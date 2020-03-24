<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminpages extends KenedoModel {

	function getTableName() {
		return '#__configbox_pages';
	}

	function getTableKey() {
		return 'id';
	}

	function getChildModel() {
		return 'ConfigboxModelAdminelements';
	}

    function getChildModelForeignKey() {
        return 'page_id';
    }

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listing'=>1,
			'order'=>100,
			'positionForm'=>100,
		);

		$propDefs['general_start'] = array(
			'name'=>'general_start',
			'type'=>'groupstart',
			'title'=>KText::_('General'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'notes'=>KText::_('GROUP_NOTE_PAGE_GENERAL', ''),
			'positionForm'=>200,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>3,
			'required'=>1,
			'listing'=>10,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminpages',
			'order'=>3,
			'positionForm'=>300,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('LABEL_PAGE_ACTIVE'),
			'default'=>1,
			'type'=>'published',
			'listing'=>40,
			'order'=>30,
			'filter'=>3,
			'listingwidth'=>'60px',
			'positionForm'=>500,
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>600,
		);

		$propDefs['display_start'] = array(
			'name'=>'display_start',
			'type'=>'groupstart',
			'title'=>KText::_('Display'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('GROUP_NOTE_PAGE_DISPLAY', ''),
			'positionForm'=>700,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['label'] = array(
				'name'=>'label',
				'label'=>KText::_('LABEL_SEF_SEGMENT'),
				'tooltip'=>KText::_('TOOLTIP_PAGE_SEF_SEGMENT'),
				'required'=>0,
				'type'=>'translatable',
				'stringTable'=>'#__configbox_strings',
				'langType'=>18,
				'positionForm'=>800,
			);

		}

		$propDefs['layoutname'] = array(
			'name'=>'layoutname',
			'label'=>KText::_('Template'),
			'type'=>'join',
			'isPseudoJoin'=>true,

			'propNameKey'=>'value',
			'propNameDisplay'=>'title',

			'modelClass'=>'ConfigboxModelAdmintemplates',
			'modelMethod'=>'getConfiguratorPageTemplates',

			'default'=>'default',

			'tooltip'=>KText::_('If you have custom templates for configurator pages set you can choose one here to display the page in a custom design.'),
			'required'=>0,
			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'positionForm'=>1000,
		);

		$propDefs['css_classes'] = array(
			'name'=>'css_classes',
			'label'=>KText::_('CSS Classes'),
			'tooltip'=>KText::_('The CSS classes entered here will be set for the wrapping DIV element of the configurator page.'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1100,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Product'),

			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array('propertyName'=>'published', 				'selectAliasOverride'=>'product_published'),
				array('propertyName'=>'title', 					'selectAliasOverride'=>'product_title'),
				array('propertyName'=>'visualization_type', 	'selectAliasOverride'=>'visualization_type'),
				array('propertyName'=>'use_recurring_pricing'),
			),

			'parent'=>1,
			'required'=>1,
			'listing'=>20,
			'order'=>1,
			'filter'=>1,
			'listingwidth'=>'200px',
			'positionForm'=>1150,
		);

		$propDefs['description'] = array(
			'name'=>'description',
			'label'=>KText::_('LABEL_PAGE_DESCRIPTION'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>13,
			'required'=>0,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>1175,
		);

		$propDefs['display_end'] = array(
			'name'=>'display_end',
			'type'=>'groupend',
			'positionForm'=>1200,
		);


		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'product_id',
			'listing'=>5,
			'order'=>20,
			'positionForm'=>1800,
		);

		return $propDefs;

	}

	/**
	 * Auto-fills empty URL segments (label) and runs the parent prepare method.
	 *
	 * @see ConfigboxModelAdminpages::fillEmptyUrlSegments
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage($data) {

		// Not for ajaxStore (no titles sent)
		if (KRequest::getKeyword('task') != 'ajaxStore') {
			// In case we got labels (CB for Magento doesn't), auto-fill labels if nec.
			$props = $this->getProperties();
			if (!empty($props['label'])) {
				$this->fillEmptyUrlSegments($data);
			}
		}

		return parent::prepareForStorage($data);
	}

	/**
	 * Checks for duplicate URL segments (label) and runs the parent checks.
	 * Copies old URL segments into the old_labels table if all checks are ok.
	 *
	 * @see ConfigboxModelAdminpages::checkForDuplicateUrlSegment
	 * @param object $data
	 * @param string $context
	 * @return bool
	 */
	function validateData($data, $context = '') {

		$response = parent::validateData($data, $context);

		if ($response === false) {
			return false;
		}

		// Fill the URL segment (label), but not for ajaxStore (no titles sent)
		if (KRequest::getKeyword('task') != 'ajaxStore') {
			$response = $this->checkForDuplicateUrlSegment($data);

			if ($response === false) {
				return false;
			}
		}

		$this->storeOldUrlSegments($data);

		return true;

	}

	/**
	 * Checks if CB elements are assigned to that page.
	 *
	 * @param $id
	 * @return bool
	 */
	function canDelete($id) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_elements` WHERE `page_id` = ".intval($id)." LIMIT 1";
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result) {
			return true;
		}
		else {
			$this->setError(KText::_('Could not delete the page, because it contains questions.'));
			return false;
		}
	}

	/**
	 * Removes old URL segments for that page
	 * @param int $id
	 * @return bool
	 */
	function afterDelete($id) {
		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_oldlabels` WHERE `key` = ".(int)$id." AND `type` = 18";
		$db->setQuery($query);
		$db->query();
		return true;
	}

	/**
	 * Helper method for prepareForStorage().
	 * Auto-fills empty URL Segment fields (property is called label)
	 *
	 * @param object $data Data object as coming in to prepareForStorage
	 */
	protected function fillEmptyUrlSegments(&$data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return;
		}

		$tags = KenedoLanguageHelper::getActiveLanguageTags();

		foreach ($tags as $tag) {

			// Prepare keys for readablity
			$segmentKey = 'label-'.$tag;
			$titleKey = 'title-'.$tag;

			if (empty($data->$segmentKey)) {

				// Get the corresponding title value
				$autoValue = $data->$titleKey;

				// Make the value URL-friendly
				$autoValue = str_replace(' ','-', trim($autoValue));
				$autoValue = preg_replace('/[^A-Za-z0-9\-]/', '', $autoValue);
				$autoValue = strtolower($autoValue);
				// If nothing is left of the value, use the current datetime
				if(trim(str_replace('-','',$autoValue)) == '') {
					$autoValue = KenedoTimeHelper::getFormatted('NOW','datetime');
				}

				// Set the label to the auto value
				$data->$segmentKey = $autoValue;

			}
		}

	}

	/**
	 * Helper method for validateData(). Checks if provided URL segment (label) is already used in other pages of
	 * the same product.
	 * @param $data
	 * @return bool
	 */
	protected function checkForDuplicateUrlSegment($data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return true;
		}

		$languages = KenedoLanguageHelper::getActiveLanguages();

		$db = KenedoPlatform::getDb();

		foreach ($languages as $language) {

			// Prepare label value for convenience
			$segmentValue = $data->{'label-'.$language->tag};

			$query = "
			SELECT str.text AS title
			FROM `#__configbox_pages` AS page
			LEFT JOIN `#__configbox_products` AS p ON p.id = page.product_id
			LEFT JOIN `#__configbox_strings` AS str ON str.key = page.id AND str.type = 3 AND str.language_tag = '".$db->getEscaped($language->tag)."'
			LEFT JOIN `#__configbox_strings` AS catlabel ON catlabel.key = page.id AND catlabel.type = 18 AND catlabel.language_tag = '".$db->getEscaped($language->tag)."'
			WHERE catlabel.text = '".$db->getEscaped($segmentValue)."' AND p.id = ".intval($data->product_id)." AND page.id != ".intval($data->id)."
			LIMIT 1";

			$db->setQuery($query);
			$title = $db->loadResult();
			if ($title) {
				$this->setError(KText::sprintf('PAGE_FEEDBACK_SEF_SEGMENT_TAKEN', $segmentValue, $title, $language->label));
				return false;
			}

		}

		return true;

	}

	/**
	 * Checks if the URL segment has changed. If so, the methods stores a copy in the oldlabels table.
	 * @param object $data
	 */
	protected function storeOldUrlSegments($data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return;
		}

		// In case of inserts, don't act
		if ($this->isInsert($data)) {
			return;
		}

		// Get the current URL segments (The ones that will get replaced)
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT segment.text, segment.language_tag, p.product_id
		FROM `#__configbox_strings` AS segment
		LEFT JOIN `#__configbox_pages` AS p ON p.id = ".intval($data->id)."
		WHERE segment.key = ".intval($data->id)." AND segment.type = 18 AND p.id = ".intval($data->id);
		$db->setQuery($query);
		$currentSegments = $db->loadObjectList('language_tag');

		// Get the active languages
		$languages = KenedoLanguageHelper::getActiveLanguages();

		foreach ($languages as $language) {

			// Prepare segment value for convenience
			$newSegment = $data->{'label-' . $language->tag};
			$currentSegment = !empty($currentSegments[$language->tag]->text) ? $currentSegments[$language->tag]->text : '';

			if ($currentSegment && $newSegment != $currentSegment) {
				$query = "
					REPLACE INTO `#__configbox_oldlabels` (`key`, `type`, `label`, `language_tag`, `created`, `prod_id`)
					VALUES (".intval($data->id).", 18, '".$db->getEscaped($currentSegment)."', '".$db->getEscaped($language->tag)."', ".time().", ".intval($currentSegments[$language->tag]->product_id).")";
				$db->setQuery($query);
				$db->query();
			}

		}

	}

}