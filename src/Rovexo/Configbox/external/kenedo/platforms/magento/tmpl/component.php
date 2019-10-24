<?php

/**
 * @var KenedoPlatformMagento $this
 */
?>
<html>
<head>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />

	<?php $this->renderStyleSheetLinks();?>
	<?php $this->renderStyleDeclarations();?>
	<?php $this->renderScriptAssets();?>
	<?php $this->renderHeadScriptDeclarations();?>

</head>
<body>

	<?php echo $output; ?>

	<?php $this->renderBodyScriptDeclarations();?>

</body>
