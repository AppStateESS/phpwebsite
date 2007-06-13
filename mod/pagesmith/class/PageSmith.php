<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PageSmith {
    var $forms   = null;
    var $panel   = null;
    var $title   = null;
    var $message = null;
    var $content = null;


    function admin()
    {

    }


    function user()
    {

    }

    function loadForms()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Forms.php');
        $this->forms = new PS_Forms;
    }

    function viewPage()
    {

    }
}

?>