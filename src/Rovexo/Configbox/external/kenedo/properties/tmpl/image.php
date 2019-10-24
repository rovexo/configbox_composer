<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyImage
 */

$filename = $this->data->{$this->propertyName};
$pathUrl = $this->getPropertyDefinition('urlBase');
$pathFilesystem = $this->getPropertyDefinition('dirBase');
$fileMissing = ($filename && !is_file($pathFilesystem.DS.$filename));
$fileSet = ($filename);
$optionTags = $this->getPropertyDefinition('optionTags');
$canDelete = ($fileSet && empty($optionTags['NODELETEFILE']) && ($this->isRequired() == false) && $this->data->id != 0);
?>

<div class="file-wrapper">

	<div class="file-current-file">
		<?php if ($filename) { ?>

			<?php if (!empty($pathUrl)) { ?>
				<span><?php echo KText::_('Current file:');?></span> <a title="<?php echo hsc($pathFilesystem.DS.$filename);?>" class="kenedo-new-tab file-link" href="<?php echo $pathUrl .'/'. $filename;?>"><?php echo hsc($filename);?></a>
			<?php } else { ?>
				<span><?php echo KText::_('Current file:');?></span> <span title="<?php echo hsc($pathFilesystem.DS.$filename);?>"><?php echo $filename;?></span>
			<?php } ?>

			<?php if ($fileMissing) { ?>
				<span class="highlighted"><?php echo KText::_('File missing');?></span>
			<?php } ?>

		<?php } else { ?>
			<span class="no-file-text"><?php echo KText::_('No file stored yet');?></span>
		<?php } ?>
	</div>

	<?php if ($canDelete) { ?>
		<div class="file-delete">
			<input type="checkbox" name="<?php echo $this->propertyName;?>-delete" value="1" id="<?php echo $this->propertyName;?>-delete" class="file-delete-checkbox" />
			<label class="file-delete-label" for="<?php echo $this->propertyName;?>-delete"><?php echo KText::_('Delete file at save');?></label>
		</div>
	<?php } ?>

	<div class="file-upload">
		<a class="show-file-uploader"><?php echo ($filename) ? KText::_('Replace file') : KText::_('Upload a file');?></a>
	</div>

	<div class="file-uploader">

		<?php
		$dims = $this->getPropertyDefinition('minimumDimensions', array());
		$extensions = $this->getPropertyDefinition('allowedExtensions', array());
		?>

		<?php if (!empty($dims['width'])) { ?>
			<div><?php echo KText::_('Minimum Width');?>: <?php echo intval($dims['width']);?>px</div>
		<?php } ?>

		<?php if (!empty($dims['height'])) { ?>
			<div><?php echo KText::_('Minimum Height');?>: <?php echo intval($dims['height']);?>px</div>
		<?php } ?>

		<?php if (count($extensions)) { ?>
			<div class="valid-extensions">
				<?php echo KText::_('Valid image types');?>: <?php echo implode(', ', $extensions);?>
			</div>
		<?php } ?>

		<input class="file-upload-field" type="file" name="<?php echo $this->propertyName;?>" />
		<a class="file-upload-cancel"><?php echo KText::_('Cancel');?></a>

	</div>

</div>