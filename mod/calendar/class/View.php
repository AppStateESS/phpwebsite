<?php

  /**
   * Contains the various functions for viewing calendars
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_View {
    var $oCal = NULL;

    function miniMonth($date=0)
    {
        if (PHPWS_Settings::get('calendar', 'use_calendar_style')) {
            Layout::addStyle('calendar');
        }

        $date = $this->oCal->checkDate($date);
        $oMonth = $this->oCal->getMonth($date);


        $oTpl = & new PHPWS_Template('calendar');
        $oTpl->setFile('view/month/mini.tpl');

        while($day = $oMonth->fetch()) {
            $data['DAY'] = $day->day;

            if ($day->empty) {
                $data['CLASS'] = 'minical-day-empty';
            } elseif ( $day->month == date('m', $this->oCal->today) &&
                       $day->day == date('d', $this->oCal->today)
                       ) {
                $data['CLASS'] = 'minical-day-current';
            } else {
                $data['CLASS'] = 'minical-day-normal';
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

        $template['FULL_MONTH_NAME'] = strftime('%B', $date);
        $template['PARTIAL_MONTH_NAME'] = strftime('%b', $date);

        $template['FULL_YEAR'] = strftime('%Y', $date);
        $template['PARTIAL_YEAR'] = strftime('%y', $date);

        $oTpl->setData($template);
        return $oTpl->get();
    }

}


?>