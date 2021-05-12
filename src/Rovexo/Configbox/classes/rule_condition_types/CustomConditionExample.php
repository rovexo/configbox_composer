<?php
defined('CB_VALID_ENTRY') or die();

class CustomConditionExample extends ConfigboxCondition {

	protected function readMe() {

		// Example for parameter $selections as used in many methods here

		$selections = array(
			3 => 'ABC',     // Customer entered ABC in question ID 3
			6 => '4',		// Customer chose answer with ID 4 in question ID 6
		);

		// Example of condition data. Anything you set as data attribute in your condition in ::getConditionHtml() will
		// be in here.

		$conditionData = array(
			'type' => 'Example',				// Simply the name of the condition type
			'name' => 'Condition 1',			// Something to show the user what the condition is about
			'fieldName' => 'field_1',			// This is just an arbitrary key and value, you can have as many data items as you like and use them in getEvaluationResult() for evaluating the condition
			'operator' => '>=',					// This is the default operator (Functionality for users to choose their operator comes in automatically)
			'shouldValue' => '3',				// The value that the thing you check for should have
		);

		// Example of the condition HTML
		$forEditing = true;
		?>
		<!-- Each item in a rule is enclosed by a <span> element -->
		<!-- it always needs the class "item" and "condition" -->
		<!-- All data attributes end up as condition data. type and operator are required. -->
		<!-- In this type it would be "Example" -->
		<!-- data-operator is the default operator. It is a machine-readable relational operator, like "==", ">", "!="
		     etc. It will automatically be updated when a user changes the operator (see below)
		-->
		<!--
			Any other data items are up to you. They will all end up as $conditionData in ::getEvaluationResult() for
			you to see if the condition is met or not.
		-->

		<span
			class="item condition"
			data-type="<?php echo $conditionData['type'];?>"
			data-operator="<?php echo $conditionData['operator'];?>"
			data-field-name="<?php echo $conditionData['fieldName'];?>">

			<!--
			class="condition-name", shows the user what the condition is about.
			For example: 'Selection in element "Color"'
			-->
			<span class="condition-name"><?php echo $conditionData['name'];?></span>

			<!--
			This shows the relational operator. The method call loads the readable version (== becomes 'is'). There
			is a set of default operators (governed by ConfigboxCondition::getOperators), which you can override.
			Clicks on that <span> make the operator picker appear (there's default JS that changes the condition data
			automatically)
			-->
			<span class="condition-operator"><?php echo $this->getOperatorText($conditionData['operator']);?></span>

			<!--
			The getConditionHtml() method got a parameter $forEditing that indicates if the HTML is for the rule editor
			or for showing the rule in elsewhere in the backend
			-->
			<?php if ($forEditing) { ?>
				<!--
				- With .input elements you can have users enter data for the condition. The data will end up in the
				  condition data. With the attribute "data-data-key" you tell the name of the input. Value needs to
				  be in camelCase.
				- The <inputs> element needs to be a direct child of span.item.condition!
				- Numbers will automatically be normalized for machine readable decimal symbols. In the output, make
				  sure you revert the decimal symbol.
				-->
				<input class="input" data-data-key="shouldValue" type="text" value="<?php echo hsc($conditionData['shouldValue']);?>" />
			<?php } else { ?>
				<!--
				This will show up in the backend in edit forms etc.
				-->
				<span class="condition-value"><?php echo hsc($conditionData['shouldValue']);?></span>
			<?php } ?>

		</span>
		<?php

	}

	/**
	 * @param ConfigboxViewAdminRuleeditor $ruleEditorView
	 * @return string The HTML for the type's panel in the rule editor. It should have a list of available conditions.
	 */
	function getConditionsPanelHtml($ruleEditorView) {

		$availableConditions = array(

			array(
				'type'=>'Example',
				'name' => 'Condition 1',
				'fieldName' => 'field_1',
				'operator' => '==',
				'shouldValue' => '',
			),
			array(
				'type'=>'Example',
				'name' => 'Condition 2',
				'fieldName' => 'field_2',
				'operator' => '==',
				'shouldValue' => '',
			),
			array(
				'type'=>'Example',
				'name' => 'Condition 3',
				'fieldName' => 'field_3',
				'operator' => '==',
				'shouldValue' => '',
			),

		);

		ob_start();
		?>
		<div class="custom-conditions-notes">
			<?php echo KText::_('CUSTOM_CONDITIONS_NOTES');?>
		</div>
		<ul class="conditions-list">
			<?php foreach ($availableConditions as $conditionData) { ?>
				<li><?php echo $this->getConditionHtml($conditionData);?></li>
			<?php } ?>
		</ul>
		<?php
		return ob_get_clean();

	}

	/**
	 * Called by ConfigboxRulesHelper::getConditionHtml to display the condition (either for editing or display)
	 *
	 * @param string[] $conditionData
	 * @param bool $forEditing If edit controls or plain display should come out
	 * @return string HTML for that condition
	 * @see ConfigboxRulesHelper::getConditionsHtml
	 */
	function getConditionHtml($conditionData, $forEditing = true) {

		ob_start();

		?>
		<span
			class="item condition"
			data-type="<?php echo $conditionData['type'];?>"
			data-name="<?php echo $conditionData['name'];?>"
			data-field-name="<?php echo $conditionData['fieldName'];?>"
			data-operator="<?php echo $conditionData['operator'];?>"
			>

			<span class="condition-name"><?php echo hsc($conditionData['name']);?></span>

			<span class="condition-operator"><?php echo $this->getOperatorText($conditionData['operator']);?></span>

			<?php if ($forEditing) { ?>
				<input class="input" data-data-key="shouldValue" type="text" value="<?php echo hsc($conditionData['shouldValue']);?>" />
			<?php } else { ?>
				<span class="condition-value"><?php echo hsc($conditionData['shouldValue']);?></span>
			<?php } ?>

		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * This method checks if the condition is met and returns true or false.
	 *
	 * @param string[] $conditionData The condition data you have set up for your type.
	 * @param string[] $selections An array with the current selections in the configuration (keys are question ids, values are selections).
	 *
	 * @return bool true if condition is met, false otherwise
	 *
	 * @see CustomConditionExample::readMe
	 */
	function getEvaluationResult($conditionData, $selections) {

		// In this example we got a $fieldName and $operator and a $shouldValue
		// You are to make something out of the $fieldName (It would stand for some field in some data in CB)
		// This method gets supplied with all selections. If you need other data, load it here yourself for evaluation.
		// If you need customer data, use ConfigboxUserHelper::getUser() to get an object with all currently
		// known customer data. Mind that conditions are evaluated multiple times each time a visitor makes a selection,
		// so be mindful about performance and cache as much as possible.

		$fieldName = $conditionData['fieldName'];
		$operator = $conditionData['operator'];
		$shouldValue = $conditionData['shouldValue'];

		return true;
	}

	/**
	 * @inheritDoc
	 */
	function getCopiedConditionData($conditionData, $copyIds) {
		return $conditionData;
	}

	function getTypeTitle() {
		return KText::_('Custom Conditions');
	}

}