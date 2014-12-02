<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
function addthis_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = <<<EOF
<pre>1.0.2
--------------
+ Added controlpanel tabs.
</pre>
EOF;
    }
    return TRUE;
}
?>