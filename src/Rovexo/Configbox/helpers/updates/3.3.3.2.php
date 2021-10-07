<?php
defined('CB_VALID_ENTRY') or die();

if (KenedoPlatform::getName() == 'wordpress') {
	ConfigboxWordpressHelper::renewPages();
}