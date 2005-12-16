<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$Calendar = & new PHPWS_Calendar;
// This needs to load the default public calendar
// or maybe the personal one?
$Calendar->loadView();
Layout::add($Calendar->view->month_grid('mini'), 'calendar', 'minimonth');

?>
