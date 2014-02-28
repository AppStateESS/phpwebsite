<?php

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
if (isset($_REQUEST['aop'])) {
    if (!Current_User::allow('properties')) {
        Current_User::disallow('Action not allowed');
    }
    PHPWS_Core::initModClass('properties', 'Admin.php');
    $admin = new Properties\Admin;
    if (isset($_GET['aop'])) {
        $admin->get();
    } elseif (isset($_POST['aop'])) {
        $admin->post();
    }
} elseif (isset($_REQUEST['uop'])) {
    PHPWS_Core::initModClass('properties', 'User.php');
    $user = new Properties\User;
    if (isset($_GET['uop'])) {
        $user->get();
    } elseif (isset($_POST['uop'])) {
        $user->post();
    }
} elseif (isset($_REQUEST['rop'])) {
    if (!Current_User::isLogged()) {
        PHPWS_Core::reroute(propertiesloginLink());
    }
    PHPWS_Core::initModClass('properties', 'Roommate_User.php');
    $roommate = new Properties\Roommate_User;
    if ($_SESSION['properties_user_checked']) {
        $roommate->denyAccess();
    } else {
        if (isset($_GET['rop'])) {
            $roommate->get();
        } elseif (isset($_POST['rop'])) {
            $roommate->post();
        }
    }
} elseif (isset($_REQUEST['cop'])) {
    PHPWS_Core::initModClass('properties', 'Contact_User.php');
    $contact = new Properties\Contact_User;
    if (isset($_GET['cop'])) {
        $contact->get();
    } elseif (isset($_POST['cop'])) {
        $contact->post();
    }
} elseif (isset($_GET['id'])) {
    PHPWS_Core::initModClass('properties', 'Property.php');
    try {
        $property = new Properties\Property($_GET['id']);
        $property->view();
    } catch (Exception $e) {
        \PHPWS_Error::log($e->getMessage());
        \PHPWS_Core::errorPage('404');
    }
}
?>