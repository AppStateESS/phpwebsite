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
                    PHPWS_Core::reroute('index.php?module=vpath&aop=menu');
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
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'vpath', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'vpath', 'main_admin.tpl'));
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
            PHPWS_Core::killSession('VP_Message');
        }
    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('vpath', 'vPath_Forms.php');
        $this->forms = new vPath_Forms;
        $this->forms->vpath = & $this;
        $this->forms->get($type);
    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
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
            PHPWS_Settings::set('vpath', 'enable_path', 1) :
            PHPWS_Settings::set('vpath', 'enable_path', 0);

        PHPWS_Settings::set('vpath', 'menu_id', $_POST['menu_id']);
        PHPWS_Settings::set('vpath', 'divider', $_POST['divider']);

        isset($_POST['divider_space']) ?
            PHPWS_Settings::set('vpath', 'divider_space', 1) :
            PHPWS_Settings::set('vpath', 'divider_space', 0);

        if (!empty($_POST['path_prefix'])) {
            PHPWS_Settings::set('vpath', 'path_prefix', PHPWS_Text::parseInput($_POST['path_prefix']));
        } else {
            PHPWS_Settings::set('vpath', 'path_prefix', null);
        }

        if (!empty($_POST['path_suffix'])) {
            PHPWS_Settings::set('vpath', 'path_suffix', PHPWS_Text::parseInput($_POST['path_suffix']));
        } else {
            PHPWS_Settings::set('vpath', 'path_suffix', null);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('vpath')) {
                return true;
            } else {
                return falsel;
            }
        }

    }


    function buildTrail($menu_id)
    {
    
        /* get all links for the menu_id */
        $db = new PHPWS_DB('menu_links');
        $db->addWhere('menu_id', $menu_id);
        $links = $db->select();
        if (empty($links) || PHPWS_Error::logIfError($links)) {
            return NULL;
        }
    
        /* get the current key */
        $current_key = Key::getCurrent();
    
        /* if there is a key */
        if (!empty($current_key)) {
    
            /* make sure it isn't a dummy key */
            if (!$current_key->isDummy()) {
                $current_key_id = $current_key->id;
            } else {
                $current_key_id = null;
            }
    
        /* if there is no key, get the url */
        } else {
    
            static $current_url = null;
            static $redirect_url = null;
    
            if (!$current_url) {
                $current_url = PHPWS_Core::getCurrentUrl(true,false);
            }
    
            if (!$redirect_url) {
                $redirect_url = PHPWS_Core::getCurrentUrl();
            }
        
        }
    
        /* initialise an array for the crumbs in the trail */
        $list = array();
    
        /* now lets go through the menu links */
        foreach ($links as $link) {
            /* check for the current link */
            if ((isset($current_key_id) && $link['key_id'] == $current_key_id) || (isset($current_url) && (strpos($link['url'], $current_url) !== false)) || (isset($redirect_url) && (strpos($link['url'], $redirect_url) !== false))) {
                /* add it's title to the crumb list */
                $list[] = $link['title'];
                /* if it has a parent keep going back */
                if ($link['parent']) {
                    vPath::getCrumbs($links, $list, $link['parent']);
                }
            }
        }
        
        if (empty($list)) {
            if (isset($GLOBALS['Layout_Page_Title_Add'])) {
                $list[0] = $GLOBALS['Layout_Page_Title_Add'];
            } else {
                $list[0] = $_SESSION['Layout_Settings']->getPageTitle();
            }
        }
        $list = array_reverse($list);
        $tpl['PREFIX'] = PHPWS_Settings::get('vpath', 'path_prefix');
        $tpl['SUFFIX'] = PHPWS_Settings::get('vpath', 'path_suffix');
        require(PHPWS_SOURCE_DIR . 'mod/vpath/inc/dividers.php');
        $divider = $vpath_dividers[PHPWS_Settings::get('vpath', 'divider')];
        if (PHPWS_Settings::get('vpath', 'divider_space')) {
            $divider = ' ' . $divider . ' ';
        } 
        $tpl['PATH'] = implode($list, $divider);
    
        if (!empty($tpl)) {
            $content = PHPWS_Template::process($tpl, 'vpath', 'path.tpl');
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


}

?>