<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Section {
    public $id      = 0;
    public $pid     = 0;
    public $content = null;
    public $secname = null;
    public $sectype = null;

    public $_error  = null;


    public function plugSection($section,$pid=0)
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