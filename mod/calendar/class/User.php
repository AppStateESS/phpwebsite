<?php

  /**
   * Contains functions specific to users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


class Calendar_User {

    /**
     * @var pointer to the parent Calendar object
     */
    var $calendar = null;

    var $content = null;
    var $title = null;
    var $current_view = null;

    /**
     * @var Calendar_View object
     */
    var $view  = null;

    function Calendar_User()
    {
        if (isset($_REQUEST['view'])) {
            $this->current_view = preg_replace('/\W/', '', $_REQUEST['view']);
        } else {
            $this->current_view = PHPWS_Settings::get('calendar', 'default_view');
        }
    }

    /**
     * Displays a single day's events
     */
    function day()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $startdate = mktime(0, 0, 0, $this->calendar->int_month, $this->calendar->int_day, $this->calendar->int_year);
        $enddate   = $startdate + 82800 + 3540 + 59; // 23 hours, 59 minutes, 59 seconds later

        $this->calendar->loadEventList($startdate, $enddate);
        $template['VIEW_LINKS'] = $this->viewLinks('day');

        $template['TITLE'] = $this->calendar->schedule->title;
        $template['DATE'] = strftime(CALENDAR_DAY_FORMAT, $startdate);

        $template['PICK'] = $this->getPick();
        
        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/day/day.tpl');

        if (empty($this->calendar->event_list)) {
            $template['MESSAGE'] = _('No events planned for this day.');
        } else {
            $hour_list = array();
            foreach ($this->calendar->event_list as $oEvent) {
                switch ($oEvent->event_type) {
                case '1':
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

                    if (Current_User::allow('calendar', 'edit_public', $this->calendar->schedule->id)) {
                        $links[] = $oEvent->editLink();
                        $links[] = $oEvent->deleteLink();
                    }
                
                    if (!empty($links)) {
                        $details['LINKS'] = implode(' | ', $links);
                    }

                    $details = $oEvent->getTpl();
                    
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

        return PHPWS_Template::process($template, 'calendar', 'view/day/day.tpl');
    }

    /**
     * Link to the day view
     */
    function dayLink($label, $month, $day, $year)
    {
        $vars = array('view' => 'day',
                      'date' => mktime(0,0,0, $month, $day, $year));
        if ($this->calendar->schedule->id) {
            $vars['sch_id'] = $this->calendar->schedule->id;
        }
    
        return PHPWS_Text::moduleLink($label, 'calendar', $vars);
    }


    function event($id, $js=false) {
        PHPWS_Core::initModClass('calendar', 'Event.php');
        $event = & new Calendar_Event($this->calendar->schedule, $id);

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


    function getPick()
    {
        $js['month'] = $this->calendar->int_month;
        $js['day'] = $this->calendar->int_day;
        $js['year'] = $this->calendar->int_year;

        $js['url']   = $this->getUrl();
        $js['type']  = 'pick';
        return javascript('js_calendar', $js);
    }

    function getUrl()
    {
        $getVars = PHPWS_Text::getGetValues();
        if (empty($getVars)) {
            return 'index.php';
        }
        $address[] = 'index.php?';
        unset($getVars['date']);
        foreach ($getVars as $key=>$value) {
            $newvars[] = "$key=$value";
        }

        $address[] = implode('&amp;', $newvars);

        return implode('', $address);
    }


    function loadDayList(&$tpl)
    {
        $month = $this->calendar->int_month;
        $year  = $this->calendar->int_year;

        if (empty($this->calendar->sorted_list[$year]['months'][$month]['days'])) {
            return FALSE;
        } else {
            foreach ($this->calendar->sorted_list[$year]['months'][$month]['days'] as $day => $d_events) {
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
            return TRUE;
        }
    }



    function main()
    {
        if (isset($_REQUEST['uop'])) {
            $command = $_REQUEST['uop'];
        } else {
            $command = 'view';
        }

        switch ($command) {
            
        case 'view':
            $this->view();
            break;
        }

        $template['CONTENT'] = $this->content;
        $template['TITLE']   = $this->title;
        $final = PHPWS_Template::process($template, 'calendar', 'user_main.tpl');
        Layout::add($final);
    }

    function mini_month()
    {
        $month = &$this->calendar->int_month;
        $year  = &$this->calendar->int_year;

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

        $vars['date'] = mktime(0,0,0, $month, 1, $year);
        $vars['view'] = 'grid';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);

        $oTpl->setData($template);
        $content = $oTpl->get();
        return $content;
    }

    /**
     * Fills in event totals for each day
     */
    function _month_days(&$oMonth, &$oTpl)
    {
        $month = &$this->calendar->int_month;
        $year  = &$this->calendar->int_year;

        while($day = $oMonth->fetch()) {
            $data['COUNT'] = null;
            $no_of_events = 0;
            $data['DAY'] = $this->dayLink($day->day, $month, $day->day, $year);


            if (isset($this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events'])) {
                $no_of_events = count($this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events']);
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
     * Standard month calendar grid view
     */
    function month_grid()
    {
        $month = $this->calendar->int_month;
        $year  = $this->calendar->int_year;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->calendar->loadEventList($startdate, $enddate);
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->calendar->getMonth();
        $date = $oMonth->thisMonth(TRUE);

        // Check cache
        $cache_key = sprintf('grid_%s_%s_%s', $oMonth->month, $oMonth->year, $this->calendar->schedule->id);

        
        $content = PHPWS_Cache::get($cache_key);
        if (!empty($content)) {
            return $content;
        }
        
        // Cache empty, make month

        $oTpl = & new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/full.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);

        // create day cells in grid
        $this->_month_days($oMonth, $oTpl);

        $vars['date'] = mktime(0,0,0,$month, 1, $year);
        $vars['view'] = 'grid';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        $template['TITLE'] = $this->calendar->schedule->title;
        $template['PICK'] = $this->getPick();
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);
        $template['VIEW_LINKS'] = $this->viewLinks('grid');

        $oTpl->setData($template);
        $content = $oTpl->get();

        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }


    function month_list()
    {
        $month = &$this->calendar->int_month;
        $year  = &$this->calendar->int_year;
        $day   = 1;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->calendar->loadEventList($startdate, $enddate);

        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/month/list.tpl');

        if (!$this->loadDayList($tpl)) {
            $main_tpl['MESSAGE'] = _('No events this month.');
        }
           
        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0,0,0, $month));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0,0,0, $month));
        $main_tpl['VIEW_LINKS']      = $this->viewLinks('list');
        $main_tpl['SCHEDULE_TITLE']  = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR']       = strftime('%Y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_YEAR']       = strftime('%y', mktime(0,0,0, $month, $day, $year));

        $main_tpl['PICK'] = $this->getPick();

        $tpl->setData($main_tpl);
        $tpl->parseCurrentBlock();
        
        $content = $tpl->get();
        return $content;
    }


    /**
     * Pathing for which view to display
     */
    function view()
    {
        if ( ($this->calendar->schedule->public && Current_User::allow('calendar', 'edit_public', $this->calendar->schedule->id) ) ||
             (!$this->calendar->schedule->public && Current_User::allow('calendar', 'edit_private', $this->calendar->schedule->id) )
             ) {
            MiniAdmin::add('calendar', $this->calendar->schedule->addEventLink($this->calendar->current_date));
        }

        switch ($this->current_view) {
        case 'day':
            $this->content = $this->day();
            break;
            
        case 'grid':
            $this->content = $this->month_grid();
            break;

        case 'list':
            $this->content = $this->month_list();
            break;

        case 'week':
            $this->content = $this->week();
            break;

        case 'event':
            $event_id = (int)$_REQUEST['id'];

            if (isset($_REQUEST['js'])) {
                $this->content = $this->event($event_id, true);
                Layout::nakedDisplay($this->content);
                return;
            } else {
                $this->content = $this->event($event_id);
            }
            break;
        default:
            $this->content = _('Incorrect option');
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
            $vars['sch_id'] = $this->calendar->schedule->id;
        }

        // Get the values for the left and right arrows in a month view
        if ($current_view == 'list' || $current_view == 'grid') {
            $oMonth = $this->calendar->getMonth();
            $left_arrow_time = $oMonth->prevMonth('timestamp');
            $right_arrow_time = $oMonth->nextMonth('timestamp');
            $left_link_title = _('Previous month');
            $right_link_title = _('Next month');
        }

        if ($current_view == 'grid') {
            $links[] = _('Grid');
        } else {
            $vars['view'] = 'grid';
            $links[] = PHPWS_Text::moduleLink(_('Grid'), 'calendar', $vars);
        }

        if ($current_view == 'list') {
            $links[] = _('Month');
        } else {
            $vars['view'] = 'list';
            $links[] = PHPWS_Text::moduleLink(_('Month'), 'calendar', $vars);
        }


        if ($current_view == 'week') {
            require_once 'Calendar/Week.php';
            $oWeek = $this->calendar->getWeek();
            $left_arrow_time = $oWeek->prevWeek('timestamp');
            $right_arrow_time = $oWeek->nextWeek('timestamp');
            $left_link_title = _('Previous week');
            $right_link_title = _('Next week');
            
            $links[] = _('Week');
        } else {
            $vars['view'] = 'week';
            $links[] = PHPWS_Text::moduleLink(_('Week'), 'calendar', $vars);
        }

        if ($current_view == 'day') {
            require_once 'Calendar/Day.php';
            $oDay = & new Calendar_Day($this->calendar->int_year, $this->calendar->int_month,
                                         $this->calendar->int_day);
            $left_arrow_time = $oDay->prevDay('timestamp');
            $right_arrow_time = $oDay->nextDay('timestamp');
            $left_link_title = _('Previous day');
            $right_link_title = _('Next day');

            $links[] = _('Day');
        } else {
            $vars['view'] = 'day';
            $links[] = PHPWS_Text::moduleLink(_('Day'), 'calendar', $vars);
        }

        $vars['view'] = $current_view;

        $vars['date'] = $left_arrow_time;
        array_unshift($links, PHPWS_Text::moduleLink('&lt;&lt;', 'calendar', $vars, null, $left_link_title));

        $vars['date'] = $right_arrow_time;
        $links[] = PHPWS_Text::moduleLink('&gt;&gt;', 'calendar', $vars, null, $right_link_title);

        return implode(' | ', $links);
    }


    function week()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oDay = $this->calendar->getDay();

        $current_weekday = date('w', $oDay->thisDay('timestamp'));

        if ($current_weekday != CALENDAR_START_DAY) {
            $week_start = $current_weekday - CALENDAR_START_DAY;
        } else {
            $week_start = 0;
        }

        $startdate = $this->calendar->current_date - (86400 * $week_start);
        $enddate = $startdate + (86400 * 7) - 1;

        $this->calendar->loadEventList($startdate, $enddate);
        $title = $this->calendar->schedule->title;

        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $tpl = & new PHPWS_Template('calendar');
        $tpl->setFile('view/week.tpl');

        if (!$this->loadDayList($tpl)) {
            $main_tpl['MESSAGE'] = _('No events this week.');
        }

        $start_range = strftime('%B %e', $startdate);

        if (date('m', $startdate) == date('m', $enddate)) {
            $end_range = strftime('%e', $enddate);
        } else {
            $end_range = strftime('%B %e', $enddate);
        }


        $main_tpl['DAY_RANGE'] = sprintf('From %s to %s', $start_range, $end_range);

        $main_tpl['VIEW_LINKS'] = $this->viewLinks('week');
        $main_tpl['SCHEDULE_TITLE'] = $title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', $this->calendar->current_date);
        $main_tpl['ABRV_YEAR'] = strftime('%y', $this->calendar->current_date);

        $main_tpl['PICK'] = $this->getPick();

        $tpl->setData($main_tpl);
        $tpl->parseCurrentBlock();
        
        $content = $tpl->get();
        return $content;
    }


    /**
     * Fills in the header weekdays on the grid layout
     */
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

}

?>