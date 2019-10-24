<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if (is_file($this->pspBridgeFilePath)) {
	include($this->pspBridgeFilePath);
}
