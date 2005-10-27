<?php

/**
 * Tracks and displays the statistics of searches
 *
 * @author Matthew McNaney <mcnaney at gmail dot com
 * @version $Id$
 */

class Search_Stats {
    var $keyword        = NULL;
    var $query_success  = 0;
    var $query_failure  = 0;
    var $total_query    = 0;
    var $highest_result = 0;
    var $last_called    = 0;
    var $multiple_word  = 0;


    function record($words, $found) {
        if (empty($words)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('search_stats');

        $word_count = count($words);

        foreach ($words as $keyword) {
            if (empty($keyword)) {
                continue;
            }

            $db->addWhere('keyword', $keyword);
            $stat = & new Search_Stats;
            $result = $db->loadObject($stat);
            if (empty($stat->keyword)) {
                $stat->keyword = $keyword;
            }

            $stat->total_query++;
            if ($found) {
                $stat->query_success += round(( 1 / $word_count), 2);
            } else {
                $stat->query_failure++;
            }

            if ($found > $stat->highest_result) {
                $stat->highest_result = $found;
            }

            test($stat);
        }

    }
}


?>