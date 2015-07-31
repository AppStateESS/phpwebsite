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

class Contact_User extends Base
{
    public $contact;

    public function __construct()
    {
        $this->loadCarryMessage();
    }

    private function contactLogin()
    {
        $vars = array();
        $form = self::contactForm();
        $vars = $form->getTemplate();
        $template = new \Template($vars);
        $template->setModuleTemplate('properties', 'contact_login.html');
        \Layout::add($template->get());
    }

    public static function contactForm()
    {
        $form = new \PHPWS_Form('contact-login');
        $form->addHidden('module', 'properties');
        $form->addHidden('cop', 'login');
        $form->addText('c_username');
        $form->setPlaceHolder('c_username', 'Username');
        $form->setSize('c_username', 10);
        $form->setClass('c_username', 'form-control');

        $form->addPassword('c_password');
        $form->setPlaceHolder('c_password', 'Password');
        $form->setSize('c_password', 10);
        $form->setClass('c_password', 'form-control');
        $form->addSubmit('submit', 'Log in to Manager Account');
        $form->setClass('submit', 'btn btn-success');
        return $form;
    }

    public function post()
    {
        $this->loadContact();
        switch ($_POST['cop']) {
            case 'login':
                if ($this->login()) {
                    \PHPWS_Core::home();
                    // login successful, contact page
                } else {
                    $this->contactLogin();
                }
                break;

            case 'submitManagerApplication':
                $this->submitManagerApplication();
                break;

            case 'save_property':
                $this->checkPermission();
                $this->loadProperty($this->contact->id);
                if ($this->property->post()) {
                    try {
                        $this->property->save();
                        $this->setCarryMessage('Property saved successfully.');
                        \PHPWS_Core::reroute($this->property->viewLink());
                    } catch (\Exception $e) {
                        $this->setCarryMessage($e->getMessage());
                        \PHPWS_Core::reroute('index.php?module=properties&cop=view_properties&k=' . $_SESSION['Contact_User']->getKey());
                    }
                } else {
                    $this->editProperty($this->contact->id);
                }
                break;

            case 'manager_sign_up':
                $this->newManagerSignup();
                break;

            case 'save_contact':
                $this->checkPermission();
                if ($this->contact->post()) {
                    try {
                        $this->contact->save();
                        $this->contact->errors = null;
                        \PHPWS_Core::home();
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
                        $url = 'index.php?module=properties&cop=view_properties&k=' . $_SESSION['Contact_User']->getKey() . '&pid=' . $photo->pid;
                    }
                    \PHPWS_Core::reroute($url);
                } catch (\Exception $e) {
                    $this->setCarryMessage($e->getMessage());
                    \PHPWS_Core::goBack();
                }
                break;
        }
        $this->display();
    }

    private function loadContact()
    {
        \PHPWS_Core::initModClass('properties', 'Contact.php');
        if (isset($_SESSION['Contact_User'])) {
            $this->contact = $_SESSION['Contact_User'];
        } else {
            $this->contact = new Contact;
        }
    }

    private function submitManagerApplication()
    {
        $request = \Server::getCurrentRequest();
        $vars = $request->getVars();

        $username = $request->getVar('managerUsername');
        $password = $request->getVar('managerPassword');
        $first_name = $request->getVar('contactFirstName');
        $last_name = $request->getVar('contactLastName');
        $email = $request->getVar('emailAddress');
        $phone = $request->getVar('phoneNumber');
        $hours = $request->getVar('contactHours');
        $company_name = $request->getVar('companyName');
        $company_url = $request->getVar('companyUrl');
        $company_address = $request->getVar('companyAddress');

        $private = $request->getVar('managerType');

        $contact = new Contact;
        try {
            $contact->setUsername($username);
            $contact->setPassword($password);
            $contact->setFirstName($first_name);
            $contact->setLastName($last_name);
            $contact->setEmailAddress($email);
            $contact->setPhone($phone);
            $contact->setTimesAvailable($hours);

            if ($private == 'false') {
                $contact->setPrivate(false);
                if (empty($company_name)) {
                    throw \Exception('Missing company name');
                } else {
                    $contact->setCompanyName($company_name);
                }

                if (empty($company_address)) {
                    throw \Exception('Missing company address');
                } else {
                    $contact->setCompanyAddress($company_address);
                }

                $contact->setCompanyUrl($company_url);
            } else {
                $contact->setPrivate(true);
                $contact->setCompanyName('Private Renter');
            }
            $contact->setApproved(false);
            $contact->save();
            $this->emailApprovalNeeded();
        } catch (\Exception $ex) {
            $address = \PHPWS_Settings::get('properties', 'email');
            \Error::log($ex);
            $this->title = 'Sorry!';
            $this->content = <<<EOF
<p>Your manager submission could not be processed. Please email <a href="mailto:$address">$address</a> to inform them of your problem.</p>
EOF;
            $this->content .= $ex->getMessage();
            return;
        }
        // success
        $this->title = 'Thank you';
        $this->content = <<<EOF
<p>We will review your manager application and email your confirmation.</p>
                <p><a href="./">Return to the home page</a></p>
EOF;
    }

    private function emailApprovalNeeded()
    {
        $db = \Database::getDB();
        $t = $db->addTable('prop_contacts');
        $t->addFieldConditional('approved', 0);
        $rows = $db->select();
        $count = count($rows);

        if ($count == 0) {
            return;
        }

        $vars['site_title'] = \Layout::getPageTitle(true);
        $vars['site_address'] = \PHPWS_Core::getHomeHttp() . 'index.php?module=properties&amp;aop=approve';
        $vars['count'] = $count > 1 ? "$count submissions" : "one submission";

        $template = new \Template($vars);
        $template->setModuleTemplate('properties', 'reminder.html');
        $content = $template->get();

        $this->emailContact('Property Manager Approval Required', $content, \PHPWS_Settings::get('properties', 'approver_email'));
    }

    private function emailContact($subject, $content, $to)
    {
        require_once PHPWS_SOURCE_DIR . 'lib/vendor/autoload.php';
        switch (SWIFT_MAIL_TRANSPORT_TYPE) {
            case 1:
                $transport = \Swift_SmtpTransport::newInstance(SWIFT_MAIL_TRANSPORT_PARAMETER);
                break;
            case 2:
                $transport = \Swift_SendmailTransport::newInstance(SWIFT_MAIL_TRANSPORT_PARAMETER);
                break;
            case 3:
                $transport = \Swift_MailTransport::newInstance();
                break;

            default:
                throw new \Exception('Wrong Swift Mail transport type');
        }

        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setFrom(\PHPWS_Settings::get('properties', 'email'));
        $message->setTo($to);
        $message->setBody($content, 'text/html');

        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($message);
    }

    private function newManagerSetup()
    {
        $development = false;

        if ($development) {
            $script_file = 'src/Signup.jsx';
            $type = 'text/jsx';
        } else {
            $script_file = 'build/Signup.js';
            $type = 'text/javascript';
        }

        $data['development'] = $development;
        $data['addons'] = true;
        javascript('react', $data);
        $script = '<script type="' . $type . '" src="' . PHPWS_SOURCE_HTTP .
                'mod/properties/javascript/ManagerSignUp/' . $script_file . '"></script>';
        \Layout::addJSHeader($script);

        $vars['authkey'] = \Current_User::getAuthKey();
        $template = new \Template($vars);
        $template->setModuleTemplate('properties', 'ManagerSignUp.html');
        $this->title = 'New Manager Sign-Up';
        $this->content = $template->get();
    }

    private function loadSession()
    {
        $_SESSION['Contact_User'] = $this->contact;
    }

    private function login()
    {
        $this->loadContact();
        if ($this->contact->id) {
            return true;
        }

        $username = trim(filter_input(INPUT_POST, 'c_username', FILTER_SANITIZE_ENCODED));
        $password = md5($_POST['c_password']);
        // anything not alphanumeric returns false
        if (preg_match('/\W/', $username)) {
            return false;
        }

        $db = new \PHPWS_DB('prop_contacts');
        $db->addWhere('username', $username);
        $db->addWhere('password', $password);

        $result = $db->loadObject($this->contact);
        $this->contact->makeKey();
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            return false;
        }
        if ($this->contact->id) {
            $this->contact->last_log = time();
            $this->contact->save();
            $this->loadSession();
            return true;
        } else {
            return false;
        }
    }

    public function checkPermission()
    {
        if (!isset($this->contact) || !$this->contact->id || $this->contact->id != $_SESSION['Contact_User']->id || !$this->contact->checkKey()) {
            unset($_SESSION['Contact_User']);
            \Layout::nakedDisplay('Command not allowed. <a href=".">Return to home page.</a>');
            exit();
        }
    }

    public function get()
    {
        $this->loadContact();

        switch ($_GET['cop']) {
            case 'logout':
                unset($_SESSION['Contact_User']);
                \PHPWS_Core::home();
                break;

            case 'manager_sign_up':
                $this->newManagerSetup();
                break;

            case 'edit_property':
                $this->checkPermission();
                $this->loadProperty($this->contact->id);
                $this->editProperty($this->contact->id);
                break;

            case 'view_properties':
                $this->checkPermission();
                $this->title = "Properties list";
                $this->propertiesList($this->contact->id);
                break;

            case 'photo_form':
                $photo = new Photo;
                echo $photo->form();
                exit();
                break;

            case 'activate_property':
                $this->checkPermission();
                $this->loadProperty();
                $this->property->setActive(true);
                $this->property->save();
                \PHPWS_Core::goBack();
                break;

            case 'deactivate_property':
                $this->checkPermission();
                $this->loadProperty();
                $this->property->setActive(false);
                $this->property->save();
                \PHPWS_Core::goBack();
                break;

            case 'edit_contact':
                $this->checkPermission();
                $this->editContact();
                break;

            case 'delete_photo':
                // called via ajax
                $this->checkPermission();
                ob_start();
                $photo = new Photo($_GET['id']);
                $photo->delete();
                echo Photo::getThumbs($photo->pid);
                exit();
                break;

            case 'delete_property':
                $this->checkPermission();
                $this->loadProperty();
                // double security
                if ($this->property->contact_id == $this->contact->id) {
                    $this->property->delete();
                }
                \PHPWS_Core::goBack();
                break;

            case 'make_main':
                $photo = new Photo($_GET['id']);
                $photo->makeMain();
                exit();
                break;

            case 'update':
                $this->checkPermission();
                $this->loadProperty();
                $this->property->update();
                \PHPWS_Core::goBack();
                break;

            case 'checkUsername':
                $this->checkUsername();
                exit;
        }
        $this->display();
    }

    private function checkUsername()
    {
        $request = \Server::getCurrentRequest();

        if (!$request->isVar('username')) {
            throw new \Http\NotAcceptableException('No username submitted');
        }

        $username = filter_var($request->getVar('username'), FILTER_SANITIZE_ENCODED);

        $db = \Database::getDB();
        $t1 = $db->addTable('prop_contacts');
        $t1->addFieldConditional('username', $username);
        $result = $db->selectOneRow();
        echo json_encode(array('result' => (bool) $result));
    }

    private function display()
    {
        $tpl['TITLE'] = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        $final_content = \PHPWS_Template::process($tpl, 'properties', 'admin.tpl');
        \Layout::add($final_content);
    }

}

?>
