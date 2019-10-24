<?php

/**
 * @var KenedoPlatformMagento $this
 */

ob_clean();

$this->renderHeadScriptDeclarations();
echo $output;
$this->renderBodyScriptDeclarations();
