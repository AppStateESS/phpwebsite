<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function cycle_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>1.0.1 changes
-------------
+ Bug fixes
</pre>';
    }
    return TRUE;
}

?>