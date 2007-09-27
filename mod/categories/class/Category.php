<?php
/**
 * Category class.
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

PHPWS_Core::configRequireOnce('categories', 'config.php');

class Category{
    var $id          = NULL;
    var $title       = NULL;
    var $description = NULL;
    var $parent      = 0;
    var $icon        = NULL;
    var $children    = NULL;


    function Category($id=NULL)
    {
        if (!isset($id)) {
            return;
        } elseif ($id == 0) {
            $this->id     = 0;
            $this->title  = DEFAULT_UNCATEGORIZED_TITLE;
            $this->icon   = DEFAULT_UNCATEGORIZED_ICON;
            return;
        }

        $this->setId($id);
        $result = $this->init();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }
  
    function init()
    {
        $db = new PHPWS_DB('categories');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->loadChildren();
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    function setParent($parent)
    {
        $this->parent = (int)$parent;
    }

    function getParent()
    {
        return $this->parent;
    }

    function getParentTitle()
    {
        static $parentTitle = array();

        if ($this->parent == 0) {
            return dgettext('categories', 'Top Level');
        }

        if (isset($parentTitle[$this->parent])) {
            return $parentTitle[$this->parent];
        }

        $parent = new Category($this->parent);
        $parentTitle[$parent->id] = $parent->title;

        return $parent->title;
    }

    function setIcon($icon)
    {
        $this->icon = $icon;

        if (is_numeric($icon)) {
            $this->loadIcon();
        }
    }

    /**
     * Returns the icon as an image object
     */

    function &getIcon()
    {
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        return new PHPWS_Image($this->icon);
    }

    function loadChildren()
    {
        if ($this->id == 0) {
            return;
        }

        $db = new PHPWS_DB('categories');
        $db->addWhere('parent', $this->id);
        $db->addOrder('title');
        $result = $db->getObjects('Category');
        if (empty($result)) {
            $this->children = NULL;
            return;
        }

        $this->children = Categories::initList($result);
    }

    function save()
    {
        $db = new PHPWS_DB('categories');
        $result = $db->saveObject($this);
        return $result;
    }

    function kill()
    {
        if (empty($this->id)) {
            return FALSE;
        }
        $db = new PHPWS_DB('categories');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    function getViewLink($module=NULL)
    {
        if (isset($module)) {
            $vars['action']  = 'view';
            $vars['id']      = $this->id;
            $vars['ref_mod'] = $module;
            return PHPWS_Text::moduleLink($this->title, 'categories', $vars);
        } else {
            return PHPWS_Text::rewriteLink($this->title, 'categories', $this->id);
        }
    }

    function _addParent(&$list, $parent)
    {
        $cat = new Category($parent);
        $list[$cat->id] = $cat;
        if ($cat->parent > 0) {
            $cat->_addParent($list, $cat->parent);
        }
    }

    function getFamily()
    {
        $list = array();
        $list[$this->id] = $this;
        if ($this->parent > 0) {
            $this->_addParent($list, $this->parent);
        }
        $list = array_reverse($list, TRUE);
        return $list;
    }

    function getRowTags()
    {
        $vars['module']      = 'categories';
        $vars['action']      = 'admin';
        $vars['category_id'] = $this->getId();

        $vars['subaction'] = 'edit';
        $links[] = PHPWS_Text::secureLink(dgettext('categories', 'Edit'), 'categories', $vars);

        if (Current_User::allow('categories', 'delete_categories')) {
            if (javascriptEnabled()) {
                $js_vars['QUESTION'] = dgettext('categories', 'Are you sure you want to delete this category?');
                $js_vars['ADDRESS']  = 'index.php?module=categories&amp;action=admin&amp;subaction=deleteCategory&amp;category_id=' . 
                    $this->getId() . '&amp;authkey=' . Current_User::getAuthKey();
                $js_vars['LINK']     = dgettext('categories', 'Delete');
                $links[] = Layout::getJavascript('confirm', $js_vars);
            } else {
                $vars['subaction'] = 'delete';
                $links[] = PHPWS_Text::moduleLink(dgettext('categories', 'Delete'), 'categories', $vars);
            }
        }

        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['DESCRIPTION'] = $this->getDescription();
        $tpl['PARENT'] = $this->getParentTitle();
        $tpl['TITLE'] = $this->getViewLink();
        return $tpl;
    }
}

?>