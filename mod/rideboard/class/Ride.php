<?php
/**
 * Generic class extended by those offering or needing rides
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class RB_Ride {
    public $id            = 0;
    public $title         = null;
    public $ride_type     = RB_RIDER;
    public $user_id       = 0;
    public $s_location    = 0;
    public $d_location    = 0;
    public $depart_time   = 0;
    public $smoking       = RB_NONSMOKER;
    public $comments      = null;
    public $detour        = 0;
    public $gender_pref   = RB_EITHER;
    public $marked        = 0;

    public $start_location = null;
    public $dest_location  = null;

    public function __construct($id=0)
    {
        if (!$id) {
            $this->s_location = Core\Settings::get('rideboard', 'default_slocation');
            return;
        }

        $this->id = (int)$id;
        $db = new Core\DB('rb_ride');
        $db->addTable('rb_location', 't1');
        $db->addTable('rb_location', 't2');
        $db->addColumn('*');
        $db->addJoin('left', 'rb_ride', 't1', 's_location', 'id');
        $db->addJoin('left', 'rb_ride', 't2', 'd_location', 'id');
        $db->addColumn('t1.city_state', null, 'start_location');
        $db->addColumn('t2.city_state', null, 'dest_location');

        if (Core\Error::logIfError($db->loadObject($this))) {
            $this->id = 0;
        }
    }

    public function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    public function setComments($comments)
    {
        $this->comments = trim(strip_tags($comments));
    }

    public function save()
    {
        $db = new Core\DB('rb_ride');
        if (!$this->user_id) {
            $this->user_id = Current_User::getId();
        }

        return $db->saveObject($this);
    }

    public function getDepartTime()
    {
        if (time() > $this->depart_time) {
            return sprintf('%s (%s)', strftime('%d %b, %Y', $this->depart_time),
            dgettext('rideboard', 'Expired'));
        } else {
            return strftime('%d %B, %Y', $this->depart_time);
        }
    }


    public function getRideType()
    {
        switch ($this->ride_type) {
            case RB_RIDER:
                return dgettext('rideboard', 'Rider');

            case RB_DRIVER:
                return dgettext('rideboard', 'Driver');

            case RB_EITHER:
                return dgettext('rideboard', 'Driver or rider');
        }
    }

    public function getGenderPref()
    {
        switch ($this->gender_pref) {
            case RB_MALE:
                return dgettext('rideboard', 'Male');

            case RB_FEMALE:
                return dgettext('rideboard', 'Female');

            case RB_EITHER:
                return dgettext('rideboard', 'Either gender');
        }
    }

    public function getSmoking()
    {
        switch ($this->smoking) {
            case RB_NONSMOKER:
                return dgettext('rideboard', 'Non-smokers only');

            case RB_SMOKER:
                return dgettext('rideboard', 'Smokers please');

            case RB_EITHER:
                return dgettext('rideboard', 'Smoking not important');
        }
    }

    public function tags($admin=true)
    {
        $tpl['TITLE']       = & $this->title;
        $tpl['RIDE_TYPE']   = $this->getRideType();
        $tpl['GENDER_PREF'] = $this->getGenderPref();
        $tpl['SMOKING']     = $this->getSmoking();
        $tpl['COMMENTS']    = Core\Text::parseOutput($this->comments);
        $tpl['DEPART_TIME'] = $this->getDepartTime();

        if ($this->s_location) {
            $tpl['START_LOCATION'] = & $this->start_location;
        } else {
            $tpl['START_LOCATION'] = dgettext('rideboard', 'Not indicated');
        }

        if ($this->d_location) {
            $tpl['DEST_LOCATION'] = & $this->dest_location;
        } else {
            $tpl['DEST_LOCATION'] = dgettext('rideboard', 'Not indicated');
        }

        $links[] = javascript('open_window',
        array('address'=>Core\Text::linkAddress('rideboard', array('uop'=>'view_ride',
                                                                                          'rid'=>$this->id)),
                                    'label'  => dgettext('rideboard', 'Read more'),
                                    'width' => 640,
                                    'height' => 480
        ));

        if ($admin && ($this->user_id == Current_User::getId() || Current_User::allow('rideboard'))) {
            $js['question'] = dgettext('rideboard', 'Are you sure you want to delete this ride?');
            $js['address'] = Core\Text::linkAddress('rideboard', array('uop'=>'delete_ride',
                                                                        'rid'=>$this->id),
            true);
            $js['link'] = dgettext('rideboard', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['ADMIN_LINKS'] = implode(' | ', $links);

        return $tpl;
    }

    public function delete()
    {
        $db = new Core\DB('rb_ride');
        $db->addWhere('id', $this->id);
        return !Core\Error::logIfError($db->delete());
    }

    public function view()
    {
        $tpl = $this->tags(false);

        if ($this->depart_time < time()) {
            $tpl['WARNING'] = dgettext('rideboard', 'The ride has expired.');
        }

        $user = new PHPWS_User($this->user_id);

        $tpl['EMAIL'] = sprintf('<a href="mailto:%s">%s</a>',
        $user->getEmail(),
        dgettext('rideboard', 'Email user'));
        $tpl['RIDE_TYPE_LABEL'] = dgettext('rideboard', 'Driver or Rider');
        $tpl['GEN_PREF_LABEL']  = dgettext('rideboard', 'Gender preference');
        $tpl['SMOKE_LABEL']     = dgettext('rideboard', 'Smoking preference');

        if ($this->s_location) {
            if ($this->d_location) {
                $drive_info = sprintf(dgettext('rideboard', 'Driving from <strong>%s</strong> on <strong>%s</strong> to <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME'],
                $tpl['DEST_LOCATION']);
                $ride_info  = sprintf(dgettext('rideboard', 'Need a ride from <strong>%s</strong> on or around <strong>%s</strong> to <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME'],
                $tpl['DEST_LOCATION']);
                $share_info = sprintf(dgettext('rideboard', 'Sharing (driving or riding) a ride from <strong>%s</strong> on or around <strong>%s</strong> to <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME'],
                $tpl['DEST_LOCATION']);
            } else {
                $drive_info = sprintf(dgettext('rideboard', 'Driving from <strong>%s</strong> on <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME']);
                $ride_info  = sprintf(dgettext('rideboard', 'Need a ride from <strong>%s</strong> on or around <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME']);
                $share_info = sprintf(dgettext('rideboard', 'Sharing (driving or riding) a ride from <strong>%s</strong> on or around <strong>%s</strong>.'),
                $tpl['START_LOCATION'], $tpl['DEPART_TIME']);
            }
        } else {
            if ($this->d_location) {
                $drive_info = sprintf(dgettext('rideboard', 'Driving to <strong>%s</strong> on <strong>%s</strong>.'),
                $tpl['DEST_LOCATION'], $tpl['DEPART_TIME']);
                $ride_info  = sprintf(dgettext('rideboard', 'Need a ride to <strong>%s</strong> on or around <strong>%s</strong>.'),
                $tpl['DEST_LOCATION'], $tpl['DEPART_TIME']);
                $share_info = sprintf(dgettext('rideboard', 'Sharing (driving or riding) a ride to <strong>%s</strong> on or around <strong>%s</strong>.'),
                $tpl['DEST_LOCATION'], $tpl['DEPART_TIME']);
            } else {
                $drive_info = sprintf(dgettext('rideboard', 'Driving on <strong>%s</strong>.'), $tpl['DEPART_TIME']);
                $ride_info  = sprintf(dgettext('rideboard', 'Need a ride on or around <strong>%s</strong>.'), $tpl['DEPART_TIME']);
                $share_info = sprintf(dgettext('rideboard', 'Sharing (driving or riding) a ride on or around <strong>%s</strong>.'), $tpl['DEPART_TIME']);
            }
        }

        if ($this->ride_type == RB_DRIVER) {
            $tpl['INFO']            = $drive_info;
        } elseif ($this->ride_type == RB_RIDER) {
            $tpl['INFO']            = $ride_info;
        } else {
            $tpl['INFO']            = $share_info;
        }

        $tpl['COMMENT_LABEL'] = dgettext('rideboard', 'Comments');

        $tpl['CLOSE'] = javascript('close_window');

        return Core\Template::process($tpl, 'rideboard', 'view_ride.tpl');
    }
}

?>