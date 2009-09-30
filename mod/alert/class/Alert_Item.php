<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Item {
    public $id               = 0;
    public $title            = null;
    public $description      = null;
    public $image_id         = 0;
    public $create_date      = 0;
    public $update_date      = 0;
    public $created_by_id    = 0;
    public $created_name     = null;
    public $updated_by_id    = 0;
    public $updated_name     = null;
    public $type_id          = 0;
    /**
     * 0 = not done
     * 1 = partially done
     * 2 = all done
     */
    public $contact_complete = 0;
    public $active           = true;

    public function Alert_Item($id=0)
    {
        if (!$id) {
            return true;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('alert_item');
        $db->loadObject($this);
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }


    public function setDescription($desc)
    {
        $this->description = PHPWS_Text::parseInput($desc);
    }

    public function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    public function rowTags()
    {
        $tpl = array();
        $vars['id'] = $this->id;

        $vars['aop'] = 'edit_item';
        $links[] = PHPWS_Text::secureLink(dgettext('alert', 'Edit'), 'alert', $vars);

        if (Current_User::allow('alert', 'reset_items')) {
            $js['question'] = dgettext('alert', 'Are you sure you want to reset this alert\\\'s contact status?');
            $js['link']     = dgettext('alert', 'Reset');
            $vars['aop'] = 'reset_item';
            $js['address']  = PHPWS_Text::linkAddress('alert', $vars, true);
            $links[] = javascript('confirm', $js);
        }

        if (Current_User::allow('alert', 'delete_items')) {
            $js['question'] = dgettext('alert', 'Are you sure you want to delete this alert?');
            $js['link']     = dgettext('alert', 'Delete');
            $vars['aop'] = 'delete_item';
            $js['address']  = PHPWS_Text::linkAddress('alert', $vars, true);
            $links[] = javascript('confirm', $js);
        }



        $vars['aop'] = 'deactivate_item';
        $yes_link = PHPWS_Text::secureLink(dgettext('alert', 'Yes'), 'alert', $vars);
        $vars['aop'] = 'activate_item';
        $no_link = PHPWS_Text::secureLink(dgettext('alert', 'No'), 'alert', $vars);

        $tpl['ACTIVE'] = $this->active ? $yes_link : $no_link;
        $tpl['ACTION'] = implode(' | ', $links);

        $tpl['CREATE_DATE'] = strftime(PHPWS_Settings::get('alert', 'date_format'), $this->create_date);
        $tpl['UPDATE_DATE'] = strftime(PHPWS_Settings::get('alert', 'date_format'), $this->update_date);

        return $tpl;
    }

    public function save()
    {
        if (!$this->id) {
            $this->create_date = mktime();
            $this->created_by_id = Current_User::getId();
            $this->created_name = Current_User::getUsername();
        }

        $this->update_date   = mktime();
        $this->updated_by_id = Current_User::getId();
        $this->updated_name  = Current_User::getUsername();

        $db = new PHPWS_DB('alert_item');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new PHPWS_DB('alert_item');
        $db->addWhere('id', $this->id);
        return !(PHPWS_Error::logIfError($db->delete()));
    }

    public function view()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $tpl['TITLE']       = $this->title;
        $tpl['DESCRIPTION'] = $this->getDescription();
        if ($this->image_id) {
            $tpl['IMAGE'] = Cabinet::getTag($this->image_id);
        } else {
            $tpl['IMAGE'] = null;
        }

        return PHPWS_Template::process($tpl, 'alert', 'view_item.tpl');
    }

    public function reset()
    {
        $this->contact_complete = 0;
        return $this->save();
    }

    public function createFeed()
    {
        PHPWS_Core::initModClass('rss', 'Feed.php');
        $feed = new Key;
        $feed->title = $this->title;
        $feed->url = 'index.php?module=alert&id=' . $this->id;
        $feed->summary = strip_tags($this->getDescription());
        return $feed;
    }

    public function getHTML()
    {
        $body[] = '<html><body>';
        $body[] =  $this->view();
        $body[] = '</body></html>';

        $content = implode('', $body);
        // Fixed relative links
        $content = str_replace('images/filecabinet', PHPWS_Core::getHomeHttp() . 'images/filecabinet', $content);

        return $content;
    }

    public function getBody()
    {
        return strip_tags($this->view());
    }

}

?>