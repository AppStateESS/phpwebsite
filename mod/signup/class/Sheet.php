<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Signup_Sheet {
    public $id            = 0;
    public $key_id        = 0;
    public $title         = null;
    public $description   = null;
    public $start_time    = 0;
    public $end_time      = 0;
    public $contact_email = null;
    public $multiple      = 0;
    public $_error        = null;

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
        $db = new PHPWS_DB('signup_sheet');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    public function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    public function getStartTime()
    {
        if (!$this->start_time) {
            return strftime('%Y/%m/%d %H:00', mktime());
        } else {
            return strftime('%Y/%m/%d %H:00', $this->start_time);
        }
    }

    public function getEndTime()
    {
        if (!$this->end_time) {
            return strftime('%Y/%m/%d %H:00', mktime() + (86400 * 7));
        } else {
            return strftime('%Y/%m/%d %H:00', $this->end_time);
        }
    }

    public function defaultStart()
    {
        $this->start_time = mktime() - 86400;
    }

    public function defaultEnd()
    {
        $this->end_time = mktime(0,0,0,1,1,2020);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('signup_sheet');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);

        $db = new PHPWS_DB('signup_slots');
        $db->addWhere('sheet_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('sheet_id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }

    public function editSlotLink()
    {
        $vars['aop'] = 'edit_slots';
        $vars['sheet_id']  = $this->id;
        return PHPWS_Text::moduleLink(dgettext('signup', 'Edit slots'), 'signup', $vars);
    }

    public function getAllSlots($bare=false, $search=null)
    {
        PHPWS_Core::initModClass('signup', 'Slots.php');
        $db = new PHPWS_DB('signup_slots');
        $db->addOrder('s_order');
        $db->addWhere('sheet_id', $this->id);

        if ($search) {
            $db->addWhere('signup_peeps.sheet_id', $this->id);
            $db->addWhere('signup_peeps.first_name', "$search%", 'like', 'and', 'search');
            $db->addWhere('signup_peeps.last_name', "$search%", 'like', 'or', 'search');
        }

        if ($bare) {
            $db->addColumn('id');
            $db->addColumn('title');
            $db->setIndexBy('id');
            return $db->select('col');
        } else {
            $db->addColumn('signup_slots.*');
            $db->addColumn('signup_peeps.id', false, '_filled', true);
            $db->addJoin('left', 'signup_slots', 'signup_peeps', 'id', 'slot_id');

            $db->addWhere('signup_peeps.registered', 1, null, null, 1);

            $db->addWhere('signup_peeps.slot_id', null, null, 'or', 1);
            $db->setGroupConj(1, 'and');

            $db->addGroupBy('signup_slots.id');
            $result = $db->getObjects('Signup_Slot');
        }
        return $result;
    }

    public function rowTag()
    {
        $vars['sheet_id'] = $this->id;
        if (Current_User::allow('signup', 'edit_sheet', $this->id, 'sheet')) {
            if (Current_User::isUnrestricted('signup')) {
                $vars['aop']  = 'edit_sheet';
                $links[] = PHPWS_Text::secureLink(dgettext('signup', 'Edit'), 'signup', $vars);
            }

            $vars['aop']  = 'edit_slots';
            $links[] = PHPWS_Text::secureLink(dgettext('signup', 'Slots'), 'signup', $vars);

            if (Current_User::isUnrestricted('signup')) {
                $links[] = Current_User::popupPermission($this->key_id);
            }
        }

        $vars['aop'] = 'report';
        $links[] = PHPWS_Text::secureLink(dgettext('signup', 'Report'), 'signup', $vars);

        if (Current_User::isUnrestricted('signup')) {
            $vars['aop'] = 'delete_sheet';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('signup', $vars, true);
            $js['QUESTION'] = dgettext('signup', 'Are you sure you want to delete this sheet?\nAll slots and signup information will be permanently removed.');
            $js['LINK'] = dgettext('signup', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function save()
    {
        $db = new PHPWS_DB('signup_sheet');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
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
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('signup');
        $key->setItemName('sheet');
        $key->setItemId($this->id);
        $key->setEditPermission('edit_sheet');

        if (MOD_REWRITE_ENABLED) {
            $key->setUrl('signup/' . $this->id);
        } else {
            $key->setUrl('index.php?module=signup&amp;id=' . $this->id);
        }

        $key->setTitle($this->title);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('signup_sheet');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }

    /**
     * Returns an array indexed by slot id containing the number
     * of slots filled so far.
     */
    public function totalSlotsFilled()
    {
        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('sheet_id', $this->id);
        $db->addWhere('registered', 1);
        $db->addColumn('slot_id');
        $taken = $db->select('col');

        if (!$taken) {
            return null;
        }

        foreach ($taken as $slot_id) {
            if (empty($totals[$slot_id])) {
                $totals[$slot_id] = 1;
            } else {
                $totals[$slot_id]++;
            }
        }

        return $totals;
    }

    public function viewLink()
    {
        return PHPWS_Text::rewriteLink($this->title, 'signup', $this->id);
    }

    public function flag()
    {
        $key = new Key($this->key_id);
        $key->flag();
    }

}

?>