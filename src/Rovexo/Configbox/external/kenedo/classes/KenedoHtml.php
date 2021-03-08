<?php
class KenedoHtml {
	
	static function getTooltip($triggerHtml, $contentHtml, $position = NULL, $minWidth = NULL, $maxWidth = NULL, $minHeight = NULL, $maxHeight = NULL, $popupCssClasses = NULL ) {
		$styles = array();
		if ($minWidth !== NULL) {
			$styles['min-width'] = $minWidth.'px;';
		}
		else {
			$styles['min-width'] = '300px;';
		}
		if ($maxWidth !== NULL) {
			$styles['max-width'] = $maxWidth.'px;';
		}
		else {
			$styles['max-width'] = '300px;';
		}
		if ($minHeight !== NULL) {
			$styles['min-height'] = $minHeight.'px;';
		}
		if ($maxHeight !== NULL) {
			$styles['max-height'] = $maxHeight.'px;';
		}
		
		$style = '';
		foreach ($styles as $prop=>$value) {
			$style .= $prop.':'.$value;
		}
		if (empty($triggerHtml)) {
			$triggerHtml = '<span class="fa fa-info-circle"></span>';
		}
		
		if (is_array($popupCssClasses)) {
			$popupCssClasses = implode(' ', $popupCssClasses);
		}
		if (is_null($popupCssClasses)) {
			$popupCssClasses = '';
		}
		
		ob_start();
		$id = rand(0, 1000000);
		$idTrigger = 'kenedo-popup-trigger-'.$id;
		$idPopup = 'kenedo-popup-original-'.$id;
		?>
		
		<div class="kenedo-popup-trigger" id="<?php echo hsc($idTrigger);?>">
			<div class="kenedo-popup-trigger-content"><?php echo $triggerHtml;?></div>
			<div id="<?php echo hsc($idPopup);?>" class="kenedo-popup cb-content <?php echo hsc($popupCssClasses);?> <?php echo ($position == 'bottom') ? 'position-prefer-bottom': 'position-prefer-top';?>"<?php echo ($style) ? ' style="min-width:'.$styles['min-width'].'max-width:'.$styles['max-width'].'"': '';?>>
				<div class="kenedo-popup-content"<?php echo ($style) ? ' style="'.$style.'"': '';?>>
					<div class="kenedo-popup-content-inner">
						<?php echo $contentHtml;?>
					</div>
				</div>
			</div>
		</div>
		
		<?php
		$tooltip = ob_get_clean();
		
		return $tooltip;
		
	}
	
	static function getListingOrderHeading($propertyName, $title, $orderingInfo) {

		foreach ($orderingInfo as $orderingInfoItem) {
			$isCurrent = ($orderingInfoItem['propertyName'] == $propertyName);
			ob_start();
			?>
			<a
				id="order-property-name-<?php echo hsc($propertyName);?>"
				class="order-property <?php echo ($isCurrent) ? 'active':'inactive';?> <?php echo (strtolower($orderingInfoItem['direction']) == 'desc') ? 'direction-desc' : 'direction-asc';?>">
				<?php echo hsc($title);?>
			</a>
			<?php
			return ob_get_clean();
		}
		return '';
	}
	
	static function getCalendar($name, $selectedValue = NULL, $defaultValue = NULL, $cssClasses = NULL, $placeholder = NULL) {
		
		if (is_array($cssClasses) && count($cssClasses)) {
			$cssClasses = implode(' ',$cssClasses);
		}
		
		$cssClasses .= ' datepicker form-control';
		
		if ($cssClasses) {
			$classAttribute = ' class="'.$cssClasses.'"';
		}
		else {
			$classAttribute = '';
		}
		
		if ($selectedValue === NULL) {
			$selectedValue = $defaultValue;
		}
		
		ob_start();
		?>
		
		<input <?php echo ($placeholder) ? 'placeholder="'.hsc($placeholder).'" ':'';?>name="<?php echo hsc($name);?>" value="<?php echo $selectedValue;?>" id="<?php echo hsc($name);?>" type="text"<?php echo $classAttribute;?> />
		
		<?php
		$calendar = ob_get_clean();
		
		return $calendar;
		
	}
	
	static function getRadioButtons($name, $options, $selectedValue = NULL, $defaultValue = NULL, $cssClasses = NULL) {
		
		if (is_array($cssClasses) && count($cssClasses)) {
			$cssClasses = implode(' ',$cssClasses);
		}
		
		if ($cssClasses) {
			$classAttribute = ' class="'.$cssClasses.'"';
		}
		else {
			$classAttribute = '';
		}
		
		if ($selectedValue === NULL) {
			$selectedValue = $defaultValue;
		}
		
		ob_start();
		?>
		
		<?php
		foreach ($options as $value=>$title) {
			$id = $name.$value.rand(0,1000);
			?>
			<input name="<?php echo hsc($name);?>" value="<?php echo $value;?>" id="<?php echo $id;?>" type="radio"<?php echo ($selectedValue == $value) ? ' checked="checked"':'';?><?php echo $classAttribute?> />
			<label class="radio-button-label" for="<?php echo $id;?>"><?php echo $title;?></label>
			<?php 
		} 
		?>
		
		<?php
		$radioButtons = ob_get_clean();
		
		return $radioButtons;
	}
	
	static function getTextField($name, $value, $cssId = NULL, $cssClasses = NULL )  {
		ob_start();
		?>
		<input type="text" name="<?php echo hsc($name);?>" id="<?php echo ($cssId) ? hsc($cssId) : hsc($name);?>" value="<?php echo hsc($value);?>"<?php echo ($cssClasses !== NULL) ? 'class="'.hsc($cssClasses).'" ': ' '?>/>
		<?php
		$field = ob_get_clean();
		
		return $field;
	}

	/**
	 * @param string 			$name			Name attribute (and ID unless $cssId is used)
	 * @param array 			$options 		Options for the <select>, can be key/value pairs or an array of objects
	 * 											with a variable 'title'. Can have a 'sub array' to make an <optgroup>
	 * @param string|string[]	$selectedValue 	The option to be preselected (or options if using multiple)
	 * @param string|string[]	$defaultValue 	Default value (for when there is no selected value) (or values if using multiple)
	 * @param bool 				$multiSelect 	If it should be a multi-select
	 * @param string|string[] 	$cssClasses 	CSS classes for the <select> tag. Can be a string or array of strings
	 * @param string 			$cssId 			CSS ID for the <select> tag (if NULL then $name is used)
	 * @param string[] 			$attributes 	Key/value pairs to add HTML attributes to the <select> tag
	 * @return string
	 */
	static function getSelectField($name, $options, $selectedValue = NULL, $defaultValue = NULL, $multiSelect = false, $cssClasses = NULL, $cssId = NULL, $attributes = array() )  {

		// Normalize the css classes to a string
		if (is_array($cssClasses) && count($cssClasses)) {
			$cssClasses = implode(' ',$cssClasses);	
		}

		// Prepare the selected value (or values for multiple)
		if ($selectedValue !== NULL) {
			if (is_array($selectedValue)) {
				$selectedValue = array_flip($selectedValue);
			}
			else {
				$selectedValue = array($selectedValue=>true);
			}
		}
		else {
			$selectedValue = array();	
		}

		// Deal with the default value
		if ($multiSelect) {
			if ($selectedValue === NULL) {
				$selectedValue = (array)$defaultValue;
			}
		}
		else {
			if ($selectedValue === NULL) {
				$selectedValue = $defaultValue;
			}
		}

		// Prepare the rest of the <select>'s HTML attributes
		$attributes['class'] = $cssClasses;

		if ($cssId === NULL && !empty($name)) {
			$cssId = $name;
		}

		if ($cssId) {
			$attributes['id'] = $cssId;
		}

		if ($name) {
			$attributes['name'] = $name;
		}

		if ($multiSelect) {
			$attributes['multiple'] = 'multiple';
		}

		// Make attribute pairs as strings..
		$attributeStrings = array();
		foreach ($attributes as $key=>$value) {
			$attributeStrings[] = hsc(trim($key)) . ' = "'.hsc(trim($value)).'"';
		}
		// .. then make one long string with all.
		$attributeString = implode(' ', $attributeStrings);

		ob_start();
		?>
		<select <?php echo $attributeString;?>>
			<?php
			foreach ($options as $key=>$item) {
			
				if (is_array($item)) {
					?>
					<optgroup label="<?php echo hsc($key);?>">
						<?php foreach ($item as $key1=>$title) { ?>
							<option <?php echo (isset($selectedValue[$key1])) ? 'selected="selected"':''; ?> value="<?php echo hsc($key1);?>"><?php echo hsc($title);?></option>
						<?php } ?>
					</optgroup>
					<?php
				}
				else {
					$title = (is_object($item)) ? $item->title : $item;
					?>
					<option <?php echo (isset($selectedValue[$key])) ? 'selected="selected"':''; ?> value="<?php echo hsc($key);?>"><?php echo hsc($title);?></option>
					<?php
				}
				
			}
			?>
		</select>
		<?php
		$selectField = ob_get_clean();
		
		return $selectField;
	}
	
	static function getCheckboxField($name, $options, $selectedValue = NULL, $defaultValue = NULL, $multiSelect = false, $cssClasses = NULL, $cssId = NULL )  {
	
		if (is_array($cssClasses) && count($cssClasses)) {
			$cssClasses = implode(' ',$cssClasses);
		}

		if ($selectedValue !== NULL) {
			if (is_array($selectedValue)) {
				$selectedValue = array_flip($selectedValue);
			}
			else {
				$selectedValue = array($selectedValue=>true);
			}
		}
		else {
			$selectedValue = array();
		}
	
		if ($multiSelect) {
			if ($selectedValue === NULL) {
				$selectedValue = array();
			}
		}
		else {
			if ($selectedValue === NULL) {
				$selectedValue = $defaultValue;
			}
		}
		
		ob_start();
		?>
		
		<div class="checkbox-field<?php echo ($cssClasses) ? ' '.$cssClasses:''?>">
			<?php foreach ($options as $key=>$item) { 
				
				$title = (is_object($item)) ? $item->title : $item;
				$id = $name.'-checkbox-'.rand(0,100000);
				?>
				<div class="checkbox-item">
					<input type="checkbox" id="<?php echo hsc($id);?>" name="<?php echo hsc($name);?>[]" value="<?php echo hsc($key);?>"<?php echo (isset($selectedValue[$key])) ? ' checked="checked"':''?> />
					<label class="checkbox-label" for="<?php echo hsc($id);?>"><?php echo hsc($title);?></label>
				</div>
			<?php } ?>
		</div>
		
		<?php
		$selectField = ob_get_clean();
		
		return $selectField;
	}
	
}


