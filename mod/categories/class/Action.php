<?php

/**
 * Administrative action class for categories
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_CORE::configRequireOnce('categories', 'config.php');
PHPWS_Core::initModClass('categories', 'Category.php');

class Categories_Action {

    function admin()
    {
        if (!Current_User::authorized('categories')) {
            Current_User::disallow(_('You are not authorized to administrate categories.'));
            return;
        }

        $message = Categories_Action::getMessage();

        $content = array();
        $panel = & Categories_Action::cpanel();

        if (isset($_REQUEST['subaction'])) {
            $subaction = $_REQUEST['subaction'];
        } else {
            $subaction = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['category_id'])) {
            $category = & new Category($_REQUEST['category_id']);
        } else {
            $category = & new Category;
        }

        switch ($subaction) {
        case 'post_item':
            Categories_Action::postItem();
            PHPWS_Core::goBack();
            break;

        case 'deleteCategory':
            Categories::delete($category);
            $title = _('Manage Categories');
            $content[] = Categories_Action::category_list();
            break;

        case 'edit':
            if ($category->id) {
                $title = _('Update Category');
            } else {
                $title = _('Add Category');
            }

            $content[] = Categories_Action::edit($category);
            break;

        case 'list':
            $panel->setCurrentTab('list');
            $title = _('Manage Categories');
            $content[] = Categories_Action::category_list();
            break;

        case 'new':
            $title = _('Add Category');
            $content[] = Categories_Action::edit($category);
            break;

        case 'set_item_category':
            $popup = Categories_Action::categoryPopup();
            if ($popup) {
                Layout::nakedDisplay($popup);
            } else {
                PHPWS_Core::errorPage('404');
            }
            break;

        case 'postCategory':
            $title = _('Manage Categories');
            $result = Categories_Action::postCategory($category);
            if (is_array($result)) {
                $content[] = Categories_Action::edit($category, $result);
            } else {
                $result = $category->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $message = _('Unable to save category.') . ' ' .  _('Please contact your administrator.');
                }
                else {
                    $message = _('Category saved successfully.');
                }

                Categories_Action::sendMessage($message, 'list');
            }

            break;
        }

        $template['TITLE']   = $title;
        $template['CONTENT'] = implode('', $content);
        $template['MESSAGE'] = $message;

        $final = PHPWS_Template::process($template, 'categories', 'menu.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function sendMessage($message, $command)
    {
        $_SESSION['Category_message'] = $message;
        PHPWS_Core::reroute(sprintf('index.php?module=categories&action=admin&subaction=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Category_message'])) {
            $message = $_SESSION['Category_message'];
        }
        unset($_SESSION['Category_message']);
        return $message;
    }


    function user()
    {
        $mod = $id = NULL;
        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
        } else {
            $action = 'view';
        }

        switch ($action) {
        case 'view':
            if (isset($_REQUEST['id'])) {
                $id = &$_REQUEST['id'];
            }

            if (isset($_REQUEST['ref_mod'])) {
                $mod = $_REQUEST['ref_mod'];
            }

            $content = Categories_Action::viewCategory($id, $mod);
            break;
        }

        Layout::add($content);
    }

    function postCategory(&$category)
    {
        PHPWS_Core::initCoreClass('File.php');

        if (empty($_POST['title'])) {
            $errors['title'] = _('Your category must have a title.');
        }

        $category->setTitle($_POST['title']);

        if (!empty($_POST['cat_description'])) {
            $description = $_POST['cat_description'];
            $category->setDescription($description);
        }

        $category->setParent((int)$_POST['parent']);

        if ($_POST['icon']) {
            $category->icon = (int)$_POST['icon'];
        } else {
            $category->icon = 0;
        }


        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }


    function &cpanel()
    {
        Layout::addStyle('categories');

        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=categories&amp;action=admin';
        $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
        
        $listLink = 'index.php?module=categories&amp;action=admin';
        $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

        $tabs['new'] = $newCommand;
        $tabs['list'] = $listCommand;

        $panel = & new PHPWS_Panel('categories');
        $panel->quickSetTabs($tabs);
        $panel->enableSecure();
        $panel->setModule('categories');
        $panel->setPanel('panel.tpl');
        return $panel;
    }
  

    function edit(&$category, $errors=NULL)
    {
        $template = NULL;
        PHPWS_Core::initCoreClass('Editor.php');

        $form = & new PHPWS_Form('edit_form');
        $form->add('module', 'hidden', 'categories');
        $form->add('action', 'hidden', 'admin');                     
        $form->add('subaction', 'hidden', 'postCategory');

        $cat_id = $category->getId();

        if (isset($cat_id)) {
            $form->add('category_id', 'hidden', $cat_id);
            $form->add('submit', 'submit', _('Update Category'));
        } else {
            $form->add('submit', 'submit', _('Add Category'));
        }

        $category_list = Categories::getCategories('list', $category->getId());

        if (is_array($category_list)) {
            $reverse = array_reverse($category_list, TRUE);
            $reverse[0] = '-' . _('Top Level') . '-';
            $category_list = array_reverse($reverse, TRUE);
        }
        else {
            $category_list = array(0=>'-' . _('Top Level') . '-');
        }


        $form->addSelect('parent', $category_list);
        $form->setMatch('parent', $category->getParent());
        $form->setLabel('parent', _('Parent'));

        if (isset($errors['title'])) {
            $template['TITLE_ERROR'] = $errors['title'];
        }
        $form->add('title', 'textfield', $category->getTitle());
        $form->setsize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('cat_description', $category->getDescription());
        $form->useEditor('cat_description');
        $form->setRows('cat_description', '10');
        $form->setWidth('cat_description', '80%');
        $form->setLabel('cat_description', _('Description'));

        $template['IMAGE_LABEL'] = _('Icon');

        $template['ICON_LABEL'] = _('Current Icon');
        $template['ICON'] = Categories_Action::getManager($category->icon, 'icon');

        $form->mergeTemplate($template);
        $final_template = $form->getTemplate();

        return PHPWS_Template::process($final_template, 'categories', 'forms/edit.tpl');
    }

    function getManager($image_id, $image_name)
    {
        PHPWS_Core::initModClass('filecabinet', 'Image_Manager.php');
        $manager = & new FC_Image_Manager($image_id);
        $manager->setMaxWidth(CAT_MAX_ICON_WIDTH);
        $manager->setMaxHeight(CAT_MAX_ICON_HEIGHT);
        $manager->setMaxSize(CAT_MAX_ICON_SIZE);
        $manager->setModule('categories');
        $manager->setItemname($image_name);

        return $manager->get();
    }


    function category_list()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['TITLE_LABEL'] = _('Title');
        $pageTags['PARENT_LABEL'] = _('Parent');
        $pageTags['ACTION_LABEL'] = _('Action');

        $pager = & new DBPager('categories', 'Category');
        $pager->setModule('categories');
        $pager->setDefaultLimit(10);
        $pager->setTemplate('category_list.tpl');
        $pager->addPageTags($pageTags);
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowTags('getRowTags');
        $content = $pager->get();

        if (empty($content)) {
            return _('No categories found.');
        }
        else {
            return $content;
        }
    }

    /**
     * The main view page for categories
     */
    function viewCategory($id=NULL, $module=NULL) 
    {
        $oMod = $category = NULL;

        if (!empty($module)) {
            PHPWS_Core::initCoreClass('Module.php');
            $oMod = & new PHPWS_Module($module);
        } 

        if (!isset($id)) {
            $content = Categories::getCategoryList($module);
            $template['TITLE'] = _('All Categories');
            if ($oMod) {
                $template['TITLE'] .= ' - ' . $oMod->getProperName();
            }
        } else {
            $category = & new Category((int)$id);
            if (isset($module) && $module != '0') {
                $template['TITLE'] = sprintf(_('Module listing for %s'), $oMod->getProperName());
                $content = Categories_Action::getAllItems($category, $module);
            } else {
                $template['TITLE'] = _('Module listing');
                $content = Categories::listModuleItems($category);
            }
        }
        if (isset($category)) {
            $subtpl = Categories_Action::moduleSelect($category->getId());
        } else {
            $subtpl = Categories_Action::moduleSelect();
        }

        $template = array_merge($subtpl, $template);

        $family_list = Categories::cookieCrumb($category, $module);

        $template['FAMILY'] = $family_list;
        $template['CONTENT'] = &$content;

        return  PHPWS_Template::process($template, 'categories', 'view_categories.tpl');
    }


    function moduleSelect($category=NULL)
    {
        $db = & new PHPWS_DB('category_items');

        if (isset($category)) {
            $db->addWhere('cat_id', $category);
            $mod_list = Categories::getModuleListing($category);
        } else {
            $mod_list = Categories::getModuleListing();
        }

        $db->addWhere('key_id', 'phpws_key.id');
        $db->addWhere('phpws_key.active', 1);
        if (!Current_User::isLogged()) {
            $db->addWhere('phpws_key.restricted', 0);
        }

        $all_no = $db->count();

        if (!empty($mod_list)) {
            array_unshift($mod_list, sprintf(_('All - %s items'), $all_no));
        } else {
            $mod_list[0] = sprintf(_('All - %s items'), $all_no);
        }

        $form = & new PHPWS_Form;
        $form->setMethod('get');
        $form->addHidden('module', 'categories');
        $form->addHidden('action', 'view');

        if ($category) {
            $form->addHidden('id', $category);
        }

        $form->addSelect('ref_mod', $mod_list);

        if (isset($_REQUEST['ref_mod'])) {
            $form->setMatch('ref_mod', $_REQUEST['ref_mod']);
        }

        $form->addSubmit('submit', _('View Module'));

        return $form->getTemplate();
    }

    /**
     * Listing of all items within a category
     */
    function getAllItems(&$category, $module) 
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['TITLE_LABEL'] = _('Item Title');
        $pageTags['CREATE_DATE_LABEL'] = _('Creation date');

        $pager = & new DBPager('phpws_key', 'Key');
        $pager->addWhere('id', 'category_items.key_id');
        $pager->addWhere('active', 1);
        $pager->addWhere('category_items.cat_id', $category->id);

        if (!Current_User::isLogged()) {
            $pager->addWhere('restricted', 0);
        }

        if (isset($module)) {
            $pager->addWhere('category_items.module', $module);
        }

        $pager->setModule('categories');
        $pager->setDefaultLimit(10);
        $pager->setTemplate('category_item_list.tpl');
        $pager->addPageTags($pageTags);
        $pager->addToggle('class="bgcolor2"');
        $pager->addRowTags('getTplTags');
        $pager->setSearch('title');
        $content = $pager->get();

        if (empty($content)) {
            return _('No items found in this category.');
        }
        else {
            return $content;
        }
    }

    function addCategoryItem($cat_id, $key_id)
    {
        $db = & new PHPWS_DB('category_items');
        $db->addValue('cat_id', (int)$cat_id);
        $db->addValue('key_id', (int)$key_id);
        $key = & new Key((int)$key_id);
        $db->addValue('module', $key->module);
        return $db->insert();
    }

    function removeCategoryItem($cat_id, $key_id)
    {
        $db = & new PHPWS_DB('category_items');
        $db->addWhere('cat_id', (int)$cat_id);
        $db->addWhere('key_id', (int)$key_id);
        return $db->delete();
    }

    function postItem()
    {
        if (isset($_POST['add']) && isset($_POST['add_category'])) {
            Categories_Action::addCategoryItem($_POST['add_category'], $_POST['key_id']);
        } elseif (isset($_POST['remove']) && isset($_POST['remove_category'])) {
            Categories_Action::removeCategoryItem($_POST['remove_category'], $_POST['key_id']);
        }
    }

    /**
     * Returns the category popup form for assigning items to 
     * categories
     */
    function categoryPopup()
    {
        $key = & new Key((int)$_REQUEST['key_id']);
        $content = Categories::showForm($key, TRUE);
        return $content;
    }
}

?>