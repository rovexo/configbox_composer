<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdminRuleeditor_calculation
 */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<ul class="conditions-list">
		<?php foreach ($this->calculations as $calculation) {
			$conditionData = array(
				'type'=>'Calculation',
				'calcId' => $calculation->id,
				'operator' => '==',
				'value' => '',
			);

			echo '<li>';
			echo ConfigboxCondition::getCondition('Calculation')->getConditionHtml($conditionData);
			echo '</li>';
		}
		?>
	</ul>

</div>

