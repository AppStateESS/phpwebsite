<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function my_page()
{
    Core\Core::initModClass('comments', 'My_Page.php');
    $content = Comments_My_Page::main();
    return $content;
}

?>