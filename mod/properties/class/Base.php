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
\PHPWS_Core::initModClass('properties', 'Photo.php');

abstract class Base {

    protected $content;
    protected $title;
    protected $message;
    protected $property;
    protected $errors;

    protected function loadProperty($contact_id=null)
    {
        \PHPWS_Core::initModClass('properties', 'Property.php');
        if (isset($_REQUEST['pid'])) {
            $this->property = new Property($_REQUEST['pid']);
            if ($contact_id && $this->property->contact_id != $contact_id) {
                throw new \Exception('Property/Contact mismatch');
            }
        } else {
            $this->property = new Property;
            $this->property->contact_id = $contact_id;
        }
    }

    protected function editProperty($contact_id=null)
    {
        if (@$this->property->id) {
            $this->title = 'Update property';
        } else {
            $this->title = 'Post new property';
        }
        $this->content = $this->property->form($contact_id);
    }

    protected function setCarryMessage($message)
    {
        $_SESSION['Properties_Message'] = $message;
    }

    protected function loadCarryMessage()
    {
        if (isset($_SESSION['Properties_Message'])) {
            $this->message = $_SESSION['Properties_Message'];
            unset($_SESSION['Properties_Message']);
        }
        return false;
    }

    protected function editContact()
    {
        if (!empty($this->contact->id)) {
            $this->title = 'Update ' . $this->contact->getCompanyName();
        } else {
            $this->title = 'Post new contact';
        }
        $this->content = $this->contact->form();
    }

    protected function propertiesList($contact_id=null)
    {
        \PHPWS_Core::initModClass('properties', 'Property.php');

        $this->title = 'Property listing';

        $pager = new \DBPager('properties', 'Properties\Property');

        if ($contact_id) {
            $pager->addWhere('contact_id', $contact_id);
            $data['is_contact'] = 1;
            $page_tags['new'] = \PHPWS_Text::moduleLink('Add new property', 'properties', array('cop' => 'edit_property', 'k' => $this->contact->getKey()));
        } else {
            $page_tags['new'] = \PHPWS_Text::secureLink('Add new property', 'properties', array('aop' => 'edit_property'));
        }

        // photo was previously uploaded
        if (!empty($_GET['pid'])) {
            $data['pid'] = $_GET['pid'];
        } else {
            $data['pid'] = 0;
        }
        javascriptMod('properties', 'photo_upload', $data);

        $pager->setSearch('name', 'company_name');
        $pager->addSortHeader('name', 'Name of property');
        $pager->addSortHeader('company_name', 'Management company');
        $pager->addSortHeader('timeout', 'Time until purge');
        $pager->setModule('properties');
        $pager->setTemplate('properties_list.tpl');
        $pager->addRowTags('row_tags', (bool) $contact_id);
        $pager->joinResult('contact_id', 'prop_contacts', 'id', 'company_name', null, true);
        $pager->addPageTags($page_tags);
        $pager->cacheQueries();
        $pager->addToggle(' style="background-color : #e3e3e3"');
        $this->content = $pager->get();
    }

}

?>