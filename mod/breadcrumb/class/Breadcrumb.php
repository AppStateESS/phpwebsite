<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: Breadcrumb.php 7776 2010-06-11 13:52:58Z jtickle $
 */

PHPWS_Core::requireConfig('breadcrumb');

class Breadcrumb {
    public $sticky_home   = BC_STICKY_HOME;
    public $view_limit    = BC_VIEW_LIMIT;
    public $queue_limit   = BC_QUEUE_LIMIT;
    public $bc_list       = null;
    public $current_count = 0;
    public $key_list      = null;
    public $position      = false;
    public $previous      = false;

    public function Breadcrumb()
    {
        $this->bc_list  = array();
        $this->key_list = array();
    }

    public function display() {
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
            $tpl['bc-list'][] = array('BC' => '...', 'CLASS' => 'bc');
        }

        if ($right >= $this->current_count) {
            $right = $this->current_count - 1;
        }

        $multiple = 0;
        for($i = $left; $i <= $right; $i++) {
            if ($bc = @$this->bc_list[$this->key_list[$i]]) {
                if ($i == $this->position) {
                    $class = 'current-bc';
                } else {
                    $class = 'bc';
                }

                if ($this->current_count > 1 && $i != $right) {
                    $separator = '&nbsp;';
                } else {
                    $separator = null;
                }

                $tpl['bc-list'][] = array('BC' => $bc, 'CLASS' => $class, 'SEPARATOR' => $separator);
            } else {
                break;
            }
            $multiple = 1;
        }

        if ($right < $last_position) {
            $tpl['bc-list'][] = array('BC' => '...', 'CLASS' => 'bc', 'SEPARATOR' => null);
        }

        if (!empty($tpl)) {
            $content = PHPWS_Template::process($tpl, 'breadcrumb', 'bc.tpl');
            Layout::add($content, 'breadcrumb', 'view');
        }
    }

    public function recordView()
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