<?php
/**
 * Category class.
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

PHPWS_Core::configRequireOnce('categories', 'config.php');

class Category {
    public $id          = NULL;
    public $title       = NULL;
    public $description = NULL;
    public $parent      = 0;
    public $icon        = 0;
    public $children    = NULL;


    public function __construct($id=NULL)
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
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('categories');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $this->loadChildren();
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    public function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    public function setParent($parent)
    {
        $this->parent = (int)$parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getParentTitle()
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

    public function setIcon($icon)
    {
        $this->icon = $icon;

        if (is_numeric($icon)) {
            $this->loadIcon();
        }
    }

    /**
     * Returns the icon as an image object
     */

    public function getIcon()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        return Cabinet::getTag($this->icon);
    }

    public function loadChildren()
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

    public function save()
    {
        $db = new PHPWS_DB('categories');
        $result = $db->saveObject($this);
        return $result;
    }

    public function kill()
    {
        if (empty($this->id)) {
            return FALSE;
        }
        $db = new PHPWS_DB('categories');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    public function getViewLink($module=NULL, $label=null)
    {
        if (empty($label)) {
            $label = & $this->title;
        }

        if (isset($module)) {
            $vars['action']  = 'view';
            $vars['id']      = $this->id;
            $vars['ref_mod'] = $module;
            return PHPWS_Text::moduleLink($label, 'categories', $vars);
        } else {
            return PHPWS_Text::rewriteLink($label, 'categories', array('id'=>$this->id));
        }
    }

    public function _addParent(&$list, $parent)
    {
        $cat = new Category($parent);
        $list[$cat->id] = $cat;
        if ($cat->parent > 0) {
            $cat->_addParent($list, $cat->parent);
        }
    }

    public function getFamily()
    {
        $list = array();
        $list[$this->id] = $this;
        if ($this->parent > 0) {
            $this->_addParent($list, $this->parent);
        }
        $list = array_reverse($list, TRUE);
        return $list;
    }

    public function getRowTags()
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