<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewQuestion_Ralcolorpicker extends ConfigboxViewQuestion {

	/**
	 * @var object[] Contains all RAL color codes
	 */
	public $ralColors;

	/**
	 * @var string[] RAL color groups
	 */
	public $ralColorGroups;

	/**
	 * @var int[] Shows RAL color IDs which RAL colors has darker background
	 */
	public $ralColorsDark;

	/**
	 * @var int Current RAL color ID
	 */
	public $selectedColorId;

	/**
	 * @var int Current RAL color Group ID
	 */
	public $selectedColorGroupId;

	public function prepareTemplateVars() {

		parent::prepareTemplateVars();

		// get model
		$colorModel = KenedoModel::getModel('ConfigboxModelAdminRalcolors');
		// get colors
		$this->ralColors = $colorModel->getColors();
		// get groups
		$this->ralColorGroups = $colorModel->getGroups();
		// get dark colors array
		$this->ralColorsDark = $colorModel->dark_colors;

		// set empty colors
		$this->selectedColorId = '';
		$this->selectedColorGroupId = 0;

		// load all available RAL color codes
		$ralColorsModel = KenedoModel::getModel('ConfigboxModelAdminRalcolors');
		$availableRalColors = $ralColorsModel->getColors();
		$allColorCodes = [];
		foreach($availableRalColors as $colorId => $ralColor) {
			$allColorCodes[] = 'RAL ' . $colorId;
		}

		// validate default value and selection
		if(!empty($this->question->default_value) && !in_array($this->question->default_value, $allColorCodes)) {
			KLog::log('Invalid default value of RAL Color Code. Value was `'.$this->question->default_value.'`', 'error');
		}
		elseif(in_array($this->selection, $allColorCodes)) {
			$this->selectedColorId = explode(' ', $this->selection)[1];
			$this->selectedColorGroupId = $this->selectedColorId[0];
		}

	}

}