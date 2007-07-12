<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireInc('signup', 'errordefines.php');

if (!defined('SIGNUP_WINDOW')) {
    define('SIGNUP_WINDOW', 3600);
}

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

        $this->loadMessage();

        switch($_REQUEST['aop']) {
        case 'add_slot_peep':
            $javascript = true;
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

        case 'edit_slot_peep':
            $javascript = true;
            $this->loadPeep();
            $this->loadForm('edit_peep');
            break;

        case 'edit_slot_popup':
            $javascript = true;
            $this->loadSlot();
            $this->loadForm('edit_slot_popup');
            break;


        case 'edit_slots':
            $this->loadSheet();
            $this->loadForm('edit_slots');
            break;

        case 'post_peep':
            $javascript = true;
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            if ($this->postPeep()) {
                // Since added by an admin, automatically registered
                $this->peep->registered = 1;
                if (PHPWS_Error::logIfError($this->peep->save())) {
                    $this->forwardMessage(dgettext('signup', 'Error occurred when saving applicant.'));
                } else {
                    $this->forwardMessage(dgettext('signup', 'Applicant saved successfully.'));
                }
            } else {
                $this->loadForm('edit_peep');
            }
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
            $javascript = true;
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }

            if ($this->postSlot()) {
                javascript('close_refresh');
                if (PHPWS_Error::logIfError($this->slot->save())) {
                    $this->forwardMessage(dgettext('signup', 'Error occurred when saving slot.'));
                } else {
                    $this->forwardMessage(dgettext('signup', 'Slot saved successfully.'));
                }
            } else {
                $this->loadForm('edit_slot_popup');
            }
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


    function forwardMessage($message)
    {
        $_SESSION['SU_Message'] = $message;
        javascript('close_refresh');
        Layout::nakedDisplay();
    }

    function loadMessage()
    {
        if (isset($_SESSION['SU_Message'])) {
            $this->message = $_SESSION['SU_Message'];
            PHPWS_Core::killSession('SU_Message');
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

        if (empty($this->slot)) {
            if ($this->peep->slot_id) {
                $this->loadSlot($this->peep->slot_id);
            } else {
                $this->loadSlot();
                $this->peep->slot_id = $this->slot->id;
            }
        }
        
        // Sheet construction will be done by the loadSlot
        if (!$this->peep->sheet_id) {
            $this->peep->sheet_id = $this->sheet->id;
        }
    }

    function loadSheet($id=0)
    {
        PHPWS_Core::initModClass('signup', 'Sheet.php');

        if ($id) {
            $this->sheet = new Signup_Sheet($id);
        } elseif (isset($_REQUEST['sheet_id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['sheet_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['id']);
        } else {
            $this->sheet = new Signup_Sheet;
        }
    }

    function loadSlot($id=0)
    {
        PHPWS_Core::initModClass('signup', 'Slots.php');
        if ($id) {
            $this->slot = new Signup_Slot($id);
        } elseif (isset($_REQUEST['slot_id'])) {
            $this->slot = new Signup_Slot($_REQUEST['slot_id']);
        } else {
            $this->slot = new Signup_Slot;
        }

        if (empty($this->sheet)) {
            if ($this->slot->sheet_id) {
                $this->loadSheet($this->slot->sheet_id);
            } else {
                $this->loadSheet();
                $this->slot->sheet_id = $this->sheet->id;
            }
        }

    }

    function userMenu($action=null)
    {
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }
            
        switch ($action) {
        case 'signup_sheet':
            $this->loadPeep();
            $this->loadForm('user_signup');
            break;

        case 'slot_signup':
            if ($this->postPeep()) {
                if ($this->saveUnregistered()) {
                    $this->title = dgettext('signup', 'Thank you');
                    $this->content = dgettext('signup', 'You should receive an email allowing you to verify your application.');
                } else {
                    $this->loadForm('user_signup');
                }
            } else {
                $this->loadForm('user_signup');
            }

            break;
            
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'signup', 'usermain.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'signup', 'usermain.tpl'));
        }

    }

    function saveUnregistered()
    {
        $peep = & $this->peep;
        $slot = & $this->slot;

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('slot_id', $peep->slot_id);

        // lock carries over to saving of peep.
        $db->setLock('signup_peeps', 'read');
        $db->addColumn('id', null, null, true);
        $db->addWhere('registered', 1);
        $filled = $db->select('one');
        $db->reset();
        $db->addWhere('email', $peep->email);
        $db->addColumn('id');
        $previous = $db->select('one');

        if (PHPWS_Error::logIfError($previous)) {
            $this->message = dgettext('signup', 'Sorry, an error occurred when trying to save your application.');
            return false;
        } elseif ($previous) {
            $this->message = dgettext('signup', 'You cannot signup for more than one slot.');
            return false;
        }

        if ($slot->openings <= $filled) {
            $this->message = dgettext('signup', 'Sorry, the slot you chose is no longer available.');
            return false;
        }
        
        $peep->registered = 0;
        $peep->hash = md5(rand());
        $peep->timeout = mktime() + SIGNUP_WINDOW;

        if (PHPWS_Error::logIfError($peep->save())) {
            $db->unlockTables();
            return false;
        } else {
            // success
            $db->unlockTables();
            $peep->emailRegistration();
            return true;
        }
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

    function postPeep()
    {
        $this->loadPeep();
        $this->peep->setFirstName($_POST['first_name']);
        $this->peep->setLastName($_POST['last_name']);

        if (empty($this->peep->first_name)) {
            $errors[] = dgettext('signup', 'Please enter a first name.');
        }

        if (empty($this->peep->last_name)) {
            $errors[] = dgettext('signup', 'Please enter a last name.');
        }

        if (empty($_POST['email']) || !PHPWS_Text::isValidInput($_POST['email'], 'email')) {
            $errors[] = dgettext('signup', 'Unsuitable email address.');
        } else {
            $this->peep->email = trim($_POST['email']);
        }

        $this->peep->setPhone($_POST['phone']);
        
        if (empty($this->peep->phone) || strlen($this->peep->phone) < 7) {
            $errors[] = dgettext('signup', 'Please enter a contact phone number.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if ($this->peep->slot_id && $this->peep->sheet_id) {
                return true;
            } else {
                $this->message = dgettext('signup', 'Missing internal information.');
                return false;
            }
        }

    }

    function postSlot()
    {
        $this->loadSlot();

        $this->slot->setTitle($_POST['title']);

        if (empty($this->slot->title)) {
            $errors[] = dgettext('signup', 'You must give your slot a title.');
        }

        $this->slot->setOpenings($_POST['openings']);
        if (empty($this->slot->openings)) {
            $errors[] = dgettext('signup', 'Please specify an openings amount.');
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
        if (empty($_P2OST['title'])) {
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