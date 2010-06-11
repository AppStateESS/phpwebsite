<?php

/**
 * Tracks and displays the statistics of searches
 *
 * @author Matthew McNaney <mcnaney at gmail dot com
 * @version $Id$
 */

class Search_Stats {
    public $keyword        = NULL;
    public $query_success  = 0;
    public $query_failure  = 0;
    public $mixed_query    = 0;
    public $total_query    = 0;
    public $highest_result = 0;
    public $last_called    = 0;
    public $multiple_word  = 0;
    public $exact_success  = 0;


    public static function record($words, $found, $exact_match) {
        if (empty($words)) {
            return FALSE;
        }

        $insert = FALSE;

        $db = new PHPWS_DB('search_stats');

        $word_count = count($words);

        foreach ($words as $keyword) {
            $db->resetWhere();
            if (empty($keyword)) {
                continue;
            }

            $keyword = trim($keyword);

            $db->addWhere('keyword', $keyword);
            $stat = new Search_Stats;
            $result = $db->loadObject($stat);

            if (empty($stat->keyword)) {
                $insert = TRUE;
                $stat->keyword = $keyword;
            } else {
                $insert = FALSE;
            }

            $stat->total_query++;
            if ($found) {
                $stat->query_success++;
                if ($exact_match) {
                    $stat->exact_success++;
                }
            } else {
                $stat->query_failure++;
            }

            if ($word_count > 1) {
                $stat->mixed_query++;
            }


            if ($found > $stat->highest_result) {
                $stat->highest_result = $found;
            }
            $stat->last_called = time();
            $stat->save($insert);
        }
    }

    public function save($insert=TRUE)
    {
        $db = new PHPWS_DB('search_stats');
        $this->keyword = trim($this->keyword);
        if (!$insert) {
            $db->addWhere('keyword', $this->keyword);
        }

        return $db->saveObject($this);
    }

    public function getLastCalled()
    {
        return strftime('%c', $this->last_called);
    }

    public function getTplTags()
    {
        $tpl['LAST_CALLED']  = $this->getLastCalled();
        $tpl['CHECKBOX'] = sprintf('<input type="checkbox" name="keyword[]" value="%s" />', $this->keyword);
        return $tpl;
    }

}


?>