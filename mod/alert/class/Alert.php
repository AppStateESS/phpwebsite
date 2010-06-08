<?php
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define('APST_NONE',   0);
define('APST_WEEKLY', 1);
define('APST_DAILY',  2);
define('APST_PERM',   3);

class Alert {
    /**
     * First three variables display the content of the module
     */
    public $title      = null;
    public $content    = null;
    public $message    = null;

    /**
     * An alert item
     */
    public $item = null;

    /**
     * An alert type object
     */
    public $type = null;

    public $type_list = null;

    /**
     * The forms object. Initialized with loadForms.
     */
    public $forms = null;
    public $panel = null;
    public $rssfeed = null;

    public function user()
    {
        if (isset($_GET['rssfeed'])) {
            $command = 'rss';
            $this->rssfeed = strip_tags($_GET['rssfeed']);
        } elseif (isset($_GET['uop'])) {
            $command = & $_GET['uop'];
        } elseif (!empty($_GET['id'])) {
            $command = 'view';
        } else {
            \core\Core::home();
        }

        switch($command) {
            case 'rss':
                $this->showRSS();
                break;

            case 'view':
                $this->loadItem();
                if ($this->item->id && $this->item->active) {
                    Layout::add($this->item->view());
                } else {
                    $this->title = dgettext('alert', 'Sorry');
                    $this->content = dgettext('alert', 'Alert could not be located.');
                }
                break;
        }
        $tpl['TITLE']   = $this->title;
        $tpl['MESSAGE'] = $this->message;
        $tpl['CONTENT'] = $this->content;

        Layout::add(core\Template::process($tpl, 'alert', 'user.tpl'));
    }

    public function viewItems()
    {
        Layout::addStyle('alert');
        $high_alert = false;

        $this->loadTypes();

        if (empty($this->type_list)) {
            return;
        }

        $db = new \core\DB('alert_item');
        $db->loadClass('alert', 'Alert_Item.php');
        $db->setIndexBy('id');
        $db->addOrder('create_date desc');

        foreach ($this->type_list as $type) {
            $content = null;
            $db->resetWhere();
            $db->addWhere('active', 1);
            $db->addWhere('type_id', $type->id);
            $alert_type = null;

            switch ((int)$type->post_type) {
                case APST_DAILY:
                    $alert_type = 'daily_alerts';
                    $db->addWhere('create_date', time() - 86400, '>');
                    break;

                case APST_WEEKLY:
                    $alert_type = 'weekly_alerts';
                    $db->addWhere('create_date', time() - (86400 * 7), '>');
                    break;

                case APST_PERM:
                    $alert_type = 'high_alerts';
                    break;

                default:
                case APST_NONE:
                    continue;
            }

            if (empty($alert_type)) {
                continue;
            }

            $result = $db->getObjects('Alert_Item');

            if (core\Error::logIfError($result)) {
                continue;
            }

            if (empty($result) && !empty($type->default_alert)) {
                $tpl['CONTENT'] = $type->getDefaultAlert();
            } else {
                foreach($result as $item) {
                    $content[] = $item->view();
                }
                $high_alert = true;
                $tpl['CONTENT'] = implode('', $content);
            }

            $tpl['TITLE'] = $type->title;
            $tpl['CLASS'] = sprintf('alert-type-%s', $type->id);

            Layout::add(core\Template::process($tpl, 'alert', 'view_type.tpl'), 'alert', $alert_type, true);
            if ($high_alert) {
                return;
            }
        }
    }

    public function loadTypes()
    {
        $db = new \core\DB('alert_type');
        $db->addOrder('post_type desc');
        $db->loadClass('alert', 'Alert_Type.php');
        $db->setIndexBy('id');
        $result = $db->getObjects('Alert_Type');

        if (core\Error::logIfError($result)) {
            $this->type_list = null;
            return;
        }
        $this->type_list = & $result;
    }


    public function admin()
    {
        if (!Current_User::allow('alert')) {
            Current_User::disallow();
            return;
        }

        $this->loadMessage();

        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $command = $_REQUEST['aop'];
        }

        if ($command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
            case 'edit_item':
                $this->loadItem();
                $this->loadForms();
                $this->forms->editItem();
                break;

            case 'list':
                $this->panel->setCurrentTab('list');
                $this->loadForms();
                $this->forms->manageItems();
                break;

            case 'post_item':
                $this->loadItem();
                if ($this->postItem()) {
                    // need to process after save
                    if (core\Error::logIfError($this->item->save())) {
                        $this->sendMessage(dgettext('alert', 'An error occurred. Could not save alert.'), 'list');
                    } else {
                        $this->sendMessage(dgettext('alert', 'Alert saved.'), 'list');
                    }
                } else {
                    $this->loadForms();
                    $this->forms->editItem();
                }
                break;

            case 'delete_item':
                $this->loadItem();
                if ($this->item->delete()) {
                    $this->sendMessage(dgettext('alert', 'Deleted alert.'), 'list');
                } else {
                    $this->sendMessage(dgettext('alert', 'Could not delete alert.'), 'list');
                }
                break;

            case 'reset_item':
                $this->loadItem();
                if ($this->item->reset()) {
                    $this->sendMessage(dgettext('alert', 'Reset alert.'), 'list');
                } else {
                    $this->sendMessage(dgettext('alert', 'Could not reset alert.'), 'list');
                }
                break;

            case 'delete_type':
                $this->loadType();
                if ($this->type->delete()) {
                    $this->sendMessage(dgettext('alert', 'Deleted alert type.'), 'types');
                } else {
                    $this->sendMessage(dgettext('alert', 'Could not delete alert type.'), 'types');
                }
                break;

            case 'deactivate_item':
                $this->loadItem();
                $this->item->active = 0;
                \core\Error::logIfError($this->item->save());
                \core\Core::goBack();
                break;

            case 'activate_item':
                $this->loadItem();
                $this->item->active = 1;
                \core\Error::logIfError($this->item->save());
                \core\Core::goBack();
                break;

            case 'types':
                $this->loadForms();
                $this->forms->manageTypes();
                break;

            case 'edit_type':
                $this->loadType();
                $this->loadForms();
                $this->forms->editType();
                break;

            case 'send_email':
                if (!Current_User::authorized('alert', 'allow_contact')) {
                    Current_User::disallow();
                }
                $this->loadItem();
                $this->sendContact();
                $this->js_display();
                break;

            case 'participants':
                $this->loadForms();
                $this->forms->manageParticipants();
                break;

            case 'settings':
                $this->loadForms();
                $this->forms->settings();
                break;

            case 'post_multiple_adds':
                $this->postMultipleAdds();
                javascript('close_refresh');
                Layout::nakedDisplay();
                break;

            case 'post_multiple_subtracts':
                $this->postMultipleSubtracts();
                javascript('close_refresh');
                Layout::nakedDisplay();
                break;

            case 'post_settings':
                $this->postSettings();
                $this->loadForms();
                $this->forms->settings();
                break;

            case 'assign_participants':
                $this->assignParticipants();
                \core\Core::goBack();
                break;

            case 'remove_all_participants':
                $this->removeAllParticipants();
                \core\Core::goBack();
                break;

            case 'add_all_participants':
                $this->addAllParticipants();
                \core\Core::goBack();
                break;

            case 'subtract_multiple':
                $this->loadForms();
                $this->forms->subtractMultiple();
                $this->js_display();
                break;

            case 'add_multiple':
                $this->loadForms();
                $this->forms->addMultiple();
                $this->js_display();
                break;

            case 'post_type':
                $this->loadType();
                if ($this->postType()) {
                    if (core\Error::logIfError($this->type->save())) {
                        $this->sendMessage(dgettext('alert', 'An error occurred. Could not save alert type.'), 'types');
                    } else {
                        $this->sendMessage(dgettext('alert', 'Type saved.'), 'types');
                    }
                } else {
                    $this->loadForms();
                    $this->forms->editType();
                }
                break;
        }

        Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->getMessage())));
    }


    public function assignParticipants()
    {
        if (empty($_POST['type_id'])) {
            return;
        }

        $db = new \core\DB('alert_prt_to_type');
        $count = 0;

        $add = isset($_POST['add_checked_participants']);
        $remove = isset($_POST['remove_checked_participants']);

        foreach ($_POST['type_id'] as $type_id=>$participants) {
            $db->reset();
            if ($add) {
                foreach ($participants as $prt) {
                    $db->addValue('type_id', $type_id);
                    $db->addValue('prt_id', $prt);
                    \core\Error::logIfError($db->insert());
                    $db->resetValues();
                }
            } elseif ($remove) {
                $db->addWhere('type_id', $type_id);
                $db->addWhere('prt_id', $participants);
                \core\Error::logIfError($db->delete());
            }
        }
    }

    public function removeAllParticipants()
    {
        if (!isset($_GET['type_id']) || !is_numeric($_GET['type_id'])) {
            return;
        }

        $type_id = & $_GET['type_id'];
        $db = new \core\DB('alert_prt_to_type');
        $db->addWhere('type_id', $type_id);
        \core\Error::logIfError($db->delete());
    }

    public function addAllParticipants()
    {
        if (!isset($_GET['type_id']) || !is_numeric($_GET['type_id'])) {
            return;
        }

        $type_id = & $_GET['type_id'];

        $db = new \core\DB('alert_participant');
        $db->addColumn('id');
        $participants = $db->select('col');
        if (core\Error::logIfError($participants) || empty($participants)) {
            return;
        }

        $db = new \core\DB('alert_prt_to_type');
        $db->addWhere('type_id', $type_id);
        \core\Error::logIfError($db->delete());

        $db->reset();
        foreach ($participants as $id) {
            $db->resetValues();
            $db->addValue('type_id', $type_id);
            $db->addValue('prt_id', $id);
            \core\Error::logIfError($db->insert());
        }
    }

    public function js_display()
    {
        $tpl['TITLE'] = $this->title;
        $tpl['MESSAGE'] = $this->getMessage();
        $tpl['CONTENT'] = $this->content;
        Layout::nakedDisplay(core\Template::process($tpl, 'alert', 'main.tpl'));
    }

    public function sendMessage($message, $aop)
    {
        $_SESSION['Alert_Message'] = $message;
        \core\Core::reroute(core\Text::linkAddress('alert', array('aop'=>$aop), true));
    }

    public function loadMessage()
    {
        if (isset($_SESSION['Alert_Message'])) {
            $this->addMessage($_SESSION['Alert_Message']);
            \core\Core::killSession('Alert_Message');
        }
    }

    public function loadItem()
    {
        \core\Core::initModClass('alert', 'Alert_Item.php');
        if (isset($_REQUEST['id'])) {
            $this->item = new Alert_Item($_REQUEST['id']);
            if (!$this->item->id) {
                $this->addMessage(dgettext('alert', 'Could not locate alert item.'));
            }
        } else {
            $this->item = new Alert_Item;
        }
    }

    public function loadTypeByFeed()
    {
        \core\Core::initModClass('alert', 'Alert_Type.php');
        $db = new \core\DB('alert_type');
        $db->addWhere('feedname', $this->rssfeed);
        $db->setLimit(1);
        $row = $db->select('row');
        if ($row) {
            if (core\Error::logIfError($row)) {
                $this->type = null;
                return false;
            } else {
                $this->type = new Alert_Type;
                \core\Core::plugObject($this->type, $row);
                return true;
            }
        }
    }

    public function loadType($type_id=0)
    {
        \core\Core::initModClass('alert', 'Alert_Type.php');

        if (!$type_id && isset($_REQUEST['type_id'])) {
            $type_id = & $_REQUEST['type_id'];
        }

        if ($type_id) {
            $this->type = new Alert_Type($type_id);
            if (!$this->type->id) {
                $this->addMessage(dgettext('alert', 'Could not locate alert type.'));
            }
        } else {
            $this->type = new Alert_Type;
        }
    }

    public function loadForms()
    {
        \core\Core::initModClass('alert', 'Alert_Forms.php');
        $this->forms = new Alert_Forms;
        $this->forms->alert = & $this;
    }

    public function loadPanel()
    {
        $this->panel = new PHPWS_Panel('alert');
        $link = 'index.php?module=alert&amp;aop=main';

        $tabs['list']  = array('title'=>dgettext('alert', 'Alerts'), 'link'=>$link,
                               'link_title'=>dgettext('alert', 'List all alerts in the system.'));
        $tabs['types'] = array('title'=>dgettext('alert', 'Alert Types'), 'link'=>$link,
                               'link_title'=>dgettext('alert', 'Create, update and define alert types.'));

        $tabs['participants'] = array('title'=>dgettext('alert', 'Participants'), 'link'=>$link,
                               'link_title'=>dgettext('alert', 'Add/Remove Alert participants.'));


        $tabs['settings'] = array('title'=>dgettext('alert', 'Settings'), 'link'=>$link,
                                  'link_title'=>dgettext('alert', 'Display settings for Alert module.'));

        $this->panel->quickSetTabs($tabs);
    }

    public function getTypes($mode='form')
    {
        $db = new \core\DB('alert_type');
        switch ($mode) {
            case 'form':
                $db->addColumn('id');
                $db->addColumn('title');
                $db->setIndexBy('id');
                $types = $db->select('col');
                break;

            case 'obj':
            default:
                $db->loadClass('alert', 'Alert_Type.php');
                $types = $db->getObjects('Alert_Type');
        }

        if (!empty($types) || \core\Error::isError($types)) {
            return $types;
        } else {
            return null;
        }
    }

    public function addMessage($message)
    {
        $this->message[] = $message;
    }

    public function getMessage()
    {
        if (empty($this->message)) {
            return null;
        } else {
            return implode('<br />', $this->message);
        }
    }

    public function postItem()
    {
        $allgood = true;

        if (!isset($_POST['type_id'])) {
            $this->addMessage(dgettext('alert', 'Error: Missing alert type id.'));
            return false;
        }

        $item = & $this->item;

        $item->image_id = (int)$_POST['image_id'];
        $item->type_id = (int)$_POST['type_id'];

        if (empty($_POST['title'])) {
            $this->addMessage(dgettext('alert', 'Please give your alert a title.'));
            $allgood = false;
        } else {
            $item->setTitle($_POST['title']);
        }

        if (empty($_POST['description'])) {
            $this->addMessage(dgettext('alert', 'Please give your alert a description.'));
            $allgood = false;
        } else {
            $item->setDescription($_POST['description']);
        }

        return $allgood;
    }

    public function postType()
    {
        $allgood = true;

        $type = & $this->type;
        if (empty($_POST['title'])) {
            $this->addMessage(dgettext('alert', 'Please give your alert type a title.'));
            $allgood = false;
        } else {
            $type->setTitle($_POST['title']);
        }

        $type->email     = isset($_POST['email']);
        $type->rssfeed   = isset($_POST['rssfeed']);

        if (!empty($_POST['feedname'])) {
            $type->setFeedName($_POST['feedname']);
        } else {
            $type->setFeedName($type->title);
        }

        if ($type->rssfeed && empty($type->feedname)) {
            $this->addMessage(dgettext('alert', 'Please give your rss feed an access name.'));
            $allgood = false;
        }

        $type->post_type = $_POST['post_type'];
        $type->setDefaultAlert($_POST['default_alert']);

        return $allgood;
    }

    public function postSettings()
    {
        if (empty($_POST['date_format'])) {
            $this->addMessage(dgettext('alert', 'Date format can not be empty.'));
            $settings['date_format'] = '%c';
        } else {
            $settings['date_format'] = strip_tags(trim($_POST['date_format']));
        }

        if (empty($_POST['email_batch_number']) || (int)$_POST['email_batch_number'] < 10) {
            $this->addMessage(dgettext('alert', 'Your email batch must be greater than 10.'));
        } else {
            $settings['email_batch_number'] = (int)$_POST['email_batch_number'];
        }

        if (empty($_POST['contact_reply_address']) || !core\Text::isValidInput($_POST['contact_reply_address'], 'email')) {
            $this->addMessage(dgettext('alert', 'Please enter an acceptable contact email address.'));
        } else {
            $settings['contact_reply_address'] = $_POST['contact_reply_address'];
        }

        \core\Settings::set('alert', $settings);
        \core\Settings::save('alert');
    }

    public function contactNeeded()
    {
        $db = new \core\DB('alert_item');
        $db->loadClass('alert', 'Alert_Item.php');
        $db->addWhere('alert_type.email', 1, '=', 'and', 1);
        $db->addWhere('alert_item.type_id', 'alert_type.id', '=', 'and', 1);
        $db->addWhere('active', 1);
        $db->addWhere('contact_complete', 2, '<');

        return $db->getObjects('Alert_Item');
    }

    public function sendContact()
    {
        $this->title = sprintf(dgettext('alert', 'Send Notices for %s'), $this->item->title);
        $item = & $this->item;
        if (!$item->id || $item->contact_complete == 2) {
            return false;
        }

        $this->loadType($item->type_id);

        if (!$this->type->id || !$this->type->email) {
            $this->content = dgettext('alert', 'An error occurred within the alert type.');
            return false;
        }

        // If contact has not started, copy all participants onto alert_contact table.
        if (!$item->contact_complete) {
            if (!isset($_SESSION['Alert_Contact_Start'])) {
                $_SESSION['Alert_Contact_Start'] = true;
                $this->content = dgettext('alert', 'Copying participant list. Please wait.');
                Layout::metaRoute(core\Core::getCurrentUrl(), 0);
                return;
            }
            $result = Alert::copyContacts();
            if (core\Error::logIfError($result)) {
                $this->content = dgettext('alert', 'An error occurred when trying to copy participants to the contact list.');
                return false;
            } elseif (!$result) {
                $this->content = dgettext('alert', 'No participants have been assigned to this alert type. No emails were sent.');
                $this->content .= '<p style="text-align : center">' . javascript('close_window') . '</p>';
                $item->contact_complete = 2;
                $item->save();
                return false;
            }
            // Set item contact_complete to 1
            $item->contact_complete = 1;
            $item->save();
            $this->content = dgettext('alert', 'Participant list created. Starting to send emails.');
            Layout::metaRoute(core\Core::getCurrentUrl(), 0);
            \core\Core::killSession('Alert_Contact_Start');
            return;
        }

        
        if (!isset($_SESSION['Total_Participants'])) {
            $db = new \core\DB('alert_contact');
            $db->addWhere('item_id', $item->id);
            $db->addColumn('prt_id');
            $result = $db->count();
            if (core\Error::logIfError($result)) {
                $this->content = dgettext('alert', 'An error occurred when trying to determine the total number of participants.');
                return false;
            }
            $_SESSION['Total_Participants'] = $result;
        }

        $batch = new Batches('email_participants');
        $batch->setTotalItems($_SESSION['Total_Participants']);
        $batch_set = \core\Settings::get('alert', 'email_batch_number');
        if (empty($batch_set)) {
            $this->content = dgettext('alert', 'Cannot continue processing batches. The batch has been set to zero.');
            return false;
        }
        $batch->setBatchSet($batch_set);

        if (!$batch->load()) {
            $batch->nextPage();
            return true;
        }

        // Grab alert_contact participants, limit by batch
        // I don't care about the batch start because I am
        // deleting the records as I go.
        $limit = $batch->getLimit();

        $db = new \core\DB('alert_contact');
        $db->addWhere('item_id', $item->id);
        $db->setLimit($limit);
        $db->addColumn('prt_id');
        $db->addColumn('email');
        $db->setIndexBy('prt_id');
        $result = $db->select('col');
        $graph = $batch->getGraph();

        $content[] = $graph;

        if (!empty($result)) {
            foreach ($result as $prt_id=>$email) {
                if (!$this->_emailParticipant($email)) {
                    $error = true;
                }
                $db->reset();
                $db->addWhere('prt_id', $prt_id);
                $db->addWhere('item_id', $item->id);
                $db->delete();
            }
        }

        $batch->completeBatch();

        // If no more results from contact_complete, we are finished.
        if ($batch->isFinished()) {
            $batch->clear();
            $content[] = dgettext('alert', 'All participants contacted.');
            $content[] = dgettext('alert', 'You may safely close this window now.');
            $content[] = sprintf('<p style="text-align : center"><input type="button" onclick="closeWindow()" value="%s" /></p>',
            dgettext('alert', 'Close this window'));
            $content[] = javascript('close_refresh', array('use_link'=>true));
            $item->contact_complete = 2;
            $this->content = implode('<br />', $content);
            $item->save();
        } else {
            if ($error) {
                $content[] = dgettext('alert', 'Notice!!! An error occurred in the last batch. Check your logs.');
            }
            $content[] = '<p style="font-weight : bold; text-align : center">' . dgettext('alert', 'Email in progress. Do not close this window.') . '</p>';
            $this->content = implode('<br />', $content);
            $batch->nextPage();
        }

        // Set item contact_complete to 2
    }

    public function _emailParticipant($email_address)
    {
                $subject = sprintf('%s: %s', $this->type->title, $this->item->title);

        $mail = new PHPWS_Mail;
        $mail->addSendTo($email_address);
        $mail->setSubject($subject);
        $mail->setFrom(core\Settings::get('alert', 'contact_reply_address'));
        $mail->setReplyTo(core\Settings::get('alert', 'contact_reply_address'));

        $mail->setHTMLBody($this->item->getHTML());
        $mail->setMessageBody($this->item->getBody());
        if (core\Error::logIfError($mail->send())) {
            return false;
        } else {
            return true;
        }
    }

    public function copyContacts()
    {
        $db = new \core\DB('alert_participant');
        $db->addWhere('item_id', $this->item->id);
        $db->delete();
        $db->resetWhere();

        $db->addWhere('alert_prt_to_type.type_id', $this->type->id);
        $db->addWhere('id', 'alert_prt_to_type.prt_id');
        $result = $db->select();
        if (empty($result) || \core\Error::logIfError($result)) {
            return $result;
        }

        $db = new \core\DB('alert_contact');

        $count = 1;

        foreach ($result as $prt) {
            $db->addValue('prt_id', $prt['id']);
            $db->addValue('email', $prt['email']);
            $db->addValue('item_id', $this->item->id);
            $result = $db->insert();
            if (core\Error::isError($result)) {
                return $result;
            }
            $count++;
        }

        return true;
    }

    public function postMultipleAdds()
    {
        if (empty($_POST['multiple'])) {
            return;
        }

        $addresses = explode("\n", $_POST['multiple']);

        if (empty($addresses)) {
            return;
        }

        $db = new \core\DB('alert_participant');
        foreach ($addresses as $email) {
            $email = trim($email);
            if (!core\Text::isValidInput($email, 'email')) {
                continue;
            }
            $db->resetValues();
            $db->addValue('email', $email);
            \core\Error::logIfError($db->insert());
        }

    }


    public function postMultipleSubtracts()
    {
        if (empty($_POST['multiple'])) {
            return;
        }

        $addresses = explode("\n", $_POST['multiple']);

        if (empty($addresses)) {
            return;
        }

        $db = new \core\DB('alert_participant');
        foreach ($addresses as $email) {
            $email = trim($email);
            $db->resetValues();
            $db->addWhere('email', $email);
            \core\Error::logIfError($db->delete());
        }
    }

    public function showRSS()
    {
        if (!$this->loadTypeByFeed() || empty($this->type)) {
            echo dgettext('alert', 'RSS feed does not exist.');
            exit();
        }

        $items = $this->type->getItems();
        if (core\Error::logIfError($items) || empty($items)) {
            exit();
        }

        foreach ($items as $item) {
            $feeds[] = $item->createFeed();
        }
        \core\Core::initModClass('rss', 'Channel.php');
        $channel = new RSS_Channel;
        $channel->_feeds = $feeds;
        $channel->module = 'alert';
        $channel->title = $this->type->title;
        $channel->description = '';
        $channel->pub_date = time();
        header('Content-type: text/xml');
        echo $channel->view();
        exit();
    }

}

?>