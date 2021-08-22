<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewQuestion_Calendar extends ConfigboxViewQuestion {

	function prepareTemplateVars() {
		parent::prepareTemplateVars();

		// Add the locale info to the calendar (will be used in question type JS)
		$locale = [
			'closeText' => KText::_('CALENDAR_DONE_TEXT'),
			'prevText' => KText::_('CALENDAR_PREV_TEXT'),
			'nextText' => KText::_('CALENDAR_NEXT_TEXT'),
			'currentText' => KText::_('CALENDAR_CURRENT_TEXT'),
			'monthNames'=>[
				KText::_('January'),
				KText::_('February'),
				KText::_('March'),
				KText::_('April'),
				KText::_('May'),
				KText::_('June'),
				KText::_('July'),
				KText::_('August'),
				KText::_('September'),
				KText::_('October'),
				KText::_('November'),
				KText::_('December'),
			],
			'monthNamesShort'=>[
				KText::_('Jan'),
				KText::_('Feb'),
				KText::_('Mar'),
				KText::_('Apr'),
				KText::_('May'),
				KText::_('Jun'),
				KText::_('Jul'),
				KText::_('Aug'),
				KText::_('Sep'),
				KText::_('Oct'),
				KText::_('Nov'),
				KText::_('Dec'),
			],
			'dayNames'=>[
				KText::_('Sunday'),
				KText::_('Monday'),
				KText::_('Tuesday'),
				KText::_('Wednesday'),
				KText::_('Thursday'),
				KText::_('Friday'),
				KText::_('Saturday'),
				],
			'dayNamesShort'=>[
				KText::_('Sun'),
				KText::_('Mon'),
				KText::_('Tue'),
				KText::_('Wed'),
				KText::_('Thu'),
				KText::_('Fri'),
				KText::_('Sat'),
				],
			'dayNamesMin'=>[
				KText::_('Su'),
				KText::_('Mo'),
				KText::_('Tu'),
				KText::_('We'),
				KText::_('Th'),
				KText::_('Fr'),
				KText::_('Sa'),
			],
			'weekHeader'=>KText::_('CALENDAR_WEEK_HEADER'),
			'dateFormat'=>KText::_('CALENDAR_DATEFORMAT_JS'),
			'firstDay'=>(KText::_('CALENDAR_FIRSTDAY') === '1') ? 1 : 0,
			'isRTL'=>(KText::_('LANG_IS_RTL') === 'true') ? true : false,
			'showMonthAfterYear'=>(KText::_('CALENDAR_MONTH_AFTER_YEAR') === 'true') ? true:false,
			'yearSuffix'=>'',

		];

		$this->questionDataAttributes .= 'data-locale="'.hsc(json_encode($locale)).'"';

	}

}