<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Class ConfigboxConditionNegation
 * Negation types can only appear as the first item in a rule. They do not appear in the rule editor drop area,
 * the rule item gets added during storing 'manually' and evaluation happens in RuleHelper::getConditionsCode
 */
class ConfigboxConditionNegation extends ConfigboxCondition {

    function getConditionHtml($conditionData, $forEditing = true) {

        if ($forEditing == true) {
           return '';
        }
        else {
            return KText::_('RULE_TEXT_PREFIX_NEGATED');
        }

    }

    function getEvaluationResult($conditionData, $selections) {
        throw new Exception('Negation conditions can never get evaluated');
    }

    function getConditionsPanelHtml($ruleEditorView) {
        return '';
    }

    function showPanel() {
        return false;
    }

}
