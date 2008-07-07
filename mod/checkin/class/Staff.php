<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Staff {
    var $id            = 0;
    var $user_id       = 0;
    var $filter        = null;
    /**
     * 0 = none
     * CO_FT_LAST_NAME = last name regexp
     * CO_FT_REASON    = by reason id
     */
    var $f_regexp      = null;
    var $filter_type   = 0;
    var $status        = 0;
    var $visitor_id    = 0;
    var $display_name  = null;
    var $_reasons      = null;

    function Checkin_Staff($id=0)
    {
        if (empty($id)) {
            return true;
        }

        $this->id = (int)$id;
        if (!$this->init()) {
            $this->id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('checkin_staff');
        $db->addJoin('left', 'checkin_staff', 'users', 'user_id', 'id');
        $db->addColumn('users.display_name');
        $db->addColumn('*');
        return $db->loadObject($this);
    }

    function loadReasons($include_summary=false)
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addColumn('id');
        if ($include_summary) {
            $db->addColumn('summary');
            $db->setIndexBy('id');
        }
        $result = $db->select('col');
        if (!PHPWS_Error::logIfError($result)) {
            $this->_reasons = & $result;
        }
    }

    function parseFilter($filter)
    {
        if (!$this->filter_type || $this->filter_type == CO_FT_REASON) {
            $this->filter   = null;
            $this->f_regexp = null;
        } else {
            $this->filter   = $filter;
            $this->f_regexp = $this->decodeFilter($filter);
        }
    }

    function decodeFilter($filter)
    {
        $filter = strtolower(str_replace(' ', '', $filter));
        $farray = explode(',', $filter);

        foreach ($farray as $val) {
            $subval = explode('-', $val);
            switch (1) {
            case strlen($val) == 1:
                $final[] = $val;
                break;

            case preg_match('/^\w{1}-\w{1}$/', $val):
                $final[] = "[$val]";
                break;

            case preg_match('/^\w{2}-\w{2}$/', $val):
                if (substr($subval[0], 0, 1) == substr($subval[1], 0, 1)) {
                    $final[] = sprintf('%s[%s-%s]', substr($subval[0], 0, 1),
                                       substr($subval[0], 1, 1),
                                       substr($subval[1], 1, 1));
                } else {
                    $char1 = substr($subval[0], 0, 1);
                    $char2 = substr($subval[0], 1, 1);
                    if ($char2 == 'a') {
                        $final[] = $char1;
                    } else {
                        $final[] = sprintf('%s[a-%s]', $char1, $char2);
                    }

                    $char3 = substr($subval[1], 0, 1);
                    $char4 = substr($subval[1], 1, 1);

                    if ($char4 == 'a') {
                        $final[] = $subval[1];
                    } else {
                        $final[] = sprintf('%s[a-%s]', $char3, $char4);
                    }
                }
                break;

            case preg_match('/^\w{1}-\w{2}$/', $val):
                $final[] = $subval[0];
                $char1 = substr($subval[1], 0, 1);
                $char2 = substr($subval[1], 1, 1);
                if ($char2 == 'a') {
                    $final[] = $subval[1];
                } else {
                    $final[] = sprintf('%s[a-%s]', $char1, $char2);
                }
                break;

            case preg_match('/^\w{2}-\w{1}$/', $val):
                $char1 = substr($subval[0], 0, 1);
                $char2 = substr($subval[0], 1, 1);
                $char3 = substr($subval[1], 0, 1);
                if ($char2 == 'z') {
                    $final[] = $subval[0];
                } else {
                    $final[] = sprintf('%s[%s-z]', $char1, $char2);
                }

                $start_char = (int)ord($char1);
                $final_char = (int)ord($char3);            
                if ($final_char - $start_char == 1) {
                    $final[] = $subval[1];
                } else {
                    for ($i = $start_char; $i < $final_char; $i++);
                    $final[] = sprintf('[%s-%s]', chr($start_char + 1), chr($i));
                }
                break;
     
            default:
                $final[] = $val;
                break;
            }
        }
        return implode('|', $final);
    }


    function row_tags()
    {
        switch ($this->filter_type) {
        case 0 :
            $tpl['FILTER_INFO'] = dgettext('checkin', 'None');
            break;

        case CO_FT_LAST_NAME:
            $tpl['FILTER_INFO'] = sprintf(dgettext('checkin', 'Last name: %s'), $this->filter);
            break;

        case CO_FT_REASON:
            $this->loadReasons(true);
            $tpl['FILTER_INFO'] = implode('<br>', $this->_reasons);
            break;
        }
        $vars['staff_id'] = $this->id;
        $vars['aop'] = 'edit_staff';
        $links[] = PHPWS_Text::secureLink(dgettext('checkin', 'Edit'), 'checkin', $vars);
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function save($new=false)
    {
        $db = new PHPWS_DB('checkin_staff');
        $result = !PHPWS_Error::logIfError($db->saveObject($this));

        if (!$result) {
            return false;
        }
        // Save reason assignments

        $db = new PHPWS_DB('checkin_rtos');
        $db->addWhere('staff_id', $this->id);
        $db->delete();
        if ($this->filter_type == CO_FT_REASON) {
            foreach ($this->_reasons as $rid) {
                $db->reset();
                $db->addValue('staff_id', $this->id);
                $db->addValue('reason_id', $rid);
                PHPWS_Error::logIfError($db->insert());
            }
        }

        return true;
    }
}

?>