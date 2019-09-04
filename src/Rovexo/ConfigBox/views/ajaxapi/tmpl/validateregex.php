<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewAjaxApi */

$expression = KRequest::getVar('expression');
$success = @preg_match($expression,'dummystring');

if ($success === false) {
	$error = error_get_last();
	if ($error) {
		echo $error['message'];
	}
	else {
		echo KText::_('Expression contains an error.');
	}
}
else {
	echo 'OK';
}
