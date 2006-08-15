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

    function dayLink($label, $month, $day, $year)
    {
        return PHPWS_Text::moduleLink($label, 'calendar',
                                      array('view' => 'day',
                                            'm' => (int)$month,
                                            'y' => (int)$year,
                                            'd' => (int)$day));
    }


    function getPick()
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

    function miniAdmin()
    {

    }


    function _month_days(&$oMonth, &$oTpl)
    {
        $month = &$this->calendar->int_month;
        $year  = &$this->calendar->int_year;

        while($day = $oMonth->fetch()) {
            $data['COUNT'] = null;
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

    function month_grid()
    {
        $month = $this->calendar->int_month;
        $year  = $this->calendar->int_year;

        $startdate = mktime(0,0,0, $month, 1, $year);
        $enddate = mktime(23, 59, 59, $month + 1, 0, $year);

        $this->calendar->schedule->loadEventList($startdate, $enddate);
        

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
        $vars['view'] = 'grid';
        $template['FULL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%B', $date), 'calendar', $vars);
        $template['PARTIAL_MONTH_NAME'] = PHPWS_Text::moduleLink(strftime('%b', $date), 'calendar', $vars);

        //        $template['TITLE'] = $title;
        $template['PICK'] = $this->getPick();
        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);
        $template['VIEW_LINKS'] = $this->viewLinks('grid');

        $oTpl->setData($template);
        $content = $oTpl->get();

        //        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }


    function view()
    {
        //        $this->miniAdmin();

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
        $vars['m'] = $this->calendar->int_month;
        $vars['d'] = $this->calendar->int_day;
        $vars['y'] = $this->calendar->int_year;

        // Get the values for the left and right arrows in a month view
        if ($current_view == 'month_list' || $current_view == 'grid') {
            $oMonth = $this->calendar->getMonth();
            $left_arrow_time = $oMonth->prevMonth('timestamp');
            $right_arrow_time = $oMonth->nextMonth('timestamp');
            $left_link_title = _('Previous month');
            $right_link_title = _('Next month');
        }

        if ($current_view == 'month_list') {
            $links[] = _('Month list');
        } else {
            $vars['view'] = 'month_list';
            $links[] = PHPWS_Text::moduleLink(_('Month list'), 'calendar', $vars);
        }

        if ($current_view == 'grid') {
            $links[] = _('Month grid');
        } else {
            $vars['view'] = 'grid';
            $links[] = PHPWS_Text::moduleLink(_('Month grid'), 'calendar', $vars);
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

        $lMonth = (int)strftime('%m', $left_arrow_time);
        $rMonth = (int)strftime('%m', $right_arrow_time);
        
        $lYear = strftime('%Y', $left_arrow_time);
        $rYear = strftime('%Y', $right_arrow_time);
        
        $lDay  = strftime('%e', $left_arrow_time);
        $rDay  = strftime('%e', $right_arrow_time);

        $vars['m'] = (int)$lMonth;
        $vars['d'] = (int)$lDay;
        $vars['y'] = (int)$lYear;
        array_unshift($links, PHPWS_Text::moduleLink('&lt;&lt;', 'calendar', $vars, null, $left_link_title));

        $vars['m'] = (int)$rMonth;
        $vars['d'] = (int)$rDay;
        $vars['y'] = (int)$rYear;
        $links[] = PHPWS_Text::moduleLink('&gt;&gt;', 'calendar', $vars, null, $right_link_title);

        return implode(' | ', $links);
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

}

?>