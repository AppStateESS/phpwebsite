<?php
/**
 * Object class for a menu
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('menu', 'Menu_Link.php');

define('MENU_MISSING_TPL', -2);

class Menu_Item {
    public $id         = 0;
    public $key_id     = 0;
    public $title      = NULL;
    public $template   = NULL;
    public $pin_all    = 0;
    public $_db        = NULL;
    public $_show_all  = false;
    public $_style     = null;
    public $_error     = NULL;

    public function __construct($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        $this->resetdb();
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
            PHPWS_Error::log($result);
        }
    }

    public function resetdb()
    {
        if (isset($this->_db)) {
            $this->_db->reset();
        } else {
            $this->_db = new PHPWS_DB('menus');
        }
    }

    public function init()
    {
        if (!isset($this->id)) {
            return FALSE;
        }

        $this->resetdb();
        $result = $this->_db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
    }

    public function getTitle()
    {
        $vars['site_map'] = $this->id;

        return PHPWS_Text::moduleLink($this->title, 'menu', $vars);
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setPinAll($pin)
    {
        $this->pin_all = (bool)$pin;
    }


    public function getTemplateList()
    {
        $result = PHPWS_File::listDirectories(PHPWS_Template::getTemplateDirectory('menu') . 'menu_layout/');
        if (PHPWS_Error::logIfError($result) || empty($result)) {
            return null;
        }

        foreach  ($result as $dir) {
            $directories[$dir] = $dir;
        }

        return $directories;
    }

    public function post()
    {
        if (empty($_POST['title'])) {
            $errors[] = dgettext('menu', 'Missing menu title.');
        } else {
            $this->setTitle($_POST['title']);
        }

        $this->setTemplate($_POST['template']);

        if (isset($_POST['pin_all'])) {
            $this->setPinAll(1);
        } else {
            $this->setPinAll(0);
        }

        if (isset($errors)) {
            return $errors;
        } else {
            $result = $this->save();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                return array(dgettext('menu', 'Unable to save menu. Please check error logs.'));
            }
            return TRUE;
        }
    }

    public function save($save_key=true)
    {
        if (empty($this->title)) {
            return FALSE;
        }

        $new_menu = !(bool)$this->id;

        $this->resetdb();
        $result = $this->_db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($save_key) {
            $this->saveKey();
        }

        if ($new_menu && PHPWS_Settings::get('menu', 'home_link')) {
            $link = new Menu_Link;
            $link->menu_id = $this->id;
            $link->title   = dgettext('menu', 'Home');
            $link->url     = 'index.php';
            $link->key_id  = 0;
            PHPWS_Error::logIfError($link->save());
        }

        return true;
    }

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
            $key->module = $key->item_name = 'menu';
            $key->item_id = $this->id;
        } else {
            $key = new Key($this->key_id);
        }

        $key->title = $this->title;
        $result = $key->save();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if (empty($this->key_id)) {
            $this->key_id = $key->id;
            $this->save(false);
        }
    }

    /**
     * Returns all the links in a menu for display
     */
    public function displayLinks($edit=FALSE)
    {
        if (Menu::isAdminMode()) {
            $this->loadJS();
        }

        $all_links = $this->getLinks();
        if (empty($all_links)) {
            return NULL;
        }

        foreach ($all_links as $link) {
            if($i = $link->view()) {
                $link_list[] = $i;
            }
        }

        return implode("\n", $link_list);
    }

    public function loadJS()
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }
        javascript('jquery');
        $vars['authkey'] = Current_User::getAuthKey();
        $vars['drag_sort'] = PHPWS_Settings::get('menu', 'drag_sort');

        javascriptMod('menu', 'admin_link', $vars);
        $loaded = true;
    }

    /**
     * Returns the menu link objects associated to a menu
     */
    public function getLinks($parent=0, $active_only=TRUE)
    {
        $final = NULL;

        // If we have been here already, return the data
        if (isset($GLOBALS['MENU_LINKS'][$this->id])) {
            return $GLOBALS['MENU_LINKS'][$this->id];
        }

        if (!$this->id) {
            return NULL;
        }
        // Get all records for this menu
        $db = new PHPWS_DB('menu_links');
        $db->setDistinct();
        $db->addWhere('menu_id', $this->id, NULL, NULL, 1);
        Key::restrictView($db);
        $db->addOrder('link_order');
        $db->setIndexBy('id');
        $data = $db->select();
        if (empty($data) || PHPWS_Error::logIfError($data)) {
            return NULL;
        }

        // Create a matrix to index by parent id so we don't have to keep looping through the list
        $hash = array();
        foreach ($data as $key => $row) {
            $hash[$row['parent']][] = $key;
        }

        // Locate the desired record(s)
        if (empty($hash[$parent])) {
            return NULL;
        }

        $final = array();
        foreach ($hash[$parent] as $rowId) {
            $link = new Menu_Link();
            PHPWS_Core::plugObject($link, $data[$rowId]);
            // Get the children for each parent using the $result data array as reference
            $link->loadChildren($data, $hash);
            $link->_menu = & $this;
            $final[$link->id] = $link;
        }

        $GLOBALS['MENU_LINKS'][$this->id] = $final;
        return $final;
    }

    public function getRowTags()
    {
        $vars['menu_id'] = $this->id;
        $vars['command'] = 'edit_menu';
        $links[] = PHPWS_Text::secureLink(dgettext('menu', 'Edit'), 'menu', $vars);

        if (!isset($_SESSION['Menu_Clip']) ||
        !isset($_SESSION['Menu_Clip'][$this->id])) {
            $vars['command'] = 'clip';
            $links[] = PHPWS_Text::secureLink(dgettext('menu', 'Clip'), 'menu', $vars);
        } else {
            $vars['command'] = 'unclip';
            $links[] = PHPWS_Text::secureLink(dgettext('menu', 'Unclip'), 'menu', $vars);
        }

        $vars['command'] = 'pin_all';
        if ($this->pin_all == 0) {
            $link_title = dgettext('menu', 'Pin');
            $vars['hook'] = 1;
        } else {
            $link_title = dgettext('menu', 'Unpin');
            $vars['hook'] = 0;
        }
        $links[] = PHPWS_Text::secureLink($link_title, 'menu', $vars);
        unset($vars['hook']);

        $vars['command'] = 'delete_menu';
        $js['QUESTION'] = dgettext('menu', 'Are you sure you want to delete this menu and all its links.');
        $js['ADDRESS']  = PHPWS_Text::linkAddress('menu', $vars, TRUE);
        $js['LINK'] = dgettext('menu', 'Delete');
        $links[] = javascript('confirm', $js);

        $links[] = PHPWS_Text::secureLink(dgettext('menu', 'Reorder links'), 'menu',
        array('command'=>'reorder_links',
                                                'menu_id'=>$this->id));
        $links[] = Current_User::popupPermission($this->key_id);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function kill()
    {
        $db = new PHPWS_DB('menu_assoc');
        $db->addWhere('menu_id', $this->id);
        $db->delete();

        $db->reset();
        $db->setTable('menu_links');
        $db->addWhere('menu_id', $this->id);
        $db->delete();

        $db->reset();
        $db->setTable('menus');
        $db->addWhere('id', $this->id);
        $db->delete();
        Layout::purgeBox('menu_' . $this->id);
    }

    public function addRawLink($title, $url, $parent=0)
    {
        if (empty($title) || empty($url)) {
            return FALSE;
        }

        $link = new Menu_Link;
        $link->key_id = 0;
        $link->setMenuId($this->id);
        $link->setTitle($title);

        $link->setUrl($url);
        $link->setParent($parent);

        return $link->save();
    }

    public function addLink($key_id, $parent=0)
    {
        $key = new Key($key_id);
        $link = new Menu_Link;

        $link->setMenuId($this->id);
        $link->setKeyId($key->id);
        $link->setTitle($key->title);
        $link->url = & $key->url;
        $link->setParent($parent);

        return $link->save();
    }

    /**
     * This link lets you add a stored link to the menu
     */
    public static function getPinLink($menu_id, $link_id=0, $popup=false)
    {
        if (!isset($_SESSION['Menu_Pin_Links'])) {
            return null;
        }

        $vars['command'] = 'pick_link';
        $vars['menu_id'] = $menu_id;
        if ($link_id) {
            $vars['link_id'] = $link_id;
        }

        $js['width']   = '300';
        $js['height']  = '100';

        $js['address'] = PHPWS_Text::linkAddress('menu', $vars, true);
        if ($popup) {
            $js['label'] = sprintf('%s %s', MENU_PIN_LINK, dgettext('menu', 'Add stored page'));
        } else {
            $js['label'] = MENU_PIN_LINK;
        }

        return javascript('open_window', $js);
    }


    public function parseIni()
    {
        $inifile = PHPWS_Template::getTemplateDirectory('menu') . 'menu_layout/' . $this->template . '/options.ini';
        if (!is_file($inifile)) {
            return;
        }

        $results = parse_ini_file($inifile);
        if (!empty($results['show_all'])) {
            $this->_show_all = (bool)$results['show_all'];;
        }

        if (!empty($results['style_sheet'])) {
            $this->_style = $results['style_sheet'];
        }

    }


    /**
     * Returns a menu and its links for display
     */
    public function view($pin_mode=FALSE, $return_content=false)
    {
        static $pin_page = true;

        $key = Key::getCurrent();

        if ($pin_mode && $key->isDummy(true)) {
            return;
        }

        $tpl_dir = PHPWS_Template::getTemplateDirectory('menu');
        $edit = FALSE;
        $file = 'menu_layout/' . $this->template . '/menu.tpl';

        if (!is_file($tpl_dir . $file)) {
            PHPWS_Error::log(MENU_MISSING_TPL, 'menu', 'Menu_Item::view', $tpl_dir . $file);
            return false;
        }

        $this->parseIni();

        if ($this->_style) {
            $style = sprintf('menu_layout/%s/%s', $this->template, $this->_style);
            Layout::addStyle('menu', $style);
        }

        $admin_link = !PHPWS_Settings::get('menu', 'miniadmin');

        $content_var = 'menu_' . $this->id;

        if ( !$pin_mode && Current_User::allow('menu') ) {
            if (Menu::isAdminMode()) {
                if(!isset($_REQUEST['authkey'])) {
                    $pinvars['command'] = 'pin_page';
                    if ($key) {
                        if ($key->isDummy()) {
                            $pinvars['ltitle'] = urlencode($key->title);
                            $pinvars['lurl'] = urlencode($key->url);
                        } else {
                            $pinvars['key_id'] = $key->id;
                        }
                    } else {
                        $pinvars['lurl'] = urlencode(PHPWS_Core::getCurrentUrl());
                    }

                    $js['address'] = PHPWS_Text::linkAddress('menu', $pinvars);
                    $js['label']   = dgettext('menu', 'Pin page');
                    $js['width']   = 300;
                    $js['height']  = 180;
                    if (!PHPWS_Settings::get('menu', 'miniadmin')) {
                        $tpl['PIN_PAGE'] = javascript('open_window', $js);
                    } elseif ($pin_page) {
                        MiniAdmin::add('menu', javascript('open_window', $js));
                        $pin_page = false;
                    }
                }

                $tpl['ADD_LINK'] = Menu::getAddLink($this->id);
                $tpl['ADD_SITE_LINK'] = Menu::getSiteLink($this->id, 0, isset($key));

                if (!empty($key)) {
                    $tpl['CLIP'] = Menu::getUnpinLink($this->id, $key->id, $this->pin_all);
                } else {
                    $tpl['CLIP'] = Menu::getUnpinLink($this->id, -1, $this->pin_all);
                }

                if ($admin_link) {
                    $vars['command'] = 'disable_admin_mode';
                    $vars['return'] = 1;
                    $tpl['ADMIN_LINK'] = PHPWS_Text::moduleLink(MENU_ADMIN_OFF, 'menu', $vars);
                }

                if (isset($_SESSION['Menu_Pin_Links'])) {
                    $tpl['PIN_LINK'] = $this->getPinLink($this->id);
                }
            } elseif ($admin_link) {
                $vars['command'] = 'enable_admin_mode';
                $vars['return'] = 1;
                $tpl['ADMIN_LINK'] = PHPWS_Text::moduleLink(MENU_ADMIN_ON, 'menu', $vars);
            }

            if (empty($tpl['ADD_LINK']) && PHPWS_Settings::get('menu', 'always_add') && Key::checkKey($key)) {
                $this->loadJS();
                $tpl['ADD_LINK'] = Menu::getAddLink($this->id);
                $tpl['ADD_SITE_LINK'] = Menu::getSiteLink($this->id, 0, isset($key));
            }
        }


        $tpl['TITLE'] = $this->getTitle();
        $tpl['LINKS'] = $this->displayLinks($edit);
        $tpl['MENU_ID'] = sprintf('menu-%s', $this->id);

        if ($pin_mode &&
        Current_User::allow('menu') &&
        isset($_SESSION['Menu_Clip']) &&
        isset($_SESSION['Menu_Clip'][$this->id])) {

            $pinvars['command'] = 'pin_menu';
            $pinvars['key_id'] = $key->id;
            $pinvars['menu_id'] = $this->id;
            $tpl['CLIP'] = PHPWS_Text::secureLink(MENU_PIN, 'menu', $pinvars);
        }

        $content = PHPWS_Template::process($tpl, 'menu', $file);

        if ($return_content) {
            return $content;
        } else {
            Layout::set($content, 'menu', $content_var);
        }
    }

    public function reorderLinks()
    {
        if (!$this->id) {
            return false;
        }
        $db = new PHPWS_DB('menu_links');
        $db->addWhere('menu_id', $this->id);
        $db->addColumn('id');
        $db->addColumn('parent');
        $db->addColumn('link_order');
        $db->addOrder('link_order');
        $db->setIndexBy('parent');

        $result = $db->select();

        if (empty($result)) {
            return;
        }

        foreach ($result as $parent_id => $links) {
            if (empty($links)) {
                continue;
            }
            $count = 1;
            if (isset($links[0])) {
                foreach ($links as $link) {
                    $db->reset();
                    $db->addWhere('id', $link['id']);
                    $db->addValue('link_order', $count);
                    PHPWS_Error::logIfError($db->update());
                    $count++;
                }
            } else {
                $db->reset();
                $db->addWhere('id', $links['id']);
                $db->addValue('link_order', $count);
                PHPWS_Error::logIfError($db->update());
            }
        }
        return true;
    }
}

?>