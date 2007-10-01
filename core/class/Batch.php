<?php

  /**
   * Separates work into batches to prevent memory caps or timeouts
   * 
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

define('DEFAULT_BATCH_SET', 50);
if (!defined('GRAPH_MULTIPLIER')) {
    define('GRAPH_MULTIPLIER', 2);
}

class Batches {
    var $total_items        = 1; // Total items to run
    var $batch_set          = 0; // number of items to compute per batch
    var $total_batches      = 1; // Total number of batches expected $total_items / $batch_set
    var $items_done         = 0; // 
    var $current_batch      = 1;
    var $finished           = FALSE;

    var $last_batch_time    = 0; // Time it took to run the last batch
    var $batches_so_far     = 0; // Batches run so far
    var $average_batch_time = 0; // Average time per batch
    var $percentage_done    = 0; // Percentage of total batch done $total_batches / $batches_so_far

    var $title              = NULL; // Name of the batch process
    var $error              = NULL; // Contains error objects


    function Batches($title)
    {
        $this->title = $title;
    }

    function setTitle($title)
    {
        $this->title;
    }


    function setTotalItems($total)
    {
        if (!is_numeric($total)) {
            return false;
        }
        $this->total_items = (int)$total;
        return true;
    }

    function completeBatch()
    {
        @$last_batch = $_SESSION['Batches'][$this->title]['last_batch'];

        if ($this->current_batch >= $this->total_batches) {
            $this->finished = TRUE;
        }

        if (!$last_batch || $this->current_batch >= $last_batch) {
            $_SESSION['Batches'][$this->title]['last_batch'] = $this->current_batch;
        }

        $this->items_done += $this->batch_set;
        $this->current_batch++;
    }

    function setBatchSet($set_no)
    {
        if (!is_numeric($set_no)) {
            return false;
        }

        $this->batch_set = (int)$set_no;
        $this->total_batches = (int)ceil($this->total_items / $this->batch_set);
        return true;
    }

    function getLimit()
    {
        return $this->batch_set;
    }

    function getStart()
    {
        return $this->batch_set * ($this->current_batch - 1);
    }

    function load()
    {
        if (isset($_REQUEST['batch'])) {
            if ($_REQUEST['batch'] > 1) {
                $this->current_batch = (int)$_REQUEST['batch'];
            }
            $this->items_done = $this->current_batch * $this->batch_set;
        }

        if (@$saved_batch = $_SESSION['Batches'][$this->title]['last_batch']) {
            if ($saved_batch >= $this->current_batch) {
                return FALSE;
            }
        }

        return TRUE;
    }

    function clear()
    {
        unset($_SESSION['Batches']);
    }

    function isFinished()
    {
        return $this->finished;
    }

    function percentDone()
    {
        $done = round( ($this->current_batch / $this->total_batches) * 100, 2);
        if ($done > 100) {
            $done = 100;
        }
        return $done;
    }

    function getAddress()
    {
        $url = PHPWS_Core::getCurrentUrl();
        $url = preg_replace('/&batch=\d+$/Ui', '', $url);
        $new_url = $url . '&amp;batch=' . $this->current_batch;

        return $new_url;
    }

    function continueLink($continue_link=NULL)
    {
        if (empty($continue_link)) {
            $continue_link = _('Continue');
        }

        $url = $this->getAddress();

        return sprintf('<a href="%s">%s</a>', $url, $continue_link);
    }

    function getGraph()
    {
        $show_wait = true;

        $percentage = ceil($this->percentDone());
        if ($percentage < 100) {
            if ($show_wait) {
                $template['please_wait'] = _('Please wait...');
                $template['wait_graphic'] = '<img src="images/core/ajax-loader.gif" />';
            }
        } else {
            $percentage = 100;
        }

        $template['percentage'] = $percentage . '%';
        $template['total_width'] = floor(100 * GRAPH_MULTIPLIER);
        $template['progress_width'] = floor($percentage * GRAPH_MULTIPLIER);
        return PHPWS_Template::process($template, '', 'templates/core/graph.tpl', TRUE);
    }

}

?>