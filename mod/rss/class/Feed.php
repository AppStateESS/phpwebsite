<?php

PHPWS_Core::requireConfig('rss');

class RSS_Feed {
    var $module        = NULL;
    var $serve_limit   = RSS_SERVE_LIMIT;
    var $age_limit     = RSS_AGE_LIMIT;
    var $time_accessed = 0;
    var $last_cached   = 0;
    var $cache_timeout = RSS_CACHE_TIMEOUT;

    

}

?>