<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>
<?php if (ConfigboxPermissionHelper::canQuickEdit()) echo ConfigboxQuickeditHelper::getAnswerEditButtons($answer);?>
