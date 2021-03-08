<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyTranslatable
 */
$languages = KenedoLanguageHelper::getActiveLanguages();

// Do text areas and html areas first
$optionTags = $this->getPropertyDefinition('optionTags');
if (isset($optionTags['USE_TEXTAREA']) || isset($optionTags['USE_HTMLEDITOR'])) {
	?>
	<div class="kenedo-translatable-textarea">
		<?php if (count($languages) > 1) { ?>
			<div class="language-switchers">
				<?php
				foreach ($languages as $language) {
					$dataFieldKey = $this->propertyName.'-'.$language->tag;
					?>
					<label class="language-switcher <?php echo ($language->tag == KText::getLanguageTag()) ? 'active':'';?>" for="<?php echo $dataFieldKey;?>"><span><?php echo hsc($language->label);?></span></label>
					<?php
				}
				?>
			</div>
		<?php } ?>
		
		<div class="translations text-area">
			<?php
			foreach ($languages as $language) {
				$dataFieldKey = $this->propertyName.'-'.$language->tag;
				$content = (isset($this->data->$dataFieldKey)) ? $this->data->$dataFieldKey : '';
				?>
				<div class="translation" id="translation-<?php echo $dataFieldKey;?>" style="display:<?php echo ($language->tag == KText::getLanguageTag()) ? 'block':'none';?>">
					<?php 
					if (isset($optionTags['USE_TEXTAREA'])) {
						?>	
						<textarea class="form-control text_area" name="<?php echo hsc($dataFieldKey);?>" id="<?php echo hsc($dataFieldKey);?>"><?php echo hsc($content);?></textarea>
						<?php 
					}
					else {
						$width = ($this->getPropertyDefinition('editorWidth')) ? $this->getPropertyDefinition('editorWidth') : '100%';
						$height = ($this->getPropertyDefinition('editorHeight')) ? $this->getPropertyDefinition('editorHeight') : '400px';
						echo KenedoPlatform::p()->renderHtmlEditor( $dataFieldKey,  $content , $width, $height, '40', '5' );
					}
					?>
					<div class="clear-left"></div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	
}
else {
	?>
	<div class="translations text row">
		<?php
		foreach ($languages as $language) {
			$dataFieldKey = $this->propertyName.'-'.$language->tag;
			$label = (count($languages) > 1) ? $this->getPropertyDefinition('label'). ' - ' .$language->label : $this->getPropertyDefinition('label');
			?>
			<div class="translation col-md-6" id="translation-<?php echo hsc($dataFieldKey);?>">
				
					<label class="translation-label" for="<?php echo hsc($dataFieldKey);?>">
					<?php 
					if ($this->getPropertyDefinition('tooltip')) {
						echo KenedoHtml::getTooltip( hsc($label), $this->getPropertyDefinition('tooltip'));
					}
					else {
						echo hsc($label);
					}
					?>
					
				</label>
				
				<input class="form-control" type="text" name="<?php echo hsc($dataFieldKey);?>" id="<?php echo hsc($dataFieldKey);?>" value="<?php echo hsc($this->data->$dataFieldKey);?>" />
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

