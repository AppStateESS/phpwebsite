<?php
/**
    * sitemap - phpwebsite module
    *
    * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
    *
    * This program is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program; if not, write to the Free Software
    * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    *
    * @version $Id$
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

PHPWS_Core::requireInc('sitemap', 'errordefines.php');
PHPWS_Core::requireConfig('sitemap');

class Sitemap {
    public $forms       = null;
    public $panel       = null;
    public $message     = null;
    public $map         = null;
    public $title       = null;
    public $content     = null;

    public function adminMenu()
    {
        if (!Current_User::allow('sitemap')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'make_map':
                if (!Current_User::authorized('sitemap')) {
                    Current_User::disallow();
                }

                if (PHPWS_Error::logIfError($this->buildFile())) {
                    $this->forwardMessage(dgettext('sitemap', 'Error occurred when creating file.'));
                    PHPWS_Core::reroute('index.php?module=sitemap&aop=menu&tab=new');
                } else {
                    $this->forwardMessage(dgettext('sitemap', 'File created successfully.'));
                    PHPWS_Core::reroute('index.php?module=sitemap&aop=menu&tab=new');
                }

                break;

            case 'post_settings':
                if (!Current_User::authorized('sitemap')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('sitemap', 'Sitemap settings saved.'));
                    PHPWS_Core::reroute('index.php?module=sitemap&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

            case 'menu':
            default:
                if (!isset($_GET['tab'])) {
                    $this->loadForm('new');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'sitemap', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'sitemap', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

   }


    public function sendMessage()
    {
        PHPWS_Core::reroute('index.php?module=sitemap&amp;uop=message');
    }

    public function forwardMessage($message, $title=null)
    {
        $_SESSION['SM_Message']['message'] = $message;
        if ($title) {
            $_SESSION['SM_Message']['title'] = $title;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['SM_Message'])) {
            $this->message = $_SESSION['SM_Message']['message'];
            if (isset($_SESSION['SM_Message']['title'])) {
                $this->title = $_SESSION['SM_Message']['title'];
            }
            PHPWS_Core::killSession('SM_Message');
        }
    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('sitemap', 'SM_Forms.php');
        $this->forms = new Sitemap_Forms;
        $this->forms->sitemap = & $this;
        $this->forms->get($type);
    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('sitemap-panel');
        $link = 'index.php?module=sitemap&aop=menu';

        if (Current_User::isUnrestricted('sitemap')) {
            $tags['new'] = array('title'=>dgettext('sitemap', 'Generate Map'),
                                 'link'=>$link);
            $tags['settings'] = array('title'=>dgettext('sitemap', 'Settings'),
                                  'link'=>$link);
            $tags['info'] = array('title'=>dgettext('sitemap', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postSettings()
    {

        isset($_POST['respect_privs']) ?
            PHPWS_Settings::set('sitemap', 'respect_privs', 1) :
            PHPWS_Settings::set('sitemap', 'respect_privs', 0);

        isset($_POST['local_only']) ?
            PHPWS_Settings::set('sitemap', 'local_only', 1) :
            PHPWS_Settings::set('sitemap', 'local_only', 0);

        isset($_POST['use_change']) ?
            PHPWS_Settings::set('sitemap', 'use_change', 1) :
            PHPWS_Settings::set('sitemap', 'use_change', 0);

        PHPWS_Settings::set('sitemap', 'change_freq', $_POST['change_freq']);

        isset($_POST['use_lastmod']) ?
            PHPWS_Settings::set('sitemap', 'use_lastmod', 1) :
            PHPWS_Settings::set('sitemap', 'use_lastmod', 0);

        isset($_POST['use_priority']) ?
            PHPWS_Settings::set('sitemap', 'use_priority', 1) :
            PHPWS_Settings::set('sitemap', 'use_priority', 0);

        isset($_POST['allow_feed']) ?
            PHPWS_Settings::set('sitemap', 'allow_feed', 1) :
            PHPWS_Settings::set('sitemap', 'allow_feed', 0);

        isset($_POST['addkeys']) ?
            PHPWS_Settings::set('sitemap', 'addkeys', 1) :
            PHPWS_Settings::set('sitemap', 'addkeys', 0);

        if (isset($_POST['exclude_keys'])) {
            PHPWS_Settings::set('sitemap', 'exclude_keys', $_POST['exclude_keys']);
        }

        $cache_timeout = (int)$_POST['cache_timeout'];
        if ((int)$cache_timeout <= 7200) {
            PHPWS_Settings::set('sitemap', 'cache_timeout', $cache_timeout);
        } else {
            PHPWS_Settings::reset('sitemap', 'cache_timeout');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('sitemap')) {
                return true;
            } else {
                return falsel;
            }
        }

    }


    public function getMenuIds()
    {
        $db = new PHPWS_DB('menus');
        $db->addWhere('restricted', 0);
        $result = $db->select();
        foreach ($result as $menu) {
            $menus[] = $menu['id'];
        }
        return $menus;
    }


    public function getMenus($match=null, $select_name='menus', $multiple=true, $count=true)
    {

        PHPWS_Core::initModClass('menu', 'Menu_Item.php');
        $db = new PHPWS_DB('menus');
        $db->addOrder('title asc');

        $result = $db->getObjects('Menu_Item');

        if ($result) {
            foreach ($result as $item) {
                if ($count) {
                    $db = new PHPWS_DB('menu_links');
                    $db->addWhere('menu_id', $item->id);
                    $qty = $db->count();
                    if ($qty == 1) {
                        $qty_label = dgettext('sitemap', 'link');
                    } else {
                        $qty_label = dgettext('sitemap', 'links');
                    }
                    $items[$item->id] = $item->title . ' ('.$qty.' '.$qty_label.')';
                } else {
                    $items[$item->id] = $item->title;
                }
            }
        }

        if ($items) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('sitemap', 'No menus created.');
        }

    }


    public function getKeyMods($match=null, $select_name='keys', $multiple=true, $count=true)
    {

        PHPWS_Core::initCoreClass('Key.php');
        $db = new PHPWS_DB('phpws_key');
        $db->addOrder('module asc');

        $result = $db->getObjects('Key');

        if ($result) {
            foreach ($result as $item) {
                if ($count) {
                    $db = new PHPWS_DB('phpws_key');
                    $db->addWhere('module', $item->module);
                    $qty = $db->count();
                    if ($qty == 1) {
                        $qty_label = dgettext('sitemap', 'item');
                    } else {
                        $qty_label = dgettext('sitemap', 'items');
                    }
                    $items[$item->module] = $item->module . ' ('.$qty.' '.$qty_label.')';
                } else {
                    $items[$item->module] = $item->module;
                }
            }
        }

        if (isset($items)) {
            if ($multiple) {
                $form = new PHPWS_Form;
                $form->addMultiple($select_name, $items);
                if (!empty($match) && is_array($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            } else {
                $form = new PHPWS_Form;
                $form->addSelect($select_name, $items);
                if (!empty($match) && is_string($match)) {
                    $form->setMatch($select_name, $match);
                }
                return $form->get($select_name);
            }
        } else {
            return dgettext('sitemap', 'No keyed items.');
        }

    }

    public function mapIt()
    {
        if (PHPWS_Settings::get('sitemap', 'allow_feed')) {
            /* what menus for auto, all non-restricted */
            $menus = $this->getMenuIds();

            /* the default exclude_keys list */
            $exclude_keys = unserialize(PHPWS_Settings::get('sitemap', 'exclude_keys'));

            /* what to do about lastmod, nothing except for keyed items */
            $lastmod = null;

            if (PHPWS_Settings::get('sitemap', 'cache_timeout') > 0) {
                $cache_key = 'sitemap_cache_key';
                $content = PHPWS_Cache::get($cache_key, PHPWS_Settings::get('sitemap', 'cache_timeout'));
                if (!empty($content)) {
                    header('Content-type: text/xml');
                    echo $content;
                    exit();
                }
            }

            $content = $this->buildXML($menus, $exclude_keys, $lastmod, PHPWS_Settings::get('sitemap', 'addkeys'));
            if (PHPWS_Settings::get('sitemap', 'cache_timeout') > 0) {
                PHPWS_Cache::save($cache_key, $content);
            }

            header('Content-type: text/xml');
            echo $content;
            exit();
        } else {
            PHPWS_Core::reroute();
        }
    }


    public function buildFile()
    {
        $menus = $_REQUEST['menus'];
        if (isset($_REQUEST['lastmod_default'])) {
            $lastmod = $_REQUEST['lastmod_default'];
        } else {
            $lastmod = null;
        }
        if (isset($_REQUEST['addkeys'])) {
            $addkeys = $_REQUEST['addkeys'];
        } else {
            $addkeys = null;
        }
        if (isset($_REQUEST['exclude_keys'])) {
            $exclude_keys = $_REQUEST['exclude_keys'];
        } else {
            $exclude_keys = null;
        }
        $content = $this->buildXML($menus, $exclude_keys, $lastmod, $addkeys);
        if ($_REQUEST['build_type']) {
            /* save to server */
            $filename = 'sitemap.xml';
            PHPWS_Core::initCoreClass('File.php');
            return PHPWS_File::writeFile(PHPWS_HOME_DIR.$filename, $content, true);
        } else {
            /* download */
            $filename = 'sitemap' . date('Ymd') . '.xml';
            Header('Content-Disposition: attachment; filename=' . $filename);
            Header('Content-Length: ' . strlen($content));
            Header('Connection: close');
            Header('Content-Type: text/xml; name=' . $filename);
            echo $content;
            exit();
        }
    }

    public function buildXML($menus, $exclude_keys=null, $lastmod=null, $addkeys=null)
    {
        $menuitems = array();
        $otheritems = array();
        $content = null;

        /* get the menu items */
        if (!empty($menus) && is_array($menus)) {
            $menuitems = $this->getMenuItems($menus);
//            print_r($menuitems); //exit;
        }

        /* get other keyed items */
        if ($addkeys) {
            $otheritems = $this->getOtherItems($exclude_keys);
//            print_r($otheritems); //exit;
            /* compare the arrays for dupes and return the cleaned array */
            $menuitems = array_udiff($menuitems, $otheritems, array($this, 'compareURL'));
//            print_r($menuitems); exit;
        }

        /* merge the two arrays */
        $allitems = array_merge($menuitems, $otheritems);
//        print_r($allitems); exit;

        if (!empty($allitems) && is_array($allitems)) {

            /* make a template array of what's left */
            foreach ($allitems as $link) {

                /* loc */
                $link_tpl['LOC'] = htmlspecialchars($link['url']);

                /* lastmod */
                if (PHPWS_Settings::get('sitemap', 'use_lastmod')) {
                    if ($link['key_id']) {
                        $link_tpl['LASTMOD'] = date("Y-m-d", $link['update_date']);
                    } else {
                        $link_tpl['LASTMOD'] = $lastmod;
                    }
                }

                /* changefreq */
                if (PHPWS_Settings::get('sitemap', 'use_change')) {
                    $link_tpl['CHANGE_FREQ'] = $this->getChangeFreq(PHPWS_Settings::get('sitemap', 'change_freq'));
                }

                /* priority */
                if (PHPWS_Settings::get('sitemap', 'use_priority')) {
                    $basep = 0.5;
                    if (!$link['parent']) {
                        $basep = $basep + 0.5;
                    }
                    if (($link['link_order'] > 1) && ($link['link_order'] < 5)) {
                        if ($link['link_order'] == 2) {
                            $basep = $basep - 0.1;
                        } elseif ($link['link_order'] == 3) {
                            $basep = $basep - 0.2;
                        } elseif ($link['link_order'] == 4) {
                            $basep = $basep - 0.3;
                        }
                    } elseif ($link['link_order'] > 4) {
                        $basep = $basep - 0.4;
                    }
                    $link_tpl['USE_PRIORITY'] = $basep;
                }

                $tpl['links-listing'][] = $link_tpl;
            }


//            print_r($tpl['links-listing']); exit;
            $content = PHPWS_Template::process($tpl, 'sitemap', 'sitemap.tpl');
            return $content;

        } else {
            return false;
        }
    }


    public function getMenuItems($menus)
    {
        $final = null;
        if (!empty($menus) && is_array($menus)) {

            $db = new PHPWS_DB('menu_links');

            $db->addColumn('menu_links.*');
            $db->addColumn('phpws_key.restricted');
            $db->addColumn('phpws_key.active');
            $db->addColumn('phpws_key.create_date');
            $db->addColumn('phpws_key.update_date');

            $db->addJoin('left', 'menu_links', 'phpws_key', 'key_id', 'id');

            foreach ($menus as $menu_id) {
                $db->addWhere('menu_id', $menu_id, NULL, 'or', 1);
            }

            $db->addOrder('link_order');
            $db->setIndexBy('id');
//            $db->setTestMode();
            $result = $db->select();

            if (empty($result) || PHPWS_Error::logIfError($result)) {
                return false;
            }

            /* pre-process the menu links */
            foreach ($result as $link) {

                $link['local'] = 1;

                /* get rid of leading .'s */
                if ($link['url'][0] == '.') {
                    $link['url'] = substr($link['url'], 1);
                }

                /* get rid of leading /'s */
                if ($link['url'][0] == '/') {
                    $link['url'] = substr($link['url'], 1);
                }

                /* check for local vs remote */
                if ($this->checkURL($link['url'])) {
                    $link['local'] = 0;
                }

                /* optionally remove remote links */
                if (PHPWS_Settings::get('sitemap', 'local_only')) {
                    if (!$link['local']) {
                        $tidy = null;
                    }
                }

                /* optionally remove private and non-active keyed items */
                if (PHPWS_Settings::get('sitemap', 'respect_privs')) {
                    if ($link['key_id'] && !$link['active']) {
                        $tidy = null;
                    }
                    if ($link['key_id'] && $link['restricted']) {
                        $tidy = null;
                    }
                }

                /* now put the http part on local links */
                if ($link['local']) {
                    $pre = 'http://' . $_SERVER['HTTP_HOST'];
                    if (dirname($_SERVER['PHP_SELF']) !== '/') {
                        $pre .= dirname($_SERVER['PHP_SELF']);
                    }
                    $link['url'] = $pre . '/' . $link['url'];
                }

                $tidy['url'] = $link['url'];
                $tidy['key_id'] = $link['key_id'];
                $tidy['update_date'] = $link['update_date'];
                $tidy['parent'] = $link['parent'];
                $tidy['link_order'] = $link['link_order'];

                $final[] = $tidy;
            }

            /* filter out the null ones and get the cleaned array */
            $final = array_filter($final);
//print_r($final); exit;

            return $final;
        } else {
            return false;
        }
    }


    public function getOtherItems($exclude_keys=null)
    {
        $final = null;
        $db = new PHPWS_DB('phpws_key');

        if ($exclude_keys) {
            foreach ($exclude_keys as $module) {
                $db->addWhere('module', $module, '!=');
            }
        }

        $db->addOrder('id');
        $db->setIndexBy('id');
//            $db->setTestMode();
        $result = $db->select();

        if (empty($result) || PHPWS_Error::logIfError($result)) {
            return false;
        }

        /* pre-process the menu links */
        foreach ($result as $link) {

            $link['local'] = 1;

            /* get rid of leading .'s */
            if ($link['url'][0] == '.') {
                $link['url'] = substr($link['url'], 1);
            }

            /* get rid of leading /'s */
            if ($link['url'][0] == '/') {
                $link['url'] = substr($link['url'], 1);
            }

            /* check for local vs remote */
            if ($this->checkURL($link['url'])) {
                $link['local'] = 0;
            }

            /* optionally remove remote links */
            if (PHPWS_Settings::get('sitemap', 'local_only')) {
                if (!$link['local']) {
                    $tidy = null;
                }
            }

            /* optionally remove private and non-active keyed items */
            if (PHPWS_Settings::get('sitemap', 'respect_privs')) {
                if (!$link['active']) {
                    $tidy = null;
                }
                if ($link['restricted']) {
                    $tidy = null;
                }
            }

            /* now put the http part on local links */
            if ($link['local']) {
                $pre = 'http://' . $_SERVER['HTTP_HOST'];
                if (dirname($_SERVER['PHP_SELF']) !== '/') {
                    $pre .= dirname($_SERVER['PHP_SELF']);
                }
                $link['url'] = $pre . '/' . $link['url'];
            }

            $tidy['url'] = $link['url'];
            $tidy['key_id'] = $link['id'];
            $tidy['update_date'] = $link['update_date'];
            $tidy['parent'] = -1;
            $tidy['link_order'] = 5;

            $final[] = $tidy;
        }

        /* filter out the null ones and get the cleaned array */
        $final = array_filter($final);
//print_r($final); exit;

        return $final;
    }


    public function compareURL($a, $b)
    {
        return strcmp($a['url'], $b['url']);
    }


    public function getChangeFreq($id)
    {
        $freqs= array('1'=>'always', '2'=>'hourly', '3'=>'daily', '4'=>'weekly', '5'=>'monthly', '6'=>'yearly', '7'=>'never');
        return $freqs[$id];
    }


    public function checkURL($link)
    {
        $pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
        preg_match($pattern, $link, $matches);
//        print_r($matches); exit;
        if ($matches[1]) {
            return true;
        } else {
            return false;
        }
    }


}
?>