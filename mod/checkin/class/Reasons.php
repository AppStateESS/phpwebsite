<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Reasons {
    var $id = 0;
    var $summary = null;
    var $message = null;

    function Checkin_Reasons($id=0)
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
        $db = new PHPWS_DB('checkin_reasons');
        return $db->loadObject($this);
    }

    function rowTags()
    {
        $vars['reason_id'] = $this->id;

        $vars['aop'] = 'edit_reason';
        $links[] = PHPWS_Text::secureLink(dgettext('checkin', 'Edit'), 'checkin', $vars);


        $vars['aop'] = 'delete_reason';
        $js['question'] = dgettext('confirm', 'Are you sure you want to delete this reason?.');
        $js['address']  = PHPWS_Text::linkAddress('checkin', $vars, true);
        $js['link'] = dgettext('checkin', 'Delete');
        $links[] = javascript('confirm', $js);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function delete()
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addWhere('id', $this->id);
        $result = !PHPWS_Error::logIfError($db->delete());
        if ($result) {
            $db = new PHPWS_DB('checkin_rtos');
            $db->addWhere('reason_id', $this->id);
            return !PHPWS_Error::logIfError($db->delete());
        }
        return false;
    }

    function save()
    {
        $db = new PHPWS_DB('checkin_reasons');
        return !PHPWS_Error::logIfError($db->saveObject($this));
    }
}

?>