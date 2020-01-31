<?php
if (!defined('CB_VALID_ENTRY')) {
    define('CB_VALID_ENTRY',true);
}

jimport('joomla.form.formfield');

class JFormFieldPage extends JFormField {

    public $_name = 'Page';

    protected function getInput() {

        // Init Kenedo framework
        require_once( dirname(__FILE__).'/../../../init.php');

        $ordering = [ ['propertyName'=>'adminpages.ordering', 'direction'=>'ASC'] ];
        $pages = KenedoModel::getModel('ConfigboxModelAdminpages')->getRecords([], [], $ordering);

        $grouped = [];

        foreach ($pages as $page) {
            $groupTitle = $page->product_id_display_value.' ('.$page->product_id.')';
            $grouped[$groupTitle][$page->id] = $page->title;
        }

        ob_start();
        ?>
		<select class="cb-page-dropdown" name="<?php echo hsc($this->name);?>" id="<?php echo hsc($this->id);?>">
            <?php foreach ($grouped as $groupTitle=>$pages) { ?>
                <optgroup label="<?php echo hsc($groupTitle);?>">
                    <?php foreach ($pages as $pageId=>$title) { ?>
                        <option value="<?php echo intval($pageId);?>"<?php echo ($pageId == $this->value) ? ' selected':'';?>><?php echo hsc($title);?></option>
                     <?php } ?>
                </optgroup>
            <?php } ?>
		</select>
        <?php
        return ob_get_clean();

    }
}
