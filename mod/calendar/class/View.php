<?php

  /**
   * Contains the various functions for viewing calendars
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_View {
    var $oCal = NULL;

    function month_grid($type='mini', $month=NULL, $year=NULL)
    {
        if ($type != 'mini' && $type != 'full') {
            PHPWS_Core::errorPage('404');
        }
        
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $oMonth = $this->oCal->getMonth($month, $year);
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
            } elseif ( $day->month == date('m', $this->oCal->today) &&
                       $day->day == date('d', $this->oCal->today)
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

}


?>