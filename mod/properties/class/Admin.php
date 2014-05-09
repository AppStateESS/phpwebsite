<?php

namespace Properties;

/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
\PHPWS_Core::initModClass('properties', 'Base.php');

class Admin extends Base {

    private $panel;
    protected $contact;

    public function __construct()
    {
        $this->loadPanel();
        $this->loadCarryMessage();
    }

    private function display()
    {
        \Layout::addStyle('properties');
        $tpl['TITLE'] = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        $final_content = \PHPWS_Template::process($tpl, 'properties',
                        'admin.tpl');
        \Layout::add(\PHPWS_ControlPanel::display($this->panel->display($final_content)));
    }

    public function get()
    {
        if (!\Current_User::allow('properties')) {
            \Current_User::disallow('Action not allowed.');
        }

        switch ($_GET['aop']) {
            case 'delete_contact':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                } else {
                    $this->loadContact();
                    try {
                        $this->contact->delete();
                    } catch (\Exception $e) {
                        \PHPWS_Error::log($e->getMessage());
                        $this->message = 'An error occurred when trying to delete a contact.';
                    }
                }

            case 'update':
                $this->loadProperty();
                $this->property->update();
                \PHPWS_Core::goBack();
                break;

            case 'show_properties':
                $this->panel->setCurrentTab('properties');
                $this->loadContact();
                $this->contactPropertiesList($_GET['cid']);
                break;

            case 'contacts':
                $this->title = 'Contacts list';
                $this->contactList();
                break;

            case 'photo_form':
                $photo = new Photo;
                echo $photo->form();
                exit();
                break;

            case 'edit_contact':
                $this->loadContact();
                $this->editContact();
                break;

            case 'edit_property':
                $this->loadProperty();
                if (isset($_GET['cid'])) {
                    $this->property->contact_id = $_GET['cid'];
                }
                $this->editProperty();
                break;

            case 'email_contacts':
                $this->emailContacts();
                break;

            case 'activate_contact':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                $this->loadContact();
                $this->contact->setActive(true);
                $this->contact->save();
                \PHPWS_Core::goBack();
                break;

            case 'show_blocked':
                $_SESSION['prop_show_blocked'] = 1;
                $this->viewReported();
                break;

            case 'hide_blocked':
                unset($_SESSION['prop_show_blocked']);
                $this->viewReported();
                break;

            case 'deactivate_contact':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                $this->loadContact();
                $this->contact->setActive(false);
                $this->contact->save();
                \PHPWS_Core::goBack();
                break;

            case 'activate_property':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                $this->loadProperty();
                $this->property->setActive(true);
                $this->property->save();
                \PHPWS_Core::goBack();
                break;

            case 'reported':
                $this->viewReported();
                break;

            case 'deactivate_property':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                $this->loadProperty();
                $this->property->setActive(false);
                $this->property->save();
                \PHPWS_Core::goBack();
                break;

            case 'delete_photo':
                // called via ajax
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                ob_start();
                $photo = new Photo($_GET['id']);
                $photo->delete();
                echo Photo::getThumbs($photo->pid);
                exit();
                break;

            case 'make_main':
                $photo = new Photo($_GET['id']);
                $photo->makeMain();
                exit();
                break;

            case 'delete_property':
                if (!\Current_User::authorized('properties')) {
                    \Current_User::disallow();
                }
                $this->loadProperty();
                $this->property->delete();
                \PHPWS_Core::goBack();
                break;

            case 'settings':
                $this->settingsForm();
                break;

            case 'report_view':
                $this->reportView($_GET['id']);
                break;

            case 'block_report':
                $this->blockReport($_GET['id']);
                break;

            case 'ignore_report':
                $this->ignoreReport($_GET['id']);
                \PHPWS_Core::goBack();
                break;

            case 'properties':
            default:
                $this->panel->setCurrentTab('properties');
                $this->title = "Properties list";
                $this->propertiesList();
                break;
        }
        $this->display();
    }

    private function contactPropertiesList($contact_id)
    {
        \PHPWS_Core::initModClass('properties', 'Property.php');

        $this->title = $this->contact->getCompanyName() . '<br /> (c/o ' . $this->contact->getFirstName() . ' ' . $this->contact->getLastName() . ')';

        $pager = new \DBPager('properties', 'Properties\Property');
        $pager->addWhere('contact_id', $contact_id);
        $data['is_contact'] = 1;
        $page_tags['new'] = \PHPWS_Text::moduleLink('Add new property',
                        'properties',
                        array('aop' => 'edit_property', 'cid' => $contact_id));

        $pager->setSearch('name', 'company_name');
        $pager->addSortHeader('name', 'Name of property');
        $pager->addSortHeader('company_name', 'Management company');
        $pager->addSortHeader('timeout', 'Time until purge');
        $pager->setModule('properties');
        $pager->setTemplate('properties_list.tpl');
        $pager->addRowTags('row_tags');
        $pager->joinResult('contact_id', 'prop_contacts', 'id', 'company_name',
                null, true);
        $pager->addPageTags($page_tags);
        $pager->cacheQueries();
        $pager->addToggle(' style="background-color : #e3e3e3"');
        $this->content = $pager->get();
    }

    public function emailContacts()
    {
        $oldtime = time() - 86400 * 30 * 12;
        $db = \Database::newDB();
        $pc = $db->addTable('prop_contacts');
        $pc->addField('first_name');
        $pc->addField('last_name');
        $pc->addField('company_name');
        $pc->addField('email_address');

        $c1 = $pc->getFieldConditional('last_log', $oldtime, '<');
        $id_pc = $pc->getField('id');
        $db->setGroupBy($id_pc);

        $properties = $db->addTable('properties');
        $exp = new \Database\Expression('count(' . $properties->getField('id') . ')',
                'properties');
        $properties->addField($exp);
        $c2 = $properties->getFieldConditional('contact_id', $id_pc);
        $c3 = $properties->getFieldConditional('active', 0);

        $db->stackConditionals($c1, $c2, $c3);

        $contacts = $db->select();
        foreach ($contacts as $row) {
            extract($row);
            $row['email_address'] = "<a href='mailto:$email_address?subject=Account&#160;query'>$email_address</a>";
            $row['action'] = '';
            $result['rows'][] = $row;
        }
        if (empty($result)) {
            $this->content = 'No inactive properties';
        } else {
            $tpl = new \Template($result);
            $tpl->setModuleTemplate('properties', 'overdue.html');
            $this->content = $tpl->__toString();
        }
    }

    public function blockReport($report_id)
    {
        \PHPWS_Core::initModClass('properties', 'Message.php');
        \PHPWS_Core::initModClass('properties', 'Report.php');
        $report = new Report($report_id);

        $message = new Message($report->message_id);

        $user = new \PHPWS_User($message->from_user_id);
        $this->title = 'Block user:' . $user->getUsername();
        $form = new \PHPWS_Form;
        $form->addHidden('module', 'properties');
        $form->addHidden('aop', 'block_post');
        $form->addHidden('report_id', $report->id);
        $form->addHidden('message_id', $message->id);
        $form->addTextarea('block_reason');
        $form->addSubmit('Block user');

        $tpl = $form->getTemplate();

        $this->content = \PHPWS_Template::process($tpl, 'properties',
                        'block.tpl');
    }

    private function blockPost()
    {
        \PHPWS_Core::initModClass('properties', 'Report.php');
        \PHPWS_Core::initModClass('properties', 'Message.php');
        $report = new Report($_POST['report_id']);
        $report->setBlockReason($_POST['block_reason']);
        $report->block = 1;
        $report->save();

        $message = new Message($report->message_id);
        $message->setHidden(1);
        $message->save();
    }

    private function ignoreReport($id)
    {
        \PHPWS_Core::initModClass('properties', 'Report.php');
        \PHPWS_Core::initModClass('properties', 'Message.php');
        $report = new Report($id);
        $message = new Message($report->message_id);
        try {
            $report->delete();
        } catch (\Exception $e) {
            \PHPWS_Core::log($e->getMessage(), 'properties.log');
            exit('Could not remove report');
        }

        $message->reported = 0;
        $message->save();
    }

    private function reportView()
    {
        \PHPWS_Core::initModClass('properties', 'Report.php');
        $db = new \PHPWS_DB('prop_report');
        $db->addColumn('prop_report.*');
        $db->addColumn('prop_messages.body', null, 'message');
        $result = $db->getObjects('\Properties\Report');
        $report = $result[0];
        echo $report->view();
        exit();
    }

    private function loadContact()
    {
        \PHPWS_Core::initModClass('properties', 'Contact.php');
        if (isset($_REQUEST['cid'])) {
            $this->contact = new Contact($_REQUEST['cid']);
        } else {
            $this->contact = new Contact;
        }
    }

    public function post()
    {
        switch ($_POST['aop']) {
            case 'save_property':
                if (!\Current_User::authorized('properties')) {
                    Current_User::disallow('Action not allowed');
                }
                $this->loadProperty();
                if ($this->property->post()) {
                    try {
                        $this->property->save();
                        $this->setCarryMessage('Property saved successfully.');
                    } catch (\Exception $e) {
                        $this->setCarryMessage($e->getMessage());
                    }
                    \PHPWS_Core::reroute('index.php?module=properties&aop=properties');
                } else {
                    $this->editProperty();
                }
                break;

            case 'save_contact':
                if (!\Current_User::authorized('properties')) {
                    Current_User::disallow();
                }
                $this->loadContact();
                if ($this->contact->post()) {
                    try {
                        $this->contact->save();
                        if (isset($_POST['contact_contact'])) {
                            $this->emailContact($this->contact->username,
                                    $_POST['password'], $_POST['email_address']);
                        }
                        $this->setCarryMessage('Contact saved successfully.');
                        \PHPWS_Core::reroute('index.php?module=properties&aop=contacts');
                    } catch (\Exception $e) {
                        $this->setCarryMessage($e->getMessage());
                        $this->editContact();
                    }
                } else {
                    $this->editContact();
                }
                break;

            case 'post_photo':
                try {
                    $photo = new Photo;
                    $photo->post();
                    $this->setCarryMessage('Photo uploaded');
                    if (isset($_POST['v'])) {
                        $property = new Property($photo->pid);
                        $url = './properties/id/' . $photo->pid . '/photo/1';
                    } else {
                        $url = 'index.php?module=properties&aop=properties&pid=' . $photo->pid;
                    }
                    \PHPWS_Core::reroute($url);
                } catch (\Exception $e) {
                    $this->setCarryMessage($e->getMessage());
                    \PHPWS_Core::goBack();
                }
                break;

            case 'post_settings':
                if ($this->postSettings()) {
                    $this->setCarryMessage('Settings updated');
                    \PHPWS_Core::reroute('index.php?module=properties&aop=settings');
                } else {
                    $this->settingsForm();
                }
                break;

            case 'block_post':
                $this->blockPost();
                $this->viewReported();
                break;
        }
        $this->display();
    }

    private function emailContact($username, $password, $email)
    {
        $subject = 'Property Manager Account';
        $message = <<<EOF
A property manager account has been created for you.

Your login information is below.

Username: $username
Password: $password

EOF;
        $reply = \PHPWS_Settings::get('properties', 'email');
        $headers = "From: $reply\r\nReply-To: $reply";

        mail($email, $subject, $message, $headers);
    }

    private function settingsForm()
    {
        \Layout::addStyle('properties', 'forms.css');
        $form = new \PHPWS_Form;
        $form->addHidden('module', 'properties');
        $form->addHidden('aop', 'post_settings');

        $form->addText('login_link',
                \PHPWS_Settings::get('properties', 'login_link'));
        $form->setLabel('login_link', 'Alternate authentication link');
        $form->setSize('login_link', 30);

        $form->addText('email', \PHPWS_Settings::get('properties', 'email'));
        $form->setLabel('email', 'Site email');
        $form->setSize('email', 30);

        $form->addCheck('roommate_only');
        $form->setMatch('roommate_only',
                \PHPWS_Settings::get('properties', 'roommate_only'));
        $form->setLabel('roommate_only', 'Only use the roommate functionality');

        $form->addSubmit('Save settings');

        $tpl = $form->getTemplate();

        if (!empty($this->errors)) {
            foreach ($this->errors as $key => $message) {
                $new_key = strtoupper($key) . '_ERROR';
                $tpl[$new_key] = $message;
            }
        }

        $this->title = 'Settings';
        $this->content = \PHPWS_Template::process($tpl, 'properties',
                        'settings.tpl');
    }

    private function postSettings()
    {
        if (!empty($_POST['login_link']) && preg_match('/[^\w\-\?&=:+\/]/',
                        $_POST['login_link'])) {
            $this->errors['login_link'] = 'Login link had non-url characters';
        } else {
            \PHPWS_Settings::set('properties', 'login_link',
                    $_POST['login_link']);
        }

        if (!\PHPWS_Text::isValidInput($_POST['email'], 'email')) {
            $this->errors['email'] = 'Email address is empty or malformed.';
        } else {
            \PHPWS_Settings::set('properties', 'email', $_POST['email']);
        }

        \PHPWS_Settings::set('properties', 'roommate_only',
                (int) isset($_POST['roommate_only']));

        if (!isset($this->errors)) {
            \PHPWS_Settings::save('properties');
            return true;
        } else {
            return false;
        }
    }

    private function contactList()
    {
        $email = \PHPWS_Settings::get('properties', 'email');
        if (empty($email)) {
            $this->content = 'Please enter the site email under settings';
            return;
        }
        \PHPWS_Core::initModClass('properties', 'Contact.php');
        $page_tags['new'] = \PHPWS_Text::secureLink('<i class="fa fa-plus"></i> Add new contact',
                        'properties', array('aop' => 'edit_contact'), null,
                        null, 'btn btn-success');
        $page_tags['email'] = \PHPWS_Text::secureLink('<i class="fa fa-minus-square-o"></i> Inactive contacts',
                        'properties', array('aop' => 'email_contacts'), null,
                        null, 'btn btn-default');

        $this->title = 'Contact listing';

        $pager = new \DBPager('prop_contacts', 'Properties\Contact');
        $pager->addSortHeader('company_name', 'Company');
        $pager->addSortHeader('last_name', 'Last, First name');
        $pager->addSortHeader('email_address', 'Email');
        $pager->addSortHeader('last_log', 'Last log');
        $pager->setModule('properties');
        $pager->setTemplate('contact_list.tpl');
        $pager->addRowTags('row_tags');
        $pager->addPageTags($page_tags);
        $pager->setSearch('company_name', 'first_name', 'last_name',
                'email_address');
        $pager->setDefaultOrder('company_name');
        $pager->cacheQueries();
        $pager->addToggle(' style="background-color : #e3e3e3"');
        $this->content = $pager->get();
    }

    private function viewReported()
    {
        javascriptMod('properties', 'report');
        javascript('confirm');
        \PHPWS_Core::initModClass('properties', 'Report.php');
        $this->title = 'Reported messages';
        $pager = new \DBPager('prop_report', 'Properties\Report');

        if (!isset($_SESSION['prop_show_blocked'])) {
            $vars['aop'] = 'show_blocked';
            $tags['BLOCKED'] = \PHPWS_Text::secureLink('Show blocked',
                            'properties', $vars, null, null, 'btn btn-default');
            $pager->db->addWhere('prop_report.block', 0);
        } else {
            $vars['aop'] = 'hide_blocked';
            $tags['BLOCKED'] = \PHPWS_Text::secureLink('Hide blocked',
                            'properties', $vars, null, null, 'btn btn-default');
        }
        $pager->addPageTags($tags);

        $pager->addSortHeader('date_sent', 'Date reported');
        $pager->joinResult('message_id', 'prop_messages', 'id', 'body',
                'message');
        $pager->db->addJoin('left', 'prop_report', 'prop_messages',
                'message_id', 'id');

        $pager->setModule('properties');
        $pager->setTemplate('reported_list.tpl');
        $pager->addRowTags('row');
        $this->content = $pager->get();
    }

    public function loadPanel()
    {
        \PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $link = array('title' => 'Properties', 'link' => 'index.php?module=properties&amp;aop=properties');
        $tabs['properties'] = $link;

        $link = array('title' => 'Contacts', 'link' => 'index.php?module=properties&amp;aop=contacts');
        $tabs['contacts'] = $link;

        $link = array('title' => 'Reported', 'link' => 'index.php?module=properties&amp;aop=reported');
        $tabs['reported'] = $link;

        $link = array('title' => 'Settings', 'link' => 'index.php?module=properties&amp;aop=settings');
        $tabs['settings'] = $link;
        $this->panel = new \PHPWS_Panel('properties');
        $this->panel->quickSetTabs($tabs);
    }

}

?>