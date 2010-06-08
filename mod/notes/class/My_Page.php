<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

Core\Core::requireConfig('notes');
Core\Core::initModClass('notes', 'Note_Item.php');

class Notes_My_Page {
    public $title   = null;
    public $content = null;
    public $message = null;

    public $errors  = null;

    public function __construct()
    {
        if (isset($_SESSION['Note_Message'])) {
            $this->message = $_SESSION['Note_Message'];
            unset($_SESSION['Note_Message']);
        }
    }

    public function main()
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
            if (Core\Error::isError($result)) {
                Core\Error::log($result);
            }

            if (isset($_REQUEST['js'])) {
                Layout::nakedDisplay(javascript('close_refresh'));
                exit();
            }

            $this->message = dgettext('notes', 'Message deleted.');
            $this->read();
            break;

        case 'read':
            $this->read();
            break;

        case 'read_note':
            Layout::addStyle('notes', 'note_style.css');
            $note = new Note_Item((int)$_REQUEST['id']);
            Layout::nakedDisplay($note->read(), dgettext('notes', 'Read note'), true);
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
            if (!$result) {
                $this->message = implode('<br />', $this->errors);
                $this->sendNote($note);
            } else {
                if ($note->save()) {
                    $this->sendMessage(dgettext('notes', 'Note sent successfully.'), $js);
                } else {
                    $this->sendMessage(dgettext('notes', 'Note was not sent successfully.'), $js);
                }
            }
            break;

        default:
            Core\Core::errorPage('404');
        }

        $tpl['TITLE'] =  $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($js) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'notes', 'main.tpl'), null, true);
        } else {
            return Core\Template::process($tpl, 'notes', 'main.tpl');
        }
    }

    public static function miniAdminLink($key)
    {
        $vars = Notes_My_Page::myPageVars(false);
        $vars['op'] = 'send_note';
        $vars['key_id'] = $key->id;
        if (javascriptEnabled()) {
            $js_vars['address'] = Core\Text::linkAddress('users', $vars);
            $js_vars['label']   = dgettext('notes', 'Associate note');
            $js_vars['width']   = 650;
            $js_vars['height']  = 650;
            MiniAdmin::add('notes', javascript('open_window', $js_vars));
        } else {
            MiniAdmin::add('notes', Core\Text::moduleLink(dgettext('notes', 'Associate note'), 'users', $vars));
        }
    }

    public static function myPageVars($include_mod=true)
    {
        $vars = array('action' => 'user', 'tab' => 'notes');

        if ($include_mod) {
            $vars['module'] = 'users';
        }

        return $vars;
    }

    public function postNote(Note_Item $note)
    {
        if (empty($_POST['title'])) {
            $this->errors['missing_title'] = dgettext('notes', 'Your note needs a title.');
        }

        if (!$_POST['uid'] && !preg_match('/[^\w\s\.]/', $_POST['username'])) {
            $db = new Core\DB('users');
            $db->addWhere('username', $_POST['username']);
            $db->addColumn('id');

            $user_id = $db->select('one');

            if ($user_id) {
                $note->setUserId($user_id);
            } else {
                $db->resetWhere();
                $db->addWhere('display_name', $_POST['username']);
                if ($user_id = $db->select('one')) {
                    $note->setUserId($user_id);
                } else {
                    $note->setUserId(0);
                }
            }
        } else {
            $note->setUserId($_POST['uid']);
        }

        $user = new PHPWS_User($note->user_id);

        $note->setTitle($_POST['title']);
        $note->setContent($_POST['content']);
        $note->sender_id = Current_User::getId();
        $note->sender = Current_User::getDisplayName();

        if (!$user->id) {
            $this->errors['bad_user_id'] =  dgettext('notes', 'Unable to resolve user name.');
        } else {
            $note->username = $user->display_name;
        }

        if (empty($note->title) && empty($note->content)) {
            $this->errors['no_content'] = dgettext('notes', 'You need to enter a title or some content.');
        }

        if (!empty($_POST['key_id'])) {
            $note->key_id = (int)$_POST['key_id'];
        }

        if (!empty($this->errors)) {
            return false;
        } else {
            return true;
        }
    }

    public function read()
    {
        Layout::addStyle('notes');
        unset($_SESSION['Notes_Unread']);
                $pager = new Core\DBPager('notes', 'Note_Item');
        $pager->setModule('notes');
        $pager->setTemplate('read.tpl');
        $pager->setEmptyMessage(dgettext('notes', 'No notes found.'));
        $pager->addWhere('user_id', Current_User::getId());
        $pager->setOrder('date_sent', 'desc', true);

        $page_tags['TITLE_LABEL'] = dgettext('notes', 'Title');
        $page_tags['DATE_SENT_LABEL'] = dgettext('notes', 'Date sent');
        $page_tags['SEND_LINK'] = Note_Item::sendLink();

        $pager->addPageTags($page_tags);
        $pager->addRowTags('getTags');
        $this->title = dgettext('notes', 'Read notes');
        $this->content = $pager->get();
    }

    public function sendMessage($message, $js=false)
    {
        $_SESSION['Note_Message'] = $message;
        if ($js) {
            javascript('close_refresh');
            Layout::nakedDisplay();
        } else {
            Core\Core::reroute('index.php?module=users&action=user&tab=notes');
            exit();
        }
    }


    public function sendNote(Note_Item $note)
    {
        Layout::addStyle('notes');
        $form = new Core\Form('send_note');

        $form->addHidden($this->myPageVars());
        $form->addHidden('op', 'post_note');

        if (isset($_REQUEST['key_id'])) {
            $key = new Core\Key($_REQUEST['key_id']);
            if ($key->id) {
                $form->addHidden('key_id', $key->id);
                $assoc = sprintf(dgettext('notes', 'Associate note to item: %s'), $key->title);
                $form->addTplTag('KEY_ASSOCIATION', $assoc);
            }
        }

        if (isset($_REQUEST['uid'])) {
            $user = new PHPWS_User((int)$_REQUEST['uid']);
            if ($user->id) {
                $note->user_id  = $user->id;
                $note->username = $user->display_name;
            }
        }

        $form->addHidden('uid', $note->user_id);

        $form->addHidden('js', 1);
        $form->addTplTag('CANCEL', javascript('close_window', array('value' =>dgettext('notes', 'Cancel'))));
        javascript('jquery');
        javascriptMod('notes', 'search_user');

        $form->addText('username', $note->username);
        $form->setLabel('username', dgettext('notes', 'Recipient'));

        $form->addText('title', $note->title);
        $form->setLabel('title', dgettext('notes', 'Title'));
        $form->setSize('title', 45);

        $form->addTextArea('content', $note->content);
        $form->useEditor('content', true, true, 0, 0, 'tinymce');
        $form->setLabel('content', dgettext('notes', 'Message'));
        $form->setRows('content', 10);
        $form->setCols('content', 50);

        $form->addSubmit(dgettext('notes', 'Send note'));

        $tpl = $form->getTemplate();

        $this->title = dgettext('notes', 'Send note');
        $this->content = Core\Template::process($tpl, 'notes', 'send_note.tpl');
    }

    public static function showAssociations($key)
    {
        $db = new Core\DB('notes');
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
        $tpl['TITLE'] = dgettext('notes', 'Associated Notes');
        $tpl['CONTENT'] = implode('<br />', $content);
        Layout::add(Core\Template::process($tpl, 'layout', 'box.tpl'), 'notes', 'reminder');
    }

    public static function showUnread()
    {
        if ( isset($_SESSION['Notes_Unread']) && ( $_SESSION['Notes_Unread']['last_check'] + (NOTE_CHECK_INTERVAL * 60) >=  time() ) ) {
            $notes = $_SESSION['Notes_Unread']['last_count'];
        } else {
            $db = new Core\DB('notes');
            $db->addWhere('user_id', Current_User::getId());
            $db->addWhere('read_once', 0);
            $notes = $db->count();
            if (Core\Error::isError($notes)) {
                Core\Error::log($notes);
                return;
            }
            $_SESSION['Notes_Unread']['last_check'] = time();
            $_SESSION['Notes_Unread']['last_count'] = &$notes;
        }

        if ($notes) {
            $tpl['TITLE'] = dgettext('notes', 'Notes');
            $link_val = sprintf(dgettext('notes', 'You have %d unread notes.'), $notes);
            $val = Notes_My_Page::myPageVars(false);
            $tpl['CONTENT'] = Core\Text::moduleLink($link_val, 'users', $val);
            $content = Core\Template::process($tpl, 'layout', 'box.tpl');
            Layout::add($content, 'notes', 'reminder');
        }

    }

}


?>