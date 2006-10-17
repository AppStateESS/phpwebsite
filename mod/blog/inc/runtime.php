<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!isset($_REQUEST['module'])){
     $content = Blog_User::show();
     Layout::add($content, 'blog', 'view', TRUE);
}

?>