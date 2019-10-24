<?php
if (!defined('CB_VALID_ENTRY')) {
	define('CB_VALID_ENTRY',true);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.form.formfield');

class JFormFieldPage extends JFormField {
	
	public $_name = 'Page';

	protected function getInput() {
		
		// Init Kenedo framework
		require_once( dirname(__FILE__).'/../../../init.php');

		$pickerObject = $this->id;
		$value = $this->value;
		$fieldName = $this->name;
		
		$link = KLink::getRoute('index.php?option=com_configbox&controller=adminpages&tmpl=component&parampicker=1&pickerobject='.$pickerObject);
		
		$db = KenedoPlatform::getDb();
		$tag = KenedoPlatform::p()->getLanguageTag();
		$query = "SELECT * FROM `#__configbox_active_languages` WHERE `tag` = '".$db->getEscaped($tag)."'";
		$db->setQuery($query);
		$isActive = (boolean)$db->loadResult();
		if (!$isActive) {
			$query = "SELECT `language_tag` FROM `#__configbox_config` WHERE `id` = 1";
			$db->setQuery($query);
			$tag = $db->loadResult();
		}
		
		$item = new stdClass;
		
		if ($value) {
			$item->title = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 3, $value, $tag);
		} 
		else {
			$item->title = KText::_('Any');
		}
		
		ob_start();
		?>
		<span class="input-append">
			<input type="text" id="<?php echo $pickerObject;?>_name" name="<?php echo $fieldName;?>" value="<?php echo htmlspecialchars($item->title, ENT_QUOTES);?>" required="required" readonly="readonly" class="input-medium">
			<a href="#<?php echo $pickerObject;?>-modal" role="button" class="btn btn-primary" data-toggle="modal" title="Select"><span class="icon-list icon-white"></span> Select</a>
		</span>

		<input type="hidden" id="<?php echo $pickerObject;?>_id" name="<?php echo $fieldName;?>" value="<?php echo (int)$value;?>" />

		<div id="<?php echo $pickerObject;?>-modal" tabindex="-1" class="modal hide fade jviewport-width80">
			<div class="modal-header">
				<button type="button" class="close novalidate" data-dismiss="modal">×</button>
			</div>
			<div class="modal-body jviewport-height70"></div>
			<div class="modal-footer">
				<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Schließen</button>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var triggerId = '<?php echo $pickerObject;?>-modal';
				$('#' + triggerId).on('show.bs.modal', function() {
					$('body').addClass('modal-open');
					var modalBody = $(this).find('.modal-body');
					modalBody.find('iframe').remove();
					modalBody.prepend('<iframe class="iframe jviewport-height70" src="<?php echo $link;?>" height="300px" width="800px"></iframe>');
				}).on('shown.bs.modal', function() {
					var modalHeight = $('div.modal:visible').outerHeight(true),
						modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
						modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true),
						modalBodyHeight = $('div.modal-body:visible').height(),
						modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
						padding = document.getElementById(triggerId).offsetTop,
						maxModalHeight = ($(window).height()-(padding*2)),
						modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
						maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
					var iframeHeight = $('.iframe').height();
					if (iframeHeight > maxModalBodyHeight){
						$('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
						$('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
					}
				}).on('hide.bs.modal', function () {
					$('body').removeClass('modal-open');
					$('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
					$('.modalTooltip').tooltip('destroy');
				});
			});
		</script>
		<?php
		$html = ob_get_clean();
		
		return $html;
	}
}
