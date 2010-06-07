<?php
/**
 * finc - phpwebsite module
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

Core\Core::requireInc('finc', 'errordefines.php');
Core\Core::requireConfig('finc');

class Finc {
    var $forms       = null;
    var $panel       = null;
    var $message     = null;
    var $file        = null;
    var $title       = null;
    var $description = null;
    var $content     = null;


    function adminMenu()
    {
        if (!Current_User::allow('finc')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'edit_file':
                $this->loadForm('edit_file');
                break;

            case 'post_file':
                if (!Current_User::authorized('finc')) {
                    Current_User::disallow();
                }
                if ($this->postFile()) {
                    if (PHPWS_Error::logIfError($this->file->save())) {
                        $this->forwardMessage(dgettext('finc', 'Error occurred when saving file.'));
                        Core\Core::reroute('index.php?module=finc&aop=list');
                    } else {
                        $this->forwardMessage(dgettext('finc', 'File saved successfully.'));
                        Core\Core::reroute('index.php?module=finc&aop=list');
                    }
                } else {
                    $this->loadForm('edit');
                }
                break;

            case 'activate_file':
                if (!Current_User::authorized('finc')) {
                    Current_User::disallow();
                }
                $this->loadFile();
                $this->file->active = 1;
                $this->file->save();
                $this->message = dgettext('finc', 'Finc file activated.');
                $this->loadForm('list');
                break;

            case 'deactivate_file':
                if (!Current_User::authorized('finc')) {
                    Current_User::disallow();
                }
                $this->loadFile();
                $this->file->active = 0;
                $this->file->save();
                $this->message = dgettext('finc', 'Finc file deactivated.');
                $this->loadForm('list');
                break;

            case 'delete_file':
                if (!Current_User::authorized('finc')) {
                    Current_User::disallow();
                }
                $this->loadFile();
                $this->file->delete();
                $this->message = dgettext('finc', 'Finc file deleted.');
                $this->loadForm('list');
                break;

            case 'post_settings':
                if (!Current_User::authorized('finc')) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('finc', 'Finc settings saved.'));
                    Core\Core::reroute('index.php?module=finc&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'finc', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'finc', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                Core\Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }
        $this->loadMessage();

        switch($action) {

            case 'view_file':
                $this->loadFile();
                if ($this->file->active) {
                    if (PHPWS_Settings::get('finc', 'show_title'))
                        $this->title = $this->file->getTitle(true);
                    if (PHPWS_Settings::get('finc', 'add_title_tag'))
                        Layout::addPageTitle($this->file->getTitle());
                    if (PHPWS_Settings::get('finc', 'show_description'))
                        $this->description = $this->file->getDescription(true);
                    $this->content = $this->file->getContents();
                } else {
                    $this->title = dgettext('finc', 'Inactive File.');
                    if (!Current_User::allow('finc')) {
                        $this->content = dgettext('finc', 'This file has been deactivated by an admin.');
                    } else {
                        $this->content = $this->file->getContents();
                    }
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['DESCRIPTION'] = $this->description;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'finc', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'finc', 'main_user.tpl'));
        }

    }


    function sendMessage()
    {
        Core\Core::reroute('index.php?module=finc&amp;uop=message');
    }

    function forwardMessage($message, $title=null)
    {
        $_SESSION['FINC_Message']['message'] = $message;
        if ($title) {
            $_SESSION['FINC_Message']['title'] = $title;
        }
    }


    function loadMessage()
    {
        if (isset($_SESSION['FINC_Message'])) {
            $this->message = $_SESSION['FINC_Message']['message'];
            if (isset($_SESSION['FINC_Message']['title'])) {
                $this->title = $_SESSION['FINC_Message']['title'];
            }
            Core\Core::killSession('FINC_Message');
        }
    }


    function loadForm($type)
    {
        Core\Core::initModClass('finc', 'FINC_Forms.php');
        $this->forms = new Finc_Forms;
        $this->forms->finc = & $this;
        $this->forms->get($type);
    }


    function loadFile($id=0)
    {
        Core\Core::initModClass('finc', 'FINC_File.php');

        if ($id) {
            $this->file = new Finc_File($id);
        } elseif (isset($_REQUEST['file_id'])) {
            $this->file = new Finc_File($_REQUEST['file_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->file = new Finc_File($_REQUEST['id']);
        } else {
            $this->file = new Finc_File;
        }
    }


    function loadPanel()
    {
        Core\Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('finc-panel');
        $link = 'index.php?module=finc&aop=menu';

        if (Current_User::isUnrestricted('finc')) {
            $tags['new'] = array('title'=>dgettext('finc', 'New File'),
                                 'link'=>$link);
            $tags['list'] = array('title'=>dgettext('finc', 'List Files'),
                                  'link'=>$link);
            $tags['settings'] = array('title'=>dgettext('finc', 'Settings'),
                                  'link'=>$link);
            $tags['info'] = array('title'=>dgettext('finc', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    function postFile()
    {
        $this->loadFile();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('finc', 'You must give this finc file a title.');
        } else {
            $this->file->setTitle($_POST['title']);
        }

        if (empty($_POST['path']) || $_POST['path'] == 'files/finc/') {
            $errors[] = dgettext('finc', 'You must provide the path and filename of this finc file.');
        } else {
            $this->file->setPath($_POST['path']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('finc', 'You must give this finc file a description.');
        } else {
            $this->file->setDescription($_POST['description']);
        }


        if (Current_User::isUnrestricted('finc')) {
            if (isset($_POST['active'])) {
                $this->file->setActive(1);
            } else {
                $this->file->setActive(0);
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_file');
            return false;
        } else {
            return true;
        }

    }


    function postSettings()
    {

        isset($_POST['show_title']) ?
            PHPWS_Settings::set('finc', 'show_title', 1) :
            PHPWS_Settings::set('finc', 'show_title', 0);

        isset($_POST['add_title_tag']) ?
            PHPWS_Settings::set('finc', 'add_title_tag', 1) :
            PHPWS_Settings::set('finc', 'add_title_tag', 0);

        isset($_POST['show_description']) ?
            PHPWS_Settings::set('finc', 'show_description', 1) :
            PHPWS_Settings::set('finc', 'show_description', 0);

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('finc')) {
                return true;
            } else {
                return falsel;
            }
        }

    }




}
?>