<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Type {
    var $id = 0;
    var $title = null;
    var $email = false;
    var $rssfeed = false;
    var $post_type = 0;
    var $default_alert = null;

    function Alert_Type($id=0)
    {
        if (!$id) {
            return true;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('alert_item');
        $result = $db->loadObject($this);
        if (!$result || PHPWS_Error::isError($result)) {
            return $result;
        }
        return true;
    }

    function rowTags()
    {
        $links[] = PHPWS_Text::secureLink(dgettext('alert', 'Edit'), 'alert', array('aop'=>'edit_type', 'type_id'=>$this->id));

        if (Alert::canDeleteType($this->id)) {
            $js['question'] = dgettext('alert', 'Are you sure you want to delete this alert type?');
            $js['link']     = dgettext('alert', 'Delete');
            $js['address']  = PHPWS_Text::linkAddress('alert', array('aop'=>'delete_type', 'type_id'=>$this->id), true);
            $links[] = javascript('confirm', $js);
        }

        $tpl['EMAIL'] = $this->email ? dgettext('alert', 'Yes') : dgettext('alert', 'No');
        $tpl['RSSFEED'] = $this->rssfeed ? dgettext('alert', 'Yes') : dgettext('alert', 'No');

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function getDefaultAlert()
    {
        return PHPWS_Text::parseOutput($this->default_alert);
    }

    function setDefaultAlert($text)
    {
        $this->default_alert = PHPWS_Text::parseInput($text);
    }

    function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    function save()
    {
        $db = new PHPWS_DB('alert_type');
        return $db->saveObject($this);
    }

}

?>