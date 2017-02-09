<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

\phpws\PHPWS_Core::initModClass('calendar', 'Event.php');

class Calendar_Suggestion extends Calendar_Event {
    public $id          = 0;
    public $schedule_id = 0;
    public $summary     = null;
    public $location    = null;
    public $loc_link    = null;
    public $description = null;
    public $all_day     = 0;
    public $start_time  = 0;
    public $end_time    = 0;
    public $submitted   = 0;


    public function __construct($id=0)
    {
        if (empty($id)) {
            $this->start_time = PHPWS_Time::getUserTime();
            $this->end_time   = PHPWS_Time::getUserTime();
            return;
        } else {
            $this->id = (int)$id;
            $result = $this->init();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $this->id = 0;
            } elseif (!$result) {
                $this->id = 0;
            }
        }
    }

    public function delete()
    {
        $db = new PHPWS_DB('calendar_suggestions');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

    public function getTpl()
    {
        $tpl['SUMMARY']     = $this->summary;
        $tpl['DESCRIPTION'] = $this->getDescription();

        if (CALENDAR_MONTH_FIRST) {
            $month_day_mode = '%B %e';
        } else {
            $month_day_mode = '%e %B';
        }

        if ($this->all_day) {
            $tpl['TO'] = '&ndash;';
            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                if (CALENDAR_MONTH_FIRST) {
                    if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%B %e, %Y', $this->start_time));
                    } else {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%B %e', $this->start_time));
                    }
                } else {
                    if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%e, %Y', $this->start_time));
                    } else {
                        $tpl['START_TIME'] =  sprintf(dgettext('calendar', 'All day event, %s'), strftime('%e', $this->start_time));
                    }
                }

                if (date('Ym', $this->start_time) != date('Ym', $this->end_time)) {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%B %e, %Y', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e, %Y', $this->end_time);
                    }
                } else {
                    if (CALENDAR_MONTH_FIRST) {
                        $tpl['END_TIME'] = strftime('%e, %Y', $this->end_time);
                    } else {
                        $tpl['END_TIME'] = strftime('%e %B, %Y', $this->end_time);
                    }
                }
            } else {
                $tpl['START_TIME'] =  'All day event';
                $tpl['END_TIME'] = $this->getStartTime($month_day_mode);
            }

        } else {
            if (date('Ymd', $this->start_time) != date('Ymd', $this->end_time)) {
                // If this event happens over 2 or more day
                if (date('Y', $this->start_time) != date('Y', $this->end_time)) {
                    $tpl['START_TIME'] = $this->getStartTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
                } else {
                    $tpl['START_TIME'] = $this->getStartTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode);
                }
                $tpl['END_TIME']   = $this->getEndTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
            } else {
                $tpl['START_TIME']   = $this->getStartTime(CALENDAR_TIME_FORMAT);
                $tpl['END_TIME'] = $this->getEndTime(CALENDAR_TIME_FORMAT . ', ' . $month_day_mode . ', %Y');
            }
            $tpl['TO'] = 'to';
        }


        if (!empty($this->location)) {
            if (!empty($this->loc_link)) {
                $tpl['LOCATION'] = sprintf('<a href="%s" title="%s">%s</a>',
                PHPWS_Text::checkLink($this->loc_link),
                dgettext('calendar', 'Visit this location\'s web site.'),
                $this->location);
            } else {
                $tpl['LOCATION'] = $this->location;
            }
        }

        $vars['suggestion_id'] = $this->id;

        $vars['aop'] = 'approve_suggestion';
        $links[] = PHPWS_Text::secureLink('Approve', 'calendar', $vars);

        $vars['aop'] = 'disapprove_suggestion';
        $links[] = PHPWS_Text::secureLink('Disapprove', 'calendar', $vars);

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }


    public function init()
    {
        $db = new PHPWS_DB('calendar_suggestions');
        return $db->loadObject($this);
    }

    public function post($suggested=false)
    {
        return parent::post(true);
    }

    public function save()
    {
        $this->schedule_id = $this->_schedule->id;
        $this->submitted = time();
        $db = new PHPWS_DB('calendar_suggestions');
        return $db->saveObject($this);
    }


}
