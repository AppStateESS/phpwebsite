<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// The default maximum amount of feeds to show regardless
// of age
define('RSS_SERVE_LIMIT', 10);

// the default maximum age in days a feed should be released
define('RSS_AGE_LIMIT', 7);

// the default number of seconds the cache should be refreshed
define('RSS_CACHE_TIMEOUT', 3600);

// the default amount of feeds displayed from a site
define('RSS_FEED_LIMIT', 10);

// the default amount between to wait before querying a feed
define('RSS_FEED_REFRESH', 3600);


// absolute max amount of feeds an admin may set for a feed
define('RSS_MAX_FEED', 50);

define('RSS_SHORT_DESC_SIZE', 70);

?>