<?php
/**
 * vmail - phpwebsite module
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


class vMail {
    public $forms      = null;
    public $panel      = null;
    public $title      = null;
    public $message    = null;
    public $content    = null;
    public $recipient  = null;


    public function adminMenu()
    {
        if (!Current_User::allow('vmail')) {
            Current_User::disallow();
        }
        $this->loadPanel();
        $javascript = false;
        $this->loadMessage();

        switch($_REQUEST['aop']) {

            case 'menu':
                if (!isset($_GET['tab'])) {
                    $this->loadForm('list_recipients');
                } else {
                    $this->loadForm($_GET['tab']);
                }
                break;

            case 'new_recipient':
            case 'edit_recipient':
                if (!Current_User::authorized('vmail', 'edit_recipient')) {
                    Current_User::disallow();
                }
                $this->loadForm('edit_recipient');
                break;

            case 'post_recipient':
                if (!Current_User::authorized('vmail', 'edit_recipient')) {
                    Current_User::disallow();
                }
                if ($this->postRecipient()) {
                    if (Core\Error::logIfError($this->recipient->save())) {
                        $this->forwardMessage(dgettext('vmail', 'Error occurred when saving recipient.'));
                        Core\Core::reroute('index.php?module=vmail&aop=menu');
                    } else {
                        $this->forwardMessage(dgettext('vmail', 'Recipient saved successfully.'));
                        Core\Core::reroute('index.php?module=vmail&aop=menu');
                    }
                } else {
                    $this->loadForm('edit_recipient');
                }
                break;

            case 'activate_recipient':
                if (!Current_User::authorized('vmail', 'edit_recipient')) {
                    Current_User::disallow();
                }
                $this->loadRecipient();
                $this->recipient->active = 1;
                $this->recipient->save();
                $this->message = sprintf(dgettext('vmail', 'Recipient %s activated.'), $this->recipient->getLabel(true));
                $this->loadForm('list_recipients');
                break;

            case 'deactivate_recipient':
                if (!Current_User::authorized('vmail', 'edit_recipient')) {
                    Current_User::disallow();
                }
                $this->loadRecipient();
                $this->recipient->active = 0;
                $this->recipient->save();
                $this->message = sprintf(dgettext('vmail', 'Recipient %s deactivated.'), $this->recipient->getLabel(true));
                $this->loadForm('list_recipients');
                break;

            case 'delete_recipient':
                if (!Current_User::authorized('vmail', 'delete_recipient')) {
                    Current_User::disallow();
                }
                $this->loadRecipient();
                $this->recipient->delete();
                $this->message = sprintf(dgettext('vmail', 'Recipient %s deleted.'), $this->recipient->getLabel(true));
                $this->loadForm('list_recipients');
                break;


            case 'post_settings':
                if (!Current_User::authorized('vmail', 'settings', null, null, true)) {
                    Current_User::disallow();
                }
                if ($this->postSettings()) {
                    $this->forwardMessage(dgettext('vmail', 'vMail settings saved.'));
                    Core\Core::reroute('index.php?module=vmail&aop=menu');
                } else {
                    $this->loadForm('settings');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'vmail', 'main_admin.tpl'));
        } else {
            $this->panel->setContent(Core\Template::process($tpl, 'vmail', 'main_admin.tpl'));
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }

    }


    public function userMenu($action=null)
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

            case 'view_recipient':
                $this->loadRecipient();
                if ($this->recipient->active) {
                    Core\Core::initModClass('vmail', 'vMail_Forms.php');
                    $this->forms = new vMail_Forms;
                    $this->forms->vmail = & $this;
                    $this->forms->composeMessage();
                } else {
                    $this->title = sprintf(dgettext('vmail', '%s inactive'), Core\Settings::get('vmail', 'module_title'));
                    $this->content = dgettext('vmail', 'This recipient is currently not active.');
                }
                break;

            case 'list_recipients':
                Core\Core::initModClass('vmail', 'vMail_Forms.php');
                $this->forms = new vMail_Forms;
                $this->forms->vmail = & $this;
                $this->forms->listRecipients();
                break;

            case 'send_message':
                if ($this->checkMessage()) {
                    if (!Core\Error::logIfError($this->sendMail())) {
                        $this->loadRecipient();
                        $this->message = dgettext('vmail', 'Message sent succesfully.');
                        $this->title = sprintf(dgettext('vmail', '%s message sent'), Core\Settings::get('vmail', 'module_title'));
                        $this->content = $this->recipient->submitMessage();
                    } else {
                        $this->forwardMessage(dgettext('vmail', 'Sorry, there was a problem sending the message.'));
//                        Core\Core::reroute('index.php?module=vmail&id=' . $this->recipient->id);
                        Core\Core::reroute('index.php?module=vmail&uop=list_recipients');
                    }
                } else {
                    $this->loadForm('compose_message');
                }
                break;

        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        if ($javascript) {
            Layout::nakedDisplay(Core\Template::process($tpl, 'vmail', 'main_user.tpl'));
        } else {
            Layout::add(Core\Template::process($tpl, 'vmail', 'main_user.tpl'));
        }

    }


    public function forwardMessage($message, $title=null)
    {
        $_SESSION['vMail_Message']['message'] = $message;
        if ($title) {
            $_SESSION['vMail_Message']['title'] = $title;
        }
    }


    public function loadMessage()
    {
        if (isset($_SESSION['vMail_Message'])) {
            $this->message = $_SESSION['vMail_Message']['message'];
            if (isset($_SESSION['vMail_Message']['title'])) {
                $this->title = $_SESSION['vMail_Message']['title'];
            }
            Core\Core::killSession('vMail_Message');
        }
    }


    public function loadForm($type)
    {
        Core\Core::initModClass('vmail', 'vMail_Forms.php');
        $this->forms = new vMail_Forms;
        $this->forms->vmail = & $this;
        $this->forms->get($type);
    }


    public function loadRecipient($id=0)
    {
        Core\Core::initModClass('vmail', 'vMail_Recipient.php');

        if ($id) {
            $this->recipient = new vMail_Recipient($id);
        } elseif (isset($_REQUEST['recipient_id'])) {
            $this->recipient = new vMail_Recipient($_REQUEST['recipient_id']);
        } elseif (isset($_REQUEST['id'])) {
            $this->recipient = new vMail_Recipient($_REQUEST['id']);
        } elseif (isset($_REQUEST['recipient'])) {
            $this->recipient = new vMail_Recipient($_REQUEST['recipient']);
        } else {
            $this->recipient = new vMail_Recipient;
        }
    }


    public function loadPanel()
    {
        Core\Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('vmail-panel');
        $link = 'index.php?module=vmail&aop=menu';

        if (Current_User::allow('vmail', 'edit_recipient')) {
            $tags['new_recipient'] = array('title'=>dgettext('vmail', 'New Recipient'),
                                 'link'=>$link);
        }
        $tags['list_recipients'] = array('title'=>dgettext('vmail', 'List Recipients'),
                                  'link'=>$link);
        if (Current_User::allow('vmail', 'settings', null, null, true)) {
            $tags['settings'] = array('title'=>dgettext('vmail', 'Settings'),
                                  'link'=>$link);
        }
        if (Current_User::isDeity()) {
            $tags['info'] = array('title'=>dgettext('vmail', 'Read me'),
                                 'link'=>$link);
        }
        $this->panel->quickSetTabs($tags);
    }


    public function postRecipient()
    {
        $this->loadRecipient();

        if (empty($_POST['label'])) {
            $errors[] = dgettext('vmail', 'You must give this recipient a label.');
        } else {
            $this->recipient->setLabel($_POST['label']);
        }

        if (isset($_POST['address']) && ($_POST['address']) !== '') {
            if (!$this->recipient->setAddress($_POST['address'])) {
                $this->recipient->address = $_POST['address'];
                $errors[] = dgettext('vmail', 'Check the e-mail address for formatting errors.');
            }
        } else {
            $errors[] = dgettext('vmail', 'You must give this recipient a valid email address.');
        }

        if (empty($_POST['subject'])) {
            $errors[] = dgettext('vmail', 'You must give this recipient a subject.');
        } else {
            $this->recipient->setSubject($_POST['subject']);
        }

        $this->recipient->setSubmit_message($_POST['submit_message']);

        if (!empty($_POST['prefix'])) {
            $this->recipient->setPrefix($_POST['prefix']);
        } else {
            $this->recipient->prefix = null;
        }

        isset($_POST['lock_subject']) ?
            $this->recipient->lock_subject = 1 :
            $this->recipient->lock_subject = 0;

        isset($_POST['active']) ?
            $this->recipient->active = 1 :
            $this->recipient->active = 0;

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            $this->loadForm('edit_recipient');
            return false;
        } else {
            return true;
        }

    }


    public function postSettings()
    {

        if (!empty($_POST['module_title'])) {
            Core\Settings::set('vmail', 'module_title', strip_tags($_POST['module_title']));
        } else {
            $errors[] = dgettext('vmail', 'Please provide a module title.');
        }

        isset($_POST['enable_sidebox']) ?
            Core\Settings::set('vmail', 'enable_sidebox', 1) :
            Core\Settings::set('vmail', 'enable_sidebox', 0);

        isset($_POST['sidebox_homeonly']) ?
            Core\Settings::set('vmail', 'sidebox_homeonly', 1) :
            Core\Settings::set('vmail', 'sidebox_homeonly', 0);

        if (!empty($_POST['sidebox_text'])) {
            Core\Settings::set('vmail', 'sidebox_text', Core\Text::parseInput($_POST['sidebox_text']));
        } else {
            Core\Settings::set('vmail', 'sidebox_text', null);
        }

        isset($_POST['use_captcha']) ?
            Core\Settings::set('vmail', 'use_captcha', 1) :
            Core\Settings::set('vmail', 'use_captcha', 0);


        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            if (Core\Settings::save('vmail')) {
                return true;
            } else {
                return falsel;
            }
        }

    }



    public function checkMessage()
    {
        $this->loadRecipient();

        if (!empty($_POST['name'])) {
            $_POST['name'] = strip_tags($_POST['name']);
        } else {
            $errors[] = dgettext('vmail', 'Please provide your name.');
        }

        if (!empty($_POST['email'])) {
            if (Core\Text::isValidInput($_POST['email'], 'email')) {
                $_POST['email'] = $_POST['email'];
            } else {
                $errors[] = dgettext('vmail', 'Your email address is improperly formatted.');
            }
        } else {
            $errors[] = dgettext('vmail', 'Please provide your email address.');
        }

        if (!empty($_POST['subject'])) {
            $_POST['subject'] = strip_tags($_POST['subject']);
        } else {
            $errors[] = dgettext('vmail', 'Please provide a subject.');
        }

        if (!empty($_POST['message'])) {
            $_POST['message'] = strip_tags($_POST['message']);
        } else {
            $errors[] = dgettext('vmail', 'Please provide a message.');
        }

        if (!vMail::confirm()) {
            $errors['CONFIRM_ERROR'] = dgettext('vmail', 'Confirmation phrase is not correct.');
        }

        if (isset($errors)) {
            $this->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }


    function confirm()
    {
        if (!Core\Settings::get('vmail', 'use_captcha') ||
            !extension_loaded('gd')) {
            return true;
        }

                return Captcha::verify();
    }


    public function sendMail()
    {
        $this->loadRecipient();

        $url = Core\Core::getHomeHttp();
        $from = $_POST['name'];
        $sender = $_POST['email'];
        $sendto = $this->recipient->getLabel();
        $subject = null;
        if ($this->recipient->prefix) {
            $subject .= $this->recipient->getPrefix();
        }
        $subject .= $_POST['subject'];
        $message = sprintf(dgettext('vmail', 'This message from %s was sent via %s.'), $from, $url) . "\n\n";
        $message .= $_POST['message'] . "\n\n";
        $message .= sprintf(dgettext('vmail', 'Sent from IP Address: %s'), $_SERVER['REMOTE_ADDR']) . "\n\n";

                $mail = new PHPWS_Mail;
        $mail->addSendTo(sprintf('%s<%s>', $this->recipient->getLabel(), $this->recipient->getAddress()));
        $mail->setSubject($subject);
        $mail->setFrom(sprintf('%s<%s>', $from, $sender));
        $mail->setMessageBody($message);

//print_r($mail); exit;
        return $mail->send();

    }




    public function navLinks()
    {

        $links[] = Core\Text::moduleLink(dgettext('vmail', 'List all recipients'), 'vmail', array('uop'=>'list_recipients'));
        if (Current_User::allow('vmail', 'settings', null, null, true) && !isset($_REQUEST['aop'])){
            $links[] = Core\Text::moduleLink(dgettext('vmail', 'Settings'), "vmail",  array('aop'=>'menu', 'tab'=>'settings'));
        }

        return $links;
    }



}
?>