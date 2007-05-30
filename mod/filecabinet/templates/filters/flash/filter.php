<?php
  /**
   * Contains extra content for Flash video filter.
   * Go to http://flowplayer.sourceforge.net/howto.html
   * for information on customizing your flash template.
   * 
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$tpl['HEIGHT'] = $tpl['HEIGHT'] + 22; //the FlowPlayer requires 22 extra height pixels

/**
 * originally I was using transbutton.jpg for the overlay. It is a transparent png that
 * allows you to see the first frame of the flv underneath. Unfortunately, you have 
 * to click the player twice to get it to play. So I using the default play-button jpeg.
 */

$tpl['START_SCREEN'] = 'templates/filecabinet/filters/flash/play-button-328x240.jpg';
$tpl['ID'] = mt_rand();

Layout::addJSHeader('<script type="text/javascript" src="templates/filecabinet/filters/flash/swfobject.js"></script>', 'swfobject');
?>