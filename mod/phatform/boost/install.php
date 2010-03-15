<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function phatform_install(&$content)
{
    if (!@mkdir('files/phatform/')) {
        $content[] = dgettext('phatform', 'Failed to create files directory.');
    }

    if (!@mkdir('files/phatform/archive/')) {
        $content[] = dgettext('phatform', 'Failed to create archive directory.');
    }

    if (!@mkdir('files/phatform/report/')) {
        $content[] = dgettext('phatform', 'Failed to create report directory.');
    }

    if (!@mkdir('files/phatform/export/')) {
        $content[] = dgettext('phatform', 'Failed to create export directory.');
    }

    return true;
}

?>