<?php 
class KenedoViewHelper {
	
	protected static $canSort = array();
	public static $messages = array();

	/**
	 * @param KenedoProperty[] $properties
	 * @return null
	 */
	static function getGroupingKey($properties) {
		$groupKey = NULL;

		if (empty($properties)) {
			return $groupKey;
		}

		foreach ($properties as $property) {
			if ($property->getPropertyDefinition('type') == 'ordering') {
				$groupKey = $property->getPropertyDefinition('group');
				break;
			}
		}
		return $groupKey;
	}

	/**
	 * @param object[] $records
	 * @param KenedoProperty[] $properties
	 * @param array[] $orderingInfoItems
	 * @return bool
	 */
	static function canSortItems($records, $properties, $orderingInfoItems) {

		foreach ($properties as $property) {
			if ($property->getPropertyDefinition('type') == 'ordering') {

				if ($property->getPropertyDefinition('disableSortable', false)) {
					return false;
				}

				$foundIt = false;

				foreach ($orderingInfoItems as $orderingInfoItem) {
					if ($property->propertyName == $orderingInfoItem['propertyName']) {
						$foundIt = true;
					}
				}
				return $foundIt;

			}
		}

		return false;

	}
	
	static function clearMessages() {
		self::$messages = array();
	}
	
	static function addMessage($text, $type = 'notice') {
		self::$messages[$type][] = $text;
	}
	
	static function getMessages($type = NULL) {
		
		if (!$type) {
			return self::$messages;
		}
		elseif(isset(self::$messages[$type])) {
			return self::$messages[$type];
		}
		else {
			return array();
		}
	}
	
	static function getState($path, $default = NULL) {
		return KSession::get('KenedoView.'.$path,$default);
	}
	
	static function setState($path, $value) {
		KSession::set('KenedoView.'.$path,$value);
	}

	static function unsetState($path) {
		KSession::delete('KenedoView.'.$path);
	}
	
	static function getUpdatedState($path, $requestKey, $defaultValue = NULL, $sanitationType = 'int') {
		$requestState = KRequest::getVar($requestKey, null, 'METHOD', $sanitationType);
		if ($requestState !== NULL) {
			self::setState($path, $requestState);
		}
		
		$state = self::getState($path, $defaultValue);
		return $state;
		
	}
	
	// Legacy method, remove in 2.6 or 3.0
	static function getTabItems() {
		return array();
	}
	
	static function renderTabItems($tabItems) {
		
		if (!$tabItems) {
			return '';
		}
		$currentViewName = KRequest::getKeyword('view');
		ob_start();
		?>
		<div class="kenedo-tabs">
			<ul class="kenedo-tab-list">
				<?php foreach ($tabItems as $viewName=>$tab) {
					
					$isActive = ($viewName == $currentViewName or (isset($tab['activeOn']) && in_array($currentViewName,$tab['activeOn'])) );
					
					if ($isActive && isset($tab['subviews'])) {
						$subviews = $tab['subviews'];
					}
					?>
					<li<?php echo ($isActive) ? ' class="active"' : '';?>><a<?php echo (!empty($tab['tooltip'])) ? ' title="'.hsc($tab['tooltip']).'" ':' ';?>href="<?php echo $tab['link'];?>"><?php echo hsc($tab['title']);?></a></li>
					
				<?php } ?>
			</ul>
			<div class="clear"></div>
			
		</div>
		
		<?php if (isset($subviews)) { ?>
			<div class="kenedo-tabs">
				<ul class="kenedo-tab-list">
					<?php foreach ($subviews as $viewName=>$tab) {
						$currentViewName = KRequest::getKeyword('view');
						
						$isActive = ($viewName == $currentViewName or (isset($tab['activeOn']) && in_array($currentViewName,$tab['activeOn'])) );
						
						?>
						<li><a href="<?php echo $tab['link'];?>" <?php echo ($isActive) ? 'class="active"' : '';?>><?php echo hsc($tab['title']);?></a></li>
					<?php } ?>
				</ul>
				<div class="clear"></div>
			</div>
		<?php } ?>
			
		<?php 
		$tabs = ob_get_clean();

		return $tabs;
	}
	
	static function renderTaskItems($taskItems) {
		
		if (!$taskItems) {
			return '';
		}
		ob_start();
		?>
		<div class="kenedo-tasks">
			<ul class="kenedo-task-list">
				<?php foreach ($taskItems as $task) {
					$cssClasses = 'trigger-kenedo-form-task btn '.( (!empty($task['primary'])) ? ' btn-primary' : 'btn-default');
					?>
					<?php if (!empty($task['link'])) { ?>
						<li class="link">
							<a href="<?php echo $task['link'];?>" class="<?php echo hsc($cssClasses);?>"><?php echo hsc($task['title']);?></a>
						</li>
					<?php } else { ?>
						<li class="task task-<?php echo hsc($task['task']);?><?php echo (!empty($task['non-ajax'])) ? ' non-ajax':'';?>">
							<a data-task="<?php echo hsc($task['task']);?>" class="<?php echo hsc($cssClasses);?>"><?php echo hsc($task['title']);?></a>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
		<?php 
		$tabs = ob_get_clean();
		
		return $tabs;
	}
	
	static function getListingPagination ($itemCount, $listingInfo) {
		
		if ($listingInfo['limit']) {
			$numPages = ceil( $itemCount / $listingInfo['limit'] );
		}
		else {
			$numPages = 1;
		}
		
		$pagination = '';
		
		if ($itemCount > 5) {
		
			$items = array(5,10,15,20,25,30,50,100,0);
			$selected = $listingInfo['limit'];
			
			ob_start();
			?>
			<div class="kenedo-limit">
				<label><?php echo KText::_('Items per page');?></label>
				<select class="kenedo-limit-select">
					<?php foreach ($items as $item) { ?>
						<option value="<?php echo (int)$item;?>" <?php echo ($item == $selected) ? 'selected="selected"' : '';?>><?php echo ($item == 0) ? KText::_('All') : (int)$item;?></option>
					<?php } ?>
				</select>
			</div>
			<?php
			$pagination = ob_get_clean();
		}
		
		$pageLinks = array();
		for ($i = 0; $i < $numPages; $i++) {
			$pageLinks[] = array('start'=>$i * $listingInfo['limit'], 'page'=>$i + 1);
		}
		
		if (count($pageLinks) > 1) {
			ob_start();
			?>
			<div class="kenedo-pagination-list">
				<div class="pagination-label"><?php echo KText::_('Go to page');?></div>
				<ul>
					<?php foreach ($pageLinks as $pageLink) { ?>
						<li <?php echo ($pageLink['start'] == $listingInfo['start']) ? 'class="active"':'';?>>
							<a class="trigger-change-page" data-start="<?php echo intval($pageLink['start']);?>"><?php echo intval($pageLink['page']);?></a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php
			$pagination .= ob_get_clean();
		}
		
		return $pagination;
		
		
	}

	/**
	 * @deprecated No longer in use (see KenedoView::loadAsssets)
	 */
	static function loadKenedoAssets() {

	}
	
}