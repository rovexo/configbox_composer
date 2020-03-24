<?php
defined('CB_VALID_ENTRY') or die();
/** @var ConfigboxViewAdminoption $this */
?>
<div <?php echo $this->getViewAttributes();?>>

        <?php
        foreach($this->properties as $property) {
            $property->setData($this->record);
            if ($property->usesWrapper()) {
                ?>
                <div id="<?php echo $property->getCssId();?>" class="<?php echo $property->renderCssClasses();?>" data-property-definition="<?php echo hsc(json_encode($property->getPropertyDefinition()));?>">
                    <?php if ($property->doesShowAdminLabel()) { ?>
                        <div class="property-label"><?php echo $property->getLabelAdmin();?></div>
                    <?php } ?>
                    <div class="property-body"><?php echo $property->getBodyAdmin();?></div>
                </div>
                <?php
            }
            else {
                echo $property->getBodyAdmin();
            }
        }
        ?>

</div>