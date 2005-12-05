<?php

  /**
   * Contains the various functions for viewing calendars
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Calendar_View {
    //    var $oCal = NULL;

    function miniMonth($date=0)
    {
        $date = $this->oCal->checkDate($date);
        $month = $this->oCal->getMonth($date);

    }

}


?>