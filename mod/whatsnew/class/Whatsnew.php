<?php
/**
 * whatsnew - phpwebsite module
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

PHPWS_Core::requireConfig('whatsnew');

class Whatsnew {
    var $forms      = null;
    var $panel      = null;
    var $title      = null;
    var $message    = null;
    var $content    = null;

    function adminMenu()
    {
        if (!Current_User::allow('whatsnew')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('settings');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'post_settings':
                if (!Current_User::authorized('whatsnew')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('whatsnew', 'Whatsnew settings saved.'));
                    PHPWS_Core::reroute('index.php?module=whatsnew&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

            case 'flush_cache':
                if (!Current_User::authorized('whatsnew')) {
                    Current_User::disallow();
                }
                if ($this->flushCache()) {
                    $this->forwardMessage(dgettext('whatsnew', 'Cache flushed.'));
                    PHPWS_Core::reroute('index.php?module=whatsnew&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'whatsnew', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'whatsnew', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }
        $this->loadMessage();

        switch($action) {

            case 'view':
                $this->title = PHPWS_Settings::get('whatsnew', 'title');
                $this->content = $this->whatsnewBlock();
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'whatsnew', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'whatsnew', 'main_user.tpl'));
        }

    }


    function forwardMessage($message, $title=null)
    {
        $_SESSION['Whatsnew_Message']['message'] = $message;
        if ($title) {
            $_SESSION['Whatsnew_Message']['title'] = $title;
        }
    }


    function loadMessage()
    {
        if (isset($_SESSION['Whatsnew_Message'])) {
            $this->message = $_SESSION['Whatsnew_Message']['message'];
            if (isset($_SESSION['Whatsnew_Message']['title'])) {
                $this->title = $_SESSION['Whatsnew_Message']['title'];
            }
            PHPWS_Core::killSession('Whatsnew_Message');
        }
    }


    function loadForm($type)
    {
        PHPWS_Core::initModClass('whatsnew', 'Whatsnew_Forms.php');
        $this->forms = new whatsnew_Forms;
        $this->forms->whatsnew = & $this;
        $this->forms->get($type);
    }


    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('whatsnew-panel');
        $link = 'index.php?module=whatsnew&aop=menu';

        if (Current_User::isUnrestricted('whatsnew')) {
            $tags['settings'] = array('title'=>dgettext('whatsnew', 'Settings'),
                                  'link'=>$link);
            $tags['info'] = array('title'=>dgettext('whatsnew', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    function postSettings()
    {

        isset($_POST['enable']) ?
        PHPWS_Settings::set('whatsnew', 'enable', 1) :
        PHPWS_Settings::set('whatsnew', 'enable', 0);

        isset($_POST['homeonly']) ?
        PHPWS_Settings::set('whatsnew', 'homeonly', 1) :
        PHPWS_Settings::set('whatsnew', 'homeonly', 0);

        if (!empty($_POST['title'])) {
            PHPWS_Settings::set('whatsnew', 'title', strip_tags(PHPWS_Text::parseInput($_POST['title'])));
        } else {
            PHPWS_Settings::reset('whatsnew', 'title');
        }

        if (!empty($_POST['text'])) {
            PHPWS_Settings::set('whatsnew', 'text', PHPWS_Text::parseInput($_POST['text']));
        } else {
            PHPWS_Settings::set('whatsnew', 'text', null);
        }

        $cache_timeout = (int)$_POST['cache_timeout'];
        if ((int)$cache_timeout <= 7200) {
            PHPWS_Settings::set('whatsnew', 'cache_timeout', $cache_timeout);
        } else {
            PHPWS_Settings::reset('whatsnew', 'cache_timeout');
        }

        $qty_items = (int)$_POST['qty_items'];
        if ((int)$qty_items <= 50) {
            PHPWS_Settings::set('whatsnew', 'qty_items', $qty_items);
        } else {
            PHPWS_Settings::reset('whatsnew', 'qty_items');
        }

        isset($_POST['show_summaries']) ?
        PHPWS_Settings::set('whatsnew', 'show_summaries', 1) :
        PHPWS_Settings::set('whatsnew', 'show_summaries', 0);

        isset($_POST['show_dates']) ?
        PHPWS_Settings::set('whatsnew', 'show_dates', 1) :
        PHPWS_Settings::set('whatsnew', 'show_dates', 0);

        isset($_POST['show_source_modules']) ?
        PHPWS_Settings::set('whatsnew', 'show_source_modules', 1) :
        PHPWS_Settings::set('whatsnew', 'show_source_modules', 0);

        if (isset($_POST['exclude'])) {
            PHPWS_Settings::set('whatsnew', 'exclude', $_POST['exclude']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            PHPWS_Cache::remove('whatsnew_cache_key');
            if (PHPWS_Settings::save('whatsnew')) {
                return true;
            } else {
                return false;
            }
        }

    }


    function getKeyMods($match=null, $select_name='exclude', $multiple=true, $count=true)
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
                        $qty_label = dgettext('whatsnew', 'item');
                    } else {
                        $qty_label = dgettext('whatsnew', 'items');
                    }
                    $items[$item->module] = $item->module . ' ('.$qty.' '.$qty_label.')';
                } else {
                    $items[$item->module] = $item->module;
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
            return dgettext('whatsnew', 'No keyed items.');
        }

    }


    public static function showBlock() {
        PHPWS_Core::initModClass('layout', 'Layout.php');
        if (PHPWS_Settings::get('whatsnew', 'homeonly')) {
            $key = Key::getCurrent();
            if (!empty($key) && $key->isHomeKey()) {
                Layout::add(Whatsnew::whatsnewBlock(), 'whatsnew', 'whatsnew_sidebox');
            }
        } else {
            Layout::add(Whatsnew::whatsnewBlock(), 'whatsnew', 'whatsnew_sidebox');
        }
    }


    public static function whatsnewBlock() {

        if (PHPWS_Settings::get('whatsnew', 'cache_timeout') > 0) {
            $cache_key = 'whatsnew_cache_key';
            $content = PHPWS_Cache::get($cache_key, PHPWS_Settings::get('whatsnew', 'cache_timeout'));
            if (!empty($content)) {
                return $content;
            }
        }

        $link = null;
        $summary = null;
        $date = null;
        $module_name = null;

        $exclude = unserialize(PHPWS_Settings::get('whatsnew', 'exclude'));
        $db = new PHPWS_DB('phpws_key');

        $db->addJoin('left', 'phpws_key', 'modules', 'module', 'title');
        $db->addWhere('active', 1);
        $db->addWhere('restricted', 0);
        if ($exclude) {
            foreach ($exclude as $module) {
                $db->addWhere('module', $module, '!=');
            }
        }

        $db->addOrder('update_date desc');
        $db->setLimit(PHPWS_Settings::get('whatsnew', 'qty_items'));
        $db->setIndexBy('id');
        $db->addColumn('phpws_key.url');
        $db->addColumn('phpws_key.title');
        $db->addColumn('phpws_key.summary');
        $db->addColumn('phpws_key.update_date');
        $db->addColumn('modules.title', null, 'module_title');
        $db->addColumn('modules.proper_name');
        //        $db->setTestMode();
        $result = $db->select();

        $tpl['TITLE'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('whatsnew', 'title'));
        $tpl['TEXT'] = PHPWS_Text::parseOutput(PHPWS_Settings::get('whatsnew', 'text'));
        if (!PHPWS_Error::logIfError($result) && !empty($result)) {
            foreach ($result as $item) {
                $link = '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
                if (PHPWS_Settings::get('whatsnew', 'show_summaries')) {
                    $summary = PHPWS_Text::parseOutput($item['summary']);
                }
                if (PHPWS_Settings::get('whatsnew', 'show_dates')) {
                    $date = strftime(WHATSNEW_DATE_FORMAT, $item['update_date']);
                }
                if (PHPWS_Settings::get('whatsnew', 'show_source_modules')) {
                    $module_name = dgettext($item['module_title'], PHPWS_Text::parseOutput($item['proper_name']));
                }
                $tpl['new-items'][] = array('LINK'=>$link, 'SUMMARY'=>$summary, 'DATE'=>$date, 'MODULE_NAME'=>$module_name);
            }
        } else {
            $tpl['new-items'][] = array('LINK'=>dgettext('whatsnew', 'Sorry, no results'));
        }

        $content = PHPWS_Template::process($tpl, 'whatsnew', 'block.tpl');
        if (PHPWS_Settings::get('whatsnew', 'cache_timeout') > 0 && !Current_User::isLogged() && !Current_User::allow('whatsnew')) {
            PHPWS_Cache::save($cache_key, $content);
        }

        return $content;
    }


    function flushCache() {
        if (PHPWS_Cache::remove('whatsnew_cache_key')) {
            return true;
        }
    }


}
?>