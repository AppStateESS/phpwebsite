<?php
/**
 * skeleton - phpwebsite module
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

PHPWS_Core::requireInc('skeleton', 'errordefines.php');
PHPWS_Core::requireConfig('skeleton');

class Skeleton {
    public $forms      = null;
    public $panel      = null;
    public $title      = null;
    public $message    = null;
    public $content    = null;
    public $skeleton   = null;
    public $bone       = null;


    public function adminMenu()
    {
        if (!Current_User::allow('skeleton')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list_skeletons');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'new_skeleton':
            case 'edit_skeleton':
                if (!Current_User::authorized('skeleton', 'edit_skeleton')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_skeleton');
                break;

            case 'post_skeleton':
                if (!Current_User::authorized('skeleton', 'edit_skeleton')) {
                    Current_User::disallow();
                }
                if ($this->postSkeleton()) {
                    if (PHPWS_Error::logIfError($this->skeleton->save())) {
                        $this->forwardMessage(dgettext('skeleton', 'Error occurred when saving skeleton.'));
                        PHPWS_Core::reroute('index.php?module=skeleton&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('skeleton', 'Skeleton saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=skeleton&aop=menu');
                    }
                } else {
                    $this->loadForm('edit_skeleton');
                }
                break;

            case 'delete_skeleton':
                if (!Current_User::authorized('skeleton', 'delete_skeleton')) {
                    Current_User::disallow();
                }
                $this->loadSkeleton();
                $this->skeleton->delete();
                $this->message = dgettext('skeleton', 'Skeleton deleted.');
                $this->loadForm('list');
                break;


            case 'edit_bone':
                if (!Current_User::authorized('skeleton', 'edit_bone')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_bone');
                break;

            case 'post_bone':
                if (!Current_User::authorized('skeleton', 'edit_bone')) {
                    Current_User::disallow();
                }
                if ($this->postBone()) {
                    if (PHPWS_Error::logIfError($this->bone->save())) {
                        $this->forwardMessage(dgettext('skeleton', 'Error occurred when saving bone.'));
                        PHPWS_Core::reroute('index.php?module=skeleton&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('skeleton', 'Bone saved successfully.'));
                        PHPWS_Core::reroute('index.php?module=skeleton&skeleton='.$this->bone->skeleton_id);
                    }
                } else {
                    $this->loadForm('edit_bone');
                }
                break;

            case 'delete_bone':
                if (!Current_User::authorized('skeleton', 'delete_bone')) {
                    Current_User::disallow();
                }
                $this->loadBone();
                $this->bone->delete();
                $this->message = dgettext('skeleton', 'Bone deleted.');
                $this->loadForm('list');
                break;


            case 'post_settings':
                if (!Current_User::authorized('skeleton', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('skeleton', 'Skeleton settings saved.'));
                    PHPWS_Core::reroute('index.php?module=skeleton&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'skeleton', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'skeleton', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    public function userMenu($action=null)
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

            case 'view_skeleton':
                $this->loadSkeleton();
                Layout::addPageTitle($this->skeleton->getTitle());
                $this->title = $this->skeleton->getTitle(true);
                $this->content = $this->skeleton->view();
                break;

            case 'view_bone':
                $this->loadBone();
                Layout::addPageTitle($this->bone->getTitle());
                $this->title = $this->bone->getTitle(true);
                $this->content = $this->bone->view();
                break;

            case 'list_skeletons':
                PHPWS_Core::initModClass('skeleton', 'Skeleton_Forms.php');
                $this->forms = new Skeleton_Forms;
                $this->forms->skeleton = & $this;
                $this->forms->listSkeletons();
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'skeleton', 'main_user.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'skeleton', 'main_user.tpl'));
        }

    }


    public function forwardMessage($message, $title=null)
    {
        $_SESSION['Skeleton_Message']['message'] = $message;
        if ($title) {
            $_SESSION['Skeleton_Message']['title'] = $title;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['Skeleton_Message'])) {
            $this->message = $_SESSION['Skeleton_Message']['message'];
            if (isset($_SESSION['Skeleton_Message']['title'])) {
                $this->title = $_SESSION['Skeleton_Message']['title'];
            }
            PHPWS_Core::killSession('Skeleton_Message');
        }
    }


    public function loadForm($type)
    {
        PHPWS_Core::initModClass('skeleton', 'Skeleton_Forms.php');
        $this->forms = new Skeleton_Forms;
        $this->forms->skeleton = & $this;
        $this->forms->get($type);
    }


    public function loadSkeleton($id=0)
    {
        PHPWS_Core::initModClass('skeleton', 'Skeleton_Skeleton.php');

        if ($id) {
            $this->skeleton = new Skeleton_Skeleton($id);
        } elseif (isset($_REQUEST['skeleton_id'])) {
            $this->skeleton = new Skeleton_Skeleton($_REQUEST['skeleton_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->skeleton = new Skeleton_Skeleton($_REQUEST['id']);
        } elseif (isset($_REQUEST['skeleton'])) {
            $this->skeleton = new Skeleton_Skeleton($_REQUEST['skeleton']);
        } else {
            $this->skeleton = new Skeleton_Skeleton;
        }
    }


    public function loadBone($id=0)
    {
        PHPWS_Core::initModClass('skeleton', 'Skeleton_Bone.php');

        if ($id) {
            $this->bone = new Skeleton_Bone($id);
        } elseif (isset($_REQUEST['bone_id'])) {
            $this->bone = new Skeleton_Bone($_REQUEST['bone_id']);
        } elseif (isset($_REQUEST['bone'])) {
            $this->bone = new Skeleton_Bone($_REQUEST['bone']);
        } else {
            $this->bone = new Skeleton_Bone;
        }

        if (empty($this->skeleton)) {
            if (isset($this->skeleton->id)) {
                $this->loadSkeleton($this->bone->skeleton_id);
            } else {
                $this->loadSkeleton();
                $this->bone->skeleton_id = $this->skeleton->id;
            }
        }
    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('skeleton-panel');
        $link = 'index.php?module=skeleton&aop=menu';

        if (Current_User::allow('skeleton', 'edit_skeleton')) {
            $tags['new_skeleton'] = array('title'=>dgettext('skeleton', 'New Skeleton'),
                                 'link'=>$link);
        }
        $tags['list_skeletons'] = array('title'=>dgettext('skeleton', 'List Skeletons'),
                                  'link'=>$link);
        if (Current_User::allow('skeleton', 'edit_bone')) {
            $tags['new_bone'] = array('title'=>dgettext('skeleton', 'New Bone'),
                                 'link'=>$link);
        }
        $tags['list_bones'] = array('title'=>dgettext('skeleton', 'List Bones'),
                                  'link'=>$link);
        if (Current_User::allow('skeleton', 'settings', null, null, true)) {
            $tags['settings'] = array('title'=>dgettext('skeleton', 'Settings'),
                                  'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('skeleton', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postSkeleton()
    {
        $this->loadSkeleton();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('skeleton', 'You must give this skeleton a title.');
        } else {
            $this->skeleton->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('skeleton', 'You must give this skeleton a description.');
        } else {
            $this->skeleton->setDescription($_POST['description']);
        }

        if (empty($_POST['died'])) {
            $this->skeleton->died = time();
        } else {
            $this->skeleton->died = strtotime($_POST['died']);
        }

        if (isset($_POST['file_id'])) {
            $this->skeleton->setFile_id((int)$_POST['file_id']);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_skeleton');
            return false;
        } else {
            return true;
        }

    }


    public function postBone()
    {
        $this->loadBone();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('skeleton', 'You must give this bone a title.');
        } else {
            $this->bone->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $errors[] = dgettext('skeleton', 'You must give this bone a description.');
        } else {
            $this->bone->setDescription($_POST['description']);
        }

        if (isset($_POST['file_id'])) {
            $this->bone->setFile_id((int)$_POST['file_id']);
        }

        $this->bone->setSkeleton_id($_POST['skeleton_id']);

        if (empty($this->bone->skeleton_id)) {
            $errors[] = dgettext('skeleton', 'Fatal error: Cannot create bone. Missing skeleton id.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_bone');
            return false;
        } else {
            return true;
        }

    }


    public function postSettings()
    {

        isset($_POST['enable_sidebox']) ?
            PHPWS_Settings::set('skeleton', 'enable_sidebox', 1) :
            PHPWS_Settings::set('skeleton', 'enable_sidebox', 0);

        isset($_POST['sidebox_homeonly']) ?
            PHPWS_Settings::set('skeleton', 'sidebox_homeonly', 1) :
            PHPWS_Settings::set('skeleton', 'sidebox_homeonly', 0);

        if (!empty($_POST['sidebox_text'])) {
            PHPWS_Settings::set('skeleton', 'sidebox_text', PHPWS_Text::parseInput($_POST['sidebox_text']));
        } else {
            PHPWS_Settings::set('skeleton', 'sidebox_text', null);
        }

        if (isset($_POST['enable_files'])) {
            PHPWS_Settings::set('skeleton', 'enable_files', 1);
            if ( !empty($_POST['max_width']) ) {
                $max_width = (int)$_POST['max_width'];
                if ($max_width >= 50 && $max_width <= 600 ) {
                    PHPWS_Settings::set('skeleton', 'max_width', $max_width);
                }
            }
            if ( !empty($_POST['max_height']) ) {
                $max_height = (int)$_POST['max_height'];
                if ($max_height >= 50 && $max_height <= 600 ) {
                    PHPWS_Settings::set('skeleton', 'max_height', $max_height);
                }
            }
        } else {
            PHPWS_Settings::set('skeleton', 'enable_files', 0);
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (PHPWS_Settings::save('skeleton')) {
                return true;
            } else {
                return falsel;
            }
        }

    }


    public function navLinks()
    {

        $links[] = PHPWS_Text::moduleLink(dgettext('skeleton', 'List all skeletons'), 'skeleton', array('uop'=>'list_skeletons'));
        if (Current_User::allow('skeleton', 'settings', null, null, true) && !isset($_REQUEST['aop'])){
            $links[] = PHPWS_Text::moduleLink(dgettext('skeleton', 'Settings'), "skeleton",  array('aop'=>'menu', 'tab'=>'settings'));
        }

        return $links;
    }



}
?>