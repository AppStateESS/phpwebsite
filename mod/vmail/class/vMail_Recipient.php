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
        $db = new Core\DB('vmail_recipients');
        $result = $db->loadObject($this);
        if (Core\Error::isError($result)) {
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
        if (Core\Text::isValidInput($address, 'email')) {
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
        $this->submit_message = Core\Text::parseInput($submit_message);
    }


    public function getLabel($print=false)
    {
        if (empty($this->label)) {
            return null;
        }

        if ($print) {
            return Core\Text::parseOutput($this->label);
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
            return Core\Text::parseOutput($this->address);
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
            return Core\Text::parseOutput($this->prefix);
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
            return Core\Text::parseOutput($this->subject);
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
            return Core\Text::parseOutput($this->submit_message);
        } else {
            return $this->submit_message;
        }
    }

    public function submitMessage()
    {
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['RECIPIENT'] = $this->getLabel(true);
        if ($this->getSubmit_message()) {
            $tpl['SUBMIT_MESSAGE'] = Core\Text::parseTag($this->getSubmit_message(true));
        } else {
            $tpl['SUBMIT_MESSAGE'] = dgettext('vmail', 'Your message was sent to');
        }

        return Core\Template::process($tpl, 'vmail', 'submit_message.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vmail', 'edit_recipient')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_recipient';
            $links[] = Core\Text::secureLink(dgettext('vmail', 'Edit recipient'), 'vmail', $vars);
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
        $db = new Core\DB('vmail_recipients');
        $db->addWhere('id', $this->id);
        Core\Error::logIfError($db->delete());

        Core\Key::drop($this->key_id);

    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('vmail', 'edit_recipient')) {
            $vars['aop']  = 'edit_recipient';
            $label = Core\Icon::show('edit');
            $links[] = Core\Text::secureLink($label, 'vmail', $vars);
        }
        if (Current_User::allow('vmail', 'delete_recipient')) {
            $vars['aop'] = 'delete_recipient';
            $js['ADDRESS'] = Core\Text::linkAddress('vmail', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vmail', 'Are you sure you want to delete the recipient %s?'), $this->getLabel());
            $js['LINK'] = Core\Icon::show('delete');
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
                $label = Core\Icon::show('active', dgettext('rolodex', 'Deactivate'));
                $active = Core\Text::secureLink($label, 'vmail', $vars);
            } else {
                $vars['aop'] = 'activate_recipient';
                $label = Core\Icon::show('inactive', dgettext('rolodex', 'Activate'));
                $active = Core\Text::secureLink($label, 'vmail', $vars);
            }
            $links[] = $active;
        }

        if($links)
            $tpl['ACTION'] = implode(' ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new Core\DB('vmail_recipients');

        $result = $db->saveObject($this);
        if (Core\Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Core\Key;
        } else {
            $key = new Core\Key($this->key_id);
            if (Core\Error::isError($key->_error)) {
                $key = new Core\Key;
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
        if (Core\Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new Core\DB('vmail_recipients');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            Core\Error::logIfError($db->update());
        }
        return true;
    }



    public function viewLink($bare=false)
    {
        $link = new Core\Link($this->label, 'vmail', array('recipient'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }
}

?>