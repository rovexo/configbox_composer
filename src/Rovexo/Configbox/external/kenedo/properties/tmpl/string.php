<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyString
 */
$value = (isset($this->data->{$this->propertyName})) ? $this->data->{$this->propertyName} : '';

$style = $this->getPropertyDefinition('style');
$size = $this->getPropertyDefinition('size');

$styleAttribute = ($style) ? 'style="'.$style.'"' : '';
$sizeAttribute = ($size) ? 'maxlength="'.intval($size).'"' : '';

$stringType = $this->getPropertyDefinition('stringType', 'string');

if ($stringType == 'number' || $stringType == 'price') {
	
	if ($value !== '') {
		$value = floatval($value);
	}
	
	if ( (empty($value) && $value !== 0) && $this->getPropertyDefinition('default')) {
		$value = $this->getPropertyDefinition('default');
	}
}
else {
	if (empty($value) && $this->getPropertyDefinition('default')) {
		$value = $this->getPropertyDefinition('default');
	}
}

// For numeric kinds of strings, replace the dot decimal symbol with the localized one
if ($stringType == 'number' or $stringType == 'price' or $stringType == 'time') {
	$value = str_replace('.', KText::_('DECIMAL_MARK', '.'), $value);
}

?>
<div class="string-type-<?php echo hsc($stringType);?><?php echo($this->getPropertyDefinition('unit')) ? ' input-group':'';?>">
	<?php
	$tags = $this->getPropertyDefinition('optionTags');
	if (isset($tags['USE_TEXTAREA'])) {
		?>
		<textarea class="form-control" name="<?php echo $this->propertyName;?>" id="<?php echo $this->propertyName;?>" <?php echo $styleAttribute;?>><?php echo hsc($value);?></textarea>
		<?php
	}
	elseif(isset($tags['USE_HTMLEDITOR'])) {
		$width = ($this->getPropertyDefinition('editorWidth')) ? $this->getPropertyDefinition('editorWidth') : '100%';
		$height = ($this->getPropertyDefinition('editorHeight')) ? $this->getPropertyDefinition('editorHeight') : '400px';
		echo KenedoPlatform::p()->renderHtmlEditor( $this->propertyName,  $value , $width, $height, '40', '5' );
	}
	else {
		?>
		<input class="form-control"
		       type="text"
		       name="<?php echo $this->propertyName;?>"
		       id="<?php echo $this->propertyName;?>"
		       value="<?php echo hsc($value);?>" <?php echo $styleAttribute. ' '.$sizeAttribute;?> />
		<?php
	}

	if ($this->getPropertyDefinition('unit')) {
		?>
		<div class="input-group-append">
			<span class="input-group-text"><?php echo hsc($this->getPropertyDefinition('unit'));?></span>
		</div>
		<?php
	}
	?>
</div>
