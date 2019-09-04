<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var KenedoPlatformWordpress $this
 */

ob_clean();

$this->renderHeadScriptDeclarations();
echo $output;
$this->renderBodyScriptDeclarations();

die();