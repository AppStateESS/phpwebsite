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
    var $event_list = NULL;

    function main()
    {
        switch ($_REQUEST['view']) {
        case 'full':
            Layout::add($this->view->month_grid('full', $_REQUEST['m'], $_REQUEST['y']));
            break;
        }
    }

    /**
     * Returns a set of links to navigate the different calendar views
     *
     * @param string current_view   Name of the current view
     * @return string
     */
    function viewLinks($current_view)
    {
        $vars = PHPWS_Text::getGetValues();
        unset($vars['module']);

        if (isset($this->calendar->schedule)) {
            $vars['schedule_id'] = $this->calendar->schedule->id;
        }
        $vars['m'] = $this->calendar->month;
        $vars['d'] = $this->calendar->day;
        $vars['y'] = $this->calendar->year;

        
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

    function mini_month()
    {
        $month = &$this->calendar->month;
        $year  = &$this->calendar->year;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->calendar->getMonth();
        $date = $oMonth->thisMonth(TRUE);

        $oTpl = & new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/mini.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);
        $this->_month_days($oMonth, $oTpl);

        $vars['m'] = $month;
        $vars['y'] = $year;
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

    function _weekday(&$oMonth, &$oTpl)
    {
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

    }

    function dayLink($label, $month, $day, $year)
    {
        return PHPWS_Text::moduleLink($label, 'calendar',
                                      array('view' => 'day',
                                            'm' => (int)$month,
                                            'y' => (int)$year,
                                            'd' => (int)$day));
    }

    function _month_days(&$oMonth, &$oTpl)
    {
        $month = &$this->calendar->month;
        $year  = &$this->calendar->year;

        while($day = $oMonth->fetch()) {
            $data['COUNT'] = NULL;
            $no_of_events = 0;
            $data['DAY'] = $this->dayLink($day->day, $month, $day->day, $year);


            if (isset($this->event_sort[$day->year]['months'][$day->month]['days'][$day->day]['events'])) {
                $no_of_events = count($this->event_sort[$day->year]['months'][$day->month]['days'][$day->day]['events']);
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

            if ($no_of_events) {
                $data['COUNT'] = sprintf('%s event(s)', $no_of_events);
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
    }


    /**
     * Adds events between the start and end date to the view's event_list
     * variable
     */
    function loadEventList($startdate, $enddate) {
        if (isset($this->calendar->schedule) ){
            $this->calendar->schedule->loadEvents($startdate, $enddate);
            $this->event_list = & $this->calendar->schedule->events;
        } else {
            $public_calendars = $this->calendar->getPublicCalendars();
            if (!empty($public_calendars)) {
                $this->event_list = $this->calendar->getEvents($startdate, $enddate, $public_calendars);
            }
        }

        $this->sortEvents();
    }


    function getViewTitle()
    {
        if (isset($this->calendar->schedule)) {
            return $this->calendar->schedule->title;
        } else {
            return _('Public events');
        }
    }

    function loadDayList($month, $year, &$tpl)
    {
        
        if (empty($this->event_sort[$year]['months'][$month]['days'])) {
            $day = 1;
        } else {
            foreach ($this->event_sort[$year]['months'][$month]['days'] as $day => $d_events) {
                if (empty($d_events['hours'])) {
                    continue;
                }
                foreach ($d_events['hours'] as $hour => $h_events) {
                    foreach ($h_events['events'] as $event) {
                        $tpl->setCurrentBlock('events');
                        $tpl->setData($event->getTpl());
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('hours');
                    $h_data['HOUR_24'] = $hour;
                    $h_data['HOUR_12'] = strftime('%l', mktime($hour));
                    $h_data['AM_PM'] = strftime('%P', mktime($hour));
                    
                    $tpl->setData($h_data);
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock('days');
                $d_data['FULL_WEEKDAY'] = $this->dayLink(strftime('%A', mktime(0,0,0, $month, $day)),
                                                         $month, $day, $year);
                $d_data['ABRV_WEEKDAY'] = $this->dayLink(strftime('%a', mktime(0,0,0, $month, $day)),
                                                         $month, $day, $year);
                $d_data['DAY_NUMBER'] = $this->dayLink($day, $month, $day, $year);
                $tpl->setData($d_data);
                $tpl->parseCurrentBlock();
            }
        }

    }

    function month_list()
    {
        $month = &$this->calendar->month;
        $year  = &$this->calendar->year;
        $day   = 1;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->loadEventList($startdate, $enddate);
        $title = $this->getViewTitle();
            
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/month/list.tpl');

        $this->loadDayList($month, $year, $tpl);
           
        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0,0,0, $month));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0,0,0, $month));


        $main_tpl['VIEW_LINKS'] = $this->viewLinks('month_list');
        $main_tpl['SCHEDULE_TITLE'] = $title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_YEAR'] = strftime('%y', mktime(0,0,0, $month, $day, $year));

        $main_tpl['PICK'] = $this->getPick();

        $tpl->setData($main_tpl);
        $tpl->parseCurrentBlock();
        
        $content = $tpl->get();
        return $content;
    }

    function month_grid()
    {
        $month = &$this->calendar->month;
        $year  = &$this->calendar->year;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);
        if (isset($this->calendar->schedule)) {
            $title = $this->calendar->schedule->title;
            $this->calendar->schedule->loadEvents($startdate, $enddate);
            $this->event_list = & $this->calendar->schedule->events;
        } else {
            $title = _('Public events');
            $public_calendars = $this->calendar->getPublicCalendars();
            if (!empty($public_calendars)) {
                $this->event_list = $this->calendar->getEvents($startdate, $enddate, $public_calendars);
            }
        }

        $this->sortEvents();
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->calendar->getMonth();
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
        $oTpl->setFile('view/month/full.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);

        // create day cells in grid
        $this->_month_days($oMonth, $oTpl);

        $vars['m'] = $month;
        $vars['y'] = $year;
        $vars['view'] = 'month_grid';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        $template['TITLE'] = $title;
        $template['PICK'] = $this->getPick();
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);
        $template['VIEW_LINKS'] = $this->viewLinks('month_grid');

        $oTpl->setData($template);
        $content = $oTpl->get();

        //        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }

    function getPick()
    {
        $js['month']    = $this->calendar->month;
        $js['day']    = $this->calendar->day;
        $js['year']    = $this->calendar->year;
        $js['url']  = $this->getUrl();
        $js['type'] = 'pick';
        return javascript('js_calendar', $js);
    }

    function day()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $startdate = mktime(0, 0, 0, $this->calendar->month, $this->calendar->day, $this->calendar->year);
        $enddate   = $startdate + 82800 + 3540 + 59; // 23 hours, 59 minutes, 59 seconds later

        $template['VIEW_LINKS'] = $this->viewLinks('day');

        if (isset($this->calendar->schedule)) {
            $this->calendar->schedule->loadEvents($startdate, $enddate);
            $title = $this->calendar->schedule->title;
            $this->event_list = & $this->calendar->schedule->events;
        } else {
            $public_calendars = $this->calendar->getPublicCalendars();
            if (!empty($public_calendars)) {
                $this->event_list = $this->calendar->getEvents($startdate, $enddate, $public_calendars);
            }

            $title = _('Public events');
        }

        $template['TITLE'] = $title;
        $template['DATE'] = strftime(CALENDAR_DAY_FORMAT, $startdate);

        $template['PICK'] = $this->getPick();

        if ( isset($this->calendar->schedule) && 
             Current_User::allow('calendar', 'edit_schedule', $this->calendar->schedule->id) ||
            ( PHPWS_Settings::get('calendar', 'personal_calendars') && 
              $this->calendar->schedule->user_id == Current_User::getId()
              )
            ) {
            MiniAdmin::add('calendar', $this->calendar->schedule->addEventLink($this->calendar->today));
        }
        
        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/day/day.tpl');

        $this->sortEvents();

        if (empty($this->event_sort)) {
            $template['MESSAGE'] = _('No events planned for this day.');
        } else {
            $hour_list = array();
            foreach ($this->event_list as $oEvent) {
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

                    $details['TITLE']   = $oEvent->getTitle();
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

    function week()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $month = &$this->calendar->month;
        $year  = &$this->calendar->year;
        $day   = &$this->calendar->day;

        $current_weekday = strftime('%u', mktime(0,0,0,$month, $day, $year));

        if ($current_weekday != CALENDAR_START_DAY) {
            $week_start = $current_weekday - CALENDAR_START_DAY;
        } else {
            $week_start = 0;
        }

        $startdate = mktime(0,0,0, $month, $day, $year) - (86400 * $week_start);
        $enddate = $startdate + (86400 * 7) - 1;

        $this->loadEventList($startdate, $enddate);
        $title = $this->getViewTitle();

        $this->sortEvents();

        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/week.tpl');
        $this->loadDayList($month, $year, $tpl);

        $start_range = strftime('%b %e', $startdate);

        if (date('m', $startdate) == date('m', $enddate)) {
            $end_range = strftime('%e', $enddate);
        } else {
            $end_range = strftime('%b %e', $enddate);
        }


        $main_tpl['DAY_RANGE'] = sprintf('From %s to %s', $start_range, $end_range);

        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0,0,0, $month));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0,0,0, $month));


        $main_tpl['VIEW_LINKS'] = $this->viewLinks('week');
        $main_tpl['SCHEDULE_TITLE'] = $title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_YEAR'] = strftime('%y', mktime(0,0,0, $month, $day, $year));

        $main_tpl['PICK'] = $this->getPick();

        $tpl->setData($main_tpl);
        $tpl->parseCurrentBlock();
        
        $content = $tpl->get();
        return $content;
    }

    function sortEvents()
    {
        if (empty($this->event_list)) {
            return;
        }

        foreach ($this->event_list as $key => $event) {
            $year = (int)date('Y', $event->start_time);
            $month = (int)date('m', $event->start_time);
            $day = (int)date('d', $event->start_time);
            $hour = (int)date('H', $event->start_time);
            $this->event_sort[$year]['events'][$key] = & $this->event_list[$key];
            $this->event_sort[$year]['months'][$month]['events'][$key] = & $this->event_list[$key];
            $this->event_sort[$year]['months'][$month]['days'][$day]['events'][$key] = & $this->event_list[$key];
            $this->event_sort[$year]['months'][$month]['days'][$day]['hours'][$hour]['events'][$key] = & $this->event_list[$key];
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

    function event($id, $js=false) {
        PHPWS_Core::initModClass('calendar', 'Event.php');
        $event = & new Calendar_Event($id);
        if (!$event->id) {
            PHPWS_Core::errorPage('404');
        }

        $template = $event->getTpl();

        if ($js) {
            $template['CLOSE_WINDOW'] = javascript('close_window', array('value'=>_('Close')));
        } else {
            $template['BACK_LINK'] = PHPWS_Text::backLink();
        }

        return PHPWS_Template::process($template, 'calendar', 'view/event.tpl');
    }

}


?>