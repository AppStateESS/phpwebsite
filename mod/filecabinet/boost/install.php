<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function filecabinet_install(&$content)
{
    $mm_dir = PHPWS_HOME_DIR . 'files/multimedia';
    if (!is_dir($mm_dir)) {
        if (!@mkdir($mm_dir)) {
            $content[] = dgettext('filecabinet', 'Failed to create files/multimedia directory.');
            return false;
        } else {
            $content[] = dgettext('filecabinet', 'files/multimedia directory created successfully.');
        }
    }
}

?>