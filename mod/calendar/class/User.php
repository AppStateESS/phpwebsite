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

if (!defined('CALENDAR_UPCOMING_FORMAT')) {
    define('CALENDAR_UPCOMING_FORMAT', '%A, %e %b');
}

class Calendar_User
{

    /**
     * @var pointer to the parent Calendar object
     */
    public $calendar = null;

    /**
     * @var string Contains printed content
     */
    public $content = null;
    public $current_view = null;

    /**
     * @var object If event is requested, contains object
     */
    public $event = null;

    /**
     * @var string Contains page title header
     */
    public $title = null;
    public $message = null;

    /**
     * @var Calendar_View object
     */
    public $view = null;

    public function __construct()
    {
        if (isset($_REQUEST['view'])) {
            $this->current_view = preg_replace('/\W/', '', $_REQUEST['view']);
        } elseif (isset($_REQUEST['id']) && isset($_REQUEST['page'])) {
            $this->current_view = 'event';
        } else {
            $this->current_view = PHPWS_Settings::get('calendar', 'default_view');
        }
    }

    public function allowSuggestion()
    {
        if (isset($_SESSION['Calendar_Total_Suggestions']) && $_SESSION['Calendar_Total_Suggestions'] >= CALENDAR_TOTAL_SUGGESTIONS) {
            return false;
        } else {
            return true;
        }
    }

    public function getDaysEvents($startdate, PHPWS_Template $tpl)
    {
        $year = (int) date('Y', $startdate);
        $month = (int) date('m', $startdate);
        $day = (int) date('d', $startdate);

        if (!isset($this->calendar->sorted_list[$year]['months'][$month]['days'][$day]['events'])) {
            return false;
        }

        $day_events = $this->calendar->sorted_list[$year]['months'][$month]['days'][$day]['events'];

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
                    $current_day = floor(($oEvent->end_time - $startdate) / 86400);
                    $day_number = $duration_day - $current_day + 1;
                    switch ($day_number) {
                        case 1:
                            $details['DAY_NUMBER'] = sprintf('First day', $day_number);
                            break;

                        case ($current_day < 1):
                            $details['DAY_NUMBER'] = sprintf('Last day', $day_number);
                            break;

                        default:
                            $details['DAY_NUMBER'] = sprintf(dgettext('calendar', 'Day %s'), $day_number);
                            break;
                    }
                }

                if (!isset($hour_list[$hour])) {
                    $hour_list[$hour] = 1;
                    if ($hour == -1) {
                        $details['HOUR'] = 'All day';
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
    public function day()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $startdate = mktime(0, 0, 0, $this->calendar->int_month, $this->calendar->int_day, $this->calendar->int_year);
        $enddate = $startdate + 82800 + 3540 + 59; // 23 hours, 59 minutes, 59 seconds later

        $this->calendar->loadEventList($startdate, $enddate);

        $tpl = new PHPWS_Template('calendar');
        $tpl->setFile('view/day.tpl');

        $template = $this->viewLinks('day');

        if (!$this->getDaysEvents($startdate, $tpl)) {
            $template['MESSAGE'] = 'No events on this day';
        }

        $template['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $template['DATE'] = '<a href="index.php?module=calendar&sch_id='
                . $this->calendar->schedule->id . '&date=' . $this->calendar->current_date . '">'
                . strftime(CALENDAR_DAY_HEADER, $startdate) . '</a>';
        $template['SCHEDULE_PICK'] = $this->schedulePick();
        $template['SUGGEST'] = $this->suggestLink();
        $template['DOWNLOAD'] = $this->downloadLink($startdate, $enddate);

        if ($this->calendar->schedule->id && $this->calendar->schedule->checkPermissions()) {
            $label = 'Add event';
            $template['ADD_EVENT'] = '<button class="add-event btn btn-success" data-view="day" data-schedule-id="' .
                    $this->calendar->schedule->id . '" data-date="' . ($this->calendar->current_date) .
                    '"><i class="fa fa-plus"></i> ' . $label . '</button>';
        }

        $tpl->setCurrentBlock('day');
        $tpl->setData($template);
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    /**
     * Link to the day view
     */
    public function dayLink($label, $month, $day, $year)
    {
        $vars = array('view' => 'day',
            'date' => mktime(0, 0, 0, $month, $day, $year));
        if ($this->calendar->schedule->id) {
            $vars['sch_id'] = $this->calendar->schedule->id;
        }

        $dlink = new PHPWS_Link($label, 'calendar', $vars);
        $dlink->setNoFollow(PHPWS_Settings::get('calendar', 'no_follow'));
        return $dlink->get();
    }

    public function event($js = false)
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');
        if (!$this->event->id) {
            \phpws\PHPWS_Core::errorPage('404');
        }
        
        $template = $this->event->getTpl();

        if ($js) {
            $template['CLOSE_WINDOW'] = javascript('close_window', array('value' => 'Close'));
        } else {
            $template['BACK_LINK'] = PHPWS_Text::backLink('Back');
        }

        $template['DOWNLOAD'] = $this->eventDownloadLink($this->event->id);
        $viewLinks = $this->viewLinks('event');
        $template = array_merge($template, $viewLinks);
        //$template['VIEW_LINKS'] = $viewLinks;
        return PHPWS_Template::process($template, 'calendar', 'view/event.tpl');
    }

    public function getUrl()
    {
        $getVars = PHPWS_Text::getGetValues();
        if (empty($getVars)) {
            return 'index.php';
        }
        $address[] = 'index.php?';
        unset($getVars['date']);
        unset($getVars['jdate']);
        foreach ($getVars as $key => $value) {
            $newvars[] = "$key=$value";
        }

        $address[] = implode('&amp;', $newvars);

        return implode('', $address);
    }

    public function loadSuggestion($id = 0)
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Suggestion.php');
        $this->event = new Calendar_Suggestion;
        $this->event->_schedule = & $this->calendar->schedule;
        $this->event->schedule_id = $this->event->_schedule->id;
    }

    public function loadEvent($event_id)
    {
        \phpws\PHPWS_Core::initModClass('calendar', 'Event.php');
        $this->event = new Calendar_Event($event_id, $this->calendar->schedule);
        return true;
    }

    public function main()
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
                    \phpws\PHPWS_Core::errorPage('403');
                }

                if (!$this->allowSuggestion()) {
                    $this->title = 'Sorry';
                    $this->content = 'You have exceeded your allowed event submissions.';
                    break;
                }

                \phpws\PHPWS_Core::initModClass('calendar', 'Admin.php');
                $this->loadSuggestion();
                $this->title = 'Suggest event';
                $this->content = Calendar_Admin::event_form($this->event, true);
                break;

            case 'post_suggestion':
                if (!$this->postSuggestion()) {
                    \phpws\PHPWS_Core::initModClass('calendar', 'Admin.php');
                    $this->title = 'Suggest event';
                    $this->content = Calendar_Admin::event_form($this->event, true);
                }
                break;

            case 'ical_dl':
                if (!empty($_GET['sdate']) && !empty($_GET['edate']) && $this->calendar->schedule->allowICalDownload()) {
                    $this->calendar->schedule->exportEvents($_GET['sdate'], $_GET['edate']);
                } else {
                    $this->title = 'Sorry';
                    $this->content = 'Schedule unavailable.';
                }

                break;

                if ((!$this->calendar->schedule->public && !$this->calendar->schedule->checkPermissions() ) || ( $this->calendar->schedule->public && (PHPWS_Settings::get('calendar', 'anon_ical') && Current_User::isLogged()) ) || empty($_GET['sdate']) || empty($_GET['edate']) || !$this->calendar->schedule->id) {
                    $this->title = 'Sorry';
                    $this->content = 'Schedule unavailable.';
                } else {

                }
                break;

            case 'ical_event_dl':
                if ($this->calendar->schedule->allowICalDownload()) {
                    $this->calendar->schedule->exportEvent($_GET['event_id']);
                } else {
                    $this->title = 'Sorry';
                    $this->content = 'Schedule unavailable.';
                }

                break;
        }

        $tpl['CONTENT'] = $this->content;
        $tpl['TITLE'] = $this->title;

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

    public function mini_month()
    {
        $no_follow = PHPWS_Settings::get('calendar', 'no_follow');

        $month = (int) date('m');
        $year = (int) date('Y');

        // Check cache
        if (PHPWS_Settings::get('calendar', 'cache_month_views')) {
            $cache_key = sprintf('mini_%s_%s_%s', $month, $year, $this->calendar->schedule->id);

            $content = PHPWS_Cache::get($cache_key);
            if (!empty($content)) {
                return $content;
            }
        }

        $startdate = mktime(0, 0, 0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        if (PHPWS_Settings::get('calendar', 'mini_event_link')) {
            $this->calendar->loadDefaultSchedule();
            $default_start = PHPWS_Settings::get('calendar', 'starting_day');
            $start_day = date('w', $startdate) - $default_start;
            $end_day = date('w', $enddate);

            $startdate -= $start_day * 86400;
            $enddate += $end_day * 86400;
            $this->calendar->loadEventList($startdate, $enddate);
            $link = false;
        } else {
            $link = true;
        }

        $oMonth = $this->calendar->getMonth($month, $year);
        $oMonth->build();
        $date = mktime(0, 0, 0, $month, 1, $year);

        $oTpl = new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/mini.tpl');

        $this->_weekday($oMonth, $oTpl);
        reset($oMonth->children);
        $this->_month_days($oMonth, $oTpl, $link);

        if (isset($_SESSION['Current_Schedule'])) {
            $vars['sch_id'] = $_SESSION['Current_Schedule'];
        }
        $vars['date'] = mktime(0, 0, 0, $month, 1, $year);
        $slink = new PHPWS_Link(strftime('%B', $date), 'calendar', $vars);
        $slink->setNoFollow($no_follow);
        $template['FULL_MONTH_NAME'] = $slink->get();
        $slink->setLabel(strftime('%b', $date));
        $template['PARTIAL_MONTH_NAME'] = $slink->get();
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
     * Fills in event totals for each day in month grids
     */
    public function _month_days($oMonth, $oTpl, $link_days = true, $list_events = false)
    {
        $no_follow = PHPWS_Settings::get('calendar', 'no_follow');
        while ($day = $oMonth->fetch()) {
            $data = array();
            $data['COUNT'] = null;
            $no_of_events = 0;
            $events = null;

            if (isset($this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events'])) {
                $events = & $this->calendar->sorted_list[$day->year]['months'][$day->month]['days'][$day->day]['events'];
            }

            if (isset($events)) {
                $no_of_events = count($events);
            }

            if ($link_days || $no_of_events) {
                $data['DAY'] = $this->dayLink($day->day, $day->month, $day->day, $day->year);
            } else {
                $data['DAY'] = $day->day;
            }

            if ($day->empty) {
                $data['CLASS'] = 'day-empty';
            } elseif ($day->month == date('m', $this->calendar->today) && $day->day == date('d', $this->calendar->today) && $day->year == date('Y', $this->calendar->today)) {
                $data['CLASS'] = 'day-current';
            } else {
                $data['CLASS'] = 'day-normal';
            }

            if ($no_of_events) {
                if ($list_events) {
                    foreach ($events as $event) {
                        $event_tpl['EVENT'] = $event->getSummary();
                        $oTpl->setCurrentBlock('event-list');
                        $oTpl->setData($event_tpl);
                        $oTpl->parseCurrentBlock();
                    }
                } else {
                    $dlink = new PHPWS_Link(sprintf('%s event(s)', $no_of_events), 'calendar', array('view' => 'day',
                        'date' => $day->thisDay(true),
                        'sch_id' => $this->calendar->schedule->id));
                    $dlink->setNoFollow($no_follow);
                    $data['COUNT'] = $dlink->get();
                }
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
    public function month_grid()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $month = $this->calendar->int_month;
        $year = $this->calendar->int_year;

        // Check cache
        if ($this->calendar->schedule->public && PHPWS_Settings::get('calendar', 'cache_month_views')) {
            $cache_key = sprintf('grid_%s_%s_%s', $month, $year, $this->calendar->schedule->id);

            $content = PHPWS_Cache::get($cache_key);
            if (!empty($content)) {
                return $content;
            }
        }

        // cache empty, make calendar
        $startdate = mktime(0, 0, 0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $default_start = PHPWS_Settings::get('calendar', 'starting_day');
        $start_day = date('w', $startdate) - $default_start;
        $end_day = date('w', $enddate);

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
        $this->_month_days($oMonth, $oTpl, true, !PHPWS_Settings::get('calendar', 'brief_grid'));

        $template['FULL_MONTH_NAME'] = strftime('%B', $date);
        $template['PARTIAL_MONTH_NAME'] = strftime('%b', $date);

        $template['TITLE'] = $this->calendar->schedule->title;
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);
        $view_links = $this->viewLinks('grid');
        if ($view_links) {
            $template = array_merge($template, $view_links);
        }
        $template['SCHEDULE_PICK'] = $this->schedulePick();
        $template['SUGGEST'] = $this->suggestLink();
        $template['DOWNLOAD'] = $this->downloadLink($startdate, $enddate);

        $oTpl->setData($template);
        $content = $oTpl->get();

        if (isset($cache_key)) {
            PHPWS_Cache::save($cache_key, $content);
        }

        return $content;
    }

    public function month_list()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $month = &$this->calendar->int_month;
        $year = &$this->calendar->int_year;
        $day = 1;

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

        $startdate = mktime(0, 0, 0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->calendar->loadEventList($startdate, $enddate);

        $tpl = new PHPWS_Template('calendar');
        $tpl->setFile('view/month/list.tpl');

        $events_found = false;

        $lvars = array('view' => 'day', 'schedule_id' => $this->calendar->schedule->id);
        $slink = new PHPWS_Link(null, 'calendar');
        $slink->setNoFollow(PHPWS_Settings::get('calendar', 'no_follow'));

        for ($i = $startdate; $i <= $enddate; $i += 86400) {
            $day_result = $this->getDaysEvents($i, $tpl);
            $lvars['date'] = $i;
            $slink->clearValues();
            $slink->addValues($lvars);
            if ($day_result) {
                $events_found = true;
                $slink->setLabel(strftime('%A', $i));
                $day_tpl['FULL_WEEKDAY'] = $slink->get();
                $slink->setLabel(strftime('%a', $i));
                $day_tpl['ABBR_WEEKDAY'] = $slink->get();
                $slink->setLabel(strftime('%e', $i));
                $day_tpl['DAY_NUMBER'] = $slink->get();
                $tpl->setCurrentBlock('days');
                $tpl->setData($day_tpl);
                $tpl->parseCurrentBlock();
            }
        }

        if (!$events_found) {
            $tpl->setVariable('MESSAGE', 'No events this month.');
        }
        $main_tpl = $this->viewLinks('list');
        $main_tpl['FULL_MONTH_NAME'] = strftime('%B', mktime(0, 0, 0, $month, $day, $year));
        $main_tpl['ABRV_MONTH_NAME'] = strftime('%b', mktime(0, 0, 0, $month, $day, $year));
        $main_tpl['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', mktime(0, 0, 0, $month, $day, $year));
        $main_tpl['ABRV_YEAR'] = strftime('%y', mktime(0, 0, 0, $month, $day, $year));
        $main_tpl['SCHEDULE_PICK'] = $this->schedulePick();
        $main_tpl['DOWNLOAD'] = $this->downloadLink($startdate, $enddate);

        $main_tpl['SUGGEST'] = $this->suggestLink();
        if ($this->calendar->schedule->checkPermissions()) {
            $main_tpl['ADD_EVENT'] = '<button class="add-event btn btn-success" data-schedule-id="' .
                    $this->calendar->schedule->id . '" data-date="' . $this->calendar->current_date .
                    '"><i class="fa fa-plus"></i> Add event</button>';
        }

        $tpl->setData($main_tpl);
        $content = $tpl->get();

        if (isset($cache_key)) {
            PHPWS_Cache::save($cache_key, $content);
        }

        return $content;
    }

    public function postSuggestion()
    {
        $this->loadSuggestion();

        if ($this->event->post()) {
            if (\phpws\PHPWS_Core::isPosted()) {
                $this->title = 'Duplicate suggestion.';
                $this->content = 'You may try to suggest a different event.';
                return true;
            }

            if (!isset($_SESSION['Calendar_Total_Suggestions'])) {
                $_SESSION['Calendar_Total_Suggestions'] = 0;
            }

            if (!$this->allowSuggestion()) {
                $this->title = 'Sorry';
                $this->content = 'You have exceeded your allowed event submissions.';
                return true;
            }

            $result = $this->event->save();

            $_SESSION['Calendar_Total_Suggestions'] ++;

            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                if (PHPWS_Calendar::isJS()) {
                    javascript('close_refresh', array('timeout' => 5, 'refresh' => 0));
                    Layout::nakedDisplay('Event suggestion failed to save. Try again later.');
                    exit();
                } else {
                    $this->title = 'Sorry';
                    $this->content = 'Unable to save your event suggestion.';
                    return true;
                }
            } else {
                if (PHPWS_Calendar::isJS()) {
                    javascript('alert', array('content' => 'Event submitted for approval.'));
                    javascript('close_refresh', array('timeout' => 1, 'refresh' => 0));
                    Layout::nakedDisplay();
                    exit();
                } else {
                    $this->title = 'Event saved';
                    $this->content = 'An administrator will review your submission. Thank you.';
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function resetCacheLink($type, $month, $year, $schedule)
    {
        $vars['aop'] = 'reset_cache';
        $vars['key'] = sprintf('%s_%s_%s_%s', $type, $month, $year, $schedule);
        MiniAdmin::add('calendar', PHPWS_Text::secureLink('Reset cache', 'calendar', $vars));
    }

    public function schedulePick()
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
        $form->addSubmit('go', 'Change schedule');
        $tpl = $form->getTemplate();
        return $tpl['START_FORM'] . $tpl['SCH_ID'] . $tpl['GO'] . $tpl['END_FORM'];
    }

    public function suggestLink()
    {
        if (!$this->allowSuggestion() || !$this->calendar->schedule->public || Current_User::allow('calendar', 'edit_public') || !PHPWS_Settings::get('calendar', 'allow_submissions')) {
            return null;
        }
        
        return $this->calendar->schedule->addSuggestLink($this->calendar->current_date);
    }
    
    public function todayLink($view)
    {
        $vars['sch_id'] = $this->calendar->schedule->id;
        if ($this->current_view == 'event') {
            $vars['view'] = 'day';
        } else {
            $vars['view'] = $this->current_view;
        }
        $vars['date'] = time();

        switch ($view) {
            case 'grid':
            case 'list':
                $view_name = 'This month';
                break;

            case 'week':
                $view_name = 'This week';
                break;

            case 'event':
            case 'day':
                $view_name = 'Today';
        }

        return PHPWS_Text::moduleLink($view_name, 'calendar', $vars, null, null, 'btn btn-default');
    }

    /**
     * Pathing for which view to display
     */
    public function view()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Event.php';
        $key = new \Canopy\Key($this->calendar->schedule->key_id);
        if (!$key->allowView()) {
            $this->calendar->loadDefaultSchedule();
        }


        $schedule_key = $this->calendar->schedule->getKey();

        if ((!$this->calendar->schedule->public && !$schedule_key->allowView())) {
            \phpws\PHPWS_Core::errorPage('403');
        }

        \Layout::disableRobots();

        $current_date = $this->calendar->current_date * 1000;

        switch ($this->current_view) {
            case 'day':
                $this->content = $this->day();
                break;

            case 'grid':
                if (ALLOW_CACHE_LITE && Current_User::allow('calendar')) {
                    if (strftime('%Y%m', $this->calendar->today) == strftime('%Y%m', $this->calendar->current_date)) {
                        $current_date = $this->calendar->today * 1000;
                    }
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
                    $event_id = (int) $_REQUEST['page'];
                } elseif (isset($_REQUEST['event_id'])) {
                    $event_id = $_REQUEST['event_id'];
                } else {
                    $this->content = $this->day();
                }

                if (!$this->loadEvent($event_id) || !$this->event->id) {
                    $this->content = $this->day();
                    break;
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
                $this->content = 'Incorrect option';
                break;
        }

        if ($this->calendar->schedule->checkPermissions()) {
            if ($this->calendar->schedule->id) {
                require_once PHPWS_SOURCE_DIR . 'mod/calendar/class/Admin.php';
                $event = new Calendar_Event(0, $this->calendar->schedule);
                Layout::add(\Calendar_Admin::eventModal($event));
                \Calendar_Admin::includeEventJS();
                $link = '<a style="cursor:pointer" class="add-event" data-schedule-id="' .
                        $this->calendar->schedule->id . '" data-date="' . $current_date .
                        '">Add event</a>';
                MiniAdmin::add('calendar', $link);
                MiniAdmin::add('calendar', $this->calendar->schedule->uploadEventsLink());
            }
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
    public function viewLinks($current_view)
    {
        if (!$this->calendar->schedule->id) {
            return null;
        }
        $no_follow = PHPWS_Settings::get('calendar', 'no_follow');
        $vars = PHPWS_Text::getGetValues();
        unset($vars['module']);

//        if ($current_view == 'grid') {
//            $vars['date'] = $this->calendar->today;
//        }

        if (isset($_REQUEST['m']) && isset($_REQUEST['y']) && isset($_REQUEST['d'])) {
            $vars['date'] = mktime(0, 0, 0, $_REQUEST['m'], $_REQUEST['d'], $_REQUEST['y']);
            unset($vars['m']);
            unset($vars['d']);
            unset($vars['y']);
        }

        $links['today'] = $this->todayLink($current_view);

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
            $left_link_title = 'Previous month';
            $right_link_title = 'Next month';
        }

        if ($current_view == 'grid') {
            //$links['GRID'] = 'Grid';
        } else {
            $vars['view'] = 'grid';
            $glink = new PHPWS_Link('Month Grid', 'calendar', $vars);
            $glink->setNoFollow($no_follow);
            $glink->addClass('btn btn-default');
            $links['GRID'] = $glink->get();
        }

        if ($current_view == 'list') {
            //$links['LIST'] = 'Month';
        } else {
            $vars['view'] = 'list';
            $glink = new PHPWS_Link('Month list', 'calendar', $vars);
            $glink->addClass('btn btn-default');
            $glink->setNoFollow($no_follow);
            $links['LIST'] = $glink->get();
        }

        if ($current_view == 'week') {
            require_once 'Calendar/Week.php';
            $oWeek = $this->calendar->getWeek();
            $left_arrow_time = $oWeek->prevWeek('timestamp');
            $right_arrow_time = $oWeek->nextWeek('timestamp');
            $left_link_title = 'Previous week';
            $right_link_title = 'Next week';
        } else {
            $vars['view'] = 'week';
            $wlink = new PHPWS_Link('Week', 'calendar', $vars);
            $wlink->setNoFollow($no_follow);
            $wlink->addClass('btn btn-default');
            $links['WEEK'] = $wlink->get();
        }

        if ($current_view == 'day') {
            require_once 'Calendar/Day.php';
            $oDay = new Calendar_Day($this->calendar->int_year, $this->calendar->int_month, $this->calendar->int_day);
            $left_arrow_time = $oDay->prevDay('timestamp');
            $right_arrow_time = $oDay->nextDay('timestamp');
            $left_link_title = 'Previous day';
            $right_link_title = 'Next day';

            $links['DAY_LINK'] = 'Day';
        } else {
            $vars['view'] = 'day';
            $dlink = new PHPWS_Link('Day', 'calendar', $vars);
            $dlink->addClass('btn btn-default');
            $dlink->setNoFollow($no_follow);
            $links['DAY_LINK'] = $dlink->get();
        }

        $vars['view'] = $current_view;

        if (!empty($left_arrow_time)) {
            $vars['date'] = $left_arrow_time;
            $larrow = new PHPWS_Link('<i class="fa fa-chevron-left"></i>&nbsp;', 'calendar', $vars);
            $larrow->addClass('btn btn-default');
            $larrow->setTitle($left_link_title);
            $larrow->setNoFollow($no_follow);
            $links['LEFT_ARROW'] = $larrow->get();
        }

        if (!empty($right_arrow_time)) {
            $vars['date'] = $right_arrow_time;
            $rarrow = new PHPWS_Link('&nbsp;<i class="fa fa-chevron-right"></i>', 'calendar', $vars);
            $rarrow->addClass('btn btn-default');
            $rarrow->setTitle($right_link_title);
            $rarrow->setNoFollow($no_follow);
            $links['RIGHT_ARROW'] = $rarrow->get();
        }
        return $links;
    }

    public function week()
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $start_day = PHPWS_Settings::get('calendar', 'starting_day');
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
                $link = PHPWS_Text::linkAddress('calendar', array('date' => $i, 'view' => 'day'));
                $day_tpl['FULL_WEEKDAY'] = sprintf('<a href="%s">%s</a>', $link, strftime('%A', $i));
                $day_tpl['ABBR_WEEKDAY'] = sprintf('<a href="%s">%s</a>', $link, strftime('%a', $i));
                $day_tpl['DAY_NUMBER'] = sprintf('<a href="%s">%s</a>', $link, strftime('%e', $i));
                $tpl->setCurrentBlock('days');
                $tpl->setData($day_tpl);
                $tpl->parseCurrentBlock();
            }
        }

        if (!$events_found) {
            $tpl->setVariable('MESSAGE', 'No events this week.');
        }

        $main_tpl = $this->viewLinks('week');
        $main_tpl['DAY_RANGE'] = '<a href="index.php?module=calendar&amp;view=grid&amp;date='
                . $startdate . '">' . sprintf(dgettext('calendar', 'From %s to %s'), $start_range, $end_range) . '</a>';
        $main_tpl['SCHEDULE_TITLE'] = $this->calendar->schedule->title;
        $main_tpl['FULL_YEAR'] = strftime('%Y', $this->calendar->current_date);
        $main_tpl['ABRV_YEAR'] = strftime('%y', $this->calendar->current_date);
        $main_tpl['SCHEDULE_PICK'] = $this->schedulePick();
        $main_tpl['SUGGEST'] = $this->suggestLink();
        $main_tpl['DOWNLOAD'] = $this->downloadLink($startdate, $enddate);
        if ($this->calendar->schedule->checkPermissions()) {
            $main_tpl['ADD_EVENT'] = '<button class="add-event btn btn-success" data-view="week" data-schedule-id="' .
                    $this->calendar->schedule->id . '" data-date="' . ($this->calendar->current_date) .
                    '"><i class="fa fa-plus"></i> ' . 'Add event' . '</button>';
        }

        $tpl->setData($main_tpl);

        return $tpl->get();
    }

    /**
     * Fills in the header weekdays on the grid layout
     */
    public function _weekday($oMonth, PHPWS_Template $oTpl)
    {
        $day_count = 0;

        while ($day = $oMonth->fetch()) {
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

    public function upcomingEvents()
    {
        $db = new PHPWS_DB('calendar_schedule');
        $db->addWhere('show_upcoming', 0, '>');
        $db->addWhere('public', 1);
        \Canopy\Key::restrictView($db, 'calendar');

        $result = $db->getObjects('Calendar_Schedule');
        if (PHPWS_Error::logIfError($result) || !$result) {
            return null;
        }

        $startdate = time();

        foreach ($result as $schedule) {
            $tpl = array();
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
            $event_list = $schedule->getEvents($startdate, $enddate);
            if (!$event_list) {
                continue;
            }

            $tpl['TITLE'] = $schedule->getViewLink();

            $current_day = null;

            $count = 0;
            if (empty($event_list)) {
                continue;
            }

            foreach ($event_list as $event) {
                $vars = array('view' => 'day',
                    'date' => $event->start_time,
                    'sch_id' => $schedule->id);

                $tpl['events'][$count] = $event->getTpl();

                if ($current_day != strftime(CALENDAR_UPCOMING_FORMAT, $event->start_time)) {
                    $current_day = strftime(CALENDAR_UPCOMING_FORMAT, $event->start_time);
                    $tpl['events'][$count]['DAY'] = PHPWS_Text::moduleLink($current_day, 'calendar', $vars);
                } else {
                    $tpl['events'][$count]['DAY'] = null;
                }

                $count++;
            }

            $upcoming[] = PHPWS_Template::process($tpl, 'calendar', 'view/upcoming.tpl');
        }

        if (!empty($upcoming)) {
            $ftpl['TITLE'] = 'Upcoming events';
            $ftpl['CONTENT'] = implode("\n", $upcoming);
            return PHPWS_Template::process($ftpl, 'calendar', 'user_main.tpl');
        } else {
            return null;
        }
    }

    function eventDownloadLink($event_id)
    {
        if ($this->calendar->schedule->allowICalDownload()) {
            $dl['uop'] = 'ical_event_dl';
            $dl['sch_id'] = $this->calendar->schedule->id;
            $dl['event_id'] = $event_id;
            $icon = Icon::show('download');
            $download = new PHPWS_Link($icon, 'calendar', $dl);
            $download->setNoFollow();
            return $download->get();
        } else {
            return null;
        }
    }

    function downloadLink($startdate, $enddate)
    {
        if ($this->calendar->schedule->allowICalDownload()) {
            $dl['uop'] = 'ical_dl';
            $dl['sch_id'] = $this->calendar->schedule->id;
            $dl['sdate'] = $startdate;
            $dl['edate'] = $enddate;
            $icon = Icon::show('download');
            $download = new PHPWS_Link($icon, 'calendar', $dl);
            $download->setNoFollow();
            return $download->get();
        } else {
            return null;
        }
    }

}
