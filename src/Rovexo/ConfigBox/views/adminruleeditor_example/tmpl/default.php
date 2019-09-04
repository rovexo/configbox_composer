<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdminRuleeditor_example
 */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<ul class="conditions-list">
		<?php
			$condition = array(
				'type'=>'Example',
				'field' => 'Anything',
				'operator' => '==',
				'value' => '',
			);

			echo '<li>';
			echo ConfigboxRulesHelper::getConditionsHtml(array($condition));
			echo '</li>';
		?>
	</ul>

</div>

