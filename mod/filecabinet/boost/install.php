<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function filecabinet_install(&$content)
{
    $home_dir = PHPWS_Boost::getHomeDir();

    $mm_dir = $home_dir . 'files/multimedia/';
    if (!is_dir($mm_dir)) {
        if (!@mkdir($mm_dir)) {
            $content[] = dgettext('filecabinet', 'Failed to create files/multimedia directory.');
            return false;
        } else {
            $content[] = dgettext('filecabinet', 'files/multimedia directory created successfully.');
        }
    }

    $files_dir = $home_dir . 'files/filecabinet/';
    if (!is_dir($files_dir)) {
        if (!@mkdir($files_dir)) {
            $content[] = dgettext('filecabinet', 'Failed to create files/filecabinet/ directory.');
            return false;
        } else {
            $content[] = dgettext('filecabinet', 'files/filecabinet/ directory created successfully.');
        }
    }

    $classify_dir = $home_dir . 'files/filecabinet/incoming/';
    if (!is_dir($classify_dir)) {
        if (!@mkdir($classify_dir)) {
            $content[] = dgettext('filecabinet', 'Failed to create files/filecabinet/incoming directory.');
            return false;
        } else {
            $content[] = dgettext('filecabinet', 'files/filecabinet/incoming directory created successfully.');
        }
    }

    return true;
}

?>