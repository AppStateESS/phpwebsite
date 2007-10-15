<?php

  /**
   * Contains functions specific to users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('CALENDAR_TOTAL_SUGGESTIONS')) {
    define('CALENDAR_TOTAL_SUGGESTIONS', 5);
 }

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

    var $message = null;

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


    function allowSuggestion()
    {
        if ( isset($_SESSION['Calendar_Total_Suggestions']) &&
             $_SESSION['Calendar_Total_Suggestions'] >= CALENDAR_TOTAL_SUGGESTIONS ) {
            return false;
        } else {
            return true;
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
                $details = $oEvent->getTpl();

                $duration = $oEvent->end_time - $oEvent->start_time;
                $duration_day = floor($duration / 86400);
                if ($duration_day) {
                    $current_day = floor( ($oEvent->end_time - $startdate) / 86400);
                    $day_number = $duration_day - $current_day + 1;
                    switch ($day_number) {
                    case 1:
                        $details['DAY_NUMBER'] = sprintf(dgettext('calendar', 'First day'), $day_number);
                        break;

                    case ($current_day < 1):
                        $details['DAY_NUMBER'] = sprintf(dgettext('calendar', 'Last day'), $day_number);
                        break;

                    default:
                        $details['DAY_NUMBER'] = sprintf(dgettext('calendar', 'Day %s'), $day_number);
                        break;
                    }
                }
                    
                if (!isset($hour_list[$hour])) {
                    $hour_list[$hour] = 1;
                    if ($hour == -1) {
                        $details['HOUR'] = dgettext('calendar', 'All day');
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
            $template['MESSAGE'] = dgettext('calendar', 'No events on this day');
        }

        $template['VIEW_LINKS']     = $this->viewLinks('day');
        $template['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $template['DATE']           = strftime(CALENDAR_DAY_HEADER, $startdate);
        $template['SCHEDULE_PICK']  = $this->schedulePick();
        $template['PICK']           = $this->getDatePick();
        $template['SUGGEST']        = $this->suggestLink();
        if ($this->calendar->schedule->checkPermissions()) {
            $template['ADD_EVENT'] = $this->calendar->schedule->addEventLink($this->calendar->current_date);
        }

        $tpl->setCurrentBlock('day');
        $tpl->setData($template);
        $tpl->parseCurrentBlock();
        return $tpl->get();
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
            $template['CLOSE_WINDOW'] = javascript('close_window', array('value'=>dgettext('calendar', 'Close')));
        } else {
            $template['BACK_LINK'] = PHPWS_Text::backLink(dgettext('calendar', 'Back'));
        }

        $template['VIEW_LINKS'] = $this->viewLinks('event');
        return PHPWS_Template::process($template, 'calendar', 'view/event.tpl');
    }


    function getDatePick()
    {
        $js['month'] = $this->calendar->int_month;
        $js['day']   = $this->calendar->int_day;
        $js['year']  = $this->calendar->int_year;

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

    function loadSuggestion($id=0)
    {
        PHPWS_Core::initModClass('calendar', 'Suggestion.php');
        $this->event = new Calendar_Suggestion;
        $this->event->_schedule = & $this->calendar->schedule;
        $this->event->schedule_id = $this->event->_schedule->id;
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

        case 'suggest_event':
            if (!PHPWS_Settings::get('calendar', 'allow_submissions')) {
                PHPWS_Core::errorPage('403');
            }

            if (!$this->allowSuggestion()) {
                $this->title = dgettext('calendar', 'Sorry');
                $this->content = dgettext('calendar', 'You have exceeded your allowed event submissions.');
                break;
            }

            PHPWS_Core::initModClass('calendar', 'Admin.php');
            $this->loadSuggestion();
            $this->title = dgettext('calendar', 'Suggest event');
            $this->content = Calendar_Admin::event_form($this->event, true);
            break;

        case 'post_suggestion':
            if (!$this->postSuggestion()) {
                PHPWS_Core::initModClass('calendar', 'Admin.php');
                $this->title = dgettext('calendar', 'Suggest event');
                $this->content = Calendar_Admin::event_form($this->event, true);
            }
            break;
        }


        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE']   = $this->title;

        if (is_array($this->message)) {
            $tpl['MESSAGE'] = implode('<br />', $this->message);
        } else {
            $tpl['MESSAGE'] = $this->message;
        } 

        // Clears in case of js window opening
        $this->content = $this->title = $this->message = null;

        $final = PHPWS_Template::process($tpl, 'calendar', 'user_main.tpl');

        if (PHPWS_Calendar::isJS()) {
            Layout::nakedDisplay($final);
        } else {
            Layout::add($final);
        }
    }

    function mini_month()
    {
        $month = (int)date('m');
        $year  = (int)date('Y');

        // Check cache
        if (PHPWS_Settings::get('calendar', 'cache_month_views')) {
            $cache_key = sprintf('mini_%s_%s_%s', $month, $year, $this->calendar->schedule->id);
        
            $content = PHPWS_Cache::get($cache_key);
            if (!empty($content)) {
                return $content;
            }
        }

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        if (PHPWS_Settings::get('calendar', 'mini_event_link')) {
            $this->calendar->loadDefaultSchedule();
            $default_start = PHPWS_Settings::get('calendar','starting_day');
            $start_day = date('w', $startdate) - $default_start;
            $end_day  = date('w', $enddate);
            
            $startdate -= $start_day * 86400;
            $enddate += $end_day * 86400;
            $this->calendar->loadEventList($startdate, $enddate);
            $link = false;
        } else {
            $link = true;
        }

        $oMonth = $this->calendar->getMonth($month, $year);
        $oMonth->build();
        $date = mktime(0,0,0, $month, 1, $year);

        $oTpl = new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/mini.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);
        $this->_month_days($oMonth, $oTpl, $link);

        $vars['date'] = mktime(0,0,0, $month, 1, $year);
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);

        $oTpl->setData($template);
        $content = $oTpl->get();

        if (isset($cache_key)) {
            PHPWS_Cache::save($cache_key, $content);
        }
        return $content;
    }

    /**
     * Fills in event totals for each day
     */
    function _month_days(&$oMonth, &$oTpl, $link_days=true)
    {
        while($day = $oMonth->fetch()) {
            $data['COUNT'] = null;
            $no_of_events = 0;

            if (isset($this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events'])) {
                $no_of_events = count($this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events']);
            } 

            if ($link_days || $no_of_events) {
                $data['DAY'] = $this->dayLink($day->day, $day->month, $day->day, $day->year);
            } else {
                $data['DAY'] = $day->day;
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
                $data['COUNT'] = PHPWS_Text::moduleLink(sprintf('%s event(s)', $no_of_events), 
                                                        'calendar', array('view'=>'day',
                                                                          'date'=>$day->thisDay(true),
                                                                          'sch_id'=>$this->calendar->schedule->id));
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
        if ($this->calendar->schedule->public && PHPWS_Settings::get('calendar', 'cache_month_views')) {
            $cache_key = sprintf('grid_%s_%s_%s', $month, $year, $this->calendar->schedule->id);
        
            $content = PHPWS_Cache::get($cache_key);
            if (!empty($content)) {
                return $content;
            }
        }

        // cache empty, make calendar

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $default_start = PHPWS_Settings::get('calendar','starting_day');
        $start_day = date('w', $startdate) - $default_start;
        $end_day  = date('w', $enddate);

        $startdate -= $start_day * 86400;
        $enddate += $end_day * 86400;

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

        $template['TITLE']         = $this->calendar->schedule->title;
        $template['PICK']          = $date_pick;
        $template['FULL_YEAR']     = strftime('%Y', $date);
        $template['PARTIAL_YEAR']  = strftime('%y', $date);
        $template['VIEW_LINKS']    = $this->viewLinks('grid');
        $template['SCHEDULE_PICK'] = $this->schedulePick();
        $template['SUGGEST']       = $this->suggestLink();

        $oTpl->setData($template);
        $content = $oTpl->get();

        if (isset($cache_key)) {
            PHPWS_Cache::save($cache_key, $content);
        }

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

        if ($this->calendar->schedule->public && !Current_User::isLogged() && PHPWS_Settings::get('calendar', 'cache_month_views')) {
            $cache_key = sprintf('list_%s_%s_%s', $month, $year, $this->calendar->schedule->id);
        }

        if (isset($cache_key)) {
            // Check cache
            $content = PHPWS_Cache::get($cache_key);
            if (!empty($content)) {
                return $content;
            }
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
                $day_tpl['FULL_WEEKDAY'] = PHPWS_Text::moduleLink(strftime('%A', $i), 'calendar',
                                                                 array('view' => 'day', 'date'=>$i, 'schedule_id'=>$this->calendar->schedule->id));
                $day_tpl['ABBR_WEEKDAY'] = PHPWS_Text::moduleLink(strftime('%a', $i), 'calendar',
                                                                 array('view' => 'day', 'date'=>$i, 'schedule_id'=>$this->calendar->schedule->id));
                $day_tpl['DAY_NUMBER']   = PHPWS_Text::moduleLink(strftime('%e', $i), 'calendar',
                                                                 array('view' => 'day', 'date'=>$i, 'schedule_id'=>$this->calendar->schedule->id));
                $tpl->setCurrentBlock('days');
                $tpl->setData($day_tpl);
                $tpl->parseCurrentBlock();
            }
        }

        if (!$events_found) {
            $tpl->setVariable('MESSAGE', dgettext('calendar', 'No events this month.'));
        }

        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0,0,0, $month, $day, $year));
        $main_tpl['VIEW_LINKS']      = $this->viewLinks('list');
        $main_tpl['SCHEDULE_TITLE']  = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR']       = strftime('%Y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['ABRV_YEAR']       = strftime('%y', mktime(0,0,0, $month, $day, $year));
        $main_tpl['SCHEDULE_PICK']   = $this->schedulePick();
        $main_tpl['PICK']            = $date_pick;
        $main_tpl['SUGGEST']         = $this->suggestLink();
        if ($this->calendar->schedule->checkPermissions()) {
            $main_tpl['ADD_EVENT'] = $this->calendar->schedule->addEventLink($this->calendar->current_date);
        }

        $tpl->setData($main_tpl);
        $content = $tpl->get();

        if (isset($cache_key)) {
            PHPWS_Cache::save($cache_key, $content);
        }
        
        return $content;
    }


    function postSuggestion()
    {
        $this->loadSuggestion();
        
        if ($this->event->post()) {
            if (PHPWS_Core::isPosted()) {
                $this->title = dgettext('calendar', 'Duplicate suggestion.');
                $this->content = dgettext('calendar', 'You may try to suggest a different event.');
                return true;
            }

            if (!isset($_SESSION['Calendar_Total_Suggestions'])) {
                $_SESSION['Calendar_Total_Suggestions'] = 0;
            }

            if (!$this->allowSuggestion()) {
                $this->title = dgettext('calendar', 'Sorry');
                $this->content = dgettext('calendar', 'You have exceeded your allowed event submissions.');
                return true;
            }

            $result = $this->event->save();

            $_SESSION['Calendar_Total_Suggestions']++;

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                if(PHPWS_Calendar::isJS()) {
                    javascript('close_refresh', array('timeout'=>5, 'refresh'=>0));
                    Layout::nakedDisplay('Event suggestion failed to save. Try again later.');
                    exit();
                } else {
                    $this->title = dgettext('calendar', 'Sorry');
                    $this->content = dgettext('calendar', 'Unable to save your event suggestion.');
                    return true;
                }
            } else {
                if(PHPWS_Calendar::isJS()) {
                    javascript('alert', array('content' =>dgettext('calendar', 'Event submitted for approval.')));
                    javascript('close_refresh', array('timeout'=>1, 'refresh'=>0));
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->title = dgettext('calendar', 'Event saved');
                    $this->content = dgettext('calendar', 'An administrator will review your submission. Thank you.');
                    return true;
                }
            }
        } else {
            return false;
        }
    }


    function resetCacheLink($type, $month, $year, $schedule)
    {
        $vars['aop'] = 'reset_cache';
        $vars['key'] = sprintf('%s_%s_%s_%s', $type, $month, $year, $schedule);
        MiniAdmin::add('calendar', PHPWS_Text::secureLink(dgettext('calendar', 'Reset cache'), 'calendar', $vars));
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
        $form->addSubmit('go', dgettext('calendar', 'Change schedule'));
        $tpl = $form->getTemplate();
        return implode("\n", $tpl);
    }

    function suggestLink()
    {
        if ( !$this->allowSuggestion()                      ||
             !$this->calendar->schedule->public             || 
             Current_User::allow('calendar', 'edit_public') ||
             !PHPWS_Settings::get('calendar', 'allow_submissions') ) {
            return null;
        }

        return $this->calendar->schedule->addSuggestLink($this->calendar->current_date);
    }

    function todayLink($view)
    {
        $vars['sch_id'] = $this->calendar->schedule->id;
        if ($this->current_view == 'event') {
            $vars['view'] = 'day';
        } else {
            $vars['view'] = $this->current_view;
        }
        $vars['date'] = mktime();

        switch ($view) {
        case 'grid':
        case 'list':
            $view_name = dgettext('calendar', 'This month');
            break;

        case 'week':
            $view_name = dgettext('calendar', 'This week');
            break;

        case 'event':
        case 'day':
            $view_name = dgettext('calendar', 'Today');
        }

        return PHPWS_Text::moduleLink($view_name, 'calendar', $vars);
    }

    /**
     * Pathing for which view to display
     */
    function view()
    {
        $key = new Key($this->calendar->schedule->key_id);
        if (!$key->allowView()) {
            $this->calendar->loadDefaultSchedule();
        }

        if (!$this->calendar->schedule->id) {
            
        }

        if ($this->calendar->schedule->checkPermissions()) {
            if ($this->calendar->schedule->id) {
                $allowed = true;
                MiniAdmin::add('calendar', $this->calendar->schedule->addEventLink($this->calendar->current_date));
            } else {
                $vars = array('aop'=>'create_schedule');
                $label = dgettext('calendar', 'Create schedule');

                if (javascriptEnabled()) {
                    $vars['js'] = 1;
                    $js_vars['address'] = PHPWS_Text::linkAddress('calendar', $vars);
                    $js_vars['label']   = $label;
                    $js_vars['width']   = 640;
                    $js_vars['height']  = 600;
                    $add_schedule = javascript('open_window', $js_vars);

                } else {
                    $add_schedule = PHPWS_Text::secureLink($label, 'calendar', $vars);
                }
                MiniAdmin::add('calendar', $add_schedule);
            }
        } else {
            $allowed = false;
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
            if (ALLOW_CACHE_LITE && Current_User::allow('calendar')) {
                $this->resetCacheLink('grid', $this->calendar->int_month, $this->calendar->int_year, $this->calendar->schedule->id);
            }
            $this->content = $this->month_grid();
            break;

        case 'list':
            if (ALLOW_CACHE_LITE && Current_User::allow('calendar')) {
                $this->resetCacheLink('list', $this->calendar->int_month, $this->calendar->int_year, $this->calendar->schedule->id);
            }
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
            $this->content = dgettext('calendar', 'Incorrect option');
            break;
        }

        if ($this->current_view == 'event') {
            $this->event->flagKey();
        } else {
            $schedule_key->flag();
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
        if (!$this->calendar->schedule->id) {
            return null;
        }

        $vars = PHPWS_Text::getGetValues();
        unset($vars['module']);

        if ($current_view == 'grid') {
            $vars['date'] = $this->calendar->today;
        }

        if (isset($_REQUEST['m']) &&
            isset($_REQUEST['y']) && 
            isset($_REQUEST['d'])) {
            $vars['date'] = mktime(0,0,0, $_REQUEST['m'], $_REQUEST['d'], $_REQUEST['y']);
            unset($vars['m']);
            unset($vars['d']);
            unset($vars['y']);
        }


        $links[] = $this->todayLink($current_view);

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
            $left_link_title = dgettext('calendar', 'Previous month');
            $right_link_title = dgettext('calendar', 'Next month');
        }

        if ($current_view == 'grid') {
            $links[] = dgettext('calendar', 'Grid');
        } else {
            $vars['view'] = 'grid';
            $links[] = PHPWS_Text::moduleLink(dgettext('calendar', 'Grid'), 'calendar', $vars);
        }

        if ($current_view == 'list') {
            $links[] = dgettext('calendar', 'Month');
        } else {
            $vars['view'] = 'list';
            $links[] = PHPWS_Text::moduleLink(dgettext('calendar', 'Month'), 'calendar', $vars);
        }


        if ($current_view == 'week') {
            require_once 'Calendar/Week.php';
            $oWeek = $this->calendar->getWeek();
            $left_arrow_time = $oWeek->prevWeek('timestamp');
            $right_arrow_time = $oWeek->nextWeek('timestamp');
            $left_link_title = dgettext('calendar', 'Previous week');
            $right_link_title = dgettext('calendar', 'Next week');
            
            $links[] = dgettext('calendar', 'Week');
        } else {
            $vars['view'] = 'week';
            $links[] = PHPWS_Text::moduleLink(dgettext('calendar', 'Week'), 'calendar', $vars);
        }

        if ($current_view == 'day') {
            require_once 'Calendar/Day.php';
            $oDay = new Calendar_Day($this->calendar->int_year, $this->calendar->int_month,
                                         $this->calendar->int_day);
            $left_arrow_time = $oDay->prevDay('timestamp');
            $right_arrow_time = $oDay->nextDay('timestamp');
            $left_link_title = dgettext('calendar', 'Previous day');
            $right_link_title = dgettext('calendar', 'Next day');

            $links[] = dgettext('calendar', 'Day');
        } else {
            $vars['view'] = 'day';
            $links[] = PHPWS_Text::moduleLink(dgettext('calendar', 'Day'), 'calendar', $vars);
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
        strftime('%c', $this->calendar->current_date);
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $start_day = PHPWS_Settings::get('calendar','starting_day');
        $current_weekday = date('w', $this->calendar->current_date);

        if ($current_weekday != $start_day) {
            $week_start = $current_weekday - $start_day;
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
            $tpl->setVariable('MESSAGE', dgettext('calendar', 'No events this week.'));
        }

        $main_tpl['DAY_RANGE']      = sprintf(dgettext('calendar', 'From %s to %s'), $start_range, $end_range);
        $main_tpl['VIEW_LINKS']     = $this->viewLinks('week');
        $main_tpl['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR']      = strftime('%Y', $this->calendar->current_date);
        $main_tpl['ABRV_YEAR']      = strftime('%y', $this->calendar->current_date);
        $main_tpl['SCHEDULE_PICK']  = $this->schedulePick();
        $main_tpl['PICK']           = $this->getDatePick();
        $main_tpl['SUGGEST']        = $this->suggestLink();
        if ($this->calendar->schedule->checkPermissions()) {
            $main_tpl['ADD_EVENT']      = $this->calendar->schedule->addEventLink($this->calendar->current_date);
        }

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

    function upcomingEvents()
    {
        $db = new PHPWS_DB('calendar_schedule');
        $db->addWhere('show_upcoming', 0, '>');
        $db->addWhere('public', 1);
        Key::restrictView($db, 'calendar');

        $result = $db->getObjects('Calendar_Schedule');
        if (PHPWS_Error::logIfError($result) || !$result) {
            return null;
        }

        $startdate = mktime();

        foreach ($result as $schedule) {
            switch ($schedule->show_upcoming) {
            case 1:
                // one week
                $days_ahead = 7;
                break;

            case 2:
                // two weeks
                $days_ahead = 14;
                break;

            case 3:
                // one month
                $days_ahead = 30;
                break;
            }

            $enddate = $startdate + (86400 * $days_ahead);
            $result = $schedule->getEvents($startdate, $enddate);
            if (!$result) {
                continue;
            }

            $tpl['TITLE'] = $schedule->getViewLink();

            $current_day = null;

            $count = 0;
            foreach ($result as $event) {
                $vars = array('view'   => 'day',
                              'date'   => $event->start_time,
                              'sch_id' => $schedule->id);

                $tpl['events'][$count] = $event->getTpl();

                if ($current_day != strftime('%A', $event->start_time)) {
                    $current_day = strftime('%A', $event->start_time);
                    $tpl['events'][$count]['DAY'] = PHPWS_Text::moduleLink($current_day, 'calendar', $vars);
                } else {
                    $tpl['events'][$count]['DAY'] = null;
                }

                $count++;
            }
            $upcoming[] = PHPWS_Template::process($tpl, 'calendar', 'view/upcoming.tpl');
        }

        if (!empty($upcoming)) {
            $ftpl['TITLE'] = dgettext('calendar', 'Upcoming events');
            $ftpl['CONTENT'] = implode("\n", $upcoming);
            return PHPWS_Template::process($ftpl, 'calendar', 'user_main.tpl');
        } else {
            return null;
        }
    }
}

?>