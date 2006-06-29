<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('breadcrumb');

class Breadcrumb {
    var $sticky_home = BC_STICKY_HOME;
    var $view_limit  = BC_VIEW_LIMIT;
    var $bc_list     = array();

    function display() {
        if (!isset($_REQUEST['authkey'])) {
            $this->recordView();
        }

        if (count($this->bc_list) > $this->view_limit) {
            $show_list = array_slice($this->bc_list, $this->view_limit, true);
        } else {
            $show_list = & $this->bc_list;
        }
        $content = implode(BC_DIVIDER, $show_list);

        Layout::add($content, 'breadcrumb', 'view');
    }

    function recordView()
    {
        $key = Key::getCurrent();
        
        if (Key::checkKey($key, TRUE)) {
            if (isset($this->bc_list[$key->id])) {
                //remove records from that point on
                $key_list = array_keys($this->bc_list);
                $array_key_count = array_search($key->id, $key_list);
                $this->bc_list = array_slice($this->bc_list, 0, $array_key_count + 1, true);
            } else {
                $this->bc_list[$key->id] = $key->getUrl();
            }
        }
    }


}

?>