<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdminRuleeditor_customergroup
 */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<ul class="conditions-list">
		<?php
		for ($i = 1; $i <= 4; $i++) {
			$conditionData = array(
				'type'=>'CustomerGroup',
				'fieldName' => 'custom_'.$i,
				'operator' => '==',
				'value' => '',
			);

			echo '<li>';
			echo ConfigboxCondition::getCondition('CustomerGroup')->getConditionHtml($conditionData);
			echo '</li>';
		}
		?>
	</ul>

</div>

