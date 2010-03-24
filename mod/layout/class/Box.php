<?php

/**
 * Controls the content "boxes" for sections of content
 * in Layout.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */


class Layout_Box {
    public $id          = NULL;
    public $theme       = NULL;
    public $content_var = NULL;
    public $module      = NULL;
    public $theme_var   = NULL;
    public $box_order   = NULL;
    public $active      = 1;

    public function __construct($id=NULL)
    {
        if (!isset($id))
        return;

        $this->setID($id);
        $result = $this->init();
        if (PHPWS_Error::isError($result))
        PHPWS_Error::log($result);
    }

    public function init()
    {
        $DB = new PHPWS_DB('layout_box');
        return $DB->loadObject($this);
    }

    public function setID($id)
    {
        $this->id = (int)$id;
    }

    public function getID()
    {
        return $this->id;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function setContentVar($content_var)
    {
        $this->content_var = $content_var;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setThemeVar($theme_var)
    {
        $this->theme_var = $theme_var;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getContentVar()
    {
        return $this->content_var;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getThemeVar()
    {
        return $this->theme_var;
    }

    public function getBoxOrder()
    {
        return $this->box_order;
    }

    public function setBoxOrder($order)
    {
        $this->box_order = $order;
    }

    public function save()
    {
        $db = new PHPWS_DB('layout_box');
        $db->addWhere('module', $this->module);
        $db->addWhere('content_var', $this->content_var);
        $db->addWhere('theme', $this->theme);
        $result = $db->select('one');

        if (PHPWS_Error::isError($result)) {
            return $result;
        } elseif ($result && $result != $this->id) {
            return FALSE;
        }

        $db->reset();

        if (empty($this->box_order)) {
            $this->box_order = $this->nextBox();
        }

        return $db->saveObject($this);
    }

    /**
     * Moves a box to a new location
     */
    public function move($dest)
    {

        if ($dest != 'move_box_up'     &&
        $dest != 'move_box_down'   &&
        $dest != 'move_box_top'    &&
        $dest != 'move_box_bottom' &&
        $dest != 'restore') {

            $themeVars = $_SESSION['Layout_Settings']->getAllowedVariables();

            if (!in_array($dest, $themeVars)) {
                return PHPWS_Error::get(LAYOUT_BAD_THEME_VAR, 'layout', 'Layout_Box::move', $dest);
            }
            $themeVar = $this->theme_var;
            $this->setThemeVar($dest);
            $this->setBoxOrder(NULL);

            $this->save();
            $this->reorderBoxes($this->theme, $themeVar);
            return;
        }

        $db = new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->id, '!=');
        $db->addWhere('theme', $this->theme);
        $db->addWhere('theme_var', $this->theme_var);
        $db->addOrder('box_order');
        $db->setIndexBy('box_order');
        $boxes = $db->getObjects('Layout_Box');

        if (empty($boxes)) {
            return NULL;
        }

        if (PHPWS_Error::isError($boxes)) {
            PHPWS_Error::log($boxes);
            return NULL;
        }

        switch ($dest) {
            case 'restore':
                $this->kill();
                $this->reorderBoxes($this->theme, $this->theme_var);
                Layout::resetBoxes();
                return;
                break;

            case 'move_box_up':
                if ($this->box_order == 1) {
                    $this->move('move_box_bottom');
                    return;
                } else {
                    $old_box = & $boxes[$this->box_order - 1];
                    $old_box->box_order++;
                    $this->box_order--;
                    if (!PHPWS_Error::logIfError($old_box->save())) {
                        PHPWS_Error::logIfError($this->save());
                    }
                    return;
                }
                break;

            case 'move_box_down':
                if ($this->box_order == (count($boxes) + 1)) {
                    $this->move('move_box_top');
                    return;
                } else {
                    $old_box = & $boxes[$this->box_order + 1];
                    $old_box->box_order--;
                    $this->box_order++;
                    if (!PHPWS_Error::logIfError($old_box->save())) {
                        PHPWS_Error::logIfError($this->save());
                    }
                    return;
                }
                break;

            case 'move_box_top':
                $this->box_order = 1;
                $this->save();
                $count = 2;
                break;

            case 'move_box_bottom':
                $this->box_order = count($boxes) + 1;
                $this->save();
                $count = 1;
                break;
        }

        foreach ($boxes as $box) {
            $box->box_order = $count;
            $box->save();
            $count++;
        }

    }


    public function reorderBoxes($theme, $themeVar)
    {
        $db = new PHPWS_DB('layout_box');
        $db->addWhere('theme', $theme);
        $db->addWhere('theme_var', $themeVar);
        $db->addOrder('box_order');
        $boxes = $db->getObjects('Layout_Box');

        if (!isset($boxes)) {
            return;
        }

        $count = 1;
        foreach ($boxes as $box){
            $box->setBoxOrder($count);
            $box->save();
            $count++;
        }
    }

    public function nextBox()
    {
        $DB = new PHPWS_DB('layout_box');
        $DB->addWhere('theme', $this->theme);
        $DB->addWhere('theme_var', $this->theme_var);
        $DB->addColumn('box_order', 'max');
        $max = $DB->select('one');
        if (isset($max)) {
            return $max + 1;
        } else {
            return 1;
        }
    }

    public function kill()
    {
        $theme_var = $this->getThemeVar();
        $theme = $this->getTheme();

        $db = new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->getId());
        $result = $db->delete();

        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        Layout_Box::reorderBoxes($theme, $theme_var);
    }

}
?>