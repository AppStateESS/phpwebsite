<?php
namespace core;
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
    public $total_items        = 1; // Total items to run
    public $batch_set          = 0; // number of items to compute per batch
    public $total_batches      = 1; // Total number of batches expected $total_items / $batch_set
    public $items_done         = 0; //
    public $current_batch      = 1;
    public $finished           = FALSE;

    public $last_batch_time    = 0; // Time it took to run the last batch
    public $batches_so_far     = 0; // Batches run so far
    public $average_batch_time = 0; // Average time per batch
    public $percentage_done    = 0; // Percentage of total batch done $total_batches / $batches_so_far

    public $title              = NULL; // Name of the batch process
    public $error              = NULL; // Contains error objects


    public function __constructor($title)
    {
        $this->title = $title;
    }

    public function setTitle($title)
    {
        $this->title;
    }


    public function setTotalItems($total)
    {
        if (!is_numeric($total)) {
            return false;
        }
        $this->total_items = (int)$total;
        return true;
    }

    public function completeBatch()
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

    public function setBatchSet($set_no)
    {
        if (!is_numeric($set_no)) {
            return false;
        }

        $this->batch_set = (int)$set_no;
        $this->total_batches = (int)ceil($this->total_items / $this->batch_set);
        return true;
    }

    public function getLimit()
    {
        return $this->batch_set;
    }

    public function getStart()
    {
        return $this->batch_set * ($this->current_batch - 1);
    }

    public function load()
    {
        if (isset($_REQUEST['batch'])) {
            if ($_REQUEST['batch'] > 1) {
                $this->current_batch = (int)$_REQUEST['batch'];
            }
            $this->items_done = $this->current_batch * $this->batch_set;
        }

        if (@$saved_batch = $_SESSION['Batches'][$this->title]['last_batch']) {
            if ($saved_batch >= $this->current_batch) {
                $this->current_batch++;
                return FALSE;
            }
        }

        return TRUE;
    }

    public function clear()
    {
        unset($_SESSION['Batches']);
    }

    public function isFinished()
    {
        return $this->finished;
    }

    public function percentDone()
    {
        $done = round( ($this->current_batch / $this->total_batches) * 100, 2);
        if ($done > 100) {
            $done = 100;
        }
        return $done;
    }

    public function getAddress()
    {
        $url = Core::getCurrentUrl();
        $url = preg_replace('/&batch=\d+$/Ui', '', $url);
        $new_url = $url . '&amp;batch=' . $this->current_batch;

        return $new_url;
    }

    public function nextPage()
    {
        Layout::metaRoute($this->getAddress(), 0);
    }

    public function continueLink($continue_link=NULL)
    {
        if (empty($continue_link)) {
            $continue_link = _('Continue');
        }

        $url = $this->getAddress();

        return sprintf('<a href="%s">%s</a>', $url, $continue_link);
    }

    public function getGraph()
    {
        $show_wait = true;

        $percentage = ceil($this->percentDone());
        if ($percentage < 100) {
            if ($show_wait) {
                $template['please_wait'] = _('Please wait...');
                $template['wait_graphic'] = Icon::show('wait');
            }
        } else {
            $percentage = 100;
        }

        $template['percentage'] = $percentage . '%';
        // 8 is a 4px padding each side
        $template['total_width'] = round(100 * GRAPH_MULTIPLIER) + 8;
        $template['progress_width'] = round($percentage * GRAPH_MULTIPLIER);
        return PHPWS_Template::process($template, 'core', 'templates/core/graph.tpl', TRUE);
    }

}

?>