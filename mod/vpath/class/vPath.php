<?php
/**
 * vpath - phpwebsite module
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

class vPath {
    public $forms       = null;
    public $panel       = null;
    public $message     = null;
    public $path        = null;
    public $title       = null;
    public $content     = null;

    public function adminMenu()
    {
        if (!Current_User::allow('vpath')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'post_settings':
                if (!Current_User::authorized('vpath')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('vpath', 'vPath settings saved.'));
                    \core\Core::reroute('index.php?module=vpath&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

            case 'menu':
            default:
                if (!isset($_GET['tab'])) {
                    $this->loadForm('settings');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(core\Template::process($tpl, 'vpath', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(core\Template::process($tpl, 'vpath', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    public function forwardMessage($message, $title=null)
    {
        $_SESSION['VP_Message']['message'] = $message;
        if ($title) {
            $_SESSION['VP_Message']['title'] = $title;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['VP_Message'])) {
            $this->message = $_SESSION['VP_Message']['message'];
            if (isset($_SESSION['VP_Message']['title'])) {
                $this->title = $_SESSION['VP_Message']['title'];
            }
            \core\Core::killSession('VP_Message');
        }
    }


    public function loadForm($type)
    {
        \core\Core::initModClass('vpath', 'vPath_Forms.php');
        $this->forms = new vPath_Forms;
        $this->forms->vpath = & $this;
        $this->forms->get($type);
    }


    public function loadPanel()
    {
        \core\Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('vpath-panel');
        $link = 'index.php?module=vpath&aop=menu';

        if (Current_User::isUnrestricted('vpath')) {
            $tags['settings'] = array('title'=>dgettext('vpath', 'Settings'),
                                  'link'=>$link);
            $tags['info'] = array('title'=>dgettext('vpath', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postSettings()
    {

        isset($_POST['enable_path']) ?
            \core\Settings::set('vpath', 'enable_path', 1) :
            \core\Settings::set('vpath', 'enable_path', 0);

        isset($_POST['show_on_home']) ?
            \core\Settings::set('vpath', 'show_on_home', 1) :
            \core\Settings::set('vpath', 'show_on_home', 0);

        \core\Settings::set('vpath', 'menu_id', $_POST['menu_id']);
        \core\Settings::set('vpath', 'divider', $_POST['divider']);

        isset($_POST['divider_space']) ?
            \core\Settings::set('vpath', 'divider_space', 1) :
            \core\Settings::set('vpath', 'divider_space', 0);

        isset($_POST['link_current']) ?
            \core\Settings::set('vpath', 'link_current', 1) :
            \core\Settings::set('vpath', 'link_current', 0);

        isset($_POST['show_sub_menu']) ?
            \core\Settings::set('vpath', 'show_sub_menu', 1) :
            \core\Settings::set('vpath', 'show_sub_menu', 0);

        isset($_POST['show_peer_menu']) ?
            \core\Settings::set('vpath', 'show_peer_menu', 1) :
            \core\Settings::set('vpath', 'show_peer_menu', 0);

        if (!empty($_POST['path_prefix'])) {
            \core\Settings::set('vpath', 'path_prefix', \core\Text::parseInput($_POST['path_prefix']));
        } else {
            \core\Settings::set('vpath', 'path_prefix', null);
        }

        if (!empty($_POST['path_suffix'])) {
            \core\Settings::set('vpath', 'path_suffix', \core\Text::parseInput($_POST['path_suffix']));
        } else {
            \core\Settings::set('vpath', 'path_suffix', null);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (core\Settings::save('vpath')) {
                return true;
            } else {
                return falsel;
            }
        }

    }


    function isCurrentUrl($url) {
        static $current_url = null;
        static $redirect_url = null;

        if (!$current_url) {
            $current_url = preg_quote(core\Core::getCurrentUrl(true,false));
        }

        if (!$redirect_url) {
            $redirect_url = preg_quote(core\Core::getCurrentUrl());
        }

        if ( preg_match("@$current_url$@", $url) ||
             preg_match("@$redirect_url$@", $url) ) {
            return true;
        } else {
            return false;
        }
    }


    function buildTrail($menu_id)
    {

        /* get all links for the menu_id */
        $db = new \core\DB('menu_links');
        $db->addWhere('menu_id', $menu_id);
        $db->addOrder('link_order asc');
        $links = $db->select();
        if (empty($links) || \core\Error::logIfError($links)) {
            return NULL;
        }

        /* get the current key */
        $current_key = \core\Key::getCurrent();

        /* if there is a key */
        if (!empty($current_key)) {

            /* make sure it isn't a dummy key */
            if (!$current_key->isDummy()) {
                $current_key_id = $current_key->id;
            } else {
                $current_key_id = null;
            }

        /* if there is no key, get the url */
        } /* else {
    
            static $current_url = null;
            static $redirect_url = null;

            if (!$current_url) {
                $current_url = preg_quote(core\Core::getCurrentUrl(true,false));
            }

            if (!$redirect_url) {
                $redirect_url = preg_quote(core\Core::getCurrentUrl());
            }

        } */

        /* initialise an array for the crumbs in the trail */
        $list = array();

        /* now lets go through the menu links */
        foreach ($links as $link) {
            /* check for the current link */
            if ( ((isset($current_key_id) && isset($link['key_id'])) && $link['key_id'] == $current_key_id) || 
                 vPath::isCurrentUrl($link['url'] == true)
          ) {
                /* add it's title to the crumb list */
                if (core\Settings::get('vpath', 'link_current')) {
                    $list[] = sprintf('<a href="%s">%s</a>', $link['url'], $link['title']);
                } else {
                    $list[] = $link['title'];
                }
                /* if it has a parent keep going back */
                if ($link['parent']) {
                    vPath::getCrumbs($links, $list, $link['parent']);
                }
                /* print the sub-menu if enabled */
                if (core\Settings::get('vpath', 'show_sub_menu')) {
                    $title = sprintf('<a href="%s">%s</a>', $link['url'], $link['title']);
                    vPath::buildSub($links, $link['id'], $title);
                }



                /* if peer-menu is enabled and if it has no children, print peer-menu  */
                if (core\Settings::get('vpath', 'show_peer_menu')) {
                    $db = new \core\DB('menu_links');
                    $db->addWhere('parent', $link['id']);
                    $subs = $db->select();
                    \core\Error::logIfError($subs);
                    if (empty($subs)) {
                        $db = new \core\DB('menu_links');
                        $db->addWhere('id', $link['parent']);
                        $parent = $db->select();
                        $title = sprintf('<a href="%s">%s</a>', $parent[0]['url'], $parent[0]['title']);
                        vPath::buildSub($links, $link['parent'], $title, $link['id']);
                    }
                }



            }
//print $current_key_id . ' ' . $current_url . ' ' . $redirect_url;
        }

        /* if the current item is not in the menu */
        if (empty($list)) {
            if (isset($GLOBALS['Layout_Page_Title_Add'])) {
                $title = $GLOBALS['Layout_Page_Title_Add'];
            } else {
                $title = $_SESSION['Layout_Settings']->getPageTitle();
            }
            if (core\Settings::get('vpath', 'link_current')) {
                $list[0] = sprintf('<a href="%s">%s</a>', \core\Core::getCurrentUrl(), $title);
            } else {
                $list[0] = $title;
            }
        }

        /* now reverse the order */
        $list = array_reverse($list);

        /* put it all together */
        $tpl['PREFIX'] = \core\Settings::get('vpath', 'path_prefix');
        $tpl['SUFFIX'] = \core\Settings::get('vpath', 'path_suffix');
        require(PHPWS_SOURCE_DIR . 'mod/vpath/inc/dividers.php');
        $divider = $vpath_dividers[core\Settings::get('vpath', 'divider')];
        if (core\Settings::get('vpath', 'divider_space')) {
            $divider = ' ' . $divider . ' ';
        }
        $tpl['PATH'] = implode($list, $divider);

        if (!empty($tpl)) {
            $content = \core\Template::process($tpl, 'vpath', 'path.tpl');
            Layout::add($content, 'vpath', 'view');
        }

    }


    function getCrumbs(&$links, &$list, $id) {
        foreach ($links as $l) {
            if ($l['id'] == $id) {
                $list[] = sprintf('<a href="%s">%s</a>', $l['url'], $l['title']);
                if ($l['parent']) {
                    vPath::getCrumbs($links, $list, $l['parent']);
                } else {
                    return;
                }
            }
        }
    }


    function buildSub(&$links, $id, $title, $active=null) {
        $tpl['LINKS'] = null;
        foreach ($links as $l) {
            $active_link = null;
            if ($l['parent'] == $id) {
                if (isset($active) && ($active == $l['id'])) {
                    $active_link = 'active-link';
                } //else {
//                    $tpl['ACTIVE'] = 'active-title';
//                }
                $link = sprintf('<a class="menu-link-href %s" href="%s">%s</a>', $active_link, $l['url'], $l['title']);
                $tpl['LINKS'][]['LINK'] = $link;
            }
        }
        if ($tpl['LINKS']) {
            $tpl['TITLE'] = $title;
            $content = \core\Template::process($tpl, 'vpath', 'sub.tpl');
            Layout::add($content, 'vpath', 'sub');
        }
    }


}

?>