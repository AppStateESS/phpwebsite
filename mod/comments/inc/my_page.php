<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function my_page()
{
    translate('comments');
    PHPWS_Core::initModClass('comments', 'My_Page.php');
    $content = Comments_My_Page::main();
    translate();
    return $content;
}

?>