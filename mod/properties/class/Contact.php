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
class Contact {

    public $id;
    public $username;
    public $password;
    public $first_name;
    public $last_name;
    public $phone;
    public $email_address;
    public $company_name;
    public $company_address;
    public $company_url;
    public $times_available;
    public $last_log = 0;
    public $active = 1;
    private $key = null;
    public $errors = null;

    public function __construct($id = null)
    {
        if (!$id) {
            return;
        }
        $db = new \PHPWS_DB('prop_contacts');
        $db->addWhere('id', (int) $id);
        $db->loadObject($this);
    }

    public function makeKey()
    {
        $this->key = md5(rand());
    }

    public function getKey()
    {
        return $this->key;
    }

    public function form()
    {
        javascript('jquery');
        javascriptMod('properties', 'generate');
        \Layout::addStyle('properties', 'forms.css');

        $form = new \PHPWS_Form('contact');
        $form->addHidden('module', 'properties');
        if (@$this->id) {
            $form->addHidden('cid', $this->id);
            $form->addSubmit('Update contact');
        } else {
            $form->addSubmit('Add contact');
        }

        if (isset($_SESSION['Contact_User']) && !\Current_User::allow('properties')) {
            $form->addHidden('cop', 'save_contact');
            $form->addHidden('k', $_SESSION['Contact_User']->getKey());
        } else {
            $form->addHidden('aop', 'save_contact');
            $form->addText('username', $this->username);
            $form->setLabel('username', 'User name');
            $form->setSize('username', '20', '20');
            $form->setRequired('username');

            $form->addButton('make_password', 'Create');
            $form->setId('make_password', 'make-password');
            $form->addCheck('contact_contact', 1);
            $form->setLabel('contact_contact', 'Send contact email');
            if (!$this->id) {
                $form->setMatch('contact_contact', 1);
            }
        }

        $form->addPassword('password');
        $form->setLabel('password', 'Password');

        $form->addPassword('pw_check');
        $form->setLabel('pw_check', 'Match');

        $form->addText('first_name', $this->first_name);
        $form->setLabel('first_name', 'Contact first name');
        $form->setRequired('first_name');

        $form->addText('last_name', $this->last_name);
        $form->setLabel('last_name', 'Contact last name');
        $form->setRequired('last_name');

        $form->addText('phone', $this->getPhone());
        $form->setLabel('phone', 'Phone number');
        $form->setRequired('phone');

        $form->addText('email_address', $this->email_address);
        $form->setLabel('email_address', 'Email address');
        $form->setRequired('email_address');
        $form->setSize('email_address', 40);

        $form->addText('company_name', $this->company_name);
        $form->setLabel('company_name', 'Company name');
        $form->setRequired('company_name');
        $form->setSize('company_name', 40);

        $form->addText('company_url', $this->company_url);
        $form->setLabel('company_url', 'Company url');
        $form->setSize('company_url', 40);

        $form->addTextArea('company_address', $this->company_address);
        $form->setLabel('company_address', 'Company (or renter) address');
        $form->setRows('company_address', 4);
        $form->setCols('company_address', 20);

        $form->addTextArea('times_available', $this->times_available);
        $form->setLabel('times_available',
                'Days and hours available for contact');
        $form->setRows('times_available', 4);
        $form->setCols('times_available', 20);


        $tpl = $form->getTemplate();

        if (!empty($this->errors)) {
            foreach ($this->errors as $key => $message) {
                $new_key = strtoupper($key) . '_ERROR';
                $tpl[$new_key] = $message;
            }
        }

        return \PHPWS_Template::process($tpl, 'properties', 'edit_contact.tpl');
    }

    public function post()
    {
        $vars = array_keys(get_object_vars($this));
        foreach ($_POST as $key => $value) {
            if ($key == 'password') {
                // new contacts must have a password
                if (!$this->id) {
                    if (empty($_POST['password'])) {
                        $this->errors['password'] = 'New contacts must be given a password';
                        continue;
                    }
                }
                if (!empty($_POST['pw_check']) || !empty($_POST['password'])) {
                    if ($_POST['pw_check'] == $_POST['password']) {
                        $this->setPassword($_POST['password']);
                    } else {
                        $this->errors['password'] = 'Passwords must must match';
                    }
                }
                continue;
            }

            if ($key == 'pw_check') {
                continue;
            }

            if (in_array($key, $vars)) {
                $func = 'set' . str_replace(' ', '',
                                ucwords(str_replace('_', ' ', $key)));
                try {
                    $this->$func($value);
                } catch (\Exception $e) {
                    $this->errors[$key] = $e->getMessage();
                }
            }
        }

        return !isset($this->errors);
    }

    public function setUsername($username)
    {
        if (empty($username)) {
            throw new \Exception('User name may not be blank');
        }

        if (strlen($username) < 3) {
            throw new \Exception('User name must be more than 3 characters');
        }

        if (preg_match('/\W]/', $username)) {
            throw new \Exception('User name may contain alphanumeric characters only');
        }

        // check for duplicate user names
        $db = new \PHPWS_DB('prop_contacts');
        $db->addWhere('username', $username);
        $db->addColumn('id');
        $result = $db->select('one');
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('A database error occurred. Contact the site administrator');
        }

        // if an id is found (user name in use) and this is a new user OR the result id
        // is not equal to the current contact id, then throw a duplicate warning
        if ($result && (!$this->id || ($this->id != $result))) {
            throw new \Exception('This user name is already in use, please choose another');
        }

        $this->username = $username;
    }

    public function setCompanyUrl($url)
    {
        if (empty($url)) {
            return;
        }
        if (!\PHPWS_Text::isValidInput($url, 'url')) {
            throw new \Exception('Improperly formatted url');
        }
        $this->company_url = strip_tags(trim($url));
    }

    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    public function setFirstName($first_name)
    {
        if (empty($first_name)) {
            throw new \Exception('First name may not be blank');
        }

        if (preg_match('/[^a-z\s\']/i', $first_name)) {
            throw new \Exception('May only contain alphabetical characters');
        }

        $this->first_name = ucwords($first_name);
    }

    public function setLastName($last_name)
    {
        if (empty($last_name)) {
            throw new \Exception('Last name may not be blank');
        }

        if (preg_match('/[^a-z\s\']/i', $last_name)) {
            throw new \Exception('May only contain alphabetical characters');
        }

        $this->last_name = ucwords($last_name);
    }

    public function setPhone($phone)
    {
        if (empty($phone)) {
            $this->phone = null;
            return true;
        }

        $phone = trim((string) preg_replace('/[^\d]/', '', $phone));
        if (strlen($phone) != 10) {
            throw new \Exception('Phone number must be 10 digits');
        }

        $this->phone = $phone;
    }

    public function setEmailAddress($email_address)
    {
        if (empty($email_address)) {
            $this->email_address = null;
            return true;
        }

        if (!\PHPWS_Text::isValidInput($email_address, 'email')) {
            throw new \Exception('Improperly formatted email');
        }
        $this->email_address = $email_address;
    }

    public function setCompanyName($company_name)
    {
        $this->company_name = ucwords(strip_tags($company_name));
    }

    public function setCompanyAddress($company_address)
    {
        $this->company_address = strip_tags($company_address);
    }

    public function setTimesAvailable($times_available)
    {
        $this->times_available = strip_tags($times_available);
    }

    public function getUsername()
    {
        return $this->username;
    }

    private function getPassword()
    {
        return $this->password;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getPhone()
    {
        if (empty($this->phone)) {
            return null;
        }

        return sprintf('(%s) %s-%s', substr($this->phone, 0, 3),
                substr($this->phone, 3, 3), substr($this->phone, 6));
    }

    public function getEmailAddress($html = false)
    {
        if ($html) {
            return sprintf('<a href="mailto:%s">%s</a>', $this->email_address,
                    $this->email_address);
        } else {
            return $this->email_address;
        }
    }

    public function getCompanyName()
    {
        return $this->company_name;
    }

    public function getCompanyUrl()
    {
        if (!empty($this->company_url)) {
            return '<a target="_blank" href="' . $this->company_url . '">' . $this->company_name . '</a>';
        } else {
            return $this->company_name;
        }
    }

    public function getCompanyAddress()
    {
        return nl2br($this->company_address);
    }

    public function getTimesAvailable()
    {
        return nl2br($this->times_available);
    }

    public function save()
    {
        if (empty($this->last_log)) {
            $this->last_log = time();
        }
        $db = new \PHPWS_DB('prop_contacts');
        $result = $db->saveObject($this);
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not save contact to the database.');
        }
        return true;
    }

    public function row_tags()
    {
        $tpl['LAST_NAME'] = sprintf('<a href="mailto:%s">%s, %s <i class="fa fa-envelope-o"></i></a>',
                $this->email_address, $this->last_name, $this->first_name);
        $tpl['PHONE'] = $this->getPhone();

        $tpl['COMPANY_NAME'] = $this->getCompanyUrl();

        if ($this->active) {
            $admin[] = \PHPWS_Text::secureLink(\Icon::show('active',
                                    'Click to deactivate'), 'properties',
                            array('aop' => 'deactivate_contact', 'cid' => $this->id));
        } else {
            $admin[] = \PHPWS_Text::secureLink(\Icon::show('inactive',
                                    'Click to activate'), 'properties',
                            array('aop' => 'activate_contact', 'cid' => $this->id));
        }

        $admin[] = \PHPWS_Text::secureLink(\Icon::show('add'), 'properties',
                        array('aop' => 'edit_property', 'cid' => $this->id));
        $admin[] = \PHPWS_Text::secureLink(\Icon::show('edit'), 'properties',
                        array('aop' => 'edit_contact', 'cid' => $this->id));

        $js['LINK'] = \Icon::show('delete');
        $js['QUESTION'] = 'Are you sure you want to delete this contact and all their properties?';
        $js['ADDRESS'] = 'index.php?module=properties&aop=delete_contact&cid=' . $this->id . '&authkey=' . \Current_User::getAuthKey();

        $admin[] = javascript('confirm', $js);

        $admin[] = \PHPWS_Text::secureLink(\Icon::show('home', 'Show properties'),
                        'properties',
                        array('aop' => 'show_properties', 'cid' => $this->id));

        if ($this->last_log) {
            $tpl['LAST_LOG'] = strftime('%x', $this->last_log);
        } else {
            $tpl['LAST_LOG'] = 'Never';
        }
        $tpl['ACTION'] = implode('', $admin);
        return $tpl;
    }

    public function delete()
    {
        if (!$this->id) {
            throw new \Exception('Missing contact id');
        }
        $c_db = new \PHPWS_DB('prop_contacts');
        $c_db->addWhere('id', $this->id);
        $result = $c_db->delete();
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not delete contact from database.');
        }

        $p_db = new \PHPWS_DB('properties');
        $p_db->addWhere('contact_id', $this->id);
        $result = $p_db->delete();
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not delete contact properties from database.');
        }

        $i_db = new \PHPWS_DB('prop_photo');
        $i_db->addWhere('cid', $this->id);
        $result = $i_db->delete();
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not delete contact photos from database.');
        }

        $dir = 'images/properties/c' . $this->id;
        @rmdir($dir);

        return true;
    }

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    public function loginMenu()
    {
        $vars['k'] = $this->key;
        $vars['cop'] = 'edit_property';
        $tpl['CREATE'] = \PHPWS_Text::moduleLink('Create property',
                        'properties', $vars);
        $vars['cop'] = 'view_properties';
        $tpl['VIEW'] = \PHPWS_Text::moduleLink('View properties', 'properties',
                        $vars);
        $vars['cop'] = 'edit_contact';
        $tpl['EDIT'] = \PHPWS_Text::moduleLink('Edit my information',
                        'properties', $vars);
        $vars['cop'] = 'logout';
        $tpl['LOGOUT'] = \PHPWS_Text::moduleLink('Logout', 'properties', $vars);
        $content = \PHPWS_Template::process($tpl, 'properties',
                        'mini_contact.tpl');
        \Layout::add($content, 'properties', 'contact_login');
    }

    public function checkKey()
    {
        return @$_REQUEST['k'] == $this->key;
    }

}

?>