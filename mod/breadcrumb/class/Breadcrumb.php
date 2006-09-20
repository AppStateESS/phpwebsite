<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('breadcrumb');

class Breadcrumb {
    var $sticky_home   = BC_STICKY_HOME;
    var $view_limit    = BC_VIEW_LIMIT;
    var $queue_limit   = BC_QUEUE_LIMIT;
    var $bc_list       = null;
    var $current_count = 0;
    var $key_list      = null;
    var $position      = false;
    var $previous      = false;

    function Breadcrumb()
    {
        $this->bc_list  = array();
        $this->key_list = array();
    }

    function display() {
        $this->view_limit = 4;

        if (!isset($_REQUEST['authkey'])) {
            $this->recordView();
        }

        $this->current_count = count($this->bc_list);
        $this->key_list      = array_keys($this->bc_list);
        $last_position = $this->current_count - 1;
        $spacing = floor($this->view_limit/2);

        $left = $this->position - $spacing;
        $right = $this->position + $spacing;

        if ($this->view_limit <= $this->current_count) {
            while ($right > $last_position) {
                $left--;
                $right--;
            }
        }

        if (!(int)($this->view_limit % 2) ) {
            if ($right - 1 > $this->position) {
                $right--;
            } else {
                $left++;
            }
        }
       
        while ($left < 0) {
            $left++;
            $right++;
        }

        if ($left > 0) {
            $content[] = '...';
        }


        for($i = $left; $i <= $right; $i++) {
            if ($bc = @$this->bc_list[$this->key_list[$i]]) {
                if ($i == $this->position) {
                    $content[] = '[' . $bc . ']';
                } else {
                    $content[] = $bc;
                }
            } else {
                break;
            }
        }

        if ($right < $last_position) {
            $content[] = '...';
        }

        $print = implode(' &gt; ', $content);
        Layout::add($print, 'breadcrumb', 'view');
    }

    function recordView()
    {
        $key = Key::getCurrent();
        if (!Key::checkKey($key, TRUE)) {
            return;
        }

        $this->position = array_search($key->id, $this->key_list);

        if ($this->position === false) {
            $this->bc_list[$key->id] = $key->getUrl();
            $this->key_list = array_keys($this->bc_list);
            $this->position = $this->current_count;
        }
    }
}

?>