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

class vMail_Forms {
    public $vmail = null;

    public function get($type)
    {
        switch ($type) {

            case 'new_recipient':
            case 'edit_recipient':
                if (empty($this->vmail->recipient)) {
                    $this->vmail->loadRecipient();
                }
                $this->editRecipient();
                break;

            case 'list_recipients':
                $this->vmail->panel->setCurrentTab('list_recipients');
                $this->listRecipients();
                break;

            case 'settings':
                $this->vmail->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'compose_message':
                if (empty($this->vmail->recipient)) {
                    $this->vmail->loadRecipient();
                }
                $this->composeMessage();
                break;

            case 'info':
                $this->vmail->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    public function listRecipients()
    {
        if (Current_User::allow('vmail', 'edit_recipient') && isset($_REQUEST['uop'])) {
            $link[] = \core\Text::secureLink(dgettext('vmail', 'Add new recipient'), 'vmail', array('aop'=>'new_recipient'));
            MiniAdmin::add('vmail', $link);
        }

        \core\Core::initModClass('vmail', 'vMail_Recipient.php');
                $ptags = array();
        $pager = new \core\DBPager('vmail_recipients', 'vMail_Recipient');
        $pager->setModule('vmail');
        if (!Current_User::authorized('vmail', 'edit_recipient')) {
            $pager->addWhere('active', 1);
        }

        $pager->addSortHeader('label', dgettext('vmail', 'Label'));
        $pager->addSortHeader('subject', dgettext('vmail', 'Subject'));
        if (Current_User::allow('vmail', 'edit_recipient')) {
            $pager->addSortHeader('address', dgettext('vmail', 'Address'));
        } else {
            $ptags['ADDRESS_SORT'] = null;
        }

        $pager->setOrder('label', 'asc', true);
        $pager->setTemplate('list_recipients.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'new_recipient';
            if (Current_User::allow('vmail', 'edit_recipient')) {
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('vmail', 'Check your %s then create a %s to begin'), \core\Text::secureLink(dgettext('vmail', 'Settings'), 'vmail', $vars),  \core\Text::secureLink(dgettext('vmail', 'New Recipient'), 'vmail', $vars2));
            } else {
                $ptags['EMPTY_MESSAGE'] = dgettext('vmail', 'Sorry, there are no recipients available at the moment.');
            }
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('label', 'address', 'subject');
        $pager->cacheQueries();

        $this->vmail->content = $pager->get();
        $this->vmail->title = sprintf(dgettext('vmail', '%s Recipients'), \core\Settings::get('vmail', 'module_title'));
    }


    public function editRecipient()
    {
        $form = new \core\Form('vmail_recipient');
        $recipient = & $this->vmail->recipient;

        $form->addHidden('module', 'vmail');
        $form->addHidden('aop', 'post_recipient');
        if ($recipient->id) {
            $form->addHidden('id', $recipient->id);
            $form->addSubmit(dgettext('vmail', 'Update'));
            $this->vmail->title = sprintf(dgettext('vmail', 'Update recipient %s'), $recipient->getLabel(true));
        } else {
            $form->addSubmit(dgettext('vmail', 'Create'));
            $this->vmail->title = dgettext('vmail', 'Create recipient');
        }

        $form->addText('label', $recipient->getLabel());
        $form->setSize('label', 40);
        $form->setRequired('label');
        $form->setLabel('label', dgettext('vmail', 'Recipient label/name'));

        $form->addText('address', $recipient->getAddress());
        $form->setSize('address', 40);
        $form->setRequired('address');
        $form->setLabel('address', dgettext('vmail', 'Recipient email address'));

        $form->addText('subject', $recipient->getSubject());
        $form->setSize('subject', 40);
        $form->setRequired('subject');
        $form->setLabel('subject', dgettext('vmail', 'Message subject'));

        $form->addTextArea('submit_message', $recipient->getSubmit_message());
        $form->setRows('submit_message', '6');
        $form->setCols('submit_message', '40');
        $form->setLabel('submit_message', dgettext('vmail', 'Post submission message to sender'));

        $form->addText('prefix', $recipient->getPrefix());
        $form->setSize('prefix', 40);
        $form->setLabel('prefix', dgettext('vmail', 'Subject prefix (not seen by sender)'));

        $form->addCheckbox('lock_subject', 1);
        $form->setMatch('lock_subject', $recipient->lock_subject);
        $form->setLabel('lock_subject', dgettext('vmail', 'Lock subject'));

        $form->addCheckbox('active', 1);
        $form->setMatch('active', $recipient->active);
        $form->setLabel('active', dgettext('vmail', 'Active'));

        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('vmail', 'Details');
        $tpl['SETTINGS_LABEL'] = dgettext('vmail', 'Settings');

        $this->vmail->content = \core\Template::process($tpl, 'vmail', 'edit_recipient.tpl');
    }


    public function editSettings()
    {

        $form = new \core\Form('vmail_settings');
        $form->addHidden('module', 'vmail');
        $form->addHidden('aop', 'post_settings');

        $form->addText('module_title', \core\Settings::get('vmail', 'module_title'));
        $form->setSize('module_title', 30);
        $form->setLabel('module_title', dgettext('vmail', 'The display title for this module, eg. vMail, Contacts, etc.'));

        $form->addCheckbox('enable_sidebox', 1);
        $form->setMatch('enable_sidebox', \core\Settings::get('vmail', 'enable_sidebox'));
        $form->setLabel('enable_sidebox', dgettext('vmail', 'Enable vmail sidebox'));

        $form->addCheckbox('sidebox_homeonly', 1);
        $form->setMatch('sidebox_homeonly', \core\Settings::get('vmail', 'sidebox_homeonly'));
        $form->setLabel('sidebox_homeonly', dgettext('vmail', 'Show sidebox on home page only'));

        $form->addTextArea('sidebox_text', \core\Text::parseOutput(core\Settings::get('vmail', 'sidebox_text')));
        $form->setRows('sidebox_text', '4');
        $form->setCols('sidebox_text', '40');
        $form->setLabel('sidebox_text', dgettext('vmail', 'Sidebox text'));

        $form->addCheckbox('use_captcha', 1);
        $form->setMatch('use_captcha', \core\Settings::get('vmail', 'use_captcha'));
        $form->setLabel('use_captcha', dgettext('vmail', 'Use graphical confirmation on vmail form (CAPTCHA)'));

        $form->addSubmit('save', dgettext('vmail', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('vmail', 'General Settings');

        $this->vmail->title = dgettext('vmail', 'Settings');
        $this->vmail->content = \core\Template::process($tpl, 'vmail', 'edit_settings.tpl');
    }


    public function composeMessage()
    {
        $recipient = & $this->vmail->recipient;

        $key = new \core\Key($recipient->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        if (isset($_POST['name'])) {
            $_POST['name'] = $_POST['name'];
        } else {
            $_POST['name'] = null;
        }
        if (isset($_POST['email'])) {
            $_POST['email'] = $_POST['email'];
        } else {
            $_POST['email'] = null;
        }
        if (isset($_POST['subject']) && $_POST['subject'] !== '') {
            $_POST['subject'] = $_POST['subject'];
        } else {
            $_POST['subject'] = $recipient->getSubject(true);
        }
        if (isset($_POST['message'])) {
            $_POST['message'] = $_POST['message'];
        } else {
            $_POST['message'] = null;
        }

        $form = new \core\Form;
        $form->addHidden('module', 'vmail');
        $form->addHidden('uop', 'send_message');
        $form->addHidden('id', $recipient->id);

        $form->addText('name', $_POST['name']);
        $form->setLabel('name', dgettext('vmail', 'Your name'));
        $form->setRequired('name');
        $form->setSize('name', 40);

        $form->addText('email', $_POST['email']);
        $form->setLabel('email', dgettext('vmail', 'Your email'));
        $form->setRequired('email');
        $form->setSize('email', 40);

        if (!$recipient->lock_subject) {
            $form->addText('subject', $_POST['subject']);
            $form->setLabel('subject', dgettext('vmail', 'Subject'));
            $form->setRequired('subject');
            $form->setSize('subject', 40);
        } else {
            $form->addHidden('subject', $recipient->getSubject(true));
        }

        $form->addTextArea('message', $_POST['message']);
        $form->setRows('message', '15');
        $form->setCols('message', '50');
        $form->setRequired('message');
        $form->setLabel('message', dgettext('vmail', 'Message'));

        $form->addText('confirm_phrase');
        $form->setLabel('confirm_phrase', dgettext('vmail', 'Confirm text'));

        if (core\Settings::get('vmail', 'use_captcha') && extension_loaded('gd')) {
            $result = $this->confirmGraphic();
            if (core\Error::isError($result)) {
                \core\Error::log($result);
            } else {
                $form->addTplTag('GRAPHIC', $result);
            }
        }

        $form->addSubmit('submit', dgettext('vmail', 'Send Message'));

        $tpl = $form->getTemplate();
        $tpl['FORM_LABEL'] = dgettext('vmail', 'Compose message');
        $tpl['FORM_INSTRUCTION'] = dgettext('vmail', 'All fields are required.');
        $tpl['LINKS'] = implode(' | ', vMail::navLinks());

        $key->flag();
        $this->vmail->title = sprintf(dgettext('vmail', 'Send a message to %s'), $recipient->getLabel(true));
        $this->vmail->content = \core\Template::process($tpl, 'vmail', 'compose_message.tpl');
    }


    public function confirmGraphic()
    {
                return Captcha::get();
    }


    public function showInfo()
    {
        $filename = 'mod/vmail/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('vmail', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('vmail', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('vmail', 'If you would like to help out with the ongoing development of vmail, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=vMail%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->vmail->title = dgettext('vmail', 'Read me');
        $this->vmail->content = \core\Template::process($tpl, 'vmail', 'info.tpl');
    }



}

?>