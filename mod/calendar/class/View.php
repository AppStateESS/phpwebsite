<?php

  /**
   * Contains the various functions for viewing calendars
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_View {
    var $calendar = NULL;

    function main()
    {
        switch ($_REQUEST['view']) {
        case 'full':
            Layout::add($this->view->month_grid('full', $_REQUEST['month'], $_REQUEST['year']));
            break;
        }
    }

    function month_grid($type='mini', $month=NULL, $year=NULL)
    {
        if ($type != 'mini' && $type != 'full') {
            PHPWS_Core::errorPage('404');
        }
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->calendar->getMonth($month, $year);
        $date = $oMonth->thisMonth(TRUE);

        // Check cache
        $cache_key = sprintf('%s_%s_%s', $type, $oMonth->month, $oMonth->year);
        $content = PHPWS_Cache::get($cache_key);
        if (!empty($content)) {
            return $content;
        }

        // Cache empty, make month

        $oTpl = & new PHPWS_Template('calendar');
        $oTpl->setFile(sprintf('view/month/%s.tpl', $type));

        $day_count = 0;

        while($day = $oMonth->fetch()) {
            $day_count++;
            $oTpl->setCurrentBlock('calendar-weekdays');
            $wData['FULL_WEEKDAY'] = strftime('%A', $day->thisDay(TRUE));
            $wData['ABRV_WEEKDAY'] = strftime('%a', $day->thisDay(TRUE));
            $wData['LETTER_WEEKDAY'] = substr($wData['ABRV_WEEKDAY'], 0, 1);
            $oTpl->setData($wData);
            $oTpl->parseCurrentBlock();

            if ($day->last) {
                break;
            }
        }

        reset($oMonth->children);

        while($day = $oMonth->fetch()) {
            $data['DAY'] = $day->day;

            if ($day->empty) {
                $data['CLASS'] = 'day-empty';
            } elseif ( $day->month == date('m', $this->calendar->today) &&
                       $day->day == date('d', $this->calendar->today)
                       ) {
                $data['CLASS'] = 'day-current';
            } else {
                $data['CLASS'] = 'day-normal';
            }

            $oTpl->setCurrentBlock('calendar-col');
            $oTpl->setData($data);
            $oTpl->parseCurrentBlock();

            if ($day->last) {
                $oTpl->setCurrentBlock('calendar-row');
                $oTpl->setData(array('CAL_ROW' => ''));
                $oTpl->parseCurrentBlock();
            }
        }

        $vars['month'] = $oMonth->month;
        $vars['year'] = $oMonth->year;
        $vars['view'] = 'full';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);

        $oTpl->setData($template);
        $content = $oTpl->get();
        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }

    function day($year=NULL, $month=NULL, $day=NULL)
    {
        if (empty($year) || $year < 1970) {
            $aDate = PHPWS_Time::getTimeArray();

            if (isset($_REQUEST['y'])) {
                $year = $_REQUEST['y'];
            } else {
                $year  = &$aDate['y'];
            }

            if (isset($_REQUEST['m'])) {
                $month = $_REQUEST['m'];
            } else {
                $month = &$aDate['m'];
            }

            if (isset($_REQUEST['d'])) {
                $day = $_REQUEST['d'];
            } else {
                $day   = &$aDate['d'];
            }

        }

        $uDate = mktime(0, 0, 0, $month, $day, $year);
        $uDateEnd = mktime(23, 59, 0, $month, $day, $year);
        $now = mktime(date('G'),(int)date('i') , 0, $month, $day, $year);

        if (Current_User::allow('calendar', 'edit_schedule', $this->calendar->schedule->id) ||
            ( PHPWS_Settings::get('calendar', 'personal_calendars') && 
              $this->calendar->schedule->user_id == Current_User::getId()
              )
            ) {
            $template['ADD_EVENT'] = $this->calendar->schedule->addEventLink($now);
        }
        $template['TITLE'] = $this->calendar->schedule->title;
        $template['DATE'] = strftime(CALENDAR_DAY_FORMAT, $uDate);


        $js['month'] = $month;
        $js['day'] = $day;
        $js['year'] = $year;
        $js['url'] = 'index.php?module=calendar&aop=main';
        $js['type'] = 'pick';
        $template['PICK'] = javascript('js_calendar', $js);


        $start_date = mktime(0,0,0, $month, $day, $year);
        $end_date = mktime(23,59,59, $month, $day, $year);

        $this->calendar->schedule->loadEvents($uDate, $uDateEnd);
        $events = & $this->calendar->schedule->events;

        if (empty($events)) {
            $template['MESSAGE'] = _('No events planned for this day.');
        } else {
            foreach ($events as $oEvent) {
                $links = array();
                if (Current_User::allow('calendar', 'edit_event', $oEvent->id)) {
                    $links[] = $oEvent->removeLink($this->calendar->schedule->id);
                    $links[] = $oEvent->editLink();
                }
                
                if (Current_User::allow('calendar', 'delete_event', $oEvent->id)) {
                    $links[] = $oEvent->deleteLink();
                }

                if (!empty($links)) {
                    $details['LINKS'] = implode(' | ', $links);
                }

                $details['TITLE']   = $oEvent->title;
                $details['SUMMARY'] = $oEvent->getSummary();
                $details['TIME']    = $oEvent->getTime();
                $template['calendar_events'][] = $details;
            }
        }

        return PHPWS_Template::process($template, 'calendar', 'view/day/day.tpl');
    }
}


?>