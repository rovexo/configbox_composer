<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var KenedoPlatformStandalone $this
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $this->getLanguageTag();?>">
<head>
<title><?php echo $this->getDocumentTitle();?></title>
<base href="<?php echo KenedoPlatform::p()->getUrlBase();?>" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo KenedoPlatform::p()->getUrlBase().'/components/com_configbox/external/kenedo/platforms/standalone/tmpl/css/standalone.css';?>" type="text/css" />

<?php 
foreach ($GLOBALS['document']['stylesheets'] as $url=>$some) {
	?>
	<link rel="stylesheet" href="<?php echo $url;?>" type="text/css" />
	<?php
}
?>

<?php 
foreach ($GLOBALS['document']['scripts'] as $url=>$some) {
	?>
	<script type="text/javascript" src="<?php echo $url;?>"></script>
	<?php
}
?>

<?php 
foreach ($GLOBALS['document']['script_codes'] as $code) {
	?>
	<?php echo $code;?>
	<?php
}
?>
</head>

<body>
<?php echo $output;?>
</body>
</html>