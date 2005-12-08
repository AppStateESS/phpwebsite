<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$Calendar = & new PHPWS_Calendar;
Layout::add($Calendar->view->miniMonth(), 'calendar', 'minimonth');

?>
