<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Type {
    public $id            = 0;
    public $title         = null;
    public $email         = false;
    public $rssfeed       = false;
    public $feedname      = null;
    public $post_type     = 0;
    public $default_alert = null;
    public $_accessed     = false;

    public function __construct($id=0)
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

    public function init()
    {
        $db = new PHPWS_DB('alert_type');
        $result = $db->loadObject($this);

        if (!$result || PHPWS_Error::isError($result)) {
            return $result;
        }
        return true;
    }

    public function setFeedName($feedname)
    {
        $feedname = str_replace(' ', '_', $feedname);
        $this->feedname = preg_replace('/\W/', '', $feedname);
    }

    public function rowTags()
    {
        $links[] = PHPWS_Text::secureLink(dgettext('alert', 'Edit'), 'alert', array('aop'=>'edit_type', 'type_id'=>$this->id));

        $links[] = sprintf('%s/%s&nbsp;all',
        PHPWS_Text::secureLink(dgettext('alert', 'Add'), 'alert',
        array('aop'=>'add_all_participants', 'type_id'=>$this->id),
        null,
        sprintf(dgettext('alert', 'Add all participants to %s'), $this->title)),
        PHPWS_Text::secureLink(dgettext('alert', 'Remove'), 'alert',
        array('aop'=>'remove_all_participants', 'type_id'=>$this->id),
        null,
        sprintf(dgettext('alert', 'Remove all participants from %s'), $this->title))

        );

        if (Current_User::allow('alert', 'delete_type')) {
            $js['question'] = dgettext('alert', 'Are you sure you want to delete this alert type?');
            $js['link']     = dgettext('alert', 'Delete');
            $js['address']  = PHPWS_Text::linkAddress('alert', array('aop'=>'delete_type', 'type_id'=>$this->id), true);
            $links[] = javascript('confirm', $js);
        }

        $tpl['EMAIL'] = $this->email ? dgettext('alert', 'Yes') : dgettext('alert', 'No');
        $tpl['RSSFEED'] = $this->rssfeed ? PHPWS_Text::rewriteLink(dgettext('alert', 'Yes'), 'alert', array('rssfeed'=>$this->feedname)) : dgettext('alert', 'No');

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function getDefaultAlert()
    {
        return PHPWS_Text::parseOutput($this->default_alert);
    }

    public function setDefaultAlert($text)
    {
        $this->default_alert = PHPWS_Text::parseInput($text);
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    public function save()
    {
        $db = new PHPWS_DB('alert_type');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new PHPWS_DB('alert_type');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PHPWS_Error::logIfError($result)) {
            return;
        }

        $db = new PHPWS_DB('alert_item');
        $db->addWhere('type_id', $this->id);
        return $db->delete();
    }

    /**
     * Returns items for this type dependent on setting
     */
    public function getItems()
    {
        $db = new PHPWS_DB('alert_item');
        $db->addWhere('type_id', $this->id);
        $db->addWhere('active', 1);
        $db->addOrder('create_date desc');
        switch($this->post_type) {
            case APST_NONE:
                break;

            case APST_WEEKLY:
                $db->addWhere('create_date', time() - (7 * 86400), '>=');
                break;

            case APST_DAILY:
                $db->addWhere('create_date', time() - 86400, '>=');
                break;

            case APST_PERM:
                break;

        }

        $db->loadClass('alert', 'Alert_Item.php');
        return $db->getObjects('Alert_Item');
    }

}

?>