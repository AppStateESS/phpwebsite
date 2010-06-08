<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class RB_Carpool {
    public $id            = 0;
    public $user_id       = 0;
    public $email         = null;
    public $created       = 0;
    public $start_address = null;
    public $dest_address  = null;
    public $comment       = null;

    public function __construct($id=0)
    {
        if (!$id) {
            $this->dest_address = Core\Settings::get('rideboard', 'default_destination');
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        $db = new Core\DB('rb_carpool');
        $result = $db->loadObject($this);
        if (Core\Error::logIfError($result) || !$result) {
            $this->id = 0;
        }
    }

    public function getCreated()
    {
        return strftime('%e %b, %Y', $this->created);
    }

    public function view()
    {
        $tpl = array();

        $tpl['EMAIL'] = sprintf('<a href="mailto:%s">%s</a>', $this->email, dgettext('rideboard', 'Contact poster'));
        $tpl['CREATED_LABEL'] = dgettext('rideboard', 'Date created');
        $tpl['CREATED'] = $this->getCreated();
        $tpl['START_ADDRESS_LABEL'] = dgettext('rideboard', 'Carpool start');
        $tpl['START_ADDRESS'] = & $this->start_address;
        $tpl['DEST_ADDRESS_LABEL'] = dgettext('rideboard', 'Carpool destination');
        $tpl['DEST_ADDRESS'] = & $this->dest_address;
        $tpl['COMMENTS_LABEL'] = dgettext('rideboard', 'Additional information');
        if (!empty($this->comment)) {
            $tpl['COMMENTS'] = $this->getComment();
        } else {
            $tpl['COMMENTS'] = dgettext('rideboard', 'None');
        }

        $tpl['CLOSE'] = javascript('close_window');

        return Core\Template::process($tpl, 'rideboard', 'carpool_view.tpl');
    }

    public function row_tags()
    {
        $tpl['CREATED'] = $this->getCreated();
        $js['address'] = Core\Text::linkAddress('rideboard', array('uop'=>'cpinfo', 'cid'=>$this->id));
        $js['label'] = dgettext('rideboard', 'More information');
        $js['width'] = 640;
        $js['height'] = 480;
        $links[] = javascript('open_window', $js);

        if ($this->allowDelete()) {
            $confirm['question'] = dgettext('rideboard', 'Are you sure you want to remove this carpool?');
            $confirm['address'] = Core\Text::linkAddress('rideboard', array('uop'=>'delete_carpool', 'cid'=>$this->id), true);
            $confirm['link'] = dgettext('rideboard', 'Delete');
            $confirm['title'] = dgettext('rideboard', 'Delete this carpool');
            $links[] = javascript('confirm', $confirm);
        }

        if ($this->allowEdit()) {
            $links[] = javascript('open_window',
            array('address' => Core\Text::linkAddress('rideboard',
            array('uop'=>'carpool_form', 'cid'=>$this->id)),
                                        'label'=> dgettext('rideboard', 'Edit'),
                                        'width'=>640, 'height'=>480));

        }

        $tpl['LINKS'] = implode(' | ', $links);

        return $tpl;
    }

    public function setComment($comment)
    {
        $this->comment = Core\Text::parseInput(strip_tags($comment));
    }

    public function allowDelete()
    {
        return (Current_User::allow('rideboard', 'delete_carpools') || Current_User::getId() == $this->user_id);
    }

    public function allowEdit()
    {
        return (Current_User::allow('rideboard') || Current_User::getId() == $this->user_id);
    }

    public function getComment()
    {
        return Core\Text::parseOutput($this->comment, null, true, true);
    }

    public function setAddress($type, $address)
    {
        $address = preg_replace('/[^\w\.\-\s\']/', '', strip_tags($address));

        if ($type == 'start') {
            $this->start_address = $address;
        } else {
            $this->dest_address = $address;
        }
    }

    public function save()
    {
        $db = new Core\DB('rb_carpool');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new Core\DB('rb_carpool');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

}

?>