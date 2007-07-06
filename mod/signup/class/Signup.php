<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireInc('signup', 'errordefines.php');

class Signup {
    var $forms   = null;
    var $panel   = null;
    var $title   = null;
    var $message = null;
    var $content = null;
    var $sheet   = null;
    var $slot    = null;
    var $peep    = null;

    function adminMenu()
    {
        if (!Current_User::allow('signup')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;

        switch($_REQUEST['aop']) {
        case 'add_slot_peep':
            $javascript = true;
            $this->loadSlot();
            $this->loadPeep();
            $this->loadForm('edit_peep');
            break;

        case 'menu':
            if (!isset($_GET['tab'])) {
                $this->loadForm('list');
            } else {
                $this->loadForm($_GET['tab']);
            }
            break;

        case 'delete_sheet':
            $this->loadSheet();
            $this->sheet->delete();
            $this->message = dgettext('signup', 'Signup sheet deleted.');
            $this->loadForm('list');
            break;
            
        case 'edit_sheet':
            $this->loadForm('edit_sheet');
            break;

        case 'post_sheet':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }

            if (PHPWS_Core::isPosted()) {
                $this->message = dgettext('signup', 'Sheet previously posted.');
                $this->loadForm('edit_sheet');
            } else {
                if ($this->postSheet()) {
                    if (PHPWS_Error::logIfError($this->sheet->save())) {
                        $this->message = dgettext('signup', 'Error occurred when saving sheet.');
                        $this->loadForm('list');
                    } else {
                        $this->message = dgettext('signup', 'Sheet saved successfully.');
                        $this->loadForm('edit_slots');
                    }
                } else {
                    $this->loadForm('edit');
                }
            }
            break;

        case post_slot:
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }

            if ($this->postSlot()) {
                if (PHPWS_Error::logIfError($this->slot->save())) {
                    $this->message = dgettext('signup', 'Error occurred when saving slot.');
                } else {
                    $this->message = dgettext('signup', 'Slot saved successfully.');
                }

            }
            $this->loadForm('edit_slots');
            break;
            
        case 'edit_slots':
            $this->loadSheet();
            $this->loadForm('edit_slots');
            break;
        }


        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'signup', 'main.tpl'));
        } else {
            $this->panel->setContent(PHPWS_Template::process($tpl, 'signup', 'main.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }

    function loadForm($type)
    {
        PHPWS_Core::initModClass('signup', 'Forms.php');
        $this->forms = new Signup_Forms;
        $this->forms->signup = & $this;
        $this->forms->get($type);
    }

    function loadPeep()
    {
        PHPWS_Core::initModClass('signup', 'Peeps.php');
        if (isset($_REQUEST['peep_id'])) {
            $this->peep = new Signup_Peep($_REQUEST['peep_id']);
        } else {
            $this->peep = new Signup_Peep;
        }
    }

    function loadSheet()
    {
        PHPWS_Core::initModClass('signup', 'Sheet.php');
        if (isset($_REQUEST['sheet_id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['sheet_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['id']);
        } else {
            $this->sheet = new Signup_Sheet;
        }
    }

    function loadSlot()
    {
        PHPWS_Core::initModClass('signup', 'Slots.php');
        if (isset($_REQUEST['slot_id'])) {
            $this->slot = new Signup_Slot($_REQUEST['slot_id']);
        } else {
            $this->slot = new Signup_Slot;
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

    function postSlot()
    {
        $this->loadSheet();
        $this->loadSlot();

        $this->slot->setTitle($_POST['title']);

        if (empty($this->slot->title)) {
            $errors[] = dgettext('signup', 'You must give your slot a title.');
        }

        $this->slot->setOpenings($_POST['openings']);
        if (empty($this->slot->openings)) {
            $errors[] = dgettext('signup', 'Please specify an opening amount.');
        }

        $this->slot->setSheetId($_POST['sheet_id']);

        if (empty($this->slot->sheet_id)) {
            $errors[] = dgettext('signup', 'Fatal error: Cannot create slot. Missing sheet id.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
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