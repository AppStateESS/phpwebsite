<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Signup {
    var $forms   = null;
    var $panel   = null;
    var $title   = null;
    var $message = null;
    var $content = null;
    var $sheet   = null;

    function adminMenu()
    {
        if (!Current_User::allow('signup')) {
            Current_User::disallow();
        }
        $this->loadPanel();

        switch($_REQUEST['aop']) {
        case 'menu':
            if (!isset($_GET['tab'])) {
                $this->loadForm('list');
            } else {
                $this->loadForm($_GET['tab']);
            }
            break;

        case 'edit_sheet':
            $this->loadForm('edit_sheet');
            break;

        case 'post_sheet':
            if (PHPWS_Core::isPosted()) {
                $this->message = dgettext('signup', 'Sheet previously posted.');
                $this->loadForm('edit');
            } else {
                if ($this->postSheet()) {
                    if (PHPWS_Error::logIfError($this->sheet->save())) {
                        $this->message = dgettext('signup', 'Error occurred when saving sheet.');
                    } else {
                        $this->message = dgettext('signup', 'Sheet saved successfully.');
                    }
                    $this->loadForm('list');
                } else {
                    $this->loadForm('edit');
                }
            }
            break;
        }


        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        $this->panel->setContent(PHPWS_Template::process($tpl, 'signup', 'main.tpl'));
        Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
    }

    function loadForm($type)
    {
        PHPWS_Core::initModClass('signup', 'Forms.php');
        $this->forms = new Signup_Forms;
        $this->forms->signup = & $this;
        $this->forms->get($type);
    }

    function loadSheet()
    {
        PHPWS_Core::initModClass('signup', 'Sheet.php');
        if (isset($_REQUEST['s_id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['s_id']);
        } else {
            $this->sheet = new Signup_Sheet;
        }
    }

    function userMenu()
    {
        

    }


    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('signup-panel');
        $link = 'index.php?module=signup&aop=menu';
        
        $tags['new'] = array('title'=>dgettext('signup', 'New'),
                             'link'=>$link);
        $tags['list'] = array('title'=>dgettext('signup', 'List'),
                              'link'=>$link);
        $this->panel->quickSetTabs($tags);
    }

    function postSheet()
    {
        $this->loadSheet();
        if (empty($_POST['title'])) {
            $errors[] = dgettext('signup', 'You must give this signup sheet a title.');
        } else {
            $this->sheet->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $this->sheet->description = null;
        } else {
            $this->sheet->setDescription($_POST['description']);
        }

        if (empty($_POST['start_time'])) {
            $this->defaultStart();
        } else {
            $this->start_time = strtotime($_POST['start_time']);
            if ($this->start_time < mktime(0,0,0,1,1,1970)) {
                $this->defaultStart();
            }
        }

        if (empty($_POST['end_time'])) {
            $this->defaultEnd();
        } else {
            $this->end_time = strtotime($_POST['end_time']);
            if ($this->end_time < mktime(0,0,0,1,1,1970)) {
                $this->defaultEnd();
            }
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }

}

?>