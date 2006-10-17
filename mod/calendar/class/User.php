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

    /**
     * @var string Contains printed content
     */
    var $content = null;

    var $current_view = null;

    /**
     * @var object If event is requested, contains object
     */
    var $event = null;


    /**
     * @var string Contains page title header
     */
    var $title = null;


    /**
     * @var Calendar_View object
     */
    var $view  = null;

    function Calendar_User()
    {
        if (isset($_REQUEST['view'])) {
            $this->current_view = preg_replace('/\W/', '', $_REQUEST['view']);
        } elseif (isset($_REQUEST['id']) && isset($_REQUEST['page'])) {
            $this->current_view = 'event';
        } else {
            $this->current_view = PHPWS_Settings::get('calendar', 'default_view');
        }
    }


    function getDaysEvents($startdate, &$tpl)
    {
        $year  = (int)date('Y', $startdate);
        $month = (int)date('m', $startdate);
        $day   = (int)date('d', $startdate);

        $day_events = @$this->calendar->sorted_list[$year]['months'][$month]['days'][$day]['events'];
        
        if (!$day_events) {
            return false;
        }

        $hour_list = array();
        foreach ($day_events as $oEvent) {
            if ($oEvent->all_day) {
                $newList[-1][] = $oEvent;
            } else {
                // checks to see if this is a multiple day event
                if (date('Ymd', $oEvent->start_time) != date('Ymd', $oEvent->end_time)) {
                    // If the events end time is equal to today,
                    // use the end time as the key
                    if (date('Ymd', $oEvent->end_time) == date('Ymd', $startdate)) {
                        $newList[strftime('%H', $oEvent->end_time)][] = $oEvent;
                    } elseif (date('Ymd', $oEvent->start_time) != date('Ymd', $startdate)) {
                        $newList[-1][] = $oEvent;
                    } else {
                        $newList[strftime('%H', $oEvent->start_time)][] = $oEvent;
                    }
                } else {
                    $newList[strftime('%H', $oEvent->start_time)][] = $oEvent;
                }
            }
        }

        ksort($newList);
        $tpl->setCurrentBlock('calendar-events');
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

                $duration = $oEvent->end_time - $oEvent->start_time;
                $duration_day = floor($duration / 86400);
                if ($duration_day) {
                    $current_day = floor( ($oEvent->end_time - $startdate) / 86400);
                    $day_number = $duration_day - $current_day + 1;
                    switch ($day_number) {
                    case 1:
                        $details['DAY_NUMBER'] = sprintf(_('First day'), $day_number);
                        break;

                    case ($current_day < 1):
                        $details['DAY_NUMBER'] = sprintf(_('Last day'), $day_number);
                        break;

                    default:
                        $details['DAY_NUMBER'] = sprintf(_('Day %s'), $day_number);
                        break;
                    }
                }
                    
                if (!isset($hour_list[$hour])) {
                    $hour_list[$hour] = 1;
                    if ($hour == -1) {
                        $details['HOUR'] = _('All day');
                    } else {
                        $details['HOUR'] = strftime('%l %p', mktime($hour));
                    }
                }

                $tpl->setData($details);
                $tpl->parseCurrentBlock();
            }
        }
        return true;
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

        $tpl = new PHPWS_Template('calendar');
        $tpl->setFile('view/day.tpl');

        if (!$this->getDaysEvents($startdate, $tpl)) {
            $template['MESSAGE'] = _('No events on this day');
        }

        $template['VIEW_LINKS'] = $this->viewLinks('day');
        $template['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $template['DATE'] = strftime(CALENDAR_DAY_HEADER, $startdate);
        $template['SCHEDULE_PICK'] = $this->schedulePick();
        $template['PICK'] = $this->getDatePick();

        $tpl->setCurrentBlock('day');
        $tpl->setData($template);
        $tpl->parseCurrentBlock();

        return $tpl->get();

        return PHPWS_Template::process($template, 'calendar', 'view/day.tpl');
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


    function event($js=false) {
        PHPWS_Core::initModClass('calendar', 'Event.php');

        if (!$this->event->id) {
            PHPWS_Core::errorPage('404');
        }

        $template = $this->event->getTpl();

        if ($js) {
            $template['CLOSE_WINDOW'] = javascript('close_window', array('value'=>_('Close')));
        } else {
            $template['BACK_LINK'] = PHPWS_Text::backLink(_('Back'));
        }

        $template['VIEW_LINKS'] = $this->viewLinks('event');

        return PHPWS_Template::process($template, 'calendar', 'view/event.tpl');
    }


    function getDatePick()
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

    function loadEvent($event_id)
    {
        PHPWS_Core::initModClass('calendar', 'Event.php');
        $this->event = new Calendar_Event($this->calendar->schedule, $event_id);
        return true;
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
        $oMonth->build();
        $date = $oMonth->thisMonth(TRUE);

        $oTpl = new PHPWS_Template('calendar');
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
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $month = $this->calendar->int_month;
        $year  = $this->calendar->int_year;

        $date_pick = $this->getDatePick();

        // Check cache
        $cache_key = sprintf('grid_%s_%s_%s', $month, $year, $this->calendar->schedule->id);

        $content = PHPWS_Cache::get($cache_key);
        if (!empty($content)) {
            return $content;
        }

        // cache empty, make calendar

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->calendar->loadEventList($startdate, $enddate);

        $oMonth = $this->calendar->getMonth();
        $oMonth->build();
        $date = $oMonth->thisMonth(TRUE);


        // Cache empty, make month
        $oTpl = new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/grid.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);

        // create day cells in grid
        $this->_month_days($oMonth, $oTpl);

        $vars['date'] = mktime(0,0,0,$month, 1, $year);
        $vars['view'] = 'grid';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        $template['TITLE'] = $this->calendar->schedule->title;
        $template['PICK'] = $date_pick;
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);
        $template['VIEW_LINKS'] = $this->viewLinks('grid');
        $template['SCHEDULE_PICK'] = $this->schedulePick();

        $oTpl->setData($template);
        $content = $oTpl->get();

        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }


    function month_list()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $month = &$this->calendar->int_month;
        $year  = &$this->calendar->int_year;
        $day   = 1;

        if (PHPWS_Cache::isEnabled() && Current_User::allow('calendar')) {
            $this->resetCacheLink('list', $month, $year, $this->calendar->schedule->id);
        }

        // Check cache
        $cache_key = sprintf('list_%s_%s_%s', $month, $year, $this->calendar->schedule->id);
        $content = PHPWS_Cache::get($cache_key);
        if (!empty($content)) {
            return $content;
        }

        // cache empty, make calendar

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $date_pick = $this->getDatePick();


        $this->calendar->loadEventList($startdate, $enddate);

        $tpl = new PHPWS_Template('calendar');
        $tpl->setFile('view/month/list.tpl');

        $events_found = false;
        for ($i = $startdate; $i <= $enddate; $i += 86400) {
            $day_result = $this->getDaysEvents($i, $tpl);
            if ($day_result) {
                $events_found = true;
                $day_tpl['FULL_WEEKDAY'] = strftime('%A', $i);
                $day_tpl['ABBR_WEEKDAY'] = strftime('%a', $i);
                $day_tpl['DAY_NUMBER']   = strftime('%e', $i);
                $tpl->setCurrentBlock('days');
                $tpl->setData($day_tpl);
                $tpl->parseCurrentBlock();
            }
        }

        if (!$events_found) {
            $tpl->setVariable('MESSAGE', _('No events this month.'));
        }

        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0,0,0, $month, $day, $year));
        $main_tpl['VIEW_LINKS']      = $this->viewLinks('list');
        $main_tpl['SCHEDULE_TITLE']  = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR']       = strftime('%Y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_YEAR']       = strftime('%y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['SCHEDULE_PICK']   = $this->schedulePick();
        $main_tpl['PICK'] = $date_pick;

        $tpl->setData($main_tpl);
        $content = $tpl->get();
        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }

    function resetCacheLink($type, $month, $year, $schedule)
    {
        $vars['aop'] = 'reset_cache';
        $vars['key'] = sprintf('%s_%s_%s_%s', $type, $month, $year, $schedule);
        MiniAdmin::add('calendar', PHPWS_Text::secureLink(_('Reset cache'), 'calendar', $vars));
    }

    function schedulePick()
    {
        $schedules = $this->calendar->getScheduleList('brief');
        if (count($schedules) < 2) {
            return null;
        }
        $form = new PHPWS_Form('schedule_pick');
        $form->setMethod('get');
        $form->addHidden('module', 'calendar');
        $form->addHidden('view', $this->current_view);
        $form->addHidden('date', $this->calendar->current_date);
        $form->addSelect('sch_id', $schedules);
        $form->setMatch('sch_id', $this->calendar->schedule->id);
        $form->addSubmit('go', _('Change schedule'));
        
        $tpl = $form->getTemplate();
        return implode("\n", $tpl);
    }

    function todayLink()
    {
        $vars['sch_id'] = $this->calendar->schedule->id;
        if ($this->current_view == 'event') {
            $vars['view'] = 'day';
        } else {
            $vars['view'] = $this->current_view;
        }
        $vars['date'] = mktime();
        return PHPWS_Text::moduleLink(_('Today'), 'calendar', $vars);
    }

    /**
     * Pathing for which view to display
     */
    function view()
    {
        if ( $this->calendar->schedule->id &&
             ( ($this->calendar->schedule->public && Current_User::allow('calendar', 'edit_public', $this->calendar->schedule->id) ) ||
               (!$this->calendar->schedule->public && Current_User::allow('calendar', 'edit_private', $this->calendar->schedule->id) )
               )
             ) {
            MiniAdmin::add('calendar', $this->calendar->schedule->addEventLink($this->calendar->current_date));
        }

        $schedule_key = $this->calendar->schedule->getKey();

        if ( (!$this->calendar->schedule->public && !$schedule_key->allowView())) {
            PHPWS_Core::errorPage('403');
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
            if (isset($_REQUEST['page'])) {
                $event_id = (int)$_REQUEST['page'];
            } elseif (isset($_REQUEST['event_id'])) {
                $event_id = $_REQUEST['event_id'];
            } else {
                PHPWS_Core::errorPage('404');
            }

            if (!$this->loadEvent($event_id) || !$this->event->id) {
                PHPWS_Core::errorPage('404');
            }

            if (isset($_REQUEST['js'])) {
                $this->content = $this->event(true);
                Layout::nakedDisplay($this->content);
                return;
            } else {
                $this->content = $this->event();
            }
            break;
            
        default:
            $this->content = _('Incorrect option');
            break;
        }

        // If the schedule is public flag the schedule or event key
        // Private schedules are not flagged.
        if ($this->calendar->schedule->public) {
            if ($this->current_view == 'event') {
                $this->event->flagKey();
            } else {
                $schedule_key->flag();
            }
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

        if (isset($_REQUEST['m']) &&
            isset($_REQUEST['y']) && 
            isset($_REQUEST['d'])) {
            $vars['date'] = mktime(0,0,0, $_REQUEST['m'], $_REQUEST['d'], $_REQUEST['y']);
            unset($vars['m']);
            unset($vars['d']);
            unset($vars['y']);
        }

        $links[] = $this->todayLink();

        if ($current_view == 'event') {
            $vars['date'] = $this->event->start_time;
        }

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
            $oDay = new Calendar_Day($this->calendar->int_year, $this->calendar->int_month,
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

        if (!empty($left_arrow_time)) {
            $vars['date'] = $left_arrow_time;
            array_unshift($links, PHPWS_Text::moduleLink('&lt;&lt;', 'calendar', $vars, null, $left_link_title));
        }

        if (!empty($right_arrow_time)) {
            $vars['date'] = $right_arrow_time;
            $links[] = PHPWS_Text::moduleLink('&gt;&gt;', 'calendar', $vars, null, $right_link_title);
        }


        return implode(' | ', $links);
    }


    function week()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $current_weekday = date('w', $this->calendar->current_date);

        if ($current_weekday != CALENDAR_START_DAY) {
            $week_start = $current_weekday - CALENDAR_START_DAY;
        } else {
            $week_start = 0;
        }

        $startdate = $this->calendar->current_date - (86400 * $week_start);
        $enddate = $startdate + (86400 * 7) - 1;

        $this->calendar->loadEventList($startdate, $enddate);
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $tpl = new PHPWS_Template('calendar');
        $tpl->setFile('view/week.tpl');

        $start_range = strftime(CALENDAR_WEEK_HEADER, $startdate);

        if (date('Y', $startdate) != date('Y', $enddate)) {
            $start_range .= strftime(', %Y', $startdate);
        }

        if (date('m', $startdate) == date('m', $enddate)) {
            $end_range = strftime('%e, %Y', $enddate);
        } else {
            $end_range = strftime(CALENDAR_WEEK_HEADER, $enddate);
            $end_range .= strftime(', %Y', $enddate);
        }

        $events_found = false;
        for ($i = $startdate; $i <= $enddate; $i += 86400) {
            $day_result = $this->getDaysEvents($i, $tpl);
            if ($day_result) {
                $events_found = true;
                $link = PHPWS_Text::linkAddress('calendar', array('date'=>$i, 'view'=>'day'));
                $day_tpl['FULL_WEEKDAY'] = sprintf('<a href="%s">%s</a>', $link, strftime('%A', $i));
                $day_tpl['ABBR_WEEKDAY'] = sprintf('<a href="%s">%s</a>', $link, strftime('%a', $i));
                $day_tpl['DAY_NUMBER']   = sprintf('<a href="%s">%s</a>', $link, strftime('%e', $i));
                $tpl->setCurrentBlock('days');
                $tpl->setData($day_tpl);
                $tpl->parseCurrentBlock();
            }
        }

        if (!$events_found) {
            $tpl->setVariable('MESSAGE', _('No events this week.'));
        }

        $main_tpl['DAY_RANGE'] = sprintf('From %s to %s', $start_range, $end_range);
        $main_tpl['VIEW_LINKS'] = $this->viewLinks('week');
        $main_tpl['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', $this->calendar->current_date);
        $main_tpl['ABRV_YEAR'] = strftime('%y', $this->calendar->current_date);
        $main_tpl['SCHEDULE_PICK'] = $this->schedulePick();
        $main_tpl['PICK'] = $this->getDatePick();

        $tpl->setData($main_tpl);
       
        return $tpl->get();
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