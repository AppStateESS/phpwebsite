<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::requireInc('signup', 'errordefines.php');
PHPWS_Core::requireConfig('signup');

if (!defined('SIGNUP_WINDOW')) {
    define('SIGNUP_WINDOW', 3600);
}

class Signup {
    public $forms   = null;
    public $panel   = null;
    public $title   = null;
    public $message = null;
    public $content = null;
    public $sheet   = null;
    public $slot    = null;
    public $peep    = null;
    public $email   = null;

    public function adminMenu()
    {
        if (!Current_User::allow('signup')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;

        $this->loadMessage();

        $command = $_REQUEST['aop'];

        switch($command) {
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

        case 'edit_peep_popup':
            $javascript = true;
            $this->loadSlot();
            $this->loadForm('edit_peep_popup');
            break;

        case 'print_applicants':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadSheet();
            $this->printApplicants();
            exit();
            break;

        case 'email_applicants':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadEmail();
            $this->loadSheet();
            $this->loadForm('email_applicants');
            break;

        case 'post_email':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadEmail();
            $this->loadSheet();
            if ($this->postEmail()) {
                $this->sendEmail();
            } else {
                $this->loadForm('email_applicants');
            }
            break;

        case 'slot_listing':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadSheet();
            $this->slotListing();
            exit();
            break;

        case 'csv_applicants':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadSheet();
            $this->csvExport();
            exit();
            break;

        case 'send_email':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->sendEmail();
            break;

        case 'edit_slots':
            $this->loadSheet();
            $this->loadForm('edit_slots');
            break;

        case 'search_slot':
            $this->searchSlots();
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
                javascript('close_refresh');
                Layout::nakedDisplay();
            } else {
                $this->loadForm('edit_peep');
            }
            break;

        case 'post_sheet':
            $this->loadSheet();
            if (!Current_User::authorized('signup', 'edit_sheet', $this->sheet->id, 'sheet')) {
                Current_User::disallow();
            }

            if ($this->postSheet()) {
                if (!$this->sheet->id && PHPWS_Core::isPosted()) {
                    $this->message = dgettext('signup', 'Sheet previously posted.');
                    $this->loadForm('edit_sheet');
                } else {
                    $new_sheet = !$this->sheet->id;
                    if (PHPWS_Error::logIfError($this->sheet->save())) {
                        $this->forwardMessage(dgettext('signup', 'Error occurred when saving sheet.'));
                        PHPWS_Core::reroute('index.php?module=signup&aop=list');
                    } else {
                        $this->forwardMessage(dgettext('signup', 'Sheet saved successfully.'));
                        if ($new_sheet) {
                            PHPWS_Core::reroute('index.php?module=signup&aop=edit_slots&sheet_id=' . $this->sheet->id);
                        } else {
                            $this->loadForm('list');
                        }
                    }
                }
            } else {
                $this->loadForm('edit_sheet');
            }
            break;


        case 'post_slot':
            $javascript = true;
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }

            if ($this->postSlot()) {
                if (PHPWS_Error::logIfError($this->slot->save())) {
                    $this->forwardMessage(dgettext('signup', 'Error occurred when saving slot.'));
                } else {
                    $this->forwardMessage(dgettext('signup', 'Slot saved successfully.'));
                }
                javascript('close_refresh');
                Layout::nakedDisplay();
            } else {
                $this->loadForm('edit_slot_popup');
            }
            break;

        case 'move_peep':
            $this->loadPeep();
            $result = $this->movePeep();
            if (PHPWS_Error::logIfError($result) || !$result) {
                $this->forwardMessage(dgettext('signup', 'Error occurred when moving applicant. Slot may be full.'));
            }
            PHPWS_Core::goBack();
            break;

        case 'move_up':
            $this->loadSlot();
            $this->slot->moveUp();
            PHPWS_Core::goBack();
            break;

        case 'move_down':
            $this->loadSlot();
            $this->slot->moveDown();
            PHPWS_Core::goBack();
            break;


        case 'delete_slot':
            $this->loadSlot();
            $this->deleteSlot();
            break;

        case 'delete_slot_peep':
            $this->loadPeep();
            $this->peep->delete();
            PHPWS_Core::goBack();
            break;


        case 'report':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadSheet();
            $this->loadForm('report');
            break;

        case 'alpha_order':
        case 'reset_slot_order':
            if (!Current_User::authorized('signup')) {
                Current_User::disallow();
            }
            $this->loadSheet();
            $this->resetSlots($command);
            $this->forwardMessage(dgettext('signup', 'Slot order reset.'));
            PHPWS_Core::reroute('index.php?module=signup&sheet_id=' . $this->sheet->id . '&aop=edit_slots&authkey=' . Current_User::getAuthKey());
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

    public function resetSlots($mode)
    {
        $db = new PHPWS_DB('signup_slots');
        $db->addWhere('sheet_id', $this->sheet->id);
        $db->addColumn('id');
        if ($mode == 'alpha_order') {
            $db->addOrder('title');
        } else {
            $db->addOrder('s_order');
        }

        $slots = $db->select('col');

        if (empty($slots)) {
            return true;
        } elseif (PHPWS_Error::logIfError($slots)) {
            return false;
        }
        $count = 1;
        foreach ($slots as $id) {
            $db->reset();
            $db->addWhere('id', $id);
            $db->addValue('s_order', $count);
            PHPWS_Error::logIfError($db->update());
            $count++;
        }

        return true;
    }

    public function sendMessage()
    {
        PHPWS_Core::reroute('index.php?module=signup&amp;uop=message');
    }

    public function forwardMessage($message, $title=null)
    {
        $_SESSION['SU_Message']['message'] = $message;
        if ($title) {
            $_SESSION['SU_Message']['title'] = $title;
        }
    }

    public function loadEmail()
    {
        $this->email['from'] = null;
        $this->email['subject'] = null;
        $this->email['message'] = null;
    }

    public function postEmail()
    {
        if (!PHPWS_Text::isValidInput($_POST['from'], 'email')) {
            $errors[] = dgettext('signup', 'Invalid reply address.');
        } else {
            $this->email['from'] = & $_POST['from'];
        }

        $subject = trim(strip_tags($_POST['subject']));
        if (empty($subject)) {
            $errors[] = dgettext('signup', 'Please enter a subject.');
        } else {
            $this->email['subject'] = & $subject;
        }

        $message = trim(strip_tags($_POST['message']));

        if (empty($message)) {
            $errors[] = dgettext('signup', 'Please enter a message.');
        } else {
            $this->email['message'] = & $message;
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['SU_Message'])) {
            $this->message = $_SESSION['SU_Message']['message'];
            if (isset($_SESSION['SU_Message']['title'])) {
                $this->title = $_SESSION['SU_Message']['title'];
            }
            PHPWS_Core::killSession('SU_Message');
        }
    }

    public function loadForm($type)
    {
        PHPWS_Core::initModClass('signup', 'Forms.php');
        $this->forms = new Signup_Forms;
        $this->forms->signup = $this;
        $this->forms->get($type);
    }

    public function loadPeep($id=0)
    {
        PHPWS_Core::initModClass('signup', 'Peeps.php');
        if ($id) {
            $this->peep = new Signup_Peep($id);
        } elseif (isset($_REQUEST['peep_id'])) {
            $this->peep = new Signup_Peep($_REQUEST['peep_id']);
        } else {
            $this->peep = new Signup_Peep;
            if (isset($_SESSION['SU_Temp_Peep'])) {
                extract($_SESSION['SU_Temp_Peep']);
                $this->peep->first_name = $first_name;
                $this->peep->last_name  = $last_name;
                $this->peep->email      = $email;
                $this->peep->phone      = $phone;
                PHPWS_Core::killSession('SU_Temp_Peep');
            }
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

    public function loadSheet($id=0)
    {
        PHPWS_Core::initModClass('signup', 'Sheet.php');
        if ($id) {
            $this->sheet = new Signup_Sheet($id);
        } elseif (isset($_REQUEST['sheet_id'])) {
            $this->sheet = new Signup_Sheet($_REQUEST['sheet_id']);
        } else {
            $this->sheet = new Signup_Sheet;
        }
    }

    public function loadSlot($id=0)
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

    public function userMenu($action=null)
    {
        $javascript = false;
        if (empty($action)) {
            if (!isset($_REQUEST['uop'])) {
                PHPWS_Core::errorPage('404');
            }

            $action = $_REQUEST['uop'];
        }

        switch ($action) {
        case 'message':
            $this->loadMessage();
            if (empty($this->message)) {
                PHPWS_Core::home();
            }
            $this->title = dgettext('signup', 'Signup');
            break;

        case 'signup_sheet':
            $this->loadPeep();
            $this->loadForm('user_signup');
            break;

        case 'slot_signup':
            if ($this->postPeep()) {
                if ($this->saveUnregistered()) {
                    $this->forwardMessage(dgettext('signup', 'You should receive an email allowing you to verify your application.<br />You have one hour to confirm your application.'), dgettext('signup', 'Thank you'));
                    $this->sendMessage();
                } else {
                    $this->loadForm('user_signup');
                }
            } else {
                $this->loadForm('user_signup');
            }
            break;

        case 'confirm':
            $this->confirmPeep();
            $this->purgeOverdue();
            break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['MESSAGE'] = $this->message;
        $tpl['CONTENT'] = $this->content;

        if ($javascript) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'signup', 'usermain.tpl'));
        } else {
            Layout::add(PHPWS_Template::process($tpl, 'signup', 'usermain.tpl'));
        }

    }

    public function saveUnregistered()
    {
        $peep = & $this->peep;
        $slot = & $this->slot;

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('slot_id', $peep->slot_id);

        // lock carries over to saving of peep.
        $db->setLock('signup_peeps', 'read');

        if ($this->sheet->multiple) {
            $previous = false;
        } else {
            $db->addColumn('id', null, null, true);
            $db->addWhere('registered', 1);
            $filled = $db->select('one');
            $db->reset();
            $db->addWhere('sheet_id', $peep->sheet_id);
            $db->addWhere('email', $peep->email);
            $db->addColumn('id');
            $previous = $db->select('one');
        }

        if (PHPWS_Error::logIfError($previous)) {
            $this->forwardMessage(dgettext('signup', 'An error occurred when trying to save your application.'), dgettext('signup', 'Sorry'));
            $this->sendMessage();
            return false;
        } elseif ($previous) {
            $this->forwardMessage(dgettext('signup', 'You cannot signup for more than one slot.'), dgettext('signup', 'Sorry'));
            $this->sendMessage();
            return false;
        }

        if ($slot->openings <= $filled) {
            $this->message = dgettext('signup', 'Sorry, the slot you chose is no longer available.');
            return false;
        }

        $peep->registered = 0;
        $peep->hashcheck = md5(rand());
        $peep->timeout = mktime() + SIGNUP_WINDOW;

        if (PHPWS_Error::logIfError($peep->save())) {
            $db->unlockTables();
            return false;
        } else {
            // success
            $db->unlockTables();
            if (PHPWS_Error::logIfError($this->emailRegistration())) {
                $peep->delete();
                $this->forwardMessage(dgettext('signup', 'There is a problem with our email server. Please try again later.'), dgettext('signup', 'Sorry'));
                $this->sendMessage();
                return false;
            } else {
                return true;
            }
        }
    }

    public function emailRegistration()
    {
        $peep  = & $this->peep;
        $sheet = & $this->sheet;
        $slot  = & $this->slot;

        PHPWS_Core::initCoreClass('Mail.php');
        $full_name = $peep->first_name . $peep->last_name;

        if (preg_match('@["\'\.]@', $full_name)) {
            $name = str_replace('"', "'", $peep->first_name . ' ' . $peep->last_name);
            $send_to = sprintf('"%s" <%s>', $name, $peep->email);
        } else {
            $send_to = sprintf('%s %s <%s>', $peep->first_name, $peep->last_name, $peep->email);
        }

        $subject = dgettext('signup', 'Signup confirmation');

        if (!empty($sheet->contact_email)) {
            $reply_to = $from = $sheet->contact_email;
        } else {
            $reply_to = $from = PHPWS_Settings::get('users', 'site_contact');
        }

        $site_title = Layout::getPageTitle(true);
        $link = PHPWS_Core::getHomeHttp() . 'index.php?module=signup&uop=confirm&h=' .
            $peep->hashcheck . '&p=' . $peep->id;

        $message[] = sprintf(dgettext('signup', 'Greetings from %s,'), $site_title);
        $message[] = '';
        $message[] = dgettext('signup', 'Click the link below to confirm your participation in the following:');
        $message[] = '';
        $message[] = sprintf(dgettext('signup', 'Signup event : %s'), $sheet->title);
        $message[] = sprintf(dgettext('signup', 'Slot : %s'), $slot->title);
        $message[] = $link;
        $message[] = '';
        $message[] = dgettext('signup', 'You have one hour to confirm your application.');

        $mail = & new PHPWS_Mail;

        $mail->addSendTo($send_to);
        $mail->setSubject($subject);
        $mail->setFrom($from);
        $mail->setReplyTo($reply_to);
        $mail->setMessageBody(implode("\n", $message));
        return $mail->send();
    }

    /**
     * Sends everyone (limited by search) in a specific sheet an email
     */
    public function sendEmail()
    {
        PHPWS_Core::initCoreClass('Mail.php');

        if (!isset($_SESSION['Email_Applicants'])) {
            $_SESSION['Email_Applicants']['email'] = & $this->email;
            $_SESSION['Email_Applicants']['sheet_id'] = $this->sheet->id;
            $_SESSION['Email_Applicants']['search'] = @ $_REQUEST['search'];
            $vars['aop'] = 'send_email';
            Layout::metaRoute(PHPWS_Text::linkAddress('signup', $vars, true), 1);
            $this->title = dgettext('signup', 'Sending emails');
            $this->content = dgettext('signup', 'Please wait');
            return;
        }

        $email_session = & $_SESSION['Email_Applicants'];

        $mail = new PHPWS_Mail;
        $mail->setSubject($email_session['email']['subject']);
        $mail->setFrom($email_session['email']['from']);
        $mail->setReplyTo($email_session['email']['from']);
        $mail->setMessageBody($email_session['email']['message']);
        $mail->sendIndividually(true);

        $this->loadSheet($email_session['sheet_id']);

        if (!$this->sheet->id) {
            $this->title = dgettext('signup', 'Sorry');
            $this->content = dgettext('signup', 'Unable to send emails. Signup sheet does not exist.');
            PHPWS_Core::killSession('Email_Applicants');
            return;
        }

        $db = new PHPWS_DB('signup_peeps');
        $db->addColumn('email');
        $db->addWhere('sheet_id', $this->sheet->id);

        if (isset($email_session['search'])) {
            $search = explode('+', $email_session['search']);
            foreach ($search as $s) {
                $db->addWhere('first_name', "%$s%", 'like', 'or', 1);
                $db->addWhere('last_name',  "%$s%", 'like', 'or', 1);
                $db->addWhere('organization',  "%$s%", 'like', 'or', 1);
            }
        }

        $result = $db->select('col');
        if (empty($result)) {
            $this->title = dgettext('signup', 'Emails not sent');
            $this->content = dgettext('signup', 'Signup sheet did not contain any applicants.');
            return;
        } elseif (PHPWS_Error::logIfError($result)) {
            $this->title = dgettext('signup', 'Emails not sent');
            $this->content = dgettext('signup', 'An error occurred when pulling applicants.');
            return;
        }

        foreach ($result as $address) {
            $mail->addSendTo($address);
        }

        $mail->send();

        $vars['aop'] = 'report';
        $vars['sheet_id'] = $this->sheet->id;
        $link = PHPWS_Text::linkAddress('signup', $vars, true);

        $this->title = dgettext('signup', 'Emails sent');
        $this->content = dgettext('signup', 'Returning to applicant listing.');
        Layout::metaRoute($link, 5);
        PHPWS_Core::killSession('Email_Applicants');
    }


    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('signup-panel');
        $link = 'index.php?module=signup&aop=menu';

        if (Current_User::isUnrestricted('signup')) {
            $tags['new'] = array('title'=>dgettext('signup', 'New'),
                                 'link'=>$link);
        }
        $tags['list'] = array('title'=>dgettext('signup', 'List'),
                              'link'=>$link);
        $this->panel->quickSetTabs($tags);
    }

    public function postPeep()
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

        if (empty($this->peep->phone)) {
            $errors[] = dgettext('signup', 'Please enter a contact phone number.');
        }

        if (empty($_POST['organization'])) {
            $this->organization = null;
        } else {
            $this->peep->setOrganization($_POST['organization']);
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

    public function postSlot()
    {
        $this->loadSlot();
        $this->slot->setTitle($_POST['title']);

        if (empty($this->slot->title)) {
            $errors[] = dgettext('signup', 'You must give your slot a title.');
        }

        $openings = (int)$_POST['openings'];

        if (empty($openings)) {
            $errors[] = dgettext('signup', 'Please specify an openings amount.');
        } elseif ($this->slot->id) {
            $db = new PHPWS_DB('signup_peeps');
            $db->addWhere('slot_id', $this->slot->id);
            $db->addColumn('id', null, null, true);
            $peeps = $db->select('one');
            if ($peeps > $openings) {
                $errors[] = dgettext('signup', 'The total signups may not exceed the slots offered.');
            } else {
                $this->slot->openings = $openings;
            }
        } else {
            $this->slot->openings = $openings;
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

    public function postSheet()
    {
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
            $this->sheet->defaultStart();
        } else {
            $this->sheet->start_time = strtotime($_POST['start_time']);
            if ($this->sheet->start_time < mktime(0,0,0,1,1,1970)) {
                $this->sheet->defaultStart();
            }
        }

        if (empty($_POST['contact_email'])) {
            $this->sheet->contact_email = null;
        } else {
            $this->sheet->contact_email = $_POST['contact_email'];
            if (!PHPWS_Text::isValidInput($this->sheet->contact_email, 'email')) {
                $errors[] = dgettext('signup', 'Contact email improperly formatted.');
            }
        }

        if (empty($_POST['end_time'])) {
            $this->sheet->defaultEnd();
        } else {
            $this->sheet->end_time = strtotime($_POST['end_time']);
            if ($this->sheet->end_time < mktime(0,0,0,1,1,1970)) {
                $this->sheet->defaultEnd();
            }
        }

        if (isset($_POST['multiple'])) {
            $this->sheet->multiple = 1;
        } else {
            $this->sheet->multiple = 0;
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }

    public function confirmPeep()
    {
        if (!isset($_REQUEST['h']) || !isset($_REQUEST['p'])) {
            return false;
        }

        $hash = & $_REQUEST['h'];
        $id = & $_REQUEST['p'];
        $this->loadPeep($id);

        if ($this->peep->registered) {
            $this->title = dgettext('signup', 'Congratulations!');
            $this->content = dgettext('signup', 'You are already registered. There isn\'t any need to return to this page.');
            return;
        }

        if (!$this->peep->id ||
            $this->peep->hashcheck != $hash ||
            $this->peep->timeout < mktime()) {
            $this->title = dgettext('signup', 'Sorry');
            $this->content = dgettext('signup', 'Your application could not be verified. If over a hour has passed since you applied, you may want to try again.');
            return;
        } else {
            $slots_filled = $this->sheet->totalSlotsFilled();

            if ($slots_filled && isset($slots_filled[$this->slot->id])) {
                if ($this->slot->openings <= $slots_filled[$this->slot->id]) {
                    $this->title = dgettext('signup', 'Sorry');
                    $content[] = dgettext('signup', 'This slot filled up before you could confirm your application.');
                    $content[] = dgettext('signup', 'Please check if there are any more available slots by clicking the link below.');
                    $content[] = $this->sheet->viewLink();
                    $this->content = implode('<br />', $content);
                    $_SESSION['SU_Temp_Peep'] = array('first_name'=> $this->peep->first_name,
                                                      'last_name' => $this->peep->last_name,
                                                      'email'     => $this->peep->email,
                                                      'phone'     => $this->peep->phone);
                    return;
                }
            }

            $this->peep->registered = 1;
            if (PHPWS_Error::logIfError($this->peep->save())) {
                $this->title = dgettext('signup', 'Sorry');
                $this->content = dgettext('signup', 'A problem occurred when trying to register your application. If you continue to receive this message, please contact the site admistrator.');
                return;
            } else {
                $this->title = dgettext('signup', 'Congratulations!');
                $this->content = sprintf(dgettext('signup', 'You are registered for the following event: %s'), $this->sheet->title);
                return;
            }
        }
    }

    public function slotListing()
    {
        $slots = $this->sheet->getAllSlots();

        $tpl = new PHPWS_Template('signup');
        $tpl->setFile('report.tpl');

        foreach ($slots as $slot) {
            $slot->loadPeeps();

            $peep_count = 0;
            if ($slot->_peeps) {
                foreach ($slot->_peeps as $peep) {
                    $tpl->setCurrentBlock('peeps');
                    $tpl->setData(array('FIRST_NAME' => $peep->first_name,
                                        'LAST_NAME' => $peep->last_name));
                    $tpl->parseCurrentBlock();
                    $peep_count++;
                }
            }

            $openings_left = $slot->openings - $peep_count;

            if ($openings_left) {
                for($i=0; $i < $openings_left; $i++) {
                    $tpl->setCurrentBlock('spaces');
                    $tpl->setData(array('SPACE' => '&nbsp;'));
                    $tpl->parseCurrentBlock();
                }
            }

            $tpl->setCurrentBlock('slot');
            $tpl->setData(array('SLOT_TITLE' => $slot->title,
                                'PRINT'=>dgettext('signup', 'Print page')));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData(array('REPORT_TITLE' => $this->sheet->title));

        echo $tpl->get();
        exit();
    }

    public function csvExport()
    {
        PHPWS_Core::initModClass('signup', 'Peeps.php');

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('sheet_id', $this->sheet->id);
        $db->addWhere('registered', 1);

        $result = $db->getObjects('Signup_Peep');
        $data[] = sprintf('"%s","%s","%s","%s","%s"',
                          dgettext('signup', 'firstname'), dgettext('signup', 'lastname'),
                          dgettext('signup', 'phone'), dgettext('signup', 'email'),
                          dgettext('signup', 'organization'));
        if (!empty($result)) {
            foreach ($result as $peep) {
                $data[] = sprintf('"%s","%s","%s","%s","%s"',
                                  $peep->first_name, $peep->last_name,
                                  $peep->getPhone(), $peep->email,
                                  $peep->organization);
            }
        }

        $content = implode("\n", $data);
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');
        echo $content;
        exit();
    }

    public function printApplicants()
    {
        PHPWS_Core::initModClass('signup', 'Peeps.php');

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('sheet_id', $this->sheet->id);
        $db->addWhere('registered', 1);

        if (isset($_REQUEST['orderby'])) {
            $db->addOrder($_REQUEST['orderby'] . ' ' . $_REQUEST['orderby_dir']);
        }

        if (isset($_REQUEST['search'])) {
            $search = explode('+', $_REQUEST['search']);
            foreach ($search as $s) {
                $db->addWhere('first_name', "%$s%", 'like', 'or', 1);
                $db->addWhere('last_name',  "%$s%", 'like', 'or', 1);
                $db->addWhere('organization',  "%$s%", 'like', 'or', 1);
            }
        }

        $result = $db->getObjects('Signup_Peep');
        if (!empty($result)) {
            foreach ($result as $peep) {
                $tpl['FIRST_NAME']   = $peep->first_name;
                $tpl['LAST_NAME']    = $peep->last_name;
                $tpl['PHONE']        = $peep->getPhone();
                $tpl['EMAIL']        = $peep->email;
                $tpl['ORGANIZATION'] = $peep->organization;
                $template['rows'][] = $tpl;
            }
        }

        $template['NAME_LABEL']         = dgettext('signup', 'Name');
        $template['PHONE_LABEL']        = dgettext('signup', 'Phone');
        $template['ORGANIZATION_LABEL'] = dgettext('signup', 'Organization');
        $template['EMAIL_LABEL']        = dgettext('signup', 'Email');
        $template['REPORT_TITLE']       = dgettext('signup', 'Applicant Listing');
        $template['SHEET_TITLE']        = $this->sheet->title;
        $template['PRINT']              = sprintf('<input type="button" id="print" value="%s" onclick="print_page()" />', dgettext('signup', 'Print'));

        echo PHPWS_Template::process($template, 'signup', 'print_applicants.tpl');
        exit();
    }


    public function purgeOverdue()
    {
        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('registered', 0);
        $db->addWhere('timeout', mktime(), '<');
        PHPWS_Error::logIfError($db->delete());
    }

    public function movePeep()
    {
        $this->loadSlot($_POST['mv_slot']);
        $current_openings = $this->slot->currentOpenings();
        if ($current_openings < 1) {
            return false;
        } else {
            $this->peep->slot_id = $this->slot->id;
            return $this->peep->save();
        }
    }

    public function deleteSlot()
    {
        $openings = $this->slot->currentOpenings();
        if ($openings == $this->slot->openings) {
            if ($this->slot->delete()) {
                $this->title = dgettext('signup', 'Slot deleted successfully.');
            } else {
                $this->title = dgettext('signup', 'Slot could not be deleted successfully.');
            }
        } else {
            $this->title = dgettext('signup', 'Slot can not be deleted until cleared of applicants.');
        }
        $this->content = PHPWS_Text::secureLink(dgettext('signup', 'Return to slot page'), 'signup',
                                                array('sheet_id'=>$this->sheet->id, 'aop'=>'edit_slots'));

    }
}

?>