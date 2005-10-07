<?php

/**
 * Links categories to specific items
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */


PHPWS_Core::configRequireOnce('categories', 'errorDefines.php');

class Category_Item {
    var $item_id      = 0;
    var $cat_id       = 0;
    var $version_id   = 0;
    var $module       = NULL;
    var $item_name    = NULL;
    var $title        = NULL;
    var $link         = NULL;
    var $_approved    = TRUE;

    function Category_Item($module=NULL, $item_name=NULL)
    {
        if (!isset($module)) {
            return;
        }

        if (empty($item_name)) {
            $item_name = $module;
        }

        $this->module = $module;
        $this->item_name = $item_name;
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function getModule()
    {
        return $this->module;
    }

    function getProperName(){
        PHPWS_Core::initCoreClass('Module.php');
        $mod = & new PHPWS_Module($this->module);
        return $mod->getProperName();
    }

    function setItemName($item_name)
    {
        $this->item_name = $item_name;
    }

    function getItemName()
    {
        if (empty($this->item_name)) {
            return $this->module;
        }

        return $this->item_name;
    }

    function setItemId($id)
    {
        $this->item_id = (int)$id;
    }

    function getItemId()
    {
        return $this->item_id;
    }

    function setCatId($id)
    {
        $this->cat_id = (int)$id;
    }

    function getCatId()
    {
        return $this->cat_id;
    }

    function setVersionId($version_id)
    {
        $this->version_id = $version_id;
    }

    function getVersionId()
    {
        return $this->version_id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function getTitle()
    {
        return $this->title;
    }

    function setLink($link)
    {
        PHPWS_Text::makeRelative($link);
        $this->link = $link;
    }

    function getLink($html=FALSE)
    {
        if ($html == TRUE) {
            return sprintf('<a href="%s">%s</a>', $this->link, $this->title);
        } else {
            return $this->link;
        }
    }

    function setApproved($approved){
        $this->_approved = (bool)$approved;
    }

    function savePost($save_uncategorized=FALSE)
    {
        if (!isset($_POST) || !$this->_testVars()) {
            return FALSE;
        }
    
        if (isset($_POST['categories'][$this->module][$this->item_name])) {
            $categories = $_POST['categories'][$this->module][$this->item_name];      
        } elseif ($save_uncategorized) {
            $categories = array(0);
        }

        if (!empty($this->version_id)) {
            $this->clearVersion();
        }

        if (empty($categories)) {
            return FALSE;
        }

        foreach ($categories as $cat_id){
            $this->cat_id = $cat_id;
            $result = $this->_save($save_uncategorized);

            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return TRUE;
    }

    function clearVersion()
    {
        $db = & new PHPWS_DB('category_items');
        $db->addWhere('version_id', $this->version_id);
        $db->addWhere('module',     $this->module);
        $db->addWhere('item_name',  $this->item_name);
        return $db->delete();
    }

    function clearItem()
    {
        $db = & new PHPWS_DB('category_items');
        $db->addWhere('item_id', $this->item_id);
        $db->addWhere('module',     $this->module);
        $db->addWhere('item_name',  $this->item_name);
        return $db->delete();
    }


    function saveVersion(){
        if (empty($this->item_id) && empty($this->version_id)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('category_items');
        $db->addWhere('version_id', (int)$this->version_id);
        $db->addWhere('item_id',    $this->item_id);
        $db->addWhere('module',     $this->module);
        $db->addWhere('item_name',  $this->item_name);
        $result = $db->select();
        $this->clearItem();

        foreach ($result as $row) {
            $db->reset();
            $row['version_id'] = 0;
            $db->addValue($row);
            $result = $db->insert();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return FALSE;
            }
        }

    }

    function _testVars(){
        if ( empty($this->module) || empty($this->item_name) ||
             ( !isset($this->item_id) && !isset($this->version_id) ) ||
             empty($this->title)     || empty($this->link) )
            {
                return FALSE;
            } else {
                return TRUE;
            }
    }

    /**
     * Removes a category item
     */
    function clear()
    {
        $db = & new PHPWS_DB('category_items');
        if (!empty($this->version_id)) {
            $db->addWhere('version_id', $this->version_id);
        }

        if (!empty($this->item_id)) {
            $db->addWhere('item_id',    $this->item_id);
        }

        $db->addWhere('module',     $this->module);
        $db->addWhere('item_name',  $this->item_name);
        return $db->delete();
    }

    function _save($save_uncategorized=FALSE){
        if (!$this->_testVars() || (empty($this->cat_id) && $save_uncategorized == FALSE)) {
            return PHPWS_Error::get(CAT_ITEM_MISSING_VAL, 'categories', 'Category_Item::save');
        }

        if ($this->version_id > 0 && $this->_approved && !empty($this->item_id)) {
            $this->version_id = 0;
            $this->clearItem();
        }

        $db = & new PHPWS_DB('category_items');

        return $db->saveObject($this);
    }

    function getForm(){
        PHPWS_Core::initModClass('categories', 'Categories.php');
        $categories = Categories::getCategories('list');

        if (PEAR::isError($categories)) {
            PHPWS_Error::log($categories);
            return PHPWS_Error::get(CAT_DB_PROBLEM, 'categories', 'Categories::getForm');
        }
      
        if (empty($categories)) {
            return _('No categories exist.');
        }

        $multiple = & new Form_Multiple('categories[' . $this->getModule() . '][' . $this->getItemName() . ']', $categories);
        $multiple->setSize(5);
        if ($this->item_id || $this->version_id) {
            $cat_items  = $this->getCategoryItems();
            if (!empty($cat_items) && is_array($cat_items)) {
                $multiple->setMatch(array_keys($cat_items));
            }
        }

        return $multiple->get();
    }

    function getCategoryItemIds(){
        $db = & new PHPWS_DB('category_items');
        $db->addWhere('version_id', $this->getVersionId());
        $db->addWhere('item_id',    $this->getItemId());
        $db->addWhere('module',     $this->getModule());
        $db->addWhere('item_name',  $this->getItemName());
        $db->addColumn('cat_id');

        return $db->select('col');
    }

    function getCategoryItems(){
        PHPWS_Core::initModClass('categories', 'Category_Item.php');

        $db = & new PHPWS_DB('category_items');
        $db->addWhere('version_id', $this->getVersionId());
        $db->addWhere('item_id', $this->getItemId());
        $db->addWhere('module', $this->getModule());
        $db->addWhere('item_name', $this->getItemName());
        $db->setIndexBy('cat_id');
        return $db->getObjects('category_item');
    }

    function _updateVersion()
    {
        $db = & new PHPWS_DB('category_items');

        if ($this->_approved && empty($this->item_id)) {
            return FALSE;
        }

        $db->addWhere('module',     $this->getModule());
        $db->addWhere('item_name',  $this->getItemName());
        $db->delete();

        if ($this->_approved) {
            $db->addValue('version_id', 0);
        }
    }

    function getTplTags()
    {
        $tpl['TITLE'] = $this->getLink(TRUE);
        return $tpl;
    }
}

?>