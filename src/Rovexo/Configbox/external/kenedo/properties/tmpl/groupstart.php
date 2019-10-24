<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyGroupstart
 */

if ($this->getPropertyDefinition('toggle') == true) {

	$sessionKey = $this->getSessionKey();
	$defaultState = $this->getPropertyDefinition('defaultState', 'closed');
	$state = KSession::get($sessionKey, $defaultState, 'kenedo');

	$fieldsetClasses = 'property-group property-group-'.$this->propertyName.' property-group-using-toggles';
	
	if ($state == 'opened') {
		$fieldsetClasses .= ' property-group-opened';
	}
	else {
		$fieldsetClasses .= ' property-group-closed';
	}
}
else {
	$fieldsetClasses = 'property-group property-group-'.$this->propertyName.' property-group-opened';
	$state = 'opened';
}

$html = '<div id="'.hsc($this->getCssId()).'" class="'.hsc($fieldsetClasses).'" data-property-definition="'.hsc(json_encode($this->getPropertyDefinition())).'">
	<h2 class="property-group-legend">'.hsc($this->getPropertyDefinition('title')).'</h2>';

if ($this->getPropertyDefinition('toggle') == true) {
	$html .= '<input style="display:none" class="property-group-toggle-state" type="hidden" name="toggle-state-'.hsc($this->propertyName).'" value="'.hsc($state).'" />';
}
$html .= '<div class="property-group-content">';

if ($this->getPropertyDefinition('notes', '') != '') {

	$heading = $this->getPropertyDefinition('noteHeading', '');

	$html .= '<div class="bs-callout bs-callout-info help-text">';
	if ($heading) {
		$html .= '<h4 class="property-group-notes-title">'.hsc($heading).'</h4>';
	}
	$html .= $this->getPropertyDefinition('notes', '');
	$html .= '</div>';
}
$html .= '<div class="property-group-properties">';

echo $html;