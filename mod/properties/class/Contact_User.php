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

class Contact_User extends Base {

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

        $username = trim($_POST['c_username']);
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
        if (!isset($this->contact) || !$this->contact->id ||
                $this->contact->id != $_SESSION['Contact_User']->id ||
                !$this->contact->checkKey()) {
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
        }
        $this->display();
    }

    private function display()
    {
        $tpl['TITLE'] = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;
        $final_content = \PHPWS_Template::process($tpl, 'properties',
                        'admin.tpl');
        \Layout::add($final_content);
    }

}

?>
