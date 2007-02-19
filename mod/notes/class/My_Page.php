<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('notes');
PHPWS_Core::initModClass('notes', 'Note_Item.php');

class Notes_My_Page {
    var $title   = null;
    var $content = null;
    var $message = null;

    var $errors  = null;

    function Notes_My_Page()
    {
        if (isset($_SESSION['Note_Message'])) {
            $this->message = $_SESSION['Note_Message'];
            unset($_SESSION['Note_Message']);
        }
    }

    function main()
    {
        $js = false;

        if (isset($_REQUEST['op'])) {
            $command = & $_REQUEST['op'];
        } else {
            $command = 'read';
        }

        switch ($command) {
        case 'delete_note':
            $note = new Note_Item((int)$_REQUEST['id']);
            $result = $note->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }

            if (isset($_REQUEST['js'])) {
                Layout::nakedDisplay(javascript('close_refresh'));
                exit();
            }

            $this->message = _('Message deleted.');
            $this->read();
            break;

        case 'read':
            $this->read();
            break;

        case 'read_note':
            $js = javascriptEnabled();
            $note = new Note_Item((int)$_REQUEST['id']);

            $content = $note->read();
            if ($js) {
                Layout::nakedDisplay($content);
            } else {
                return $content;
            }
            break;

        case 'send_note':
            $js = javascriptEnabled();
            $note = new Note_Item;
            $this->sendNote($note);
            break;

        case 'post_note':
            if (javascriptEnabled()) {
                $js = 1;
            }

            $note = new Note_Item;
            $result = $this->postNote($note);

            if (is_array($result)) {
                $this->sendNote($note, $result);
            } elseif (!$result) {
                $this->message = implode('<br />', $this->errors);
                $this->sendNote($note);
            } else {
                if ($note->save()) {
                    $this->sendMessage(_('Note sent successfully.'), $js);
                } else {
                    $this->sendMessage(_('Note was not sent successfully.'), $js);
                }
            }
            break;

        default:
            PHPWS_Core::errorPage('404');
        }

        $tpl['TITLE'] =  $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        
        if ($js) {
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'notes', 'main.tpl'));
        } else {
            return PHPWS_Template::process($tpl, 'notes', 'main.tpl');
        }
    }

    function miniAdminLink($key)
    {
        $vars = Notes_My_Page::myPageVars(false);
        $vars['op'] = 'send_note';
        $vars['key_id'] = $key->id;
        if (javascriptEnabled()) {
            $js_vars['address'] = PHPWS_Text::linkAddress('users', $vars);
            $js_vars['label']   = _('Associate note');
            $js_vars['width']   = 640;
            $js_vars['height']  = 480;
            MiniAdmin::add('notes', javascript('open_window', $js_vars));
        } else {
            MiniAdmin::add('notes', PHPWS_Text::moduleLink(_('Associate note'), 'users', $vars));
        }
    }

    function myPageVars($include_mod=true)
    {
        $vars = array('action' => 'user', 'tab' => 'notes');

        if ($include_mod) {
            $vars['module'] = 'users';
        }

        return $vars;
    }

    function postNote(&$note)
    {
        $note->setTitle($_POST['title']);
        $note->setContent($_POST['content']);

        $note->sender_id = Current_User::getId();
        if (!empty($_POST['key_id'])) {
            $note->key_id = (int)$_POST['key_id'];
        }

        if (empty($_POST['user_id'])) {
            if (empty($_POST['username'])) {
                $this->errors['missing_username'] = _('You must enter a username.');
            } elseif (!Current_User::allowUsername($_POST['username'])) {
                $this->errors['bad_username'] = _('Unsuitable user name characters.');
            } else {
                $db = new PHPWS_DB('users');
                $db->addWhere('display_name', $_POST['username']);
                if (Current_User::allow('notes', 'search_usernames')) {
                    $db->addWhere('username', $_POST['username'], null, 'or');
                }

                $db->addColumn('id');
                $db->addColumn('display_name');
                $db->setIndexBy('id');
                $result = $db->select('col');

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $this->errors['unknown'] = _('An error occurred when accessing the database.');
                } 

                if (empty($result)) {
                    if (NOTE_ALLOW_USERNAME_SEARCH) {
                        $db->resetWhere();
                        $db->addWhere('display_name', '%' . $_POST['username'] . '%', 'like');
                        if (Current_User::allow('notes', 'search_usernames')) {
                            $db->addWhere('username', '%' . $_POST['username'] . '%', 'like', 'or');
                        }

                        $result = $db->select('col');

                        if (PEAR::isError($result)) {
                            PHPWS_Error::log($result);
                            $this->errors['unknown'] = _('An error occurred when accessing the database.');
                        } elseif (empty($result)) {
                            $this->errors['no_match'] = _('Could not find match.');
                        } else {
                            $note->username = $_POST['username'];
                            return $result;
                        }
                    } else {
                        $this->errors['no_match'] = _('Unknown user.');
                    }
                } else {
                    list($note->user_id, $note->username) = each($result);
                } 
            }
        } else {
            if (empty($_POST['title']) && empty($_POST['content'])) {
                $this->errors['no_content'] = _('You need to enter a title or some content.');
            }
            
            $user = new PHPWS_User($_POST['user_id']);

            if ($user->id) {
                $note->user_id = $user->id;
                $note->username = $user->username;
            } else {
                $this->errors['bad_user_id'] = _('Unable to resolve user name.');
            }
        }

        if (!empty($this->errors)) {
            return false;
        } else {
            return true;
        }
    }

    function read()
    {
        unset($_SESSION['Notes_Unread']);
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('notes', 'Note_Item');
        $pager->setModule('notes');
        $pager->setTemplate('read.tpl');
        $pager->setEmptyMessage(_('No notes found.'));
        $pager->addWhere('user_id', Current_User::getId());
        $pager->setOrder('date_sent', 'desc', true);

        $page_tags['TITLE_LABEL'] = _('Title');
        $page_tags['DATE_SENT_LABEL'] = _('Date sent');
        $page_tags['SEND_LINK'] = Note_Item::sendLink();

        $pager->addPageTags($page_tags);
        $pager->addRowTags('getTags');
        $this->title = _('Read notes');
        $this->content = $pager->get();
    }
    
    function sendMessage($message, $js=false)
    {
        $_SESSION['Note_Message'] = $message;
        if ($js) {
            javascript('close_refresh');
            Layout::nakedDisplay();
        } else {
            PHPWS_Core::reroute('index.php?module=users&action=user&tab=notes');
            exit();
        }
    }


    function sendNote(&$note, $users=null)
    {
        $form = new PHPWS_Form('send_note');

        $form->addHidden($this->myPageVars());
        $form->addHidden('op', 'post_note');

        if (isset($_REQUEST['key_id'])) {
            $key = new Key($_REQUEST['key_id']);
            if ($key->id) {
                $form->addHidden('key_id', $key->id);
                $assoc = sprintf(_('Associate note to item: %s'), $key->title);
                $form->addTplTag('KEY_ASSOCIATION', $assoc);
            }
        }

        if (isset($_REQUEST['user_id'])) {
            $user = new PHPWS_User((int)$_REQUEST['user_id']);
            if ($user->id) {
                $note->user_id  = $user->id;
                $note->username = $user->username;
            }
        }


        if (javascriptEnabled()) {
            $form->addHidden('js', 1);
            $form->addTplTag('CANCEL', javascript('close_window', array('value' =>_('Cancel'))));
        }

        if (isset($users) && is_array($users)) {
            $new_users = array(0 => _('- Search again -')) + $users;
            $form->addSelect('user_id', $new_users);
        }

        $form->addText('username', $note->username);
        $form->setLabel('username', _('Recipient'));

        $form->addText('title', $note->title);
        $form->setLabel('title', _('Title'));
        $form->setSize('title', 45);

        $form->addTextArea('content', $note->content);
        $form->setLabel('content', _('Message'));
        $form->setRows('content', 10);
        $form->setCols('content', 50);

        /*
        $form->addCheck('encrypted', 1);
        $form->setMatch('encrypted', $note->encrypted);
        $form->setLabel('encrypted', _('Encrypt message?'));
        */

        $form->addSubmit(_('Send note'));

        $tpl = $form->getTemplate();

        $this->title = _('Send note');
        $this->content = PHPWS_Template::process($tpl, 'notes', 'send_note.tpl');
    }

    function showAssociations($key)
    {
        $db = new PHPWS_DB('notes');
        $db->addWhere('user_id', Current_User::getId());
        $db->addWhere('key_id', $key->id);
        $db->addOrder('date_sent', 'desc');
        $notes = $db->getObjects('Note_Item');

        if (empty($notes)) {
            return;
        }

        foreach ($notes as $note) {
            $content[] = $note->readLink();
        }
        $tpl['TITLE'] = _('Associated Notes');
        $tpl['CONTENT'] = implode('<br />', $content);
        Layout::add(PHPWS_Template::process($tpl, 'layout', 'box.tpl'), 'notes', 'reminder');
    }

    function showUnread()
    {
        if ( isset($_SESSION['Notes_Unread']) && ( $_SESSION['Notes_Unread']['last_check'] + (NOTE_CHECK_INTERVAL * 60) >=  mktime() ) ) {
            $notes = $_SESSION['Notes_Unread']['last_count'];
        } else {
            $db = new PHPWS_DB('notes');
            $db->addWhere('user_id', Current_User::getId());
            $db->addWhere('read_once', 0);
            $notes = $db->count();
            if (PEAR::isError($notes)) {
                PHPWS_Error::log($notes);
                return;
            }
            $_SESSION['Notes_Unread']['last_check'] = mktime();
            $_SESSION['Notes_Unread']['last_count'] = &$notes;
        }

        if ($notes) {
            $tpl['TITLE'] = _('Notes');
            $link_val = sprintf(_('You have %d unread notes.'), $notes);
            $val = Notes_My_Page::myPageVars(false);
            $tpl['CONTENT'] = PHPWS_Text::moduleLink($link_val, 'users', $val);
            $content = PHPWS_Template::process($tpl, 'layout', 'box.tpl');
            Layout::add($content, 'notes', 'reminder');
        }

    }

}


?>