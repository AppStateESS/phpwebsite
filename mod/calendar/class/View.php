<?php

  /**
   * Contains the various functions for viewing calendars
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_View {
    var $calendar = NULL;
    /**
     * pointer to events
     */
    var $event_sort = NULL;

    function main()
    {
        switch ($_REQUEST['view']) {
        case 'full':
            Layout::add($this->view->month_grid('full', $_REQUEST['month'], $_REQUEST['year']));
            break;
        }
    }

    function viewLinks($current_view)
    {
        $vars = PHPWS_Text::getGetValues();
        unset($vars['module']);

        $vars['schedule_id'] = $this->calendar->schedule->id;
        
        if ($current_view == 'month_list') {
            $links[] = _('Month list');
        } else {
            $vars['view'] = 'month_list';
            $links[] = PHPWS_Text::moduleLink(_('Month list'), 'calendar', $vars);
        }

        if ($current_view == 'month_grid') {
            $links[] = _('Month grid');
        } else {
            $vars['view'] = 'month_grid';
            $links[] = PHPWS_Text::moduleLink(_('Month grid'), 'calendar', $vars);
        }

        if ($current_view == 'week') {
            $links[] = _('Week');
        } else {
            $vars['view'] = 'week';
            $links[] = PHPWS_Text::moduleLink(_('Week'), 'calendar', $vars);
        }

        if ($current_view == 'day') {
            $links[] = _('Day');
        } else {
            $vars['view'] = 'day';
            $links[] = PHPWS_Text::moduleLink(_('Day'), 'calendar', $vars);
        }
        
        return implode(' | ', $links);

    }


    function month_grid($type='mini', $month=NULL, $year=NULL)
    {
        if (empty($month)) {
            $month = date('m');
        }

        if (empty($year)) {
            $year = date('Y');
        }

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);
        if (isset($this->calendar->schedule) ){
            $this->calendar->schedule->loadEvents($startdate, $enddate);
        }
        $this->sortEvents();
        if ($type != 'mini' && $type != 'full') {
            PHPWS_Core::errorPage('404');
        }
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->calendar->getMonth($month, $year);
        $date = $oMonth->thisMonth(TRUE);

        // Check cache
        //        $cache_key = sprintf('%s_%s_%s', $type, $oMonth->month, $oMonth->year);

        /*
        $content = PHPWS_Cache::get($cache_key);
        if (!empty($content)) {
            return $content;
        }
        */
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

            if (isset($this->event_sort[$year][$month][$day->day])) {
                $no_of_events = count($this->event_sort[$year][$month][$day->day]);
            } else {
                $no_of_events = 0;
            }
            
            if ($day->empty) {
                $data['CLASS'] = 'day-empty';
            } elseif ( $day->month == date('m', $this->calendar->today) &&
                       $day->day == date('d', $this->calendar->today)
                       ) {
                $data['CLASS'] = 'day-current';
            } else {
                $data['CLASS'] = 'day-normal';
            }

            $data['COUNT'] = sprintf('%s event(s)', $no_of_events);

            $oTpl->setCurrentBlock('calendar-col');
            $oTpl->setData($data);
            $oTpl->parseCurrentBlock();

            if ($day->last) {
                $oTpl->setCurrentBlock('calendar-row');
                $oTpl->setData(array('CAL_ROW' => ''));
                $oTpl->parseCurrentBlock();
            }
        }

        $vars['month'] = $month;
        $vars['year'] = $year;
        $vars['view'] = 'full';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);

        $oTpl->setData($template);
        $content = $oTpl->get();
        //        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }

    function day()
    {
        $uDate    = mktime(0, 0, 0, $this->calendar->month, $this->calendar->day, $this->calendar->year);
        $uDateEnd = $uDate + 82800 + 3540 + 59; // 23 hours, 59 minutes, 59 seconds later

        $template['VIEW_LINKS'] = $this->viewLinks('day');

        $template['TITLE'] = $this->calendar->schedule->title;
        $template['DATE'] = strftime(CALENDAR_DAY_FORMAT, $uDate);

        $js['month'] = $this->calendar->month;
        $js['day']   = $this->calendar->day;
        $js['year']  = $this->calendar->year;
        $js['url']   = $this->getUrl();
        $js['type']  = 'pick';
        $template['PICK'] = javascript('js_calendar', $js);

        /*
         // need to replace the below

        if (Current_User::allow('calendar', 'edit_schedule', $this->calendar->schedule->id) ||
            ( PHPWS_Settings::get('calendar', 'personal_calendars') && 
              $this->calendar->schedule->user_id == Current_User::getId()
              )
            ) {
            $template['ADD_EVENT'] = $this->calendar->schedule->addEventLink($now);
        }
        */

        $this->calendar->schedule->loadEvents($uDate, $uDateEnd);
        $events = & $this->calendar->schedule->events;

        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/day/day.tpl');

        if (empty($events)) {
            $template['MESSAGE'] = _('No events planned for this day.');
        } else {
            $hour_list = array();
            foreach ($events as $oEvent) {
                switch ($oEvent->event_type) {
                case '1':
                    if ($oEvent->block) {
                        $block_time = ceil( ($oEvent->end_time - $oEvent->start_time) / 3600);
                        $block_hour = strftime('%H', $oEvent->start_time);
                        $blocked[$block_hour] = 1;
                        if ($block_time > 1) {
                            for ($i = 1; $i < $block_time; $i++) {
                                $blocked[$block_hour + $i] = 1;
                            }
                        }
                    }
                case '3':
                    $newList[strftime('%H', $oEvent->start_time)][] = $oEvent;
                    break;

                case '2':
                    $newList[-1][] = $oEvent;
                    break;

                case '4':
                    $newList[strftime('%H', $oEvent->end_time)][] = $oEvent;
                    break;
                }
            }
            ksort($newList);

            foreach ($newList as $hour => $events) {
                foreach ($events as $oEvent) {
                    $details = $links = array();

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

                    if (!isset($hour_list[$hour])) {
                        $hour_list[$hour] = 1;
                        if ($hour == -1) {
                            $details['HOUR']    = _('All day');
                        } else {
                            $details['HOUR']    = strftime('%l %p', mktime($hour));
                        }
                    }

                    $template['calendar_events'][] = $details;
                }
            }

        }

        for ($j=8; $j < 18; $j++) {
            if (isset($blocked[$j])) {
                $block_class = 'full';
            } else {
                $block_class = 'free';
            }
            $template['glance_rows'][] = array('GLANCE_HOUR' => strftime('%l %p', mktime($j)),
                                               'BLOCK_CLASS' => $block_class);
        }

        return PHPWS_Template::process($template, 'calendar', 'view/day/day.tpl');
    }

    function sortEvents()
    {
        if (empty($this->calendar->schedule->events)) {
            return;
        }

        $events = & $this->calendar->schedule->events;
        foreach ($events as $key => $event) {
            $year = date('Y', $event->start_time);
            $month = date('m', $event->start_time);
            $day = date('d', $event->start_time);
            $this->event_sort[$year][$month][$day][] = & $this->calendar->schedule->events[$key];
        }
    }

    function getUrl()
    {
        $getVars = PHPWS_Text::getGetValues();
        $address[] = 'index.php?';
        unset($getVars['m']);
        unset($getVars['d']);
        unset($getVars['y']);
        foreach ($getVars as $key=>$value) {
            $newvars[] = "$key=$value";
        }

        $address[] = implode('&amp;', $newvars);

        return implode('', $address);
    }
}


?>