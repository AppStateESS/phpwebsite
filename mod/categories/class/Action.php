<?php

/**
 * Administrative action class for categories
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::configRequireOnce('categories', 'config.php');
core\Core::initModClass('categories', 'Category.php');

class Categories_Action {

    public static function admin()
    {
        if (!Current_User::authorized('categories')) {
            Current_User::disallow(dgettext('categories', 'You are not authorized to administrate categories.'));
            return;
        }

        $message = Categories_Action::getMessage();

        $content = array();
        $panel = Categories_Action::cpanel();

        if (isset($_REQUEST['subaction'])) {
            $subaction = $_REQUEST['subaction'];
        } else {
            $subaction = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['category_id'])) {
            $category = new Category($_REQUEST['category_id']);
        } else {
            $category = new Category;
        }

        switch ($subaction) {
            case 'post_item':
                if (isset($_POST['quick_add'])) {
                    Categories_Action::quickAdd($_POST['category_name'], $_POST['key_id']);
                } else {
                    Categories_Action::postItem();
                }

                \core\Core::goBack();
                break;

            case 'deleteCategory':
                if (Current_User::authorized('categories', 'delete_categories')) {
                    Categories::delete($category);
                } else {
                    Current_User::disallow();
                }
                \core\Core::goBack();
                break;

            case 'edit':
                if ($category->id) {
                    $title = dgettext('categories', 'Update Category');
                } else {
                    $title = dgettext('categories', 'Add Category');
                }

                $content[] = Categories_Action::edit($category);
                break;

            case 'list':
                $panel->setCurrentTab('list');
                $title = dgettext('categories', 'Manage Categories');
                $content[] = Categories_Action::category_list();
                break;

            case 'new':
                $title = dgettext('categories', 'Add Category');
                $content[] = Categories_Action::edit($category);
                break;

            case 'set_item_category':
                $popup = Categories_Action::categoryPopup();
                if ($popup) {
                    Layout::nakedDisplay($popup);
                } else {
                    \core\Core::errorPage('404');
                }
                break;

            case 'postCategory':
                $title = dgettext('categories', 'Manage Categories');
                $result = Categories_Action::postCategory($category);
                if (is_array($result)) {
                    $content[] = Categories_Action::edit($category, $result);
                } else {
                    $result = $category->save();
                    if (core\Error::isError($result)) {
                        \core\Error::log($result);
                        $message = dgettext('categories', 'Unable to save category.') . ' ' .  dgettext('categories', 'Please contact your administrator.');
                    }
                    else {
                        $message = dgettext('categories', 'Category saved successfully.');
                    }

                    Categories_Action::sendMessage($message, 'list');
                }

                break;
        }

        $template['TITLE']   = $title;
        $template['CONTENT'] = implode('', $content);
        $template['MESSAGE'] = $message;

        $final = \core\Template::process($template, 'categories', 'menu.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    public function sendMessage($message, $command)
    {
        $_SESSION['Category_message'] = $message;
        \core\Core::reroute(sprintf('index.php?module=categories&action=admin&subaction=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    public static function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Category_message'])) {
            $message = $_SESSION['Category_message'];
        }
        unset($_SESSION['Category_message']);
        return $message;
    }


    public function user()
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
                    $id = & $_REQUEST['id'];
                }

                if (isset($_REQUEST['ref_mod'])) {
                    $mod = & $_REQUEST['ref_mod'];
                }

                $content = Categories_Action::viewCategory($id, $mod);
                break;
        }

        Layout::add($content);
    }

    public function postCategory(Category $category)
    {
        
        if (empty($_POST['title'])) {
            $errors['title'] = dgettext('categories', 'Your category must have a title.');
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


    public static function cpanel()
    {
        Layout::addStyle('categories');

        \core\Core::initModClass('controlpanel', 'Panel.php');
        $newLink = 'index.php?module=categories&amp;action=admin';
        $newCommand = array ('title'=>dgettext('categories', 'New'), 'link'=> $newLink);

        $listLink = 'index.php?module=categories&amp;action=admin';
        $listCommand = array ('title'=>dgettext('categories', 'List'), 'link'=> $listLink);

        $tabs['new'] = $newCommand;
        $tabs['list'] = $listCommand;

        $panel = new PHPWS_Panel('categories');
        $panel->quickSetTabs($tabs);
        $panel->enableSecure();
        $panel->setModule('categories');
        $panel->setPanel('panel.tpl');
        return $panel;
    }


    public static function edit(Category $category, $errors=NULL)
    {
        $template = NULL;
        
        $form = new \core\Form('edit_form');
        $form->add('module', 'hidden', 'categories');
        $form->add('action', 'hidden', 'admin');
        $form->add('subaction', 'hidden', 'postCategory');

        $cat_id = $category->getId();

        if (isset($cat_id)) {
            $form->add('category_id', 'hidden', $cat_id);
            $form->add('submit', 'submit', dgettext('categories', 'Update Category'));
        } else {
            $form->add('submit', 'submit', dgettext('categories', 'Add Category'));
        }

        $category_list = Categories::getCategories('list', $category->getId());

        if (is_array($category_list)) {
            $reverse = array_reverse($category_list, TRUE);
            $reverse[0] = '-' . dgettext('categories', 'Top Level') . '-';
            $category_list = array_reverse($reverse, TRUE);
        }
        else {
            $category_list = array(0=>'-' . dgettext('categories', 'Top Level') . '-');
        }


        $form->addSelect('parent', $category_list);
        $form->setMatch('parent', $category->getParent());
        $form->setLabel('parent', dgettext('categories', 'Parent'));

        if (isset($errors['title'])) {
            $template['TITLE_ERROR'] = $errors['title'];
        }
        $form->add('title', 'textfield', $category->getTitle());
        $form->setsize('title', 40);
        $form->setLabel('title', dgettext('categories', 'Title'));

        $form->addTextArea('cat_description', $category->getDescription());
        $form->useEditor('cat_description');
        $form->setRows('cat_description', '10');
        $form->setWidth('cat_description', '80%');
        $form->setLabel('cat_description', dgettext('categories', 'Description'));

        $template['IMAGE_LABEL'] = dgettext('categories', 'Icon');

        $template['ICON_LABEL'] = dgettext('categories', 'Current Icon');
        $template['ICON'] = Categories_Action::getManager($category->icon, 'icon');

        $form->mergeTemplate($template);
        $final_template = $form->getTemplate();
        return \core\Template::process($final_template, 'categories', 'forms/edit.tpl');
    }

    public static function getManager($image_id, $image_name)
    {
        \core\Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager($image_name, $image_id);
        $manager->maxImageWidth(CAT_MAX_ICON_WIDTH);
        $manager->maxImageHeight(CAT_MAX_ICON_HEIGHT);
        $manager->imageOnly(false, false);
        $manager->forceResize();
        $manager->moduleLimit(true);
        return $manager->get();
    }


    public static function category_list()
    {
        
        $pageTags['TITLE_LABEL'] = dgettext('categories', 'Title');
        $pageTags['PARENT_LABEL'] = dgettext('categories', 'Parent');
        $pageTags['ACTION_LABEL'] = dgettext('categories', 'Action');

        $pager = new \core\DBPager('categories', 'Category');
        $pager->setModule('categories');
        $pager->setDefaultLimit(10);
        $pager->setTemplate('category_list.tpl');
        $pager->addPageTags($pageTags);
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowTags('getRowTags');
        $content = $pager->get();

        if (empty($content)) {
            $content = dgettext('categories', 'No categories found.');
        }
        return $content;
    }

    /**
     * The main view page for categories
     */
    public function viewCategory($id=NULL, $module=NULL)
    {
        $oMod = $category = NULL;

        if (!empty($module)) {
                        $oMod = new PHPWS_Module($module);
        }

        if (!isset($id)) {
            $content = Categories::getCategoryList($module);
            $template['TITLE'] = dgettext('categories', 'All Categories');
            if ($oMod) {
                $template['TITLE'] .= ' - ' . $oMod->getProperName();
            }
        } else {
            $category = new Category((int)$id);
            $template['CATEGORY_DESCRIPTION'] = $category->getDescription();
            if ($category->icon) {
                $icon = $category->getIcon();
                $template['CATEGORY_ICON'] = $icon;
            }
            if (isset($module) && $module != '0') {
                $template['TITLE'] = sprintf(dgettext('categories', '%s: Module listing for %s'), $category->title, $oMod->getProperName());
                $content = Categories_Action::getAllItems($category, $module);
            } else {
                $template['TITLE'] = sprintf(dgettext('categories', '%s: Module listing'), $category->title);
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

        $template['FAMILY'] = & $family_list;
        $template['CONTENT'] = & $content;

        return \core\Template::process($template, 'categories', 'view_categories.tpl');
    }


    public function moduleSelect($category=NULL)
    {
        $db = new \core\DB('category_items');

        if (isset($category)) {
            $db->addWhere('cat_id', $category);
            $mod_list = Categories::getModuleListing($category);
        } else {
            $mod_list = Categories::getModuleListing();
        }

        \core\Key::restrictView($db);
        $all_no = $db->count();

        if (!empty($mod_list)) {
            array_unshift($mod_list, sprintf(dgettext('categories', 'All - %s items'), $all_no));
        } else {
            $mod_list[0] = sprintf(dgettext('categories', 'All - %s items'), $all_no);
        }

        $form = new \core\Form('module_select');
        $form->noAuthKey();
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

        $form->addSubmit('submit', dgettext('categories', 'View Module'));

        return $form->getTemplate();
    }

    /**
     * Listing of all items within a category
     */
    public function getAllItems(Category $category, $module)
    {
        
        $pageTags['TITLE_LABEL'] = dgettext('categories', 'Item Title');
        $pageTags['CREATE_DATE_LABEL'] = dgettext('categories', 'Creation date');

        $pager = new \core\DBPager('phpws_key', 'Key');
        $pager->addWhere('category_items.cat_id', $category->id);
        $pager->addWhere('category_items.module', $module);

        \core\Key::restrictView($pager->db, $module);
        $pager->setModule('categories');
        $pager->setLimitList(array(10, 25, 50));
        $pager->setDefaultLimit(25);
        $pager->setTemplate('category_item_list.tpl');
        $pager->addPageTags($pageTags);
        $pager->addToggle('class="bgcolor2"');
        $pager->addRowTags('getTplTags');
        $pager->setOrder('create_date', 'desc', true);
        $pager->setSearch('title');
        $content = $pager->get();

        if (empty($content)) {
            $content =  dgettext('categories', 'No items found in this category.');
        }

        return $content;
    }

    public function addCategoryItem($cat_id, $key_id)
    {
        $db = new \core\DB('category_items');
        $db->addValue('cat_id', (int)$cat_id);
        $db->addValue('key_id', (int)$key_id);
        $key = new \core\Key((int)$key_id);
        $db->addValue('module', $key->module);
        return $db->insert();
    }

    public function removeCategoryItem($cat_id, $key_id)
    {
        $db = new \core\DB('category_items');
        $db->addWhere('cat_id', (int)$cat_id);
        $db->addWhere('key_id', (int)$key_id);
        return $db->delete();
    }

    public function quickAdd($title, $key_id)
    {
        $title = strip_tags($title);

        if (empty($title)) {
            return false;
        }

        $db = new \core\DB('categories');
        $db->addWhere('title', $title, 'like');
        $db->addColumn('id');
        $result = $db->select('one');
        if (core\Error::isError($result)) {
            \core\Error::log($result);
            return false;
        } elseif ($result) {
            $result = Categories_Action::addCategoryItem($result, $key_id);
            return true;
        }

        $category = new Category;
        $category->setTitle($title);
        if ($_POST['quick_parent']) {
            $category->setParent($_POST['quick_parent']);
        }

        $result = $category->save();

        if (core\Error::isError($result)) {
            \core\Error::log($result);
            return false;
        }

        Categories_Action::addCategoryItem($category->id, $key_id);
        return true;
    }

    public function postItem()
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
    public function categoryPopup()
    {
        $key = new \core\Key((int)$_REQUEST['key_id']);
        $content = Categories::showForm($key, TRUE);
        return $content;
    }
}

?>