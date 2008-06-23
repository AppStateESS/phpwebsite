<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Section {
    var $id      = 0;
    var $pid     = 0;
    var $content = null;
    var $secname = null;
    var $sectype = null;

    var $_error  = null;


    function plugSection($section,$pid=0)
    {
        $this->pid = $pid;
        $this->secname = $section['NAME'];
        $this->sectype = $section['TYPE'];
        if (isset($section['WIDTH'])) {
            $this->width   = $section['WIDTH'];
            $this->height  = $section['HEIGHT'];
        }
    }

}

?>