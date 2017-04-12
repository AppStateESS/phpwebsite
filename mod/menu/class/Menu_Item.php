<?php

/**
 * Object class for a menu
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
\phpws\PHPWS_Core::initModClass('menu', 'Menu_Link.php');

define('MENU_MISSING_TPL', -2);

class Menu_Item {

    public $id = 0;
    public $key_id = 0;
    public $title = NULL;
    public $template = NULL;
    public $pin_all = 0;
    public $queue = 0;
    public $assoc_key;
    public $assoc_url;
    public $assoc_image;
    public $_db = NULL;
    public $_show_all = false;
    public $_style = null;
    public $_error = NULL;

    public function __construct($id = NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int) $id;
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
        $db = \phpws2\Database::newDB();
        $m = $db->addTable('menus');
        $k = $db->addTable('phpws_key');
        $k->addField('url');
        $db->joinResources($m, $k,
                $db->createConditional($m->getField('assoc_key'),
                        $k->getField('id'), '='), 'left');
        $m->addFieldConditional('id', $this->id);

        $result = $db->selectOneRow();
        $this->id = $result['id'];
        $this->key_id = $result['key_id'];
        $this->title = $result['title'];
        $this->template = $result['template'];
        $this->pin_all = $result['pin_all'];
        $this->queue = $result['queue'];
        $this->assoc_key = $result['assoc_key'];
        if (!empty($result['assoc_url'])) {
            $this->assoc_url = $result['assoc_url'];
        } elseif ($result['assoc_key']) {
            $this->assoc_url = $result['url'];
        }
        $this->assoc_image = $result['assoc_image'];
    }

    public function setAssocUrl($url)
    {
        $this->assoc_url = $url;
    }

    public function getAssocUrl()
    {
        return $this->assoc_url;
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

    public function setAssocKey($key)
    {
        $this->assoc_key = (int) $key;
    }

    public function getAssocKey()
    {
        return $this->assoc_key;
    }

    public function setPinAll($pin)
    {
        $this->pin_all = (bool) $pin;
    }

    public function getTemplateList()
    {
        $included_result = PHPWS_File::listDirectories(PHPWS_Template::getTemplateDirectory('menu') . 'menu_layout/');
        $theme_result = PHPWS_File::listDirectories(PHPWS_SOURCE_DIR . Layout::getThemeDir() . 'templates/menu/menu_layout/');

        if (PHPWS_Error::logIfError($included_result) || PHPWS_Error::logIfError($theme_result)) {
            return null;
        }

        if ($theme_result) {
            $result = array_unique(array_merge($included_result, $theme_result));
        } else {
            $result = $included_result;
        }

        $result = array_combine($result, $result);

        if (empty($result)) {
            return null;
        }

        foreach ($result as $dir) {
            $directories[$dir] = $dir;
        }

        return $directories;
    }

    public function post()
    {
        if (empty($_POST['title'])) {
            $errors[] = 'Missing menu title.';
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
                return array(dgettext('menu',
                            'Unable to save menu. Please check error logs.'));
            }
            return TRUE;
        }
    }

    private function thumbPath($path)
    {
        return preg_replace('/\.(jpg|jpeg|gif|png)$/i', '_tn.\\1', $path);
    }

    public function deleteImage()
    {
        $file = $this->assoc_image;
        if (empty($file)) {
            return;
        }
        if (is_file($file)) {
            unlink($file);
        }
        $thumb = $this->thumbPath($file);
        if (is_file($thumb)) {
            unlink($thumb);
        }
    }

    public function setAssocImage($path)
    {
        $this->assoc_image = $path;
    }

    public function getAssocImage()
    {
        return $this->assoc_image;
    }

    public function showAssocImage()
    {
        if (empty($this->assoc_image)) {
            return null;
        }
        return "<img id=\"menu-associated-image\" data-src=\">768:$this->assoc_image\"
        style=\"display:none;\" onload=\"this.style.display='inline';this.style.opacity='1';\" />
        <noscript><img id=\"menu-associated-image\" src=\">768:$this->assoc_image\" \></noscript>";
    }

    public function getAssocImageThumbnail()
    {
        return $this->thumbPath($this->assoc_image);
    }

    public function save($save_key = true)
    {
        if (empty($this->title)) {
            return FALSE;
        }

        $new_menu = !(bool) $this->id;

        $this->resetdb();

        if (!$this->id) {
            $db = \phpws2\Database::newDB();
            $tbl = $db->addTable('menus', null, false);
            $queue_field = $tbl->getField('queue');
            $db->addExpression('max(' . $queue_field . ')', 'max');
            $row = $db->selectOneRow();
            if ($row) {
                $queue = $row['max'];
            } else {
                $queue = 0;
            }
            $this->queue = $queue + 1;
        }
        if (!$this->assoc_key) {
            $this->assoc_key = 0;
        }
        $result = $this->_db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            throw new \Exception($result->getMessage());
        }

        if ($save_key) {
            $this->saveKey();
        }

        if ($new_menu && \PHPWS_Settings::get('menu', 'display_type') == 0 && PHPWS_Settings::get('menu', 'home_link')) {
            $link = new Menu_Link;
            $link->menu_id = $this->id;
            $link->title = 'Home';
            $link->url = 'index.php';
            $link->key_id = 0;
            $result = $link->save();
            if (\PHPWS_Error::isError($result)) {
                throw new \Exception($result->getMessage());
            }
        }

        return true;
    }

    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new \Canopy\Key;
            $key->module = $key->item_name = 'menu';
            $key->item_id = $this->id;
        } else {
            $key = new \Canopy\Key($this->key_id);
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
    public function displayLinks($admin = false)
    {
        $all_links = $this->getLinks();
        if (empty($all_links)) {
            return NULL;
        }

        foreach ($all_links as $link) {
            $i = $link->view(1, $admin);
            if ($i) {
                $link_list[] = $i;
            }
        }

        return implode("\n", $link_list);
    }

    /**
     * Returns the menu link objects associated to a menu
     */
    public function getLinks($parent = 0, $active_only = TRUE)
    {
        if (!$this->id) {
            return NULL;
        }
        // Get all records for this menu
        $db = new PHPWS_DB('menu_links');
        $db->setDistinct();
        $db->addWhere('menu_id', $this->id, NULL, NULL, 1);
        \Canopy\Key::restrictView($db);
        $db->addOrder('link_order');
        $db->setIndexBy('id');
        $data = $db->getObjects('Menu_Link');
        if (empty($data) || PHPWS_Error::logIfError($data)) {
            return NULL;
        }

        $final = $this->formLink($data);

        $GLOBALS['MENU_LINKS'][$this->id] = $final;
        return $final;
    }

    public function formLink($data)
    {
        $new_list = array();
        foreach ($data as $key => $link) {
            $link->_menu = $this;
            if ($link->parent == 0) {
                $new_list[$link->id] = $link;
            } elseif (isset($new_list[$link->parent])) {
                $new_list[$link->parent]->_children[$link->id] = $link;
            } elseif (isset($data[$link->parent])) {
                $data[$link->parent]->_children[$link->id] = $link;
            }
        }
        return $new_list;
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

        $db2 = \phpws2\Database::newDB();
        $tbl = $db2->addTable('menus');
        $tbl->addFieldConditional('queue', $this->queue, '>');
    }

    public function addRawLink($title, $url, $parent = 0)
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

    public function addLink($key_id, $parent = 0)
    {
        $key = new \Canopy\Key($key_id);
        $link = new Menu_Link;

        $link->setMenuId($this->id);
        $link->setKeyId($key->id);
        $link->setTitle($key->title);

        $db = \phpws2\Database::newDB();
        $t1 = $db->addTable('access_shortcuts');
        $t1->addFieldConditional('url', 'pagesmith:' . $key->item_id);
        $t1->addFieldConditional('active', '1');
        $access = $db->selectOneRow();
        if (!empty($access)) {
            $link->url = './' . $access['keyword'];
        } else {
            $link->url = & $key->url;
        }
        $link->setParent($parent);

        return $link->save();
    }

    private function parseIni($directory)
    {
        $inifile = $directory . 'options.ini';
        if (!is_file($inifile)) {
            return;
        }

        $results = parse_ini_file($inifile);
        if (!empty($results['show_all'])) {
            $this->_show_all = (bool) $results['show_all'];
        }

        if (!empty($results['style_sheet'])) {
            $this->_style = $results['style_sheet'];
        }
    }

    /**
     * Returns a menu and its links for display
     */
    public function view($admin = false)
    {
        $links = $this->displayLinks($admin);
        if (empty($links)) {
            return null;
        }
        $key = \Canopy\Key::getCurrent();
        if ($key && $key->isDummy(true)) {
            return;
        }
        $theme_tpl_dir = \phpws\PHPWS_Template::getTplDir('menu') . 'menu_layout/';
        $menu_tpl_dir = PHPWS_SOURCE_DIR . 'mod/menu/templates/menu_layout/';

        $theme_path = $theme_tpl_dir . $this->template . '/';
        $menu_path = $menu_tpl_dir . $this->template . '/';
        if (is_file($theme_path . 'menu.tpl')) {
            $file = $theme_path . 'menu.tpl';
            $path = $theme_path;
            $http = 'themes/' . Layout::getTheme() . '/templates/menu/menu_layout/' . $this->template . '/';
        } elseif (is_file($menu_path . 'menu.tpl')) {
            $file = $menu_path . 'menu.tpl';
            $path = $menu_path;
            $http = 'mod/menu/templates/menu_layout/' . $this->template . '/';
        } else {
            $this->template = 'basic';
            $this->save();
            $path = $menu_tpl_dir . 'basic/';
            $http = 'mod/menu/templates/menu_layout/basic/';
            $file = $path . '/menu.tpl';
        }

        $this->parseIni($path);

        if ($this->_style) {
            $style = $http . 'style.css';
            Layout::addToStyleList($style);
        }

        $tpl['TITLE'] = $this->getTitle();
        $tpl['LINKS'] = $links;
        $tpl['MENU_ID'] = sprintf('menu-%s', $this->id);
        $content = PHPWS_Template::process($tpl, 'menu', $file, true);

        return $content;
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
