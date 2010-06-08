<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Reasons {
    public $id = 0;
    public $summary = null;
    public $message = null;

    public function __construct($id=0)
    {
        if (empty($id)) {
            return true;
        }

        $this->id = (int)$id;
        if (!$this->init()) {
            $this->id = 0;
        }
    }

    public function init()
    {
        $db = new \core\DB('checkin_reasons');
        return $db->loadObject($this);
    }

    public function rowTags()
    {
        $vars['reason_id'] = $this->id;

        $vars['aop'] = 'edit_reason';
        $links[] = \core\Text::secureLink(core\Icon::show('edit'), 'checkin', $vars);


        $vars['aop'] = 'delete_reason';
        $js['question'] = dgettext('confirm', 'Are you sure you want to delete this reason?.');
        $js['address']  = \core\Text::linkAddress('checkin', $vars, true);
        $js['link'] = \core\Icon::show('delete');
        $links[] = javascript('confirm', $js);

        $tpl['ACTION'] = implode('', $links);
        return $tpl;
    }

    public function delete()
    {
        $db = new \core\DB('checkin_reasons');
        $db->addWhere('id', $this->id);
        $result = !core\Error::logIfError($db->delete());
        if ($result) {
            $db = new \core\DB('checkin_rtos');
            $db->addWhere('reason_id', $this->id);
            return !core\Error::logIfError($db->delete());
        }
        return false;
    }

    public function save()
    {
        $db = new \core\DB('checkin_reasons');
        return !core\Error::logIfError($db->saveObject($this));
    }
}

?>