<?php

/**
 * @var KenedoPlatformMagento2 $this
 */

ob_clean();

$this->renderHeadScriptDeclarations();
echo $output;
$this->renderBodyScriptDeclarations();
