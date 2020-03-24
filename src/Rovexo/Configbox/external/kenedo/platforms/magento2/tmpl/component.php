<?php

/**
 * @var KenedoPlatformMagento2 $this
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $this->getLanguageTag();?>">
<head>
	<title><?php echo $this->getDocumentTitle();?></title>
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
</html>