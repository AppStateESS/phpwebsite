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

class vMail_Recipient {

    public $id             = 0;
    public $key_id         = 0;
    public $label          = null;
    public $address        = null;
    public $prefix         = null;
    public $subject        = null;
    public $submit_message = null;
    public $lock_subject   = 0;
    public $active         = 1;

    public $_error         = null;


    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    public function init()
    {
        $db = new PHPWS_DB('vmail_recipients');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setLabel($label)
    {
        $this->label = strip_tags($label);
    }

    public function setAddress($address)
    {
        if (PHPWS_Text::isValidInput($address, 'email')) {
            $this->address = $address;
            return true;
        } else {
            return false;
        }
    }

    public function setPrefix($prefix)
    {
        $this->prefix = strip_tags($prefix);
    }

    public function setSubject($subject)
    {
        $this->subject = strip_tags($subject);
    }

    public function setSubmit_message($submit_message)
    {
        $this->submit_message = PHPWS_Text::parseInput($submit_message);
    }


    public function getLabel($print=false)
    {
        if (empty($this->label)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->label);
        } else {
            return $this->label;
        }
    }

    public function getAddress($print=false)
    {
        if (empty($this->address)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->address);
        } else {
            return $this->address;
        }
    }

    public function getPrefix($print=false)
    {
        if (empty($this->prefix)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->prefix);
        } else {
            return $this->prefix;
        }
    }

    public function getSubject($print=false)
    {
        if (empty($this->subject)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->subject);
        } else {
            return $this->subject;
        }
    }

    public function getSubmit_message($print=false)
    {
        if (empty($this->submit_message)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->submit_message);
        } else {
            return $this->submit_message;
        }
    }

    public function submitMessage()
    {
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['RECIPIENT'] = $this->getLabel(true);
        if ($this->getSubmit_message()) {
            $tpl['SUBMIT_MESSAGE'] = PHPWS_Text::parseTag($this->getSubmit_message(true));
        } else {
            $tpl['SUBMIT_MESSAGE'] = dgettext('vmail', 'Your message was sent to');
        }

        return PHPWS_Template::process($tpl, 'vmail', 'submit_message.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vmail', 'edit_recipient')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_recipient';
            $links[] = PHPWS_Text::secureLink(dgettext('vmail', 'Edit recipient'), 'vmail', $vars);
        }

        $links = array_merge($links, vMail::navLinks());

        if($links)
            return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the recipient */
        $db = new PHPWS_DB('vmail_recipients');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('vmail', 'edit_recipient')) {
            $vars['aop']  = 'edit_recipient';
            $links[] = PHPWS_Text::secureLink(dgettext('vmail', 'Edit'), 'vmail', $vars);
        }
        if (Current_User::allow('vmail', 'delete_recipient')) {
            $vars['aop'] = 'delete_recipient';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vmail', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vmail', 'Are you sure you want to delete the recipient %s?'), $this->getLabel());
            $js['LINK'] = dgettext('vmail', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['LABEL'] = $this->viewLink();
        $tpl['SUBJECT'] = $this->getSubject(true);
        if (Current_User::allow('vmail', 'edit_recipient')) {
            $tpl['ADDRESS'] = $this->getAddress(true);
        } else {
            $tpl['ADDRESS'] = null;
        }

        if (Current_User::allow('vmail', 'edit_recipient')) {
            if ($this->active) {
                $vars['aop'] = 'deactivate_recipient';
                $active = PHPWS_Text::secureLink(dgettext('vmail', 'Deactivate'), 'vmail', $vars);
            } else {
                $vars['aop'] = 'activate_recipient';
                $active = PHPWS_Text::secureLink(dgettext('vmail', 'Activate'), 'vmail', $vars);
            }
            $links[] = $active;
        }

        if($links)
            $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vmail_recipients');

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('vmail');
        $key->setItemName('recipient');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_recipient');
        $key->setUrl($this->viewLink(true));
        $key->active = (int)$this->active;
        $key->setTitle($this->label);
        $key->setSummary($this->subject);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('vmail_recipients');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }



    public function viewLink($bare=false)
    {
        $link = new PHPWS_Link($this->label, 'vmail', array('recipient'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }
}

?>