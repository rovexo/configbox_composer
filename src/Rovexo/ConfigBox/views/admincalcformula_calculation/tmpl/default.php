<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this ConfigboxViewAdmincalcformula_calculation
 */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">

	<ul class="conditions-list">
		<?php foreach ($this->calculations as $calculation) {
			// Avoid showing itself as calculation
			if ($calculation->id == $this->calculationId) {
				continue;
			}
			$termData = array(
				'type'=>'Calculation',
				'value' => $calculation->id,
			);

			echo '<li>';
			echo ConfigboxCalcTerm::getTerm('Calculation')->getTermHtml($termData);
			echo '</li>';
		}
		?>
	</ul>

</div>

