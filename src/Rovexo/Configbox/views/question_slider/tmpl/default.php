<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion_Slider */
$min = (empty($this->question->set_min_value) || $this->question->set_min_value == 'none') ? '0' : floatval($this->question->minval);
$max = (empty($this->question->set_max_value) || $this->question->set_max_value == 'none') ? '100' : floatval($this->question->maxval);
$step = (empty($this->question->slider_steps)) ? '1' : floatval($this->question->slider_steps);
?>
<div id="<?php echo hsc($this->questionCssId); ?>"
     class="<?php echo hsc($this->questionCssClasses); ?>" <?php echo $this->questionDataAttributes; ?>>

    <?php echo $this->getViewOutput('question_edit_buttons'); ?>
    <?php echo $this->getViewOutput('question_heading'); ?>
    <?php echo $this->getViewOutput('question_decoration'); ?>
    <?php echo $this->getViewOutput('descriptions'); ?>

    <div class="form-group">

        <div class="wrapper-slider">
            <label class="sr-only" for="cb-slider-<?php echo intval($this->question->id); ?>"></label>
            <input type="range"
                   <?php echo (!$this->question->applies()) ? 'disabled': '';?>
                   class="form-range"
                   value="<?php echo floatval($this->selection);?>"
                   min="<?php echo floatval($min); ?>"
                   max="<?php echo floatval($max); ?>"
                   step="<?php echo floatval($step); ?>"
                   id="cb-slider-<?php echo intval($this->question->id); ?>"/>
        </div>


        <div class="wrapper-input">
            <?php if ($this->question->unit) { ?>
                <div class="input-group">
                    <input value="<?php echo hsc($this->selection); ?>"
                           type="text"
                           id="input-<?php echo hsc($this->questionCssId); ?>"
                           class="form-control"
                           aria-label="<?php echo hsc($this->question->title); ?>"
                            <?php echo ($this->question->disableControl) ? 'disabled="disabled"' : ''; ?>>
                    <span class="input-group-text"><?php echo hsc($this->question->unit); ?></span>

                </div>
            <?php } else { ?>
                <input value="<?php echo hsc($this->selection); ?>"
                       type="text"
                       id="input-<?php echo hsc($this->questionCssId); ?>"
                       class="form-control"
                       aria-label="<?php echo hsc($this->question->title); ?>"
                        <?php echo ($this->question->disableControl) ? 'disabled="disabled"' : ''; ?>>
            <?php } ?>
        </div>

    </div>

    <div class="invalid-feedback validation-message-target">
        <?php echo ($this->hasValidationMessage) ? hsc($this->validationMessage) : ''; ?>
    </div>

</div>