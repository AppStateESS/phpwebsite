<?php

/**
 * Controls the content "boxes" for sections of content
 * in Layout.
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */


class Layout_Box {
    var $id          = NULL;
    var $theme       = NULL; 
    var $content_var = NULL;
    var $module      = NULL;
    var $theme_var   = NULL;
    var $box_order   = NULL;
    var $active      = NULL;

    function Layout_Box($id=NULL)
    {
        if (!isset($id))
            return;

        $this->setID($id);
        $result = $this->init();
        if (PEAR::isError($result))
            PHPWS_Error::log($result);
    }

    function init()
    {
        $DB = new PHPWS_DB('layout_box');
        return $DB->loadObject($this);
    }

    function setID($id)
    {
        $this->id = (int)$id;
    }

    function getID()
    {
        return $this->id;
    }

    function setTheme($theme)
    {
        $this->theme = $theme;
    }

    function setContentVar($content_var)
    {
        $this->content_var = $content_var;
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function setThemeVar($theme_var)
    {
        $this->theme_var = $theme_var;
    }

    function getTheme()
    {
        return $this->theme;
    }

    function getContentVar()
    {
        return $this->content_var;
    }

    function getModule()
    {
        return $this->module;
    }

    function getThemeVar()
    {
        return $this->theme_var;
    }

    function getBoxOrder()
    {
        return $this->box_order;
    }

    function setBoxOrder($order)
    {
        $this->box_order = $order;
    }

    function save()
    {
        $db = new PHPWS_DB('layout_box');
        $db->addWhere('module', $this->module);
        $db->addWhere('content_var', $this->content_var);
        $db->addWhere('theme', $this->theme);
        $result = $db->select('one');

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!empty($result) && $result != $this->id) {
            return FALSE;
        }

        $db->reset();

        if (!isset($this->box_order)) {
            $this->box_order = $this->nextBox();
        }

        if (!isset($this->active)) {
            $this->active = 1;
        }

        return $db->saveObject($this);
    }

    /**
     * Moves a box to a new location
     */
    function move($dest)
    {
        if ($dest != 'move_box_up'     &&
            $dest != 'move_box_down'   &&
            $dest != 'move_box_top'    &&
            $dest != 'move_box_bottom' &&
            $dest != 'restore') {

            $themeVar = $this->theme_var;
            $this->setThemeVar($_POST['box_dest']);
            $this->setBoxOrder(NULL);
            $this->save();
            $this->reorderBoxes($this->theme, $themeVar);
            return;
        }

        if ($dest == 'restore') {
            $this->kill();
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

        if (PEAR::isError($boxes)) {
            PHPWS_Error::log($boxes);
            return NULL;
        }

        switch ($dest) {
        case 'move_box_up':
            if ($this->box_order == 1) {
                $this->move('bottom');
                return;
            } else {
                $this->box_order--;
                $this->save();
                $boxes[$this->box_order]->box_order++;
                $boxes[$this->box_order]->save();
                return;
            }
            break;
            
        case 'move_box_down':
            if ($this->box_order == (count($boxes) + 1)) {
                $this->move('top');
                return;
            } else {
                $this->box_order++;
                $this->save();
                $boxes[$this->box_order]->box_order--;
                $boxes[$this->box_order]->save();
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


    function reorderBoxes($theme, $themeVar)
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

    function nextBox()
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

    function kill()
    {
        $theme_var = $this->getThemeVar();
        $theme = $this->getTheme();

        $db = new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->getId());
        $result = $db->delete();

        if (PEAR::isError($result)) {
            return $result;
        }

        Layout_Box::reorderBoxes($theme, $theme_var);
    }
  
}
?>